import { createRemoteJWKSet, jwtVerify } from 'jose';

const json = (body, status = 200) => Response.json(body, { status, headers: { 'cache-control': 'no-store', 'x-content-type-options': 'nosniff' } });
const cents = value => Math.round(Number(value) * 100);
const dollars = value => Number(value || 0) / 100;
const required = (body, fields) => fields.every(field => typeof body?.[field] === 'string' && body[field].trim());
const verifiers = new Map();
const encoder = new TextEncoder(), decoder = new TextDecoder();
const toBase64 = bytes => btoa(String.fromCharCode(...bytes));
const fromBase64 = value => Uint8Array.from(atob(value), character => character.charCodeAt(0));

async function credentialKey(env) {
  const raw = String(env.CREDENTIAL_ENCRYPTION_KEY || '');
  let bytes;
  try { bytes = fromBase64(raw); } catch { throw json({ error: 'Credential encryption is not configured.' }, 503); }
  if (bytes.length !== 32) throw json({ error: 'Credential encryption is not configured.' }, 503);
  return crypto.subtle.importKey('raw', bytes, 'AES-GCM', false, ['encrypt', 'decrypt']);
}
async function encryptSecret(value, env) {
  if (!value) return '';
  const iv = crypto.getRandomValues(new Uint8Array(12)), key = await credentialKey(env);
  const encrypted = new Uint8Array(await crypto.subtle.encrypt({ name: 'AES-GCM', iv }, key, encoder.encode(String(value))));
  const packed = new Uint8Array(iv.length + encrypted.length); packed.set(iv); packed.set(encrypted, iv.length);
  return toBase64(packed);
}
async function decryptSecret(value, env) {
  if (!value) return '';
  const packed = fromBase64(value), key = await credentialKey(env);
  if (packed.length < 29) throw json({ error: 'Stored credential is invalid.' }, 500);
  return decoder.decode(await crypto.subtle.decrypt({ name: 'AES-GCM', iv: packed.slice(0, 12) }, key, packed.slice(12)));
}
function base32Bytes(value) {
  const alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', clean=String(value||'').toUpperCase().replace(/[^A-Z2-7]/g,''), output=[]; let bits=0, buffer=0;
  for (const character of clean) { const index=alphabet.indexOf(character); if(index<0) continue; buffer=(buffer<<5)|index; bits+=5; if(bits>=8){bits-=8;output.push((buffer>>bits)&255);} }
  return new Uint8Array(output);
}
async function totpCode(secret) {
  const keyBytes=base32Bytes(secret); if(!keyBytes.length) throw json({error:'This account does not have a valid MFA secret.'},400);
  const period=30,counter=Math.floor(Date.now()/1000/period),message=new Uint8Array(8); let value=counter;
  for(let index=7;index>=0;index--){message[index]=value&255;value=Math.floor(value/256);}
  const key=await crypto.subtle.importKey('raw',keyBytes,{name:'HMAC',hash:'SHA-1'},false,['sign']),digest=new Uint8Array(await crypto.subtle.sign('HMAC',key,message)),offset=digest[digest.length-1]&15;
  const number=((digest[offset]&127)<<24)|(digest[offset+1]<<16)|(digest[offset+2]<<8)|digest[offset+3];
  return {code:String(number%1000000).padStart(6,'0'),expiresIn:period-(Math.floor(Date.now()/1000)%period)};
}

function accessErrorCode(error) {
  const code = String(error?.code || '');
  if (code === 'ERR_JWT_CLAIM_VALIDATION_FAILED') return 'ACCESS_CLAIM_MISMATCH';
  if (code === 'ERR_JWT_EXPIRED') return 'ACCESS_TOKEN_EXPIRED';
  if (code === 'ERR_JWS_SIGNATURE_VERIFICATION_FAILED') return 'ACCESS_SIGNATURE_INVALID';
  if (code === 'ERR_JWKS_NO_MATCHING_KEY') return 'ACCESS_SIGNING_KEY_UNKNOWN';
  if (code.startsWith('ERR_JWT') || code.startsWith('ERR_JWS')) return 'ACCESS_TOKEN_INVALID';
  if (error?.message === 'missing identity') return 'ACCESS_IDENTITY_MISSING';
  return 'ACCESS_CERTS_UNAVAILABLE';
}

async function identityFor(request, env) {
  const token = request.headers.get('Cf-Access-Jwt-Assertion');
  if (!token) throw json({ error: 'A valid Cloudflare Access session is required. [ACCESS_TOKEN_MISSING]', code: 'ACCESS_TOKEN_MISSING' }, 401);
  const issuer = String(env.CF_ACCESS_TEAM_DOMAIN || '').replace(/\/$/, ''), audience = env.CF_ACCESS_AUD;
  if (!issuer || !audience) throw json({ error: 'Cloudflare Access is not configured for this service. [ACCESS_CONFIG_MISSING]', code: 'ACCESS_CONFIG_MISSING' }, 503);
  let jwks = verifiers.get(issuer);
  if (!jwks) { jwks = createRemoteJWKSet(new URL(`${issuer}/cdn-cgi/access/certs`)); verifiers.set(issuer, jwks); }
  try {
    const { payload } = await jwtVerify(token, jwks, { issuer, audience, algorithms: ['RS256'], clockTolerance: 5 });
    const email = String(payload.email || '').trim().toLowerCase();
    if (!email || !payload.sub) throw new Error('missing identity');
    return { id: String(payload.sub), email, name: email.split('@')[0].replace(/[._-]/g, ' ') };
  } catch (error) {
    const code = accessErrorCode(error);
    console.error('Cloudflare Access verification failed', { code, joseCode: error?.code, name: error?.name });
    throw json({ error: `Access session could not be verified. [${code}]`, code }, 401);
  }
}

async function workspaceForIdentity(db, identity) {
  const workspaceId = `ws_${identity.id}`;
  await db.batch([
    db.prepare('INSERT OR IGNORE INTO workspaces (id, name) VALUES (?, ?)').bind(workspaceId, `${identity.name}'s workspace`),
    db.prepare("INSERT OR IGNORE INTO workspace_members (workspace_id, email, role) VALUES (?, ?, 'owner')").bind(workspaceId, identity.email),
  ]);
  if (!await db.prepare('SELECT role FROM workspace_members WHERE workspace_id = ? AND email = ?').bind(workspaceId, identity.email).first()) throw json({ error: 'You are not a member of this workspace.' }, 403);
  return workspaceId;
}

