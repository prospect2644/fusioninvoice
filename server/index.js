import express from 'express';
import path from 'node:path';
import { requireIdentity } from './auth.js';
import { addClient, addCustomField, addEstimate, addExpense, addInvoice, addItem, addPayment, addSubscription, addTask, addTicket, addTicketNote, addTicketTime, convertEstimate, removeCustomField, updateInvoiceItems, updateInvoiceStatus, updateTicketStatus, workspaceFor } from './store.js';

const app = express();
app.disable('x-powered-by');
app.use(express.json({ limit: '128kb' }));
app.use((req, res, next) => {
  res.setHeader('Cache-Control', 'no-store'); res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('Referrer-Policy', 'same-origin');
  res.setHeader('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'");
  next();
});
app.use((req, res, next) => !['GET','HEAD','OPTIONS'].includes(req.method) && req.get('Sec-Fetch-Site') === 'cross-site' ? res.status(403).json({ error: 'Cross-site writes are not allowed.' }) : next());
app.all('/api/helpdesk/inbound-email',(req,res)=>req.method==='POST'?res.status(501).json({error:'Inbound helpdesk email is reserved but not configured.',code:'EMAIL_INGEST_NOT_CONFIGURED'}):res.set('Allow','POST').sendStatus(405));
app.use('/api', requireIdentity);
app.get('/api/session', (req, res) => res.json(req.identity));
app.get('/api/workspace', (req, res) => res.json(workspaceFor(req.identity.email)));

const required = (body, fields) => fields.every(field => typeof body?.[field] === 'string' && body[field].trim());
const action = fn => (req, res) => { try { res.status(201).json(fn(req.body || {}, req.params)); } catch (error) { res.status(400).json({ error: error.message }); } };
app.post('/api/clients', action(body => {
  if (!required(body, ['name','city','state','zip']) || !Number.isFinite(Number(body.hourlyRate || 0))) throw new Error('Name, city, state, and ZIP are required.');
  return addClient({ name: body.name.trim(), city: body.city.trim(), state: body.state.trim().toUpperCase(), zip: body.zip.trim(), hourlyRate: body.hourlyRate || 0 });
}));
app.post('/api/invoices', action(body => {
  if (!required(body, ['clientId','issued','due']) || !Array.isArray(body.items) || !body.items.length) throw new Error('Add at least one complete invoice item.');
  return addInvoice(body);
}));
app.post('/api/items', action(body => addItem(body)));
app.post('/api/subscriptions', action(body => addSubscription(body)));
app.post('/api/expenses', action(body => addExpense(body)));
app.post('/api/tasks', (req, res) => { try { res.status(201).json(addTask(req.body || {}, req.identity.email)); } catch (error) { res.status(400).json({ error: error.message }); } });
app.post('/api/tickets', action(body => addTicket(body)));
app.patch('/api/tickets/:id/status', action((body,params)=>updateTicketStatus(params.id,body.status)));
app.post('/api/tickets/:id/notes', (req,res)=>{try{res.status(201).json(addTicketNote(req.params.id,req.body||{},req.identity.email))}catch(error){res.status(400).json({error:error.message})}});
app.post('/api/tickets/:id/time', (req,res)=>{try{res.status(201).json(addTicketTime(req.params.id,req.body||{},req.identity.email))}catch(error){res.status(400).json({error:error.message})}});
app.patch('/api/invoices/:id/status', action((body, params) => updateInvoiceStatus(params.id, body.status)));
app.patch('/api/invoices/:id/items', action((body, params) => updateInvoiceItems(params.id, body)));
app.post('/api/estimates', action(body => {
  if (!required(body, ['clientId','validUntil','quote','amount']) || !Number.isFinite(Number(body.amount))) throw new Error('Complete all estimate fields.');
  return addEstimate(body);
}));
app.post('/api/estimates/:id/convert', action((body, params) => convertEstimate(params.id)));
app.post('/api/payments', action(body => {
  if (!required(body, ['invoiceId','date','method','amount']) || !['cash','check','credit_card'].includes(body.method)) throw new Error('Complete all payment fields.');
  return addPayment(body);
}));
app.post('/api/settings/custom-fields', action(body => addCustomField(body)));
app.delete('/api/settings/custom-fields/:id', action((body, params) => removeCustomField(params.id)));
app.get('/api/logout', (req, res) => res.json({ logoutUrl: req.identity.source === 'local-development' ? null : '/cdn-cgi/access/logout' }));

const dist = path.resolve('dist');
app.use(express.static(dist, { index: false, maxAge: '1h' }));
app.use((req, res, next) => req.method === 'GET' && !req.path.startsWith('/api/') ? res.sendFile(path.join(dist, 'index.html')) : next());
const port = Number(process.env.PORT || 8787);
app.listen(port, '127.0.0.1', () => console.log(`Kindred Invoice API on http://127.0.0.1:${port}`));
