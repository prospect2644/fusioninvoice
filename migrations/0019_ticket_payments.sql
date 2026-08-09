CREATE TABLE IF NOT EXISTS ticket_payments (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  ticket_id TEXT NOT NULL,
  payment_date TEXT NOT NULL,
  method TEXT NOT NULL CHECK (method IN ('cash', 'check', 'credit_card')),
  amount_cents INTEGER NOT NULL CHECK (amount_cents > 0),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_ticket_payments_ticket
ON ticket_payments(workspace_id, ticket_id, payment_date);
