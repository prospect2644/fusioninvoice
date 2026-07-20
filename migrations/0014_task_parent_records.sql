ALTER TABLE tasks ADD COLUMN invoice_id TEXT REFERENCES invoices(id) ON DELETE CASCADE;
ALTER TABLE tasks ADD COLUMN ticket_id TEXT REFERENCES tickets(id) ON DELETE CASCADE;

CREATE INDEX IF NOT EXISTS idx_tasks_invoice ON tasks(invoice_id);
CREATE INDEX IF NOT EXISTS idx_tasks_ticket ON tasks(ticket_id);
