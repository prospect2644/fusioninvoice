CREATE TABLE IF NOT EXISTS items (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  name TEXT NOT NULL,
  company TEXT NOT NULL DEFAULT '',
  category TEXT NOT NULL DEFAULT '',
  description TEXT NOT NULL DEFAULT '',
  stock_quantity REAL NOT NULL DEFAULT 0 CHECK (stock_quantity >= 0),
  price_cents INTEGER NOT NULL DEFAULT 0 CHECK (price_cents >= 0),
  tax_1 REAL NOT NULL DEFAULT 0 CHECK (tax_1 >= 0),
  tax_2 REAL NOT NULL DEFAULT 0 CHECK (tax_2 >= 0),
  status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_items_workspace_name ON items(workspace_id, name);
CREATE INDEX IF NOT EXISTS idx_items_workspace_category ON items(workspace_id, category);
