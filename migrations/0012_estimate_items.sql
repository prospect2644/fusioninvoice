CREATE TABLE IF NOT EXISTS estimate_items (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  estimate_id TEXT NOT NULL,
  description TEXT NOT NULL,
  quantity REAL NOT NULL CHECK (quantity > 0),
  rate_cents INTEGER NOT NULL CHECK (rate_cents >= 0),
  position INTEGER NOT NULL DEFAULT 0,
  source_type TEXT CHECK (source_type IN ('item', 'subscription')),
  source_id TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_estimate_items_estimate ON estimate_items(workspace_id, estimate_id, position);
