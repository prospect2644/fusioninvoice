import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './styles.css';

const money = n => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
const icons = { Overview: '⌂', Invoices: '↗', Clients: '◌', Estimates: '◇', Payments: '✓', Recurring: '↻', Settings: '⚙' };
const nav = Object.keys(icons);

function App() {
  const [data, setData] = useState(null);
  const [page, setPage] = useState('Overview');
  const [modal, setModal] = useState(null);
  const load = () => fetch('/api/workspace').then(async r => { if (!r.ok) throw new Error((await r.json()).error); return r.json(); }).then(setData);
  useEffect(() => { load().catch(e => setData({ error: e.message })); }, []);
  if (!data) return <div className="loading"><span>Kindred</span><p>Preparing your workspace…</p></div>;
  if (data.error) return <div className="auth-error"><p>Protected workspace</p><h1>Your identity could not be verified.</h1><span>{data.error}</span></div>;
  return <div className="shell">
    <aside>
      <div className="brand"><div className="mark">ki</div><div><strong>Kindred</strong><span>Invoice care</span></div></div>
      <nav>{nav.map(item => <button key={item} className={page === item ? 'active' : ''} onClick={() => setPage(item)}><i>{icons[item]}</i>{item}</button>)}</nav>
      <div className="aside-note"><small>Careful systems</small><p>Your financial work, held clearly and securely.</p><span>Protected by Zero Trust</span></div>
      <div className="user"><div>{data.user.email[0].toUpperCase()}</div><span><strong>{data.user.name}</strong><small>{data.user.email}</small></span></div>
    </aside>
    <main>
      <header><div><small>Financial workspace</small><h1>{page}</h1></div><div className="header-actions"><button className="quiet">⌕</button><button className="primary" onClick={() => setModal(page === 'Clients' ? 'client' : 'invoice')}>＋ {page === 'Clients' ? 'New client' : 'New invoice'}</button></div></header>
      {page === 'Overview' ? <Dashboard data={data} setPage={setPage} /> : page === 'Invoices' ? <Invoices data={data} /> : page === 'Clients' ? <Clients data={data} /> : <Placeholder page={page} />}
    </main>
    {modal && <Modal type={modal} clients={data.clients} close={() => setModal(null)} saved={() => { setModal(null); load(); }} />}
  </div>;
}

