CREATE TABLE IF NOT EXISTS expenses (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  client_id TEXT,
  vendor TEXT NOT NULL,
  expense_date TEXT NOT NULL,
  company TEXT NOT NULL DEFAULT '',
  category TEXT NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  amount_cents INTEGER NOT NULL CHECK (amount_cents > 0),
  tax_cents INTEGER NOT NULL DEFAULT 0 CHECK (tax_cents >= 0),
  status TEXT NOT NULL DEFAULT 'unbilled' CHECK (status IN ('unbilled', 'billed', 'reimbursed')),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_expenses_workspace_date ON expenses(workspace_id, expense_date);
CREATE INDEX IF NOT EXISTS idx_expenses_workspace_vendor ON expenses(workspace_id, vendor);
CREATE INDEX IF NOT EXISTS idx_expenses_client ON expenses(client_id);
