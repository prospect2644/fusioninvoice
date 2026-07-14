import express from 'express';
import path from 'node:path';
import { requireIdentity } from './auth.js';
import { addClient, addCustomField, addEstimate, addInvoice, addPayment, convertEstimate, removeCustomField, workspaceFor } from './store.js';

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
app.use('/api', requireIdentity);
app.get('/api/session', (req, res) => res.json(req.identity));
app.get('/api/workspace', (req, res) => res.json(workspaceFor(req.identity.email)));

const required = (body, fields) => fields.every(field => typeof body?.[field] === 'string' && body[field].trim());
const action = fn => (req, res) => { try { res.status(201).json(fn(req.body || {}, req.params)); } catch (error) { res.status(400).json({ error: error.message }); } };
app.post('/api/clients', action(body => {
  if (!required(body, ['name','city','state','zip','hourlyRate']) || !Number.isFinite(Number(body.hourlyRate))) throw new Error('Name, city, state, ZIP, and hourly rate are required.');
  return addClient({ name: body.name.trim(), city: body.city.trim(), state: body.state.trim().toUpperCase(), zip: body.zip.trim(), hourlyRate: body.hourlyRate });
}));
app.post('/api/invoices', action(body => {
  if (!required(body, ['clientId','issued','due','description','amount']) || !Number.isFinite(Number(body.amount))) throw new Error('Complete all invoice fields.');
  return addInvoice(body);
}));
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
