ALTER TABLE expenses ADD COLUMN ticket_id TEXT REFERENCES tickets(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_expenses_ticket ON expenses(ticket_id);