function Dashboard({ data, setPage }) {
  const totals = useMemo(() => ({ outstanding: data.invoices.filter(i => ['sent','overdue'].includes(i.status)).reduce((a,b)=>a+b.amount,0), paid: data.invoices.filter(i=>i.status==='paid').reduce((a,b)=>a+b.amount,0), overdue: data.invoices.filter(i=>i.status==='overdue').reduce((a,b)=>a+b.amount,0) }), [data]);
  return <>
    <section className="intro"><div><p>Monday, July 13</p><h2>A clear view of what’s moving.</h2><span>Welcome back. Here is the financial rhythm of your practice.</span></div><div className="orb"><span>72%</span><small>collected</small></div></section>
    <section className="stats"><Stat label="Outstanding" value={money(totals.outstanding)} note="Across 2 open invoices" tone="sage"/><Stat label="Collected this month" value={money(totals.paid)} note="Healthy and on pace" tone="ink"/><Stat label="Overdue" value={money(totals.overdue)} note="One invoice needs care" tone="clay"/></section>
    <section className="grid"><div className="panel wide"><div className="panel-head"><div><small>Cash flow</small><h3>Revenue rhythm</h3></div><select><option>Last 6 months</option></select></div><Chart/><div className="legend"><span><i className="dot solid"/>Collected</span><span><i className="dot pale"/>Invoiced</span></div></div><div className="panel"><div className="panel-head"><div><small>Gentle attention</small><h3>To tend next</h3></div></div><div className="task"><b>01</b><span><strong>Follow up with Hearth</strong><small>Invoice INV-1046 · 3 days overdue</small></span></div><div className="task"><b>02</b><span><strong>Review recurring care</strong><small>2 retainers renew next week</small></span></div><button className="text-btn" onClick={()=>setPage('Invoices')}>View all invoices →</button></div></section>
    <Recent data={data}/>
  </>;
}
function Stat({label,value,note,tone}) { return <div className={`stat ${tone}`}><small>{label}</small><strong>{value}</strong><span>{note}</span></div>; }
function Chart(){ return <div className="chart"><div className="y"><span>$4k</span><span>$3k</span><span>$2k</span><span>$1k</span><span>$0</span></div><svg viewBox="0 0 700 190" preserveAspectRatio="none"><defs><linearGradient id="fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stopColor="#536a5a" stopOpacity=".23"/><stop offset="1" stopColor="#536a5a" stopOpacity="0"/></linearGradient></defs><path className="area" d="M0,150 C80,120 80,132 150,105 S250,125 310,74 S410,105 470,56 S580,78 700,28 L700,190 L0,190Z"/><path className="line pale-line" d="M0,125 C70,115 115,95 170,104 S280,76 340,86 S465,48 520,64 S620,38 700,45"/><path className="line solid-line" d="M0,150 C80,120 80,132 150,105 S250,125 310,74 S410,105 470,56 S580,78 700,28"/></svg><div className="months"><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span><span>Jul</span></div></div>; }
function Recent({data}) { return <section className="panel recent"><div className="panel-head"><div><small>Recent work</small><h3>Latest invoices</h3></div><button className="text-btn">View all →</button></div><InvoiceTable data={data}/></section>; }
function InvoiceTable({data}) { return <div className="table"><div className="tr th"><span>Invoice</span><span>Client</span><span>Due</span><span>Status</span><span>Amount</span></div>{data.invoices.map(i => { const c=data.clients.find(c=>c.id===i.clientId); return <div className="tr" key={i.id}><span><strong>{i.id}</strong><small>{i.description}</small></span><span>{c?.name}</span><span>{i.due}</span><span><em className={`status ${i.status}`}>{i.status}</em></span><span><strong>{money(i.amount)}</strong></span></div>})}</div>; }
function Invoices({data}) { return <section className="panel page-panel"><div className="toolbar"><div className="tabs"><button className="selected">All</button><button>Draft</button><button>Sent</button><button>Paid</button><button>Overdue</button></div><input placeholder="Search invoices"/></div><InvoiceTable data={data}/></section>; }
function Clients({data}) { return <section className="cards">{data.clients.map(c=><article className="client" key={c.id}><div className="client-top"><div>{c.name[0]}</div><em>Active</em></div><h3>{c.name}</h3><p>{c.contact}</p><span>{c.email}</span><footer><small>Client balance</small><strong>{money(data.invoices.filter(i=>i.clientId===c.id && i.status!=='paid').reduce((a,b)=>a+b.amount,0))}</strong></footer></article>)}</section>; }
function Placeholder({page}) { return <section className="empty"><span>{icons[page]}</span><small>Connected workflow</small><h2>{page} are ready to shape.</h2><p>This module is included in the application shell and ready for your business rules, payment provider, and document templates.</p><button className="primary">Configure {page.toLowerCase()}</button></section>; }

function Modal({type,clients,close,saved}) {
  const [busy,setBusy]=useState(false); const submit=async e=>{e.preventDefault();setBusy(true);const body=Object.fromEntries(new FormData(e.currentTarget));const r=await fetch(`/api/${type==='client'?'clients':'invoices'}`,{method:'POST',headers:{'content-type':'application/json'},body:JSON.stringify(body)});setBusy(false);if(r.ok)saved();};
  return <div className="modal-bg" onMouseDown={e=>e.target===e.currentTarget&&close()}><form className="modal" onSubmit={submit}><div className="modal-head"><div><small>New record</small><h2>{type==='client'?'Welcome a client':'Create an invoice'}</h2></div><button type="button" onClick={close}>×</button></div>{type==='client'?<><label>Organization<input name="name" required placeholder="Willow Yoga & Sound"/></label><label>Contact name<input name="contact" required placeholder="Maya Chen"/></label><label>Email<input name="email" type="email" required placeholder="maya@example.com"/></label></>:<><label>Client<select name="clientId" required>{clients.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}</select></label><div className="two"><label>Issued<input name="issued" type="date" required defaultValue="2026-07-13"/></label><label>Due<input name="due" type="date" required defaultValue="2026-07-27"/></label></div><label>Description<input name="description" required placeholder="Monthly systems care"/></label><label>Amount<input name="amount" type="number" min="0" step="0.01" required placeholder="1200"/></label></>}<div className="modal-actions"><button type="button" className="quiet" onClick={close}>Cancel</button><button className="primary" disabled={busy}>{busy?'Saving…':'Save record'}</button></div></form></div>;
}

createRoot(document.getElementById('root')).render(<App/>);
