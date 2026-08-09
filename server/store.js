import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const dataFile = path.resolve('server/data.json');
const defaultTicketBoards=[{id:'technical_support',name:'Technical Support',position:0},{id:'projects',name:'Projects',position:1},{id:'maintenance',name:'Maintenance',position:2}],defaultTicketCategories=[{id:'general',boardId:'technical_support',name:'General',position:0},{id:'project_work',boardId:'projects',name:'Project work',position:0},{id:'scheduled_maintenance',boardId:'maintenance',name:'Scheduled maintenance',position:0}];
const emptyStore = { clients: [], invoices: [], estimates: [], payments: [], items: [], subscriptions: [], subscriptionInvoiceRuns: [], expenses: [], tasks: [], tickets: [], documentFolders: [], documents: [], settings: { customFields: [],ticketBoards:defaultTicketBoards,ticketCategories:defaultTicketCategories } };

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

const advanceBillingDate=(value,count,unit)=>{const [year,month,day]=String(value||'').slice(0,10).split('-').map(Number),amount=Number(count);if(!year||!month||!day||!Number.isInteger(amount)||amount<1)return'';if(unit==='days'||unit==='weeks'){const date=new Date(Date.UTC(year,month-1,day));date.setUTCDate(date.getUTCDate()+amount*(unit==='weeks'?7:1));return date.toISOString().slice(0,10)}const offset=unit==='years'?amount*12:amount,index=year*12+month-1+offset,targetYear=Math.floor(index/12),targetMonth=index%12,last=new Date(Date.UTC(targetYear,targetMonth+1,0)).getUTCDate();return`${String(targetYear).padStart(4,'0')}-${String(targetMonth+1).padStart(2,'0')}-${String(Math.min(day,last)).padStart(2,'0')}`};
function generateLocalSubscriptionInvoices(data,through=new Date().toISOString().slice(0,10)){for(const subscription of data.subscriptions.filter(item=>item.status==='active'&&item.nextDate<=through)){let scheduled=subscription.nextDate,cycles=0;while(scheduled&&scheduled<=through&&cycles++<120){if(subscription.stopDate&&scheduled>subscription.stopDate){subscription.status='ended';break}const exists=data.subscriptionInvoiceRuns.some(run=>run.subscriptionId===subscription.id&&run.scheduledDate===scheduled),next=advanceBillingDate(scheduled,subscription.intervalCount,subscription.intervalUnit),ended=Boolean(subscription.stopDate&&next>subscription.stopDate);if(!exists){const invoiceId=nextNumber(data.invoices,'INV'),item={id:`item_${crypto.randomUUID()}`,description:subscription.summary,quantity:1,rate:Number(subscription.amount),position:0,sourceType:'subscription',sourceId:subscription.id};data.invoices.unshift({id:invoiceId,clientId:subscription.clientId,issued:scheduled,due:scheduled,description:subscription.summary,amount:Number(subscription.amount),status:'sent',items:[item],customFields:{},createdAt:new Date().toISOString()});data.subscriptionInvoiceRuns.push({id:`sir_${crypto.randomUUID()}`,subscriptionId:subscription.id,scheduledDate:scheduled,invoiceId})}subscription.nextDate=next;subscription.status=ended?'ended':'active';subscription.updatedAt=new Date().toISOString();scheduled=next;if(ended)break}}}
export function workspaceFor(email) {
  const data=read();generateLocalSubscriptionInvoices(data);write(data);return { ...decorate(data), user: { email, name: email.split('@')[0].replace(/[._-]/g, ' ') } };
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

export function removePayment(id) {
  const data = read(), payment = data.payments.find(item => item.id === id);
  if (!payment) throw new Error('Payment not found.');
  const invoice = data.invoices.find(item => item.id === payment.invoiceId);
  data.payments = data.payments.filter(item => item.id !== id);
  if (invoice?.status === 'paid') invoice.status = invoice.due < new Date().toISOString().slice(0,10) ? 'overdue' : 'sent';
  write(data);
  return { id, invoiceId: payment.invoiceId, deleted: true };
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
  const subscription = { id: `sub_${crypto.randomUUID()}`, clientId: input.clientId, summary: String(input.summary || '').trim(), nextDate: input.nextDate, stopDate: input.stopDate || null, intervalCount: Number(input.intervalCount || 1), intervalUnit: input.intervalUnit || 'months', amount: Number(input.amount || 0),managedIt:input.managedIt==='on'||input.managedIt===true,boardIds:Array.isArray(input.boardIds)?[...new Set(input.boardIds.map(String))]:[],hourlyAllotment:Number(input.hourlyAllotment||0), status: input.status || 'active', createdAt: new Date().toISOString() };
  if (!subscription.summary || !subscription.nextDate || !(subscription.intervalCount > 0) || !Number.isInteger(subscription.intervalCount) || !['days','weeks','months','years'].includes(subscription.intervalUnit) || subscription.amount < 0||!Number.isFinite(subscription.hourlyAllotment)||subscription.hourlyAllotment<0 || !['active','paused','ended'].includes(subscription.status)||subscription.managedIt&&(!subscription.boardIds.length||subscription.boardIds.some(id=>!data.settings.ticketBoards.some(board=>board.id===id)))) throw new Error('Complete the subscription details and select at least one valid covered ticket board.');
  data.subscriptions.unshift(subscription); generateLocalSubscriptionInvoices(data); write(data); return subscription;
}

export function updateSubscription(id,input){const data=read(),subscription=data.subscriptions.find(item=>item.id===id);if(!subscription)throw new Error('Subscription not found.');if(!data.clients.some(client=>client.id===input.clientId))throw new Error('Client not found.');const updated={clientId:input.clientId,summary:String(input.summary||'').trim(),nextDate:input.nextDate,stopDate:input.stopDate||null,intervalCount:Number(input.intervalCount||1),intervalUnit:input.intervalUnit||'months',amount:Number(input.amount||0),managedIt:input.managedIt==='on'||input.managedIt===true,boardIds:Array.isArray(input.boardIds)?[...new Set(input.boardIds.map(String))]:[],hourlyAllotment:Number(input.hourlyAllotment||0),status:input.status||'active'};if(!updated.summary||!updated.nextDate||!(updated.intervalCount>0)||!Number.isInteger(updated.intervalCount)||!['days','weeks','months','years'].includes(updated.intervalUnit)||updated.amount<0||!Number.isFinite(updated.hourlyAllotment)||updated.hourlyAllotment<0||!['active','paused','ended'].includes(updated.status)||updated.managedIt&&(!updated.boardIds.length||updated.boardIds.some(id=>!data.settings.ticketBoards.some(board=>board.id===id))))throw new Error('Complete the subscription details and select at least one valid covered ticket board.');Object.assign(subscription,updated,{updatedAt:new Date().toISOString()});generateLocalSubscriptionInvoices(data);write(data);return subscription;}
export function removeSubscription(id){const data=read(),subscription=data.subscriptions.find(item=>item.id===id);if(!subscription)throw new Error('Subscription not found.');data.tickets.filter(ticket=>ticket.subscriptionId===id).forEach(ticket=>{ticket.subscriptionId=null;ticket.billingType='hourly';ticket.updatedAt=new Date().toISOString()});data.subscriptions=data.subscriptions.filter(item=>item.id!==id);write(data);return{id,deleted:true,clientId:subscription.clientId};}

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
  const parentType=String(input.parentType||''),parentId=String(input.parentId||''),parent=parentType==='invoice'?data.invoices.find(item=>item.id===parentId):parentType==='ticket'?data.tickets.find(item=>item.id===parentId):null;
  if(!parent)throw new Error('Choose an existing invoice or ticket for this task.');
  const task = { id: nextNumber(data.tasks, 'TSK', 1), clientId: parent.clientId, invoiceId: parentType==='invoice'?parentId:null, ticketId: parentType==='ticket'?parentId:null, title: String(input.title || '').trim(), description: String(input.description || '').trim(), dueDate: input.dueDate, assigneeEmail, completedAt: null, status: input.status || 'open', createdAt: new Date().toISOString() };
  if (!task.title || !task.dueDate || !['open','in_progress','completed','cancelled'].includes(task.status)) throw new Error('Complete the task title, due date, and status.');
  if (task.status === 'completed') task.completedAt = new Date().toISOString();
  data.tasks.unshift(task); write(data); return task;
}

