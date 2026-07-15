import { createRemoteJWKSet, jwtVerify } from 'jose';

const json = (body, status = 200) => Response.json(body, { status, headers: { 'cache-control': 'no-store', 'x-content-type-options': 'nosniff' } });
const cents = value => Math.round(Number(value) * 100);
const dollars = value => Number(value || 0) / 100;
const required = (body, fields) => fields.every(field => typeof body?.[field] === 'string' && body[field].trim());
const verifiers = new Map();

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
    if (/no such table/i.test(String(error?.message || error))) return [];
    throw error;
  }
};
async function readWorkspace(db, workspaceId, identity) {
  const [clients, invoices, invoiceItems, estimates, payments, items, subscriptions, expenses, tasks, fields, values] = await Promise.all([
    all(db.prepare('SELECT id, name, city, state, zip, hourly_rate_cents, status FROM clients WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    all(db.prepare('SELECT id, client_id, estimate_id, issued, due, description, amount_cents, status, created_at FROM invoices WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    all(db.prepare('SELECT id, invoice_id, description, quantity, rate_cents, position FROM invoice_items WHERE workspace_id = ? ORDER BY invoice_id, position').bind(workspaceId)),
    all(db.prepare('SELECT id, client_id, quote, valid_until, amount_cents, status, converted_invoice_id, created_at FROM estimates WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    all(db.prepare('SELECT id, invoice_id, payment_date, method, amount_cents, created_at FROM payments WHERE workspace_id = ? ORDER BY created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, name, company, category, description, stock_quantity, price_cents, tax_1, tax_2, status, created_at FROM items WHERE workspace_id = ? ORDER BY name').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, summary, next_date, stop_date, interval_count, interval_unit, amount_cents, status, created_at FROM subscriptions WHERE workspace_id = ? ORDER BY next_date, created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, vendor, expense_date, company, category, description, amount_cents, tax_cents, status, created_at FROM expenses WHERE workspace_id = ? ORDER BY expense_date DESC, created_at DESC').bind(workspaceId)),
    optionalAll(db.prepare('SELECT id, client_id, title, description, due_date, assignee_email, completed_at, status, created_at FROM tasks WHERE workspace_id = ? ORDER BY due_date, created_at DESC').bind(workspaceId)),
    all(db.prepare('SELECT id, label, entity_type, position FROM custom_fields WHERE workspace_id = ? ORDER BY entity_type, position, created_at').bind(workspaceId)),
    all(db.prepare('SELECT v.custom_field_id, v.record_id, v.value FROM custom_field_values v JOIN custom_fields f ON f.id = v.custom_field_id WHERE f.workspace_id = ?').bind(workspaceId)),
  ]);
  const customFor = id => Object.fromEntries(values.filter(value => value.record_id === id).map(value => [value.custom_field_id, value.value]));
  const paidFor = id => payments.filter(payment => payment.invoice_id === id).reduce((sum, payment) => sum + payment.amount_cents, 0);
  return {
    clients: clients.map(row => ({ id: row.id, name: row.name, city: row.city, state: row.state, zip: row.zip, hourlyRate: dollars(row.hourly_rate_cents), status: row.status, customFields: customFor(row.id) })),
    invoices: invoices.map(row => { const paid = paidFor(row.id), balance = Math.max(0, row.amount_cents - paid); const items = invoiceItems.filter(item => item.invoice_id === row.id).map(item => ({ id: item.id, description: item.description, quantity: Number(item.quantity), rate: dollars(item.rate_cents) })); return { id: row.id, clientId: row.client_id, estimateId: row.estimate_id, issued: row.issued, due: row.due, description: row.description, items: items.length ? items : [{ id: `${row.id}_legacy`, description: row.description, quantity: 1, rate: dollars(row.amount_cents) }], amount: dollars(row.amount_cents), paid: dollars(paid), balance: dollars(balance), status: balance === 0 && row.amount_cents > 0 ? 'paid' : row.status, createdAt: row.created_at, customFields: customFor(row.id) }; }),
    estimates: estimates.map(row => ({ id: row.id, clientId: row.client_id, quote: row.quote, validUntil: row.valid_until, amount: dollars(row.amount_cents), status: row.status, invoiceId: row.converted_invoice_id, createdAt: row.created_at, customFields: customFor(row.id) })),
    payments: payments.map(row => ({ id: row.id, invoiceId: row.invoice_id, date: row.payment_date, method: row.method, amount: dollars(row.amount_cents), createdAt: row.created_at })),
    items: items.map(row => ({ id: row.id, name: row.name, company: row.company, category: row.category, description: row.description, stock: Number(row.stock_quantity), price: dollars(row.price_cents), tax1: Number(row.tax_1), tax2: Number(row.tax_2), status: row.status, createdAt: row.created_at })),
    subscriptions: subscriptions.map(row => ({ id: row.id, clientId: row.client_id, summary: row.summary, nextDate: row.next_date, stopDate: row.stop_date, intervalCount: Number(row.interval_count), intervalUnit: row.interval_unit, amount: dollars(row.amount_cents), status: row.status, createdAt: row.created_at })),
    expenses: expenses.map(row => ({ id: row.id, clientId: row.client_id, vendor: row.vendor, date: row.expense_date, company: row.company, category: row.category, description: row.description, amount: dollars(row.amount_cents), tax: dollars(row.tax_cents), status: row.status, createdAt: row.created_at })),
    tasks: tasks.map(row => ({ id: row.id, clientId: row.client_id, title: row.title, description: row.description, dueDate: row.due_date, assigneeEmail: row.assignee_email, completedAt: row.completed_at, status: row.status, createdAt: row.created_at })),
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
    await env.DB.batch([env.DB.prepare('INSERT INTO clients (id, workspace_id, name, city, state, zip, hourly_rate_cents) VALUES (?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, body.name.trim(), body.city.trim(), body.state.trim().toUpperCase(), body.zip.trim(), cents(body.hourlyRate || 0)), ...values]);
    return json({ id }, 201);
  }
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
    const intervalCount = Number(body.intervalCount || 1), intervalUnit = String(body.intervalUnit || 'months'), amount = cents(body.amount || 0), status = String(body.status || 'active');
    if (!clientId || !summary || !nextDate || !(intervalCount > 0) || !Number.isInteger(intervalCount) || !['days','weeks','months','years'].includes(intervalUnit) || !(amount >= 0) || !['active','paused','ended'].includes(status)) return json({ error: 'Complete the subscription details with a valid recurrence and amount.' }, 400);
    if (!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    const id = `sub_${crypto.randomUUID()}`;
    await env.DB.prepare('INSERT INTO subscriptions (id, workspace_id, client_id, summary, next_date, stop_date, interval_count, interval_unit, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, clientId, summary, nextDate, stopDate, intervalCount, intervalUnit, amount, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/expenses') {
    const clientId = String(body.clientId || '') || null, vendor = String(body.vendor || '').trim(), date = String(body.date || ''), company = String(body.company || '').trim(), category = String(body.category || '').trim(), description = String(body.description || '').trim();
    const amount = cents(body.amount), tax = cents(body.tax || 0), status = String(body.status || 'unbilled');
    if (!vendor || !date || !description || !(amount > 0) || !(tax >= 0) || !['unbilled','billed','reimbursed'].includes(status)) return json({ error: 'Complete the expense details with a valid amount and status.' }, 400);
    if (clientId && !await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    const id = await nextId(env.DB, workspaceId, 'expenses', 'EXP', 1);
    await env.DB.prepare('INSERT INTO expenses (id, workspace_id, client_id, vendor, expense_date, company, category, description, amount_cents, tax_cents, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, clientId, vendor, date, company, category, description, amount, tax, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/tasks') {
    const clientId = String(body.clientId || '') || null, title = String(body.title || '').trim(), description = String(body.description || '').trim(), dueDate = String(body.dueDate || ''), status = String(body.status || 'open');
    if (!title || !dueDate || !['open','in_progress','completed','cancelled'].includes(status)) return json({ error: 'Complete the task title, due date, and status.' }, 400);
    if (clientId && !await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    const id = await nextId(env.DB, workspaceId, 'tasks', 'TSK', 1), completedAt = status === 'completed' ? new Date().toISOString() : null;
    await env.DB.prepare('INSERT INTO tasks (id, workspace_id, client_id, title, description, due_date, assignee_email, completed_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)').bind(id, workspaceId, clientId, title, description, dueDate, identity.email, completedAt, status).run();
    return json({ id }, 201);
  }
  if (method === 'POST' && path === '/api/invoices') {
    const items = Array.isArray(body.items) ? body.items.map(item => ({ description: String(item.description || '').trim(), quantity: Number(item.quantity), rateCents: cents(item.rate) })) : [];
    if (!required(body, ['clientId', 'issued', 'due']) || !items.length || items.some(item => !item.description || !(item.quantity > 0) || !(item.rateCents >= 0))) return json({ error: 'Add at least one complete invoice item.' }, 400);
    if (!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(body.clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    const id = await nextId(env.DB, workspaceId, 'invoices', 'INV', 1001), amount = items.reduce((sum, item) => sum + Math.round(item.quantity * item.rateCents), 0), values = await customValueStatements(env.DB, workspaceId, 'invoice', id, body.customFields);
    const itemStatements = items.map((item, position) => env.DB.prepare('INSERT INTO invoice_items (id, workspace_id, invoice_id, description, quantity, rate_cents, position) VALUES (?, ?, ?, ?, ?, ?, ?)').bind(`item_${crypto.randomUUID()}`, workspaceId, id, item.description, item.quantity, item.rateCents, position));
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
  if (method === 'POST' && path === '/api/estimates') {
    if (!required(body, ['clientId', 'validUntil', 'quote', 'amount']) || !(cents(body.amount) > 0)) return json({ error: 'Complete all estimate fields.' }, 400);
    if (!await env.DB.prepare('SELECT id FROM clients WHERE id = ? AND workspace_id = ?').bind(body.clientId, workspaceId).first()) return json({ error: 'Client not found.' }, 404);
    const id = await nextId(env.DB, workspaceId, 'estimates', 'EST', 501), values = await customValueStatements(env.DB, workspaceId, 'estimate', id, body.customFields);
    await env.DB.batch([env.DB.prepare("INSERT INTO estimates (id, workspace_id, client_id, quote, valid_until, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')").bind(id, workspaceId, body.clientId, body.quote.trim(), body.validUntil, cents(body.amount)), ...values]);
    return json({ id }, 201);
  }
  const conversion = path.match(/^\/api\/estimates\/([^/]+)\/convert$/);
  if (method === 'POST' && conversion) {
    const estimate = await env.DB.prepare('SELECT * FROM estimates WHERE id = ? AND workspace_id = ?').bind(decodeURIComponent(conversion[1]), workspaceId).first();
    if (!estimate) return json({ error: 'Estimate not found.' }, 404);
    if (estimate.converted_invoice_id) return json({ error: 'Estimate already converted.' }, 400);
    const id = await nextId(env.DB, workspaceId, 'invoices', 'INV', 1001), issued = new Date().toISOString().slice(0, 10), due = new Date(); due.setUTCDate(due.getUTCDate() + 14);
    await env.DB.batch([env.DB.prepare("INSERT INTO invoices (id, workspace_id, client_id, estimate_id, issued, due, description, amount_cents, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sent')").bind(id, workspaceId, estimate.client_id, estimate.id, issued, due.toISOString().slice(0, 10), estimate.quote, estimate.amount_cents), env.DB.prepare("UPDATE estimates SET status = 'converted', converted_invoice_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND workspace_id = ?").bind(id, estimate.id, workspaceId)]);
    return json({ id }, 201);
  }
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