const all = async statement => (await statement.all()).results || [];
const optionalAll = async statement => {
  try { return await all(statement); }
  catch (error) {
    if (/no such (table|column)/i.test(String(error?.message || error))) return [];
    throw error;
  }
};
async function readWorkspace(db, workspaceId, identity) {
  const [clients, clientAddresses, invoices, invoiceItems, estimates, estimateItems, payments, items, subscriptions, expenses, tasks, tickets, ticketNotes, ticketTime, assets, accounts, contacts, fields, values] = await Promise.all([
    all(db.prepare('SELECT id, name, city, state, zip, hourly_rate_cents, status FROM clients WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, address FROM clients WHERE workspace_id = ?').bind(workspaceId)),
    all(db.prepare('SELECT id, client_id, estimate_id, issued, due, description, amount_cents, status, created_at FROM invoices WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, invoice_id, description, quantity, rate_cents, position, source_type, source_id FROM invoice_items WHERE workspace_id = ? ORDER BY invoice_id, position').bind(workspaceId)),
    all(db.prepare('SELECT id, client_id, quote, valid_until, amount_cents, status, converted_invoice_id, created_at FROM estimates WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, estimate_id, description, quantity, rate_cents, position, source_type, source_id FROM estimate_items WHERE workspace_id = ? ORDER BY estimate_id, position').bind(workspaceId)),
    all(db.prepare('SELECT id, invoice_id, payment_date, method, amount_cents, created_at FROM payments WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, name, company, category, description, stock_quantity, price_cents, tax_1, tax_2, status, created_at FROM items WHERE workspace_id = ? ORDER BY name').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, summary, next_date, stop_date, interval_count, interval_unit, amount_cents, managed_it, hourly_allotment_minutes, status, created_at FROM subscriptions WHERE workspace_id = ? ORDER BY next_date, created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, ticket_id, vendor, expense_date, company, category, description, amount_cents, tax_cents, status, created_at FROM expenses WHERE workspace_id = ? ORDER BY expense_date DESC, created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, invoice_id, ticket_id, title, description, due_date, assignee_email, completed_at, status, created_at FROM tasks WHERE workspace_id = ? ORDER BY due_date, created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, contact_id, contact_name, contact_email, title, board, status, billing_type, subscription_id, hourly_rate_cents, closed_at, created_at, updated_at FROM tickets WHERE workspace_id = ? ORDER BY updated_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, ticket_id, author_email, visibility, body, created_at FROM ticket_notes WHERE workspace_id = ? ORDER BY created_at').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, ticket_id, technician_email, minutes, description, created_at FROM ticket_time_entries WHERE workspace_id = ? ORDER BY created_at').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, asset_type, name, serial_number, hostname, operating_system, manufacturer, model, ip_address, notes, created_at FROM client_assets WHERE workspace_id = ? ORDER BY name').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, name, notes, created_at, CASE WHEN username_encrypted != \'\' THEN 1 ELSE 0 END AS has_username, CASE WHEN password_encrypted != \'\' THEN 1 ELSE 0 END AS has_password, CASE WHEN website_encrypted != \'\' THEN 1 ELSE 0 END AS has_website, CASE WHEN totp_secret_encrypted != \'\' THEN 1 ELSE 0 END AS has_totp FROM client_accounts WHERE workspace_id = ? ORDER BY name').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, name, email, phone, title, authorized_user, created_at FROM client_contacts WHERE workspace_id = ? ORDER BY name').bind(workspaceId)),
    all(db.prepare('SELECT id, label, entity_type, position FROM custom_fields WHERE workspace_id = ? ORDER BY entity_type, position, created_at').bind(workspaceId)),
    all(db.prepare('SELECT v.custom_field_id, v.record_id, v.value FROM custom_field_values v JOIN custom_fields f ON f.id = v.custom_field_id WHERE f.workspace_id = ?').bind(workspaceId)),
  ]);
  const customFor = id => Object.fromEntries(values.filter(value => value.record_id === id).map(value => [value.custom_field_id, value.value]));
  const paidFor = id => payments.filter(payment => payment.invoice_id === id).reduce((sum, payment) => sum + payment.amount_cents, 0);
  return {
    clients: clients.map(row => ({ id: row.id, name: row.name, address:clientAddresses.find(item=>item.id===row.id)?.address||'', city: row.city, state: row.state, zip: row.zip, hourlyRate: dollars(row.hourly_rate_cents), status: row.status, customFields: customFor(row.id) })),
    invoices: invoices.map(row => { const paid = paidFor(row.id), balance = Math.max(0, row.amount_cents - paid); const items = invoiceItems.filter(item => item.invoice_id === row.id).map(item => ({ id: item.id, description: item.description, quantity: Number(item.quantity), rate: dollars(item.rate_cents), sourceType:item.source_type, sourceId:item.source_id, catalogId:item.source_type==='item'?item.source_id:'' })); return { id: row.id, clientId: row.client_id, estimateId: row.estimate_id, issued: row.issued, due: row.due, description: row.description, items: items.length ? items : [{ id: `${row.id}_legacy`, description: row.description, quantity: 1, rate: dollars(row.amount_cents) }], amount: dollars(row.amount_cents), paid: dollars(paid), balance: dollars(balance), status: balance === 0 && row.amount_cents > 0 ? 'paid' : row.status, createdAt: row.created_at, customFields: customFor(row.id) }; }),
    estimates: estimates.map(row => { const lines=estimateItems.filter(item=>item.estimate_id===row.id).map(item=>({id:item.id,description:item.description,quantity:Number(item.quantity),rate:dollars(item.rate_cents),sourceType:item.source_type,sourceId:item.source_id,catalogId:item.source_type==='item'?item.source_id:''}));return { id: row.id, clientId: row.client_id, quote: row.quote, description:row.quote,items:lines.length?lines:[{id:`${row.id}_legacy`,description:row.quote,quantity:1,rate:dollars(row.amount_cents)}], validUntil: row.valid_until, amount: dollars(row.amount_cents), status: row.status, invoiceId: row.converted_invoice_id, createdAt: row.created_at, customFields: customFor(row.id) };}),
    payments: payments.map(row => ({ id: row.id, invoiceId: row.invoice_id, date: row.payment_date, method: row.method, amount: dollars(row.amount_cents), createdAt: row.created_at })),
    items: items.map(row => ({ id: row.id, name: row.name, company: row.company, category: row.category, description: row.description, stock: Number(row.stock_quantity), price: dollars(row.price_cents), tax1: Number(row.tax_1), tax2: Number(row.tax_2), status: row.status, createdAt: row.created_at })),
    subscriptions: subscriptions.map(row => ({ id: row.id, clientId: row.client_id, summary: row.summary, nextDate: row.next_date, stopDate: row.stop_date, intervalCount: Number(row.interval_count), intervalUnit: row.interval_unit, amount: dollars(row.amount_cents), managedIt:!!row.managed_it, hourlyAllotment:Number(row.hourly_allotment_minutes||0)/60, status: row.status, createdAt: row.created_at })),
    expenses: expenses.map(row => ({ id: row.id, clientId: row.client_id, ticketId: row.ticket_id, vendor: row.vendor, date: row.expense_date, company: row.company, category: row.category, description: row.description, amount: dollars(row.amount_cents), tax: dollars(row.tax_cents), status: row.status, createdAt: row.created_at })),
    tasks: tasks.map(row => ({ id: row.id, clientId: row.client_id, invoiceId: row.invoice_id, ticketId: row.ticket_id, title: row.title, description: row.description, dueDate: row.due_date, assigneeEmail: row.assignee_email, completedAt: row.completed_at, status: row.status, createdAt: row.created_at })),
    tickets: tickets.map(row => ({ id: row.id, clientId: row.client_id, contactId: row.contact_id, contactName: row.contact_name, contactEmail: row.contact_email, title: row.title, board:row.board||'technical_support', status: row.status, billingType: row.billing_type, subscriptionId: row.subscription_id, hourlyRate: dollars(row.hourly_rate_cents), closedAt: row.closed_at, createdAt: row.created_at, updatedAt: row.updated_at, notes: ticketNotes.filter(note=>note.ticket_id===row.id).map(note=>({ id:note.id,authorEmail:note.author_email,visibility:note.visibility,body:note.body,createdAt:note.created_at })), timeEntries: ticketTime.filter(entry=>entry.ticket_id===row.id).map(entry=>({ id:entry.id,technicianEmail:entry.technician_email,minutes:Number(entry.minutes),description:entry.description,createdAt:entry.created_at })) })),
    assets: assets.map(row=>({id:row.id,clientId:row.client_id,type:row.asset_type,name:row.name,serialNumber:row.serial_number,hostname:row.hostname,operatingSystem:row.operating_system,manufacturer:row.manufacturer,model:row.model,ipAddress:row.ip_address,notes:row.notes,createdAt:row.created_at})),
    accounts: accounts.map(row=>({id:row.id,clientId:row.client_id,name:row.name,notes:row.notes,hasUsername:!!row.has_username,hasPassword:!!row.has_password,hasWebsite:!!row.has_website,hasTotp:!!row.has_totp,createdAt:row.created_at})),
    contacts: contacts.map(row=>({id:row.id,clientId:row.client_id,name:row.name,email:row.email,phone:row.phone,title:row.title,authorizedUser:!!row.authorized_user,createdAt:row.created_at})),
    settings: { customFields: fields.map(row => ({ id: row.id, label: row.label, appliesTo: row.entity_type, position: row.position })) },
    user: identity,
  };
}

