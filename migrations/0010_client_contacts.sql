CREATE TABLE IF NOT EXISTS client_contacts (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  client_id TEXT NOT NULL,
  name TEXT NOT NULL,
  email TEXT NOT NULL DEFAULT '',
  phone TEXT NOT NULL DEFAULT '',
  title TEXT NOT NULL DEFAULT '',
  authorized_user INTEGER NOT NULL DEFAULT 0 CHECK (authorized_user IN (0,1)),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

ALTER TABLE tickets ADD COLUMN contact_id TEXT REFERENCES client_contacts(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_client_contacts_client ON client_contacts(workspace_id, client_id, name);
CREATE INDEX IF NOT EXISTS idx_tickets_contact ON tickets(contact_id);
