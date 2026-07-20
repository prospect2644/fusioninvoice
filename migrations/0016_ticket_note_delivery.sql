CREATE TABLE IF NOT EXISTS ticket_email_preferences (
  workspace_id TEXT NOT NULL,
  ticket_id TEXT NOT NULL,
  recipient_emails TEXT NOT NULL DEFAULT '[]',
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (workspace_id, ticket_id),
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ticket_note_delivery (
  note_id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  ticket_id TEXT NOT NULL,
  send_to_client INTEGER NOT NULL DEFAULT 0 CHECK (send_to_client IN (0,1)),
  recipient_emails TEXT NOT NULL DEFAULT '[]',
  time_entry_id TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (note_id) REFERENCES ticket_notes(id) ON DELETE CASCADE,
  FOREIGN KEY (time_entry_id) REFERENCES ticket_time_entries(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_ticket_note_delivery_ticket ON ticket_note_delivery(ticket_id, created_at);