export function addTicket(input) {
  const data=read(),client=data.clients.find(item=>item.id===input.clientId);if(!client)throw new Error('Company not found');
  const board=input.board||'technical_support',category=String(input.category||''),remaining=item=>Number(item.hourlyAllotment||0)*60-data.tickets.filter(ticket=>ticket.subscriptionId===item.id).flatMap(ticket=>ticket.timeEntries||[]).reduce((sum,entry)=>sum+Number(entry.minutes||0),0),managed=data.subscriptions.find(item=>item.clientId===input.clientId&&item.managedIt&&(item.boardIds||[]).includes(board)&&item.status==='active'&&remaining(item)>0),ticket={id:nextNumber(data.tickets,'TKT',1001),clientId:input.clientId,contactName:String(input.contactName||'').trim(),contactEmail:String(input.contactEmail||'').trim(),title:String(input.title||'').trim(),board,category,status:input.status||'open',billingType:managed?'subscription':input.billingType||'hourly',subscriptionId:managed?.id||input.subscriptionId||null,hourlyRate:Number(input.hourlyRate||client.hourlyRate||0),closedAt:null,notes:[],timeEntries:[],createdAt:new Date().toISOString(),updatedAt:new Date().toISOString()};
  if(!ticket.contactName||!ticket.title||!data.settings.ticketBoards.some(item=>item.id===board)||!data.settings.ticketCategories.some(item=>item.id===category&&item.boardId===board)||!['open','in_progress','waiting_customer','waiting_vendor','closed'].includes(ticket.status)||!['hourly','subscription'].includes(ticket.billingType))throw new Error('Company, contact, title, board, category, billing method, and valid status are required.');if(ticket.billingType==='subscription'&&!data.subscriptions.some(item=>item.id===ticket.subscriptionId&&item.clientId===ticket.clientId))throw new Error('Choose a subscription belonging to this company.');if(ticket.status==='closed')ticket.closedAt=new Date().toISOString();data.tickets.unshift(ticket);write(data);return ticket;
}
export function updateTicketStatus(id,status){const data=read(),ticket=data.tickets.find(item=>item.id===id);if(!ticket)throw new Error('Ticket not found');if(!['open','in_progress','waiting_customer','waiting_vendor','closed'].includes(status))throw new Error('Choose a valid ticket status.');ticket.status=status;ticket.closedAt=status==='closed'?new Date().toISOString():null;ticket.updatedAt=new Date().toISOString();write(data);return ticket;}
export function updateTicketBilling(id,input){const data=read(),ticket=data.tickets.find(item=>item.id===id),target=String(input.billingType||''),subscriptionId=String(input.subscriptionId||'')||null;if(!ticket)throw new Error('Ticket not found');if(!['hourly','subscription'].includes(target))throw new Error('Choose hourly or subscription billing.');if(target==='subscription'){if(!data.subscriptions.some(item=>item.id===subscriptionId&&item.clientId===ticket.clientId&&item.status==='active'))throw new Error('Choose an active subscription belonging to this company.');Object.assign(ticket,{billingType:'subscription',subscriptionId,subscriptionCoveredMinutes:0,updatedAt:new Date().toISOString()});write(data);return ticket}if(ticket.billingType==='subscription'&&ticket.subscriptionId){const subscription=data.subscriptions.find(item=>item.id===ticket.subscriptionId),allotment=Number(subscription?.hourlyAllotment||0)*60,entries=data.tickets.filter(item=>item.subscriptionId===ticket.subscriptionId).flatMap((item,ticketIndex)=>(item.timeEntries||[]).map((entry,entryIndex)=>({...entry,ticketId:item.id,order:`${entry.createdAt||''}:${entry.id||entryIndex}:${ticketIndex}`}))).sort((a,b)=>a.order.localeCompare(b.order));let used=0,ownOverage=0;for(const entry of entries){const minutes=Number(entry.minutes||0),before=Math.max(0,used-allotment);used+=minutes;const overtime=Math.max(0,used-allotment)-before;if(entry.ticketId===ticket.id)ownOverage+=overtime}const own=(ticket.timeEntries||[]).reduce((sum,entry)=>sum+Number(entry.minutes||0),0);Object.assign(ticket,{billingType:'hourly',subscriptionCoveredMinutes:Math.max(0,own-ownOverage),updatedAt:new Date().toISOString()});write(data);return ticket}Object.assign(ticket,{billingType:'hourly',subscriptionId:null,subscriptionCoveredMinutes:0,updatedAt:new Date().toISOString()});write(data);return ticket;}
export function addTicketNote(id,input,email){const data=read(),ticket=data.tickets.find(item=>item.id===id),body=String(input.body||'').trim(),visibility=input.visibility||'public',minutes=Number(input.minutes||0),sendToClient=visibility==='public'&&(input.sendToClient==='on'||input.sendToClient===true),recipients=[...new Set(String(input.recipientEmails||'').split(/[;,\s]+/).map(value=>value.trim().toLowerCase()).filter(Boolean))];if(!ticket)throw new Error('Ticket not found');if(!body||!['public','private'].includes(visibility))throw new Error('Enter a note and choose its visibility.');if(minutes&&!Number.isInteger(minutes)||minutes<0)throw new Error('Enter time in whole minutes.');if(sendToClient&&(!recipients.length||recipients.some(value=>!/^\S+@\S+\.\S+$/.test(value))))throw new Error('Enter at least one valid client email address.');const createdAt=new Date().toISOString(),timeEntry=minutes?{id:`tt_${crypto.randomUUID()}`,technicianEmail:email,minutes,description:String(input.timeDescription||body).trim(),noteId:null,createdAt}:null,note={id:`tn_${crypto.randomUUID()}`,authorEmail:email,visibility,body,sendToClient,recipientEmails:sendToClient?recipients:[],timeEntryId:timeEntry?.id||null,createdAt};if(timeEntry){timeEntry.noteId=note.id;ticket.timeEntries.push(timeEntry)}if(visibility==='public'&&recipients.length)ticket.notificationEmails=recipients;ticket.notes.push(note);ticket.updatedAt=createdAt;write(data);return note;}
export function addTicketTime(id,input,email){const data=read(),ticket=data.tickets.find(item=>item.id===id),minutes=Number(input.minutes);if(!ticket)throw new Error('Ticket not found');if(!Number.isInteger(minutes)||minutes<=0)throw new Error('Enter time in whole minutes greater than zero.');const entry={id:`tt_${crypto.randomUUID()}`,technicianEmail:email,minutes,description:String(input.description||'').trim(),createdAt:new Date().toISOString()};ticket.timeEntries.push(entry);ticket.updatedAt=entry.createdAt;write(data);return entry;}
export function updateTicketTime(id,input){const data=read(),ticket=data.tickets.find(item=>item.timeEntries?.some(entry=>entry.id===id)),entry=ticket?.timeEntries.find(item=>item.id===id),minutes=Number(input.minutes),description=String(input.description||'').trim();if(!entry)throw new Error('Time entry not found');if(!Number.isInteger(minutes)||minutes<=0)throw new Error('Enter time in whole minutes greater than zero.');if(description.length>500)throw new Error('Keep the time-entry description under 500 characters.');entry.minutes=minutes;entry.description=description;ticket.updatedAt=new Date().toISOString();write(data);return entry;}
export function removeTicketTime(id){const data=read(),ticket=data.tickets.find(item=>item.timeEntries?.some(entry=>entry.id===id));if(!ticket)throw new Error('Time entry not found');ticket.timeEntries=ticket.timeEntries.filter(entry=>entry.id!==id);ticket.notes?.forEach(note=>{if(note.timeEntryId===id)note.timeEntryId=null});ticket.updatedAt=new Date().toISOString();write(data);return {id,deleted:true};}