async function nextId(db, workspaceId, table, prefix, start) {
  const row = await db.prepare(`SELECT MAX(CAST(SUBSTR(id, ?) AS INTEGER)) AS value FROM ${table} WHERE workspace_id = ? AND id LIKE ?`).bind(prefix.length + 2, workspaceId, `${prefix}-%`).first();
  return `${prefix}-${row?.value ? Number(row.value) + 1 : start}`;
}

async function customValueStatements(db, workspaceId, entityType, recordId, supplied = {}) {
  const fields = await all(db.prepare('SELECT id FROM custom_fields WHERE workspace_id = ? AND entity_type = ?').bind(workspaceId, entityType));
  return fields.filter(field => String(supplied[field.id] || '').trim()).map(field => db.prepare('INSERT INTO custom_field_values (custom_field_id, record_id, value) VALUES (?, ?, ?)').bind(field.id, recordId, String(supplied[field.id]).trim()));
}

async function api(request, env) {
  const identity = await identityFor(request, env), workspaceId = await workspaceForIdentity(env.DB, identity);
  const path = new URL(request.url).pathname, method = request.method;
  if (!['GET', 'HEAD', 'OPTIONS'].includes(method) && request.headers.get('Sec-Fetch-Site') === 'cross-site') return json({ error: 'Cross-site writes are not allowed.' }, 403);
  if (method === 'GET' && path === '/api/workspace') return json(await readWorkspace(env.DB, workspaceId, identity));
  if (method === 'GET' && path === '/api/session') return json(identity);
  if (method === 'GET' && path === '/api/logout') return json({ logoutUrl: '/cdn-cgi/access/logout' });
  const body = ['POST', 'PUT', 'PATCH'].includes(method) ? await request.json() : {};
  if (method === 'POST' && path === '/api/clients') {
    if (!required(body, ['name', 'city', 'state', 'zip']) || !Number.isFinite(cents(body.hourlyRate || 0))) return json({ error: 'Name, city, state, and ZIP are required.' }, 400);
    const id = `cl_${crypto.randomUUID()}`, values = await customValueStatements(env.DB, workspaceId, 'client', id, body.customFields);
    await env.DB.batch([env.DB.prepare('INSERT INTO clients (id, workspace_id, name, address, city, state, zip, hourly_rate_cents) VALUES (?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, body.name.trim(), String(body.address||'').trim(), body.city.trim(), body.state.trim().toUpperCase(), body.zip.trim(), cents(body.hourlyRate || 0)), ...values]);
    return json({ id }, 201);
  }
  const clientUpdate=path.match(/^\/api\/clients\/([^/]+)$/);
  if(method==='PATCH'&&clientUpdate){const id=decodeURIComponent(clientUpdate[1]),name=String(body.name||'').trim(),address=String(body.address||'').trim(),city=String(body.city||'').trim(),state=String(body.state||'').trim().toUpperCase(),zip=String(body.zip||'').trim(),hourlyRate=cents(body.hourlyRate||0),status=String(body.status||'active');if(!name||address.length>240||!city||!state||!zip||state.length>2||!Number.isFinite(hourlyRate)||hourlyRate<0||!['active','inactive'].includes(status))return json({error:'Enter a valid name, address, city, state, ZIP, hourly rate, and status.'},400);const result=await env.DB.prepare('UPDATE clients SET name = ?, address = ?, city = ?, state = ?, zip = ?, hourly_rate_cents = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(name,address,city,state,zip,hourlyRate,status,id,workspaceId).run();if(!result.meta.changes)return json({error:'Client not found.'},404);return json({id});}
  const clientAsset = path.match(/^\/api\/clients\/([^/]+)\/assets$/);
  if(method==='POST'&&clientAsset){const clientId=decodeURIComponent(clientAsset[1]),type=String(body.type||'other'),name=String(body.name||'').trim();if(!name||name.length>120||!['computer','server','network','printer','mobile','other'].includes(type))return json({error:'Enter an asset name and valid type.'},400);if(!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId,workspaceId).first())return json({error:'Client not found.'},404);const id=`asset_${crypto.randomUUID()}`;await env.DB.prepare('INSERT INTO client_assets (id, workspace_id, client_id, asset_type, name, serial_number, hostname, operating_system, manufacturer, model, ip_address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id,workspaceId,clientId,type,name,String(body.serialNumber||'').trim(),String(body.hostname||'').trim(),String(body.operatingSystem||'').trim(),String(body.manufacturer||'').trim(),String(body.model||'').trim(),String(body.ipAddress||'').trim(),String(body.notes||'').trim()).run();return json({id},201);}
  const clientContact=path.match(/^\/api\/clients\/([^/]+)\/contacts$/);
  if(method==='POST'&&clientContact){const clientId=decodeURIComponent(clientContact[1]),name=String(body.name||'').trim(),email=String(body.email||'').trim(),phone=String(body.phone||'').trim(),title=String(body.title||'').trim(),authorized=body.authorizedUser==='on'||body.authorizedUser===true?1:0;if(!name||name.length>120||email.length>254||phone.length>40||title.length>120)return json({error:'Enter a valid contact name, email, phone, and title.'},400);if(email&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))return json({error:'Enter a valid contact email.'},400);if(!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId,workspaceId).first())return json({error:'Client not found.'},404);const id=`contact_${crypto.randomUUID()}`;await env.DB.prepare('INSERT INTO client_contacts (id,workspace_id,client_id,name,email,phone,title,authorized_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?)').bind(id,workspaceId,clientId,name,email,phone,title,authorized).run();return json({id},201);}
  const clientAccount = path.match(/^\/api\/clients\/([^/]+)\/accounts$/);
  if(method==='POST'&&clientAccount){const clientId=decodeURIComponent(clientAccount[1]),name=String(body.name||'').trim();if(!name||name.length>120)return json({error:'Enter an account name.'},400);if(!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId,workspaceId).first())return json({error:'Client not found.'},404);let totp=String(body.totpSecret||'').trim();if(totp.startsWith('otpauth://')){try{totp=new URL(totp).searchParams.get('secret')||''}catch{return json({error:'The MFA setup URI is invalid.'},400)}}const encrypted=await Promise.all([encryptSecret(String(body.username||'').trim(),env),encryptSecret(String(body.password||''),env),encryptSecret(String(body.website||'').trim(),env),encryptSecret(totp,env)]),id=`account_${crypto.randomUUID()}`;await env.DB.prepare('INSERT INTO client_accounts (id, workspace_id, client_id, name, username_encrypted, password_encrypted, website_encrypted, totp_secret_encrypted, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id,workspaceId,clientId,name,...encrypted,String(body.notes||'').trim()).run();return json({id},201);}
  const accountReveal=path.match(/^\/api\/client-accounts\/([^/]+)\/reveal$/);
  if(method==='GET'&&accountReveal){const id=decodeURIComponent(accountReveal[1]),row=await env.DB.prepare('SELECT username_encrypted,password_encrypted,website_encrypted FROM client_accounts WHERE id = ? AND workspace_id = ?').bind(id,workspaceId).first();if(!row)return json({error:'Account not found.'},404);const [username,password,website]=await Promise.all([decryptSecret(row.username_encrypted,env),decryptSecret(row.password_encrypted,env),decryptSecret(row.website_encrypted,env)]);await env.DB.prepare("INSERT INTO credential_audit (id,workspace_id,account_id,actor_email,action) VALUES (?, ?, ?, ?, 'reveal')").bind(`audit_${crypto.randomUUID()}`,workspaceId,id,identity.email).run();return json({username,password,website});}
  const accountTotp=path.match(/^\/api\/client-accounts\/([^/]+)\/totp$/);
  if(method==='GET'&&accountTotp){const id=decodeURIComponent(accountTotp[1]),row=await env.DB.prepare('SELECT totp_secret_encrypted FROM client_accounts WHERE id = ? AND workspace_id = ?').bind(id,workspaceId).first();if(!row)return json({error:'Account not found.'},404);const result=await totpCode(await decryptSecret(row.totp_secret_encrypted,env));await env.DB.prepare("INSERT INTO credential_audit (id,workspace_id,account_id,actor_email,action) VALUES (?, ?, ?, ?, 'totp')").bind(`audit_${crypto.randomUUID()}`,workspaceId,id,identity.email).run();return json(result);}
  if (method === 'POST' && path === '/api/items') {
    const name = String(body.name || '').trim(), company = String(body.company || '').trim(), category = String(body.category || '').trim(), description = String(body.description || '').trim();
    const stock = Number(body.stock || 0), price = cents(body.price || 0), tax1 = Number(body.tax1 || 0), tax2 = Number(body.tax2 || 0), status = String(body.status || 'active');
    if (!name || name.length > 120 || !Number.isFinite(stock) || stock < 0 || !Number.isFinite(price) || price < 0 || !Number.isFinite(tax1) || tax1 < 0 || !Number.isFinite(tax2) || tax2 < 0 || !['active', 'inactive'].includes(status)) return json({ error: 'Enter a valid item name, stock, price, taxes, and status.' }, 400);
    const id = `itm_${crypto.randomUUID()}`;
    await env.DB.prepare('INSERT INTO items (id, workspace_id, name, company, category, description, stock_quantity, price_cents, tax_1, tax_2, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, name, company, category, description, stock, price, tax1, tax2, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/subscriptions') {
    const clientId = String(body.clientId || ''), summary = String(body.summary || '').trim(), nextDate = String(body.nextDate || ''), stopDate = String(body.stopDate || '') || null;
    const intervalCount = Number(body.intervalCount || 1), intervalUnit = String(body.intervalUnit || 'months'), amount = cents(body.amount || 0),managedIt=body.managedIt==='on'||body.managedIt===true?1:0,hourlyAllotmentMinutes=Math.round(Number(body.hourlyAllotment||0)*60), status = String(body.status || 'active');
    if (!clientId || !summary || !nextDate || !(intervalCount > 0) || !Number.isInteger(intervalCount) || !['days','weeks','months','years'].includes(intervalUnit) || !(amount >= 0) || !Number.isInteger(hourlyAllotmentMinutes)||hourlyAllotmentMinutes<0||!['active','paused','ended'].includes(status)) return json({ error: 'Complete the subscription details with a valid recurrence, allotment, and amount.' }, 400);
    if (!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    const id = `sub_${crypto.randomUUID()}`;
    await env.DB.prepare('INSERT INTO subscriptions (id, workspace_id, client_id, summary, next_date, stop_date, interval_count, interval_unit, amount_cents, managed_it, hourly_allotment_minutes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, clientId, summary, nextDate, stopDate, intervalCount, intervalUnit, amount, managedIt, hourlyAllotmentMinutes, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/expenses') {
    const clientId = String(body.clientId || '') || null, ticketId = String(body.ticketId || '') || null, vendor = String(body.vendor || '').trim(), date = String(body.date || ''), company = String(body.company || '').trim(), category = String(body.category || '').trim(), description = String(body.description || '').trim();
    const amount = cents(body.amount), tax = cents(body.tax || 0), status = String(body.status || 'unbilled');
    if (!vendor || !date || !description || !(amount > 0) || !(tax >= 0) || !['unbilled','billed','reimbursed'].includes(status)) return json({ error: 'Complete the expense details with a valid amount and status.' }, 400);
    if (clientId && !await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    if (ticketId) { const ticket = await env.DB.prepare('SELECT client_id FROM tickets WHERE id = ? AND workspace_id = ?').bind(ticketId, workspaceId).first(); if (!ticket) return json({ error: 'Ticket not found.' }, 404); if (!clientId || ticket.client_id !== clientId) return json({ error: 'Expense client must match the selected ticket company.' }, 400); }
    const id = await nextId(env.DB, workspaceId, 'expenses', 'EXP', 1);
    await env.DB.prepare('INSERT INTO expenses (id, workspace_id, client_id, ticket_id, vendor, expense_date, company, category, description, amount_cents, tax_cents, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, clientId, ticketId, vendor, date, company, category, description, amount, tax, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/tasks') {
    const parentType=String(body.parentType||''),parentId=String(body.parentId||''),invoiceId=parentType==='invoice'?parentId:null,ticketId=parentType==='ticket'?parentId:null,title = String(body.title || '').trim(), description = String(body.description || '').trim(), dueDate = String(body.dueDate || ''), status = String(body.status || 'open');
    if (!title || !dueDate || !parentId || !['invoice','ticket'].includes(parentType) || !['open','in_progress','completed','cancelled'].includes(status)) return json({ error: 'Choose an invoice or ticket and complete the task title, due date, and status.' }, 400);
    const parent=parentType==='invoice'?await env.DB.prepare('SELECT client_id FROM invoices WHERE id = ? AND workspace_id = ?').bind(parentId,workspaceId).first():await env.DB.prepare('SELECT client_id FROM tickets WHERE id = ? AND workspace_id = ?').bind(parentId,workspaceId).first();
    if(!parent)return json({error:`${parentType==='invoice'?'Invoice':'Ticket'} not found.`},404);const clientId=parent.client_id;
    const id = await nextId(env.DB, workspaceId, 'tasks', 'TSK', 1), completedAt = status === 'completed' ? new Date().toISOString() : null;
    await env.DB.prepare('INSERT INTO tasks (id, workspace_id, client_id, invoice_id, ticket_id, title, description, due_date, assignee_email, completed_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, clientId, invoiceId, ticketId, title, description, dueDate, identity.email, completedAt, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/tickets') {
    const clientId=String(body.clientId||''),contactId=String(body.contactId||''),title=String(body.title||'').trim(),board=String(body.board||'technical_support'),status=String(body.status||'open');let billingType=String(body.billingType||'hourly'),subscriptionId=String(body.subscriptionId||'')||null,hourlyRate=cents(body.hourlyRate||0);
    if(!clientId||!contactId||!title||!['technical_support','projects','maintenance'].includes(board)||!['open','in_progress','waiting_customer','waiting_vendor','closed'].includes(status)||!['hourly','subscription'].includes(billingType)||hourlyRate<0)return json({error:'Company, saved contact, title, board, billing method, and valid status are required.'},400);
    const contact=await env.DB.prepare('SELECT name,email FROM client_contacts WHERE id = ? AND client_id = ? AND workspace_id = ?').bind(contactId,clientId,workspaceId).first();if(!contact)return json({error:'Choose a saved contact belonging to this company.'},400);const contactName=contact.name,contactEmail=contact.email;
    const client=await env.DB.prepare('SELECT id,hourly_rate_cents FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId,workspaceId).first();if(!client)return json({error:'Company not found.'},404);if(!hourlyRate)hourlyRate=Number(client.hourly_rate_cents||0);if(board==='technical_support'){const managed=await env.DB.prepare("SELECT id FROM subscriptions WHERE client_id = ? AND workspace_id = ? AND managed_it = 1 AND status = 'active' ORDER BY created_at DESC LIMIT 1").bind(clientId,workspaceId).first();if(managed){billingType='subscription';subscriptionId=managed.id;}}
    if(billingType==='subscription'&&(!subscriptionId||!await env.DB.prepare('SELECT id FROM subscriptions WHERE id = ? AND client_id = ? AND workspace_id = ?').bind(subscriptionId,clientId,workspaceId).first()))return json({error:'Choose a subscription belonging to this company.'},400);
    const id=await nextId(env.DB,workspaceId,'tickets','TKT',1001),closedAt=status==='closed'?new Date().toISOString():null;
    await env.DB.prepare('INSERT INTO tickets (id, workspace_id, client_id, contact_id, contact_name, contact_email, title, board, status, billing_type, subscription_id, hourly_rate_cents, closed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id,workspaceId,clientId,contactId,contactName,contactEmail,title,board,status,billingType,subscriptionId,hourlyRate,closedAt).run();return json({id},201);
  }
  const ticketDelete=path.match(/^\/api\/tickets\/([^/]+)$/);
  if(method==='DELETE'&&ticketDelete){const id=decodeURIComponent(ticketDelete[1]);if(!await env.DB.prepare('SELECT id FROM tickets WHERE id = ? AND workspace_id = ?').bind(id,workspaceId).first())return json({error:'Ticket not found.'},404);await env.DB.batch([env.DB.prepare('DELETE FROM ticket_notes WHERE ticket_id = ? AND workspace_id = ?').bind(id,workspaceId),env.DB.prepare('DELETE FROM ticket_time_entries WHERE ticket_id = ? AND workspace_id = ?').bind(id,workspaceId),env.DB.prepare('UPDATE expenses SET ticket_id = NULL WHERE ticket_id = ? AND workspace_id = ?').bind(id,workspaceId),env.DB.prepare('DELETE FROM tickets WHERE id = ? AND workspace_id = ?').bind(id,workspaceId)]);return json({id,deleted:true});}
  const ticketStatus=path.match(/^\/api\/tickets\/([^/]+)\/status$/);
  if(method==='PATCH'&&ticketStatus){const id=decodeURIComponent(ticketStatus[1]),status=String(body.status||'');if(!['open','in_progress','waiting_customer','waiting_vendor','closed'].includes(status))return json({error:'Choose a valid ticket status.'},400);const result=await env.DB.prepare('UPDATE tickets SET status = ?, closed_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(status,status==='closed'?new Date().toISOString():null,id,workspaceId).run();if(!result.meta.changes)return json({error:'Ticket not found.'},404);return json({id,status});}
  const ticketNote=path.match(/^\/api\/tickets\/([^/]+)\/notes$/);
  if(method==='POST'&&ticketNote){const ticketId=decodeURIComponent(ticketNote[1]),visibility=String(body.visibility||'public'),noteBody=String(body.body||'').trim();if(!noteBody||!['public','private'].includes(visibility))return json({error:'Enter a note and choose its visibility.'},400);if(!await env.DB.prepare('SELECT id FROM tickets WHERE id = ? AND workspace_id = ?').bind(ticketId,workspaceId).first())return json({error:'Ticket not found.'},404);const id=`tn_${crypto.randomUUID()}`;await env.DB.batch([env.DB.prepare('INSERT INTO ticket_notes (id, workspace_id, ticket_id, author_email, visibility, body) VALUES (?, ?, ?, ?, ?, ?)').bind(id,workspaceId,ticketId,identity.email,visibility,noteBody),env.DB.prepare('UPDATE tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(ticketId,workspaceId)]);return json({id},201);}
  const ticketTimeEntry=path.match(/^\/api\/tickets\/([^/]+)\/time$/);
  if(method==='POST'&&ticketTimeEntry){const ticketId=decodeURIComponent(ticketTimeEntry[1]),minutes=Number(body.minutes),description=String(body.description||'').trim();if(!Number.isInteger(minutes)||minutes<=0)return json({error:'Enter time in whole minutes greater than zero.'},400);if(!await env.DB.prepare('SELECT id FROM tickets WHERE id = ? AND workspace_id = ?').bind(ticketId,workspaceId).first())return json({error:'Ticket not found.'},404);const id=`tt_${crypto.randomUUID()}`;await env.DB.batch([env.DB.prepare('INSERT INTO ticket_time_entries (id, workspace_id, ticket_id, technician_email, minutes, description) VALUES (?, ?, ?, ?, ?, ?)').bind(id,workspaceId,ticketId,identity.email,minutes,description),env.DB.prepare('UPDATE tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(ticketId,workspaceId)]);return json({id},201);}
  if (method === 'POST' && path === '/api/invoices') {
    const items = Array.isArray(body.items) ? body.items.map(item => ({ description: String(item.description || '').trim(), quantity: Number(item.quantity), rateCents: cents(item.rate),sourceType:['item','subscription'].includes(item.sourceType)?item.sourceType:null,sourceId:String(item.sourceId||'')||null })) : [];
    if (!required(body, ['clientId', 'issued', 'due']) || !items.length || items.some(item => !item.description || !(item.quantity > 0) || !(item.rateCents >= 0))) return json({ error: 'Add at least one complete invoice item.' }, 400);
    if (!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(body.clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    for(const item of items){if(item.sourceType==='subscription'){if(item.rateCents!==0)return json({error:'Subscription reference items must remain $0.00.'},400);if(!await env.DB.prepare('SELECT id FROM subscriptions WHERE id = ? AND client_id = ? AND workspace_id = ?').bind(item.sourceId,body.clientId,workspaceId).first())return json({error:'Choose a subscription belonging to the invoice client.'},400);}if(item.sourceType==='item'&&!await env.DB.prepare('SELECT id FROM items WHERE id = ? AND workspace_id = ?').bind(item.sourceId,workspaceId).first())return json({error:'Catalog item not found.'},400);}
    const id = await nextId(env.DB, workspaceId, 'invoices', 'INV', 1001), amount = items.reduce((sum, item) => sum + Math.round(item.quantity * item.rateCents), 0), values = await customValueStatements(env.DB, workspaceId, 'invoice', id, body.customFields);
    const itemStatements = items.map((item, position) => env.DB.prepare('INSERT INTO invoice_items (id, workspace_id, invoice_id, description, quantity, rate_cents, position, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(`item_${crypto.randomUUID()}`, workspaceId, id, item.description, item.quantity, item.rateCents, position,item.sourceType,item.sourceId));
    await env.DB.batch([env.DB.prepare("INSERT INTO invoices (id, workspace_id, client_id, issued, due, description, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'sent')").bind(id, workspaceId, body.clientId, body.issued, body.due, items.map(item => item.description).join('; '), amount), ...itemStatements, ...values]);
    return json({ id }, 201);
  }
  const invoiceStatus = path.match(/^\/api\/invoices\/([^/]+)\/status$/);
  if (method === 'PATCH' && invoiceStatus) {
    const status = String(body.status || '');
    if (!['draft', 'sent', 'paid', 'overdue', 'void'].includes(status)) return json({ error: 'Choose a valid invoice status.' }, 400);
    const id = decodeURIComponent(invoiceStatus[1]), result = await env.DB.prepare('UPDATE invoices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(status, id, workspaceId).run();
    if (!result.meta.changes) return json({ error: 'Invoice not found.' }, 404);
    return json({ id, status });
  }
  const invoiceItemsUpdate = path.match(/^\/api\/invoices\/([^/]+)\/items$/);
  if (method === 'PATCH' && invoiceItemsUpdate) {
    const id = decodeURIComponent(invoiceItemsUpdate[1]), invoice = await env.DB.prepare('SELECT amount_cents, client_id FROM invoices WHERE id = ? AND workspace_id = ?').bind(id, workspaceId).first();
    if (!invoice) return json({ error: 'Invoice not found.' }, 404);
    const items = Array.isArray(body.items) ? body.items.map(item => ({ description: String(item.description || '').trim(), quantity: Number(item.quantity), rateCents: cents(item.rate),sourceType:['item','subscription'].includes(item.sourceType)?item.sourceType:null,sourceId:String(item.sourceId||'')||null })) : [];
    if (!items.length || items.some(item => !item.description || !(item.quantity > 0) || !(item.rateCents >= 0))) return json({ error: 'Add at least one complete invoice item.' }, 400);
    for(const item of items){if(item.sourceType==='subscription'){if(item.rateCents!==0)return json({error:'Subscription reference items must remain $0.00.'},400);if(!await env.DB.prepare('SELECT id FROM subscriptions WHERE id = ? AND client_id = ? AND workspace_id = ?').bind(item.sourceId,invoice.client_id,workspaceId).first())return json({error:'Choose a subscription belonging to the invoice client.'},400);}if(item.sourceType==='item'&&!await env.DB.prepare('SELECT id FROM items WHERE id = ? AND workspace_id = ?').bind(item.sourceId,workspaceId).first())return json({error:'Catalog item not found.'},400);}
    const amount = items.reduce((sum,item)=>sum+Math.round(item.quantity*item.rateCents),0), paid = await env.DB.prepare('SELECT COALESCE(SUM(amount_cents), 0) AS value FROM payments WHERE invoice_id = ? AND workspace_id = ?').bind(id, workspaceId).first();
    if (amount < Number(paid.value || 0)) return json({ error: 'Invoice total cannot be less than payments already received.' }, 400);
    const statements = [env.DB.prepare('DELETE FROM invoice_items WHERE invoice_id = ? AND workspace_id = ?').bind(id, workspaceId), ...items.map((item,position)=>env.DB.prepare('INSERT INTO invoice_items (id, workspace_id, invoice_id, description, quantity, rate_cents, position, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(`item_${crypto.randomUUID()}`,workspaceId,id,item.description,item.quantity,item.rateCents,position,item.sourceType,item.sourceId)), env.DB.prepare('UPDATE invoices SET description = ?, amount_cents = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(items.map(item=>item.description).join('; '),amount,id,workspaceId)];
    await env.DB.batch(statements);
    return json({ id, amount: dollars(amount) });
  }
  if (method === 'POST' && path === '/api/estimates') {
    const estimateLines=Array.isArray(body.items)?body.items.map(item=>({description:String(item.description||'').trim(),quantity:Number(item.quantity),rateCents:cents(item.rate),sourceType:['item','subscription'].includes(item.sourceType)?item.sourceType:null,sourceId:String(item.sourceId||'')||null})):[];
    if (!required(body, ['clientId', 'validUntil']) || !estimateLines.length || estimateLines.some(item=>!item.description||!(item.quantity>0)||!(item.rateCents>=0))) return json({ error: 'Choose a client, validity date, and at least one complete estimate item.' }, 400);
    if (!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(body.clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    for(const item of estimateLines){if(item.sourceType==='subscription'&&item.rateCents!==0)return json({error:'Subscription reference items must remain $0.00.'},400);}
    const id = await nextId(env.DB, workspaceId, 'estimates', 'EST', 501), values = await customValueStatements(env.DB, workspaceId, 'estimate', id, body.customFields),amount=estimateLines.reduce((sum,item)=>sum+Math.round(item.quantity*item.rateCents),0),quote=String(body.summary||estimateLines.map(item=>item.description).join('; ')).trim();
    const lineStatements=estimateLines.map((item,position)=>env.DB.prepare('INSERT INTO estimate_items (id, workspace_id, estimate_id, description, quantity, rate_cents, position, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(`estitem_${crypto.randomUUID()}`,workspaceId,id,item.description,item.quantity,item.rateCents,position,item.sourceType,item.sourceId));
    await env.DB.batch([env.DB.prepare("INSERT INTO estimates (id, workspace_id, client_id, quote, valid_until, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')").bind(id, workspaceId, body.clientId, quote, body.validUntil, amount),...lineStatements, ...values]);
    return json({ id }, 201);
  }
  const conversion = path.match(/^\/api\/estimates\/([^/]+)\/convert$/);
  if (method === 'POST' && conversion) {
    const estimate = await env.DB.prepare('SELECT * FROM estimates WHERE id = ? AND workspace_id = ?').bind(decodeURIComponent(conversion[1]), workspaceId).first();
    if (!estimate) return json({ error: 'Estimate not found.' }, 404);
    if (estimate.converted_invoice_id) return json({ error: 'Estimate already converted.' }, 400);
    const id = await nextId(env.DB, workspaceId, 'invoices', 'INV', 1001), issued = new Date().toISOString().slice(0, 10), due = new Date(); due.setUTCDate(due.getUTCDate() + 14);
    const estimateLines=await optionalAll(env.DB.prepare('SELECT description, quantity, rate_cents, position, source_type, source_id FROM estimate_items WHERE estimate_id = ? AND workspace_id = ? ORDER BY position').bind(estimate.id,workspaceId));
    const invoiceLines=(estimateLines.length?estimateLines:[{description:estimate.quote,quantity:1,rate_cents:estimate.amount_cents,source_type:null,source_id:null}]).map((item,position)=>env.DB.prepare('INSERT INTO invoice_items (id, workspace_id, invoice_id, description, quantity, rate_cents, position, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(`item_${crypto.randomUUID()}`,workspaceId,id,item.description,item.quantity,item.rate_cents,position,item.source_type,item.source_id));
    await env.DB.batch([env.DB.prepare("INSERT INTO invoices (id, workspace_id, client_id, estimate_id, issued, due, description, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sent')").bind(id, workspaceId, estimate.client_id, estimate.id, issued, due.toISOString().slice(0, 10), estimate.quote, estimate.amount_cents),...invoiceLines, env.DB.prepare("UPDATE estimates SET status = 'converted', converted_invoice_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?").bind(id, estimate.id, workspaceId)]);
    return json({ id }, 201);
  }
  const invoiceToEstimate=path.match(/^\/api\/invoices\/([^/]+)\/convert-to-estimate$/);
  if(method==='POST'&&invoiceToEstimate){const invoiceId=decodeURIComponent(invoiceToEstimate[1]),invoice=await env.DB.prepare('SELECT * FROM invoices WHERE id = ? AND workspace_id = ?').bind(invoiceId,workspaceId).first();if(!invoice)return json({error:'Invoice not found.'},404);const sourceLines=await optionalAll(env.DB.prepare('SELECT description, quantity, rate_cents, position, source_type, source_id FROM invoice_items WHERE invoice_id = ? AND workspace_id = ? ORDER BY position').bind(invoiceId,workspaceId));const lines=sourceLines.length?sourceLines:[{description:invoice.description,quantity:1,rate_cents:invoice.amount_cents,source_type:null,source_id:null}];let estimateId=invoice.estimate_id;if(estimateId&&await env.DB.prepare('SELECT id FROM estimates WHERE id = ? AND workspace_id = ?').bind(estimateId,workspaceId).first()){const statements=[env.DB.prepare('UPDATE estimates SET client_id = ?, quote = ?, valid_until = ?, amount_cents = ?, status = \'draft\', converted_invoice_id = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(invoice.client_id,invoice.description,invoice.due,invoice.amount_cents,estimateId,workspaceId),env.DB.prepare('DELETE FROM estimate_items WHERE estimate_id = ? AND workspace_id = ?').bind(estimateId,workspaceId),...lines.map((item,position)=>env.DB.prepare('INSERT INTO estimate_items (id, workspace_id, estimate_id, description, quantity, rate_cents, position, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(`estitem_${crypto.randomUUID()}`,workspaceId,estimateId,item.description,item.quantity,item.rate_cents,position,item.source_type,item.source_id))];await env.DB.batch(statements);}else{estimateId=await nextId(env.DB,workspaceId,'estimates','EST',501);await env.DB.batch([env.DB.prepare("INSERT INTO estimates (id, workspace_id, client_id, quote, valid_until, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')").bind(estimateId,workspaceId,invoice.client_id,invoice.description,invoice.due,invoice.amount_cents),...lines.map((item,position)=>env.DB.prepare('INSERT INTO estimate_items (id, workspace_id, estimate_id, description, quantity, rate_cents, position, source_type, source_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(`estitem_${crypto.randomUUID()}`,workspaceId,estimateId,item.description,item.quantity,item.rate_cents,position,item.source_type,item.source_id)),env.DB.prepare('UPDATE invoices SET estimate_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?').bind(estimateId,invoiceId,workspaceId)]);}return json({id:estimateId});}
  if (method === 'POST' && path === '/api/payments') {
    if (!required(body, ['invoiceId', 'date', 'method', 'amount']) || !['cash', 'check', 'credit_card'].includes(body.method)) return json({ error: 'Complete all payment fields.' }, 400);
    const invoice = await env.DB.prepare('SELECT amount_cents FROM invoices WHERE id = ? AND workspace_id = ?').bind(body.invoiceId, workspaceId).first();
    if (!invoice) return json({ error: 'Invoice not found.' }, 404);
    const paid = await env.DB.prepare('SELECT COALESCE(SUM(amount_cents), 0) AS value FROM payments WHERE invoice_id = ? AND workspace_id = ?').bind(body.invoiceId, workspaceId).first(), amount = cents(body.amount);
    if (!(amount > 0) || amount > invoice.amount_cents - Number(paid.value || 0)) return json({ error: 'Payment must be greater than zero and no more than the balance due.' }, 400);
    const id = await nextId(env.DB, workspaceId, 'payments', 'PAY', 301);
    await env.DB.prepare('INSERT INTO payments (id, workspace_id, invoice_id, payment_date, method, amount_cents) VALUES (?, ?, ?, ?, ?, ?)').bind(id, workspaceId, body.invoiceId, body.date, body.method, amount).run();
    return json({ id }, 201);
  }
  const paymentRecord=path.match(/^\/api\/payments\/([^/]+)$/);
  if(method==='PATCH'&&paymentRecord){const id=decodeURIComponent(paymentRecord[1]),date=String(body.date||''),methodName=String(body.method||''),amount=cents(body.amount);if(!date||!['cash','check','credit_card'].includes(methodName)||!(amount>0))return json({error:'Enter a valid payment date, method, and amount.'},400);const payment=await env.DB.prepare('SELECT invoice_id FROM payments WHERE id = ? AND workspace_id = ?').bind(id,workspaceId).first();if(!payment)return json({error:'Payment not found.'},404);const invoice=await env.DB.prepare('SELECT amount_cents FROM invoices WHERE id = ? AND workspace_id = ?').bind(payment.invoice_id,workspaceId).first(),otherPaid=await env.DB.prepare('SELECT COALESCE(SUM(amount_cents), 0) AS value FROM payments WHERE invoice_id = ? AND workspace_id = ? AND id != ?').bind(payment.invoice_id,workspaceId,id).first();if(!invoice||amount>invoice.amount_cents-Number(otherPaid.value||0))return json({error:'Payment cannot exceed the remaining invoice total.'},400);await env.DB.prepare('UPDATE payments SET payment_date = ?, method = ?, amount_cents = ? WHERE id = ? AND workspace_id = ?').bind(date,methodName,amount,id,workspaceId).run();return json({id});}
  if(method==='DELETE'&&paymentRecord){const id=decodeURIComponent(paymentRecord[1]),result=await env.DB.prepare('DELETE FROM payments WHERE id = ? AND workspace_id = ?').bind(id,workspaceId).run();if(!result.meta.changes)return json({error:'Payment not found.'},404);return json({id});}
  if (method === 'POST' && path === '/api/settings/custom-fields') {
    const label = String(body.label || '').trim(), entityType = String(body.appliesTo || '');
    if (!label || label.length > 60 || !['client', 'invoice', 'estimate'].includes(entityType)) return json({ error: 'Choose a category and enter a field name up to 60 characters.' }, 400);
    const id = `field_${crypto.randomUUID()}`;
    try { await env.DB.prepare('INSERT INTO custom_fields (id, workspace_id, label, entity_type) VALUES (?, ?, ?, ?)').bind(id, workspaceId, label, entityType).run(); } catch { return json({ error: 'That custom field already exists in this category.' }, 400); }
    return json({ id }, 201);
  }
  const fieldDelete = path.match(/^\/api\/settings\/custom-fields\/([^/]+)$/);
  if (method === 'DELETE' && fieldDelete) {
    const id = decodeURIComponent(fieldDelete[1]), result = await env.DB.prepare('DELETE FROM custom_fields WHERE id = ? AND workspace_id = ?').bind(id, workspaceId).run();
    if (!result.meta.changes) return json({ error: 'Custom field not found.' }, 404);
    return json({ id });
  }
  return json({ error: 'API route not found.' }, 404);
}

export async function handleApi(request, env) {
  try {
    return await api(request, env);
  } catch (error) {
    if (error instanceof Response) return error;
    console.error(error);
    return json({ error: 'The invoice service could not complete this request.' }, 500);
  }
}

export default {
  async fetch(request, env) {
    if (new URL(request.url).pathname.startsWith('/api/')) return handleApi(request, env);
    return env.ASSETS.fetch(request);
  },
};
