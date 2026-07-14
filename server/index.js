import express from 'express';
import path from 'node:path';
import { requireIdentity } from './auth.js';
import { addClient, addInvoice, workspaceFor } from './store.js';

const app = express();
app.disable('x-powered-by');
app.use(express.json({ limit: '128kb' }));
app.use((req, res, next) => {
  res.setHeader('Cache-Control', 'no-store');
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('Referrer-Policy', 'same-origin');
  res.setHeader('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'");
  next();
});
app.use((req, res, next) => {
  if (!['GET', 'HEAD', 'OPTIONS'].includes(req.method) && req.get('Sec-Fetch-Site') === 'cross-site') return res.status(403).json({ error: 'Cross-site writes are not allowed.' });
  next();
});
app.use('/api', requireIdentity);
app.get('/api/session', (req, res) => res.json(req.identity));
app.get('/api/workspace', (req, res) => res.json(workspaceFor(req.identity.email)));
app.post('/api/invoices', (req, res) => {
  const { clientId, issued, due, description, amount } = req.body || {};
  if (![clientId, issued, due, description].every(v => typeof v === 'string' && v.trim()) || !Number.isFinite(Number(amount))) return res.status(400).json({ error: 'Invalid invoice.' });
  res.status(201).json(addInvoice({ clientId, issued, due, description, amount }));
});
app.post('/api/clients', (req, res) => {
  const { name, contact, email } = req.body || {};
  if (![name, contact, email].every(v => typeof v === 'string' && v.trim()) || !email.includes('@')) return res.status(400).json({ error: 'Invalid client.' });
  res.status(201).json(addClient({ name, contact, email: email.toLowerCase() }));
});

const dist = path.resolve('dist');
app.use(express.static(dist, { index: false, maxAge: '1h' }));
app.use((req, res, next) => req.method === 'GET' && !req.path.startsWith('/api/') ? res.sendFile(path.join(dist, 'index.html')) : next());

const port = Number(process.env.PORT || 8787);
app.listen(port, '127.0.0.1', () => console.log(`Kindred Invoice API on http://127.0.0.1:${port}`));
