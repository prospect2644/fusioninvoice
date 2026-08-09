CREATE TABLE IF NOT EXISTS wireless_assets (
  asset_id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  network_name TEXT NOT NULL DEFAULT '',
  physical_location TEXT NOT NULL DEFAULT '',
  ssid TEXT NOT NULL DEFAULT '',
  security_type TEXT NOT NULL DEFAULT '',
  pre_shared_key_encrypted TEXT NOT NULL DEFAULT '',
  access_points TEXT NOT NULL DEFAULT '',
  wireless_controllers TEXT NOT NULL DEFAULT '',
  guest_network TEXT NOT NULL DEFAULT '',
  authorized_users TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (asset_id) REFERENCES client_assets(id) ON DELETE CASCADE,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_wireless_assets_workspace ON wireless_assets(workspace_id, network_name);
