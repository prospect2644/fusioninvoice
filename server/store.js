import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const dataFile = path.resolve('server/data.json');
const emptyStore = { clients: [], invoices: [], estimates: [], payments: [], items: [], subscriptions: [], expenses: [], tasks: [], tickets: [], settings: { customFields: [] } };

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
export function updateInvoiceItems(id, input) {
  const data = read(), invoice = data.invoices.find(item => item.id === id);
  if (!invoice) throw new Error('Invoice not found');
  const items = (input.items || []).map((item, index) => ({ id: item.id || `item_${crypto.randomUUID()}`, description: String(item.description || '').trim(), quantity: Number(item.quantity), rate: Number(item.rate), position: index }));
  if (!items.length || items.some(item => !item.description || !(item.quantity > 0) || !(item.rate >= 0))) throw new Error('Add at least one complete invoice item.');
  const amount = items.reduce((sum,item)=>sum+item.quantity*item.rate,0), paid = paymentsFor(data, id);
  if (amount < paid) throw new Error('Invoice total cannot be less than payments already received.');
  invoice.items = items; invoice.description = items.map(item=>item.description).join('; '); invoice.amount = amount; invoice.updatedAt = new Date().toISOString();
  write(data); return invoice;
}

export function addItem(input) {
  const data = read();
  const item = { id: `itm_${crypto.randomUUID()}`, name: String(input.name || '').trim(), company: String(input.company || '').trim(), category: String(input.category || '').trim(), description: String(input.description || '').trim(), stock: Number(input.stock || 0), price: Number(input.price || 0), tax1: Number(input.tax1 || 0), tax2: Number(input.tax2 || 0), status: input.status || 'active', createdAt: new Date().toISOString() };
  if (!item.name || item.stock < 0 || item.price < 0 || item.tax1 < 0 || item.tax2 < 0 || !['active','inactive'].includes(item.status)) throw new Error('Enter a valid item name, stock, price, taxes, and status.');
  data.items.unshift(item); write(data); return item;
}

export function addSubscription(input) {
  const data = read();
  if (!data.clients.some(client => client.id === input.clientId)) throw new Error('Client not found');
  const subscription = { id: `sub_${crypto.randomUUID()}`, clientId: input.clientId, summary: String(input.summary || '').trim(), nextDate: input.nextDate, stopDate: input.stopDate || null, intervalCount: Number(input.intervalCount || 1), intervalUnit: input.intervalUnit || 'months', amount: Number(input.amount || 0), status: input.status || 'active', createdAt: new Date().toISOString() };
  if (!subscription.summary || !subscription.nextDate || !(subscription.intervalCount > 0) || !Number.isInteger(subscription.intervalCount) || !['days','weeks','months','years'].includes(subscription.intervalUnit) || subscription.amount < 0 || !['active','paused','ended'].includes(subscription.status)) throw new Error('Complete the subscription details with a valid recurrence and amount.');
  data.subscriptions.unshift(subscription); write(data); return subscription;
}

export function addExpense(input) {
  const data = read();
  if (input.clientId && !data.clients.some(client => client.id === input.clientId)) throw new Error('Client not found');
  const ticket=input.ticketId?data.tickets.find(item=>item.id===input.ticketId):null;if(input.ticketId&&!ticket)throw new Error('Ticket not found');if(ticket&&ticket.clientId!==input.clientId)throw new Error('Expense client must match the selected ticket company.');
  const expense = { id: nextNumber(data.expenses, 'EXP', 1), clientId: input.clientId || null, ticketId: input.ticketId || null, vendor: String(input.vendor || '').trim(), date: input.date, company: String(input.company || '').trim(), category: String(input.category || '').trim(), description: String(input.description || '').trim(), amount: Number(input.amount), tax: Number(input.tax || 0), status: input.status || 'unbilled', createdAt: new Date().toISOString() };
  if (!expense.vendor || !expense.date || !expense.description || !(expense.amount > 0) || expense.tax < 0 || !['unbilled','billed','reimbursed'].includes(expense.status)) throw new Error('Complete the expense details with a valid amount and status.');
  data.expenses.unshift(expense); write(data); return expense;
}

