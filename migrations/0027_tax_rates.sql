CREATE TABLE IF NOT EXISTS tax_rates (
  id TEXT NOT NULL,
  workspace_id TEXT NOT NULL,
  name TEXT NOT NULL,
  percent REAL NOT NULL DEFAULT 0 CHECK (percent >= 0 AND percent <= 100),
  company_profile TEXT NOT NULL DEFAULT '',
  category TEXT NOT NULL DEFAULT 'sales_tax',
  included INTEGER NOT NULL DEFAULT 0 CHECK (included IN (0, 1)),
  compound INTEGER NOT NULL DEFAULT 0 CHECK (compound IN (0, 1)),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (workspace_id, id),
  UNIQUE (workspace_id, name),
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_tax_rates_workspace_name
  ON tax_rates(workspace_id, name);
