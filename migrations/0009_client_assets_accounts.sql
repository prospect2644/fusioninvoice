CREATE TABLE IF NOT EXISTS client_assets (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  client_id TEXT NOT NULL,
  asset_type TEXT NOT NULL CHECK (asset_type IN ('computer','server','network','printer','mobile','other')),
  name TEXT NOT NULL,
  serial_number TEXT NOT NULL DEFAULT '',
  hostname TEXT NOT NULL DEFAULT '',
  operating_system TEXT NOT NULL DEFAULT '',
  manufacturer TEXT NOT NULL DEFAULT '',
  model TEXT NOT NULL DEFAULT '',
  ip_address TEXT NOT NULL DEFAULT '',
  notes TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS client_accounts (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  client_id TEXT NOT NULL,
  name TEXT NOT NULL,
  username_encrypted TEXT NOT NULL DEFAULT '',
  password_encrypted TEXT NOT NULL DEFAULT '',
  website_encrypted TEXT NOT NULL DEFAULT '',
  totp_secret_encrypted TEXT NOT NULL DEFAULT '',
  notes TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS credential_audit (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  account_id TEXT NOT NULL,
  actor_email TEXT NOT NULL,
  action TEXT NOT NULL CHECK (action IN ('reveal','totp')),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (account_id) REFERENCES client_accounts(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_client_assets_client ON client_assets(workspace_id, client_id, name);
CREATE INDEX IF NOT EXISTS idx_client_accounts_client ON client_accounts(workspace_id, client_id, name);
CREATE INDEX IF NOT EXISTS idx_credential_audit_account ON credential_audit(workspace_id, account_id, created_at);