export function addTask(input, assigneeEmail) {
  const data = read();
  if (input.clientId && !data.clients.some(client => client.id === input.clientId)) throw new Error('Client not found');
  const task = { id: nextNumber(data.tasks, 'TSK', 1), clientId: input.clientId || null, title: String(input.title || '').trim(), description: String(input.description || '').trim(), dueDate: input.dueDate, assigneeEmail, completedAt: null, status: input.status || 'open', createdAt: new Date().toISOString() };
  if (!task.title || !task.dueDate || !['open','in_progress','completed','cancelled'].includes(task.status)) throw new Error('Complete the task title, due date, and status.');
  if (task.status === 'completed') task.completedAt = new Date().toISOString();
  data.tasks.unshift(task); write(data); return task;
}

export function addTicket(input) {
  const data=read(),client=data.clients.find(item=>item.id===input.clientId);if(!client)throw new Error('Company not found');
  const ticket={id:nextNumber(data.tickets,'TKT',1001),clientId:input.clientId,contactName:String(input.contactName||'').trim(),contactEmail:String(input.contactEmail||'').trim(),title:String(input.title||'').trim(),status:input.status||'open',billingType:input.billingType||'hourly',subscriptionId:input.subscriptionId||null,hourlyRate:Number(input.hourlyRate||client.hourlyRate||0),closedAt:null,notes:[],timeEntries:[],createdAt:new Date().toISOString(),updatedAt:new Date().toISOString()};
  if(!ticket.contactName||!ticket.title||!['open','in_progress','waiting_customer','waiting_vendor','closed'].includes(ticket.status)||!['hourly','subscription'].includes(ticket.billingType))throw new Error('Company, contact, title, billing method, and valid status are required.');if(ticket.billingType==='subscription'&&!data.subscriptions.some(item=>item.id===ticket.subscriptionId&&item.clientId===ticket.clientId))throw new Error('Choose a subscription belonging to this company.');if(ticket.status==='closed')ticket.closedAt=new Date().toISOString();data.tickets.unshift(ticket);write(data);return ticket;
}
export function updateTicketStatus(id,status){const data=read(),ticket=data.tickets.find(item=>item.id===id);if(!ticket)throw new Error('Ticket not found');if(!['open','in_progress','waiting_customer','waiting_vendor','closed'].includes(status))throw new Error('Choose a valid ticket status.');ticket.status=status;ticket.closedAt=status==='closed'?new Date().toISOString():null;ticket.updatedAt=new Date().toISOString();write(data);return ticket;}
export function addTicketNote(id,input,email){const data=read(),ticket=data.tickets.find(item=>item.id===id),body=String(input.body||'').trim(),visibility=input.visibility||'public';if(!ticket)throw new Error('Ticket not found');if(!body||!['public','private'].includes(visibility))throw new Error('Enter a note and choose its visibility.');const note={id:`tn_${crypto.randomUUID()}`,authorEmail:email,visibility,body,createdAt:new Date().toISOString()};ticket.notes.push(note);ticket.updatedAt=note.createdAt;write(data);return note;}
export function addTicketTime(id,input,email){const data=read(),ticket=data.tickets.find(item=>item.id===id),minutes=Number(input.minutes);if(!ticket)throw new Error('Ticket not found');if(!Number.isInteger(minutes)||minutes<=0)throw new Error('Enter time in whole minutes greater than zero.');const entry={id:`tt_${crypto.randomUUID()}`,technicianEmail:email,minutes,description:String(input.description||'').trim(),createdAt:new Date().toISOString()};ticket.timeEntries.push(entry);ticket.updatedAt=entry.createdAt;write(data);return entry;}

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
