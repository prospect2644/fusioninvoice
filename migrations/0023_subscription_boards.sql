CREATE TABLE IF NOT EXISTS subscription_boards (
  workspace_id TEXT NOT NULL,
  subscription_id TEXT NOT NULL,
  board_id TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (workspace_id, subscription_id, board_id),
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_subscription_boards_board ON subscription_boards(workspace_id, board_id, subscription_id);

INSERT OR IGNORE INTO subscription_boards (workspace_id, subscription_id, board_id)
SELECT workspace_id, id, 'technical_support' FROM subscriptions WHERE managed_it = 1;
