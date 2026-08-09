CREATE TABLE IF NOT EXISTS configuration_assets (
  asset_id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  asset_tag TEXT NOT NULL DEFAULT '',
  physical_location TEXT NOT NULL DEFAULT '',
  contact_name TEXT NOT NULL DEFAULT '',
  installed_date TEXT,
  mac_address TEXT NOT NULL DEFAULT '',
  default_gateway TEXT NOT NULL DEFAULT '',
  purchased_date TEXT,
  expiration_date TEXT,
  authorized_users TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (asset_id) REFERENCES client_assets(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_configuration_assets_workspace
  ON configuration_assets(workspace_id, asset_tag);
