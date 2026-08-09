ALTER TABLE tickets ADD COLUMN category TEXT NOT NULL DEFAULT 'general';

CREATE TABLE IF NOT EXISTS ticket_boards (
  id TEXT NOT NULL,
  workspace_id TEXT NOT NULL,
  name TEXT NOT NULL,
  position INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (workspace_id, id),
  UNIQUE (workspace_id, name),
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ticket_categories (
  id TEXT NOT NULL,
  workspace_id TEXT NOT NULL,
  board_id TEXT NOT NULL,
  name TEXT NOT NULL,
  position INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (workspace_id, id),
  UNIQUE (workspace_id, board_id, name),
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_ticket_boards_workspace ON ticket_boards(workspace_id, position, name);
CREATE INDEX IF NOT EXISTS idx_ticket_categories_board ON ticket_categories(workspace_id, board_id, position, name);
CREATE INDEX IF NOT EXISTS idx_tickets_category ON tickets(workspace_id, board, category);