export function updateInvoiceStatus(id, status) {
  const data = read();
  const invoice = data.invoices.find(item => item.id === id);
  if (!invoice) throw new Error('Invoice not found');
  if (!['draft', 'sent', 'paid', 'overdue', 'void'].includes(status)) throw new Error('Choose a valid invoice status.');
  invoice.status = status;
  write(data);
  return { id, status };
}

export function removeInvoice(id) {
  const data = read(), invoice = data.invoices.find(item => item.id === id);
  if (!invoice) throw new Error('Invoice not found.');
  data.payments = data.payments.filter(item => item.invoiceId !== id);
  data.tasks = data.tasks.filter(item => item.invoiceId !== id);
  data.estimates.forEach(estimate => { if (estimate.invoiceId === id || estimate.convertedInvoiceId === id) { estimate.invoiceId = null; estimate.convertedInvoiceId = null; if (estimate.status === 'converted') estimate.status = 'draft'; } });
  data.invoices = data.invoices.filter(item => item.id !== id);
  write(data);
  return { id, deleted: true };
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

export function addTicketBoard(input){const data=read(),name=String(input.name||'').trim();if(!name||name.length>80)throw new Error('Enter a board name up to 80 characters.');if(data.settings.ticketBoards.some(item=>item.name.toLowerCase()===name.toLowerCase()))throw new Error('A ticket board with that name already exists.');const id=`${name.toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'')||'board'}_${crypto.randomUUID().slice(0,8)}`,board={id,name,position:data.settings.ticketBoards.length};data.settings.ticketBoards.push(board);write(data);return board;}
export function addTicketCategory(input){const data=read(),name=String(input.name||'').trim(),boardId=String(input.boardId||'');if(!data.settings.ticketBoards.some(item=>item.id===boardId))throw new Error('Ticket board not found.');if(!name||name.length>80)throw new Error('Enter a category name up to 80 characters.');if(data.settings.ticketCategories.some(item=>item.boardId===boardId&&item.name.toLowerCase()===name.toLowerCase()))throw new Error('That category already exists on this board.');const id=`${name.toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'')||'category'}_${crypto.randomUUID().slice(0,8)}`,category={id,boardId,name,position:data.settings.ticketCategories.filter(item=>item.boardId===boardId).length};data.settings.ticketCategories.push(category);write(data);return category;}
export function removeTicketBoard(id){const data=read();if(['technical_support','projects','maintenance'].includes(id))throw new Error('Default ticket boards cannot be deleted.');if(data.tickets.some(item=>item.board===id))throw new Error('Move tickets off this board before deleting it.');data.settings.ticketBoards=data.settings.ticketBoards.filter(item=>item.id!==id);data.settings.ticketCategories=data.settings.ticketCategories.filter(item=>item.boardId!==id);write(data);return{id,deleted:true};}
export function removeTicketCategory(id){const data=read();if(['general','project_work','scheduled_maintenance'].includes(id))throw new Error('Default ticket categories cannot be deleted.');if(data.tickets.some(item=>item.category===id))throw new Error('Move tickets out of this category before deleting it.');data.settings.ticketCategories=data.settings.ticketCategories.filter(item=>item.id!==id);write(data);return{id,deleted:true};}

const cleanDocumentHtml=value=>String(value||'').replace(/<\/?(?:script|style|iframe|object|embed|form|input|button)[^>]*>/gi,'').replace(/\son\w+\s*=\s*(?:"[^"]*"|'[^']*'|[^\s>]+)/gi,'').replace(/\s(?:style|class|id)\s*=\s*(?:"[^"]*"|'[^']*'|[^\s>]+)/gi,'').replace(/href\s*=\s*["']\s*(?:javascript|data):[^"']*["']/gi,'href="#"');
export function addDocumentFolder(input){const data=read(),name=String(input.name||'').trim();if(!name||name.length>100)throw new Error('Enter a folder name up to 100 characters.');const folder={id:`folder_${crypto.randomUUID()}`,name,parentId:null,createdAt:new Date().toISOString(),updatedAt:new Date().toISOString()};data.documentFolders.push(folder);write(data);return folder;}
export function addDocument(input,email){const data=read(),title=String(input.title||'Untitled document').trim(),folderId=String(input.folderId||'')||null;if(!title||title.length>180)throw new Error('Enter a document title up to 180 characters.');if(folderId&&!data.documentFolders.some(folder=>folder.id===folderId))throw new Error('Folder not found.');const document={id:`doc_${crypto.randomUUID()}`,folderId,title,contentHtml:cleanDocumentHtml(input.contentHtml),createdByEmail:email,createdAt:new Date().toISOString(),updatedAt:new Date().toISOString()};data.documents.unshift(document);write(data);return document;}
export function updateDocument(id,input){const data=read(),document=data.documents.find(item=>item.id===id);if(!document)throw new Error('Document not found.');const title=String(input.title||'').trim(),folderId=String(input.folderId||'')||null;if(!title||title.length>180)throw new Error('Enter a document title up to 180 characters.');if(folderId&&!data.documentFolders.some(folder=>folder.id===folderId))throw new Error('Folder not found.');Object.assign(document,{title,folderId,contentHtml:cleanDocumentHtml(input.contentHtml),updatedAt:new Date().toISOString()});write(data);return document;}
export function removeDocument(id){const data=read(),before=data.documents.length;data.documents=data.documents.filter(item=>item.id!==id);if(before===data.documents.length)throw new Error('Document not found.');write(data);return{id,deleted:true};}
export function removeDocumentFolder(id){const data=read();if(data.documents.some(item=>item.folderId===id))throw new Error('Move or delete the documents in this folder first.');const before=data.documentFolders.length;data.documentFolders=data.documentFolders.filter(item=>item.id!==id);if(before===data.documentFolders.length)throw new Error('Folder not found.');write(data);return{id,deleted:true};}
