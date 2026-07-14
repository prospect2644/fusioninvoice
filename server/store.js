import fs from 'node:fs';
import path from 'node:path';

const dataFile = path.resolve('server/data.json');
const seed = {
  clients: [
    { id: 'cl_willow', name: 'Willow Yoga & Sound', email: 'hello@willowyoga.example', contact: 'Maya Chen', status: 'active' },
    { id: 'cl_emote', name: 'Emote Psychology', email: 'care@emote.example', contact: 'Danielle F.', status: 'active' },
    { id: 'cl_hearth', name: 'Hearth Community Studio', email: 'team@hearth.example', contact: 'Rowan Bell', status: 'active' }
  ],
  invoices: [
    { id: 'INV-1048', clientId: 'cl_willow', issued: '2026-07-08', due: '2026-07-22', status: 'sent', amount: 2480, description: 'Website care & booking integration' },
    { id: 'INV-1047', clientId: 'cl_emote', issued: '2026-07-01', due: '2026-07-15', status: 'paid', amount: 1850, description: 'Digital presence retainer' },
    { id: 'INV-1046', clientId: 'cl_hearth', issued: '2026-06-26', due: '2026-07-10', status: 'overdue', amount: 960, description: 'Front desk systems support' },
    { id: 'INV-1045', clientId: 'cl_willow', issued: '2026-06-14', due: '2026-06-28', status: 'paid', amount: 1320, description: 'Email operations & training' }
  ],
  payments: [{ id: 'PAY-301', invoiceId: 'INV-1047', date: '2026-07-10', amount: 1850 }]
};

function read() {
  if (!fs.existsSync(dataFile)) fs.writeFileSync(dataFile, JSON.stringify(seed, null, 2));
  return JSON.parse(fs.readFileSync(dataFile, 'utf8'));
}
function write(data) { fs.writeFileSync(dataFile, JSON.stringify(data, null, 2)); }

export function workspaceFor(email) {
  const data = read();
  return { ...data, user: { email, name: email.split('@')[0].replace(/[._-]/g, ' ') } };
}
export function addInvoice(input) {
  const data = read();
  const next = Math.max(...data.invoices.map(i => Number(i.id.split('-')[1]))) + 1;
  const invoice = { id: `INV-${next}`, status: 'draft', ...input, amount: Number(input.amount) };
  data.invoices.unshift(invoice); write(data); return invoice;
}
export function addClient(input) {
  const data = read();
  const client = { id: `cl_${crypto.randomUUID()}`, status: 'active', ...input };
  data.clients.unshift(client); write(data); return client;
}
