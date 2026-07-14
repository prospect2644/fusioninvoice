import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const dataFile = path.resolve('server/data.json');
const emptyStore = { clients: [], invoices: [], estimates: [], payments: [], settings: { customFields: [] } };

function read() {
  if (!fs.existsSync(dataFile)) fs.writeFileSync(dataFile, JSON.stringify(emptyStore, null, 2));
  const saved = JSON.parse(fs.readFileSync(dataFile, 'utf8'));
  return { ...emptyStore, ...saved, settings: { ...emptyStore.settings, ...(saved.settings || {}) } };
}
function write(data) { fs.writeFileSync(dataFile, JSON.stringify(data, null, 2)); }
const nextNumber = (items, prefix, start = 1001) => {
  const values = items.map(item => Number(String(item.id).replace(`${prefix}-`, ''))).filter(Number.isFinite);
  return `${prefix}-${values.length ? Math.max(...values) + 1 : start}`;
};
const paymentsFor = (data, invoiceId) => data.payments.filter(p => p.invoiceId === invoiceId).reduce((sum, p) => sum + Number(p.amount), 0);
const customValuesFor = (data, appliesTo, input = {}) => Object.fromEntries(
  data.settings.customFields
    .filter(field => field.appliesTo === appliesTo)
    .map(field => [field.id, String(input.customFields?.[field.id] || '').trim()])
    .filter(([, value]) => value)
);
const decorate = data => ({
  ...data,
  invoices: data.invoices.map(invoice => {
    const paid = paymentsFor(data, invoice.id);
    const balance = Math.max(0, Number(invoice.amount) - paid);
    return { ...invoice, paid, balance, status: balance === 0 && Number(invoice.amount) > 0 ? 'paid' : invoice.status };
  })
});

export function workspaceFor(email) {
  return { ...decorate(read()), user: { email, name: email.split('@')[0].replace(/[._-]/g, ' ') } };
}
export function addClient(input) {
  const data = read();
  const client = { id: `cl_${crypto.randomUUID()}`, status: 'active', ...input, customFields: customValuesFor(data, 'client', input), hourlyRate: Number(input.hourlyRate) };
  data.clients.unshift(client); write(data); return client;
}
export function addInvoice(input) {
  const data = read();
  if (!data.clients.some(c => c.id === input.clientId)) throw new Error('Client not found');
  const items = input.items.map((item, index) => ({ id: `item_${crypto.randomUUID()}`, description: String(item.description || '').trim(), quantity: Number(item.quantity), rate: Number(item.rate), position: index }));
  if (items.some(item => !item.description || !(item.quantity > 0) || !(item.rate >= 0))) throw new Error('Add at least one complete invoice item.');
  const amount = items.reduce((sum,item)=>sum+item.quantity*item.rate,0);
  const invoice = { id: nextNumber(data.invoices, 'INV'), status: 'sent', ...input, items, description: items.map(item=>item.description).join('; '), customFields: customValuesFor(data, 'invoice', input), amount, createdAt: new Date().toISOString() };
  data.invoices.unshift(invoice); write(data); return invoice;
}
export function addEstimate(input) {
  const data = read();
  if (!data.clients.some(c => c.id === input.clientId)) throw new Error('Client not found');
  const estimate = { id: nextNumber(data.estimates, 'EST', 501), status: 'draft', ...input, customFields: customValuesFor(data, 'estimate', input), amount: Number(input.amount), createdAt: new Date().toISOString() };
  data.estimates.unshift(estimate); write(data); return estimate;
}
export function convertEstimate(id) {
  const data = read();
  const estimate = data.estimates.find(e => e.id === id);
  if (!estimate) throw new Error('Estimate not found');
  if (estimate.invoiceId) throw new Error('Estimate already converted');
  const issued = new Date().toISOString().slice(0, 10);
  const dueDate = new Date(); dueDate.setDate(dueDate.getDate() + 14);
  const invoice = { id: nextNumber(data.invoices, 'INV'), clientId: estimate.clientId, issued, due: dueDate.toISOString().slice(0, 10), description: estimate.quote, amount: Number(estimate.amount), status: 'sent', estimateId: estimate.id, createdAt: new Date().toISOString() };
  data.invoices.unshift(invoice); estimate.status = 'converted'; estimate.invoiceId = invoice.id; write(data); return { estimate, invoice };
}
export function addPayment(input) {
  const data = read();
  const invoice = data.invoices.find(i => i.id === input.invoiceId);
  if (!invoice) throw new Error('Invoice not found');
  const amount = Number(input.amount);
  const balance = Number(invoice.amount) - paymentsFor(data, invoice.id);
  if (!(amount > 0) || amount > balance) throw new Error('Payment must be greater than zero and no more than the balance due');
  const payment = { id: nextNumber(data.payments, 'PAY', 301), ...input, amount, createdAt: new Date().toISOString() };
  data.payments.unshift(payment); write(data); return payment;
}

export function updateInvoiceStatus(id, status) {
  const data = read();
  const invoice = data.invoices.find(item => item.id === id);
  if (!invoice) throw new Error('Invoice not found');
  if (!['draft', 'sent', 'paid', 'overdue', 'void'].includes(status)) throw new Error('Choose a valid invoice status.');
  invoice.status = status;
  write(data);
  return { id, status };
}

export function addCustomField(input) {
  const data = read();
  const label = String(input.label || '').trim();
  const appliesTo = String(input.appliesTo || '');
  if (!label || label.length > 60 || !['client', 'invoice', 'estimate'].includes(appliesTo)) throw new Error('Choose a category and enter a field name up to 60 characters.');
  if (data.settings.customFields.some(field => field.appliesTo === appliesTo && field.label.toLowerCase() === label.toLowerCase())) throw new Error('That custom field already exists in this category.');
  const field = { id: `field_${crypto.randomUUID()}`, label, appliesTo };
  data.settings.customFields.push(field); write(data); return field;
}

export function removeCustomField(id) {
  const data = read();
  const before = data.settings.customFields.length;
  data.settings.customFields = data.settings.customFields.filter(field => field.id !== id);
  if (data.settings.customFields.length === before) throw new Error('Custom field not found.');
  write(data); return { id };
}
