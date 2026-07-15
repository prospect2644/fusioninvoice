CREATE TABLE IF NOT EXISTS subscriptions (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  client_id TEXT NOT NULL,
  summary TEXT NOT NULL,
  next_date TEXT NOT NULL,
  stop_date TEXT,
  interval_count INTEGER NOT NULL DEFAULT 1 CHECK (interval_count > 0),
  interval_unit TEXT NOT NULL DEFAULT 'months' CHECK (interval_unit IN ('days', 'weeks', 'months', 'years')),
  amount_cents INTEGER NOT NULL CHECK (amount_cents >= 0),
  status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'paused', 'ended')),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_subscriptions_workspace_date ON subscriptions(workspace_id, next_date);
CREATE INDEX IF NOT EXISTS idx_subscriptions_client ON subscriptions(client_id);
