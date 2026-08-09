CREATE TABLE IF NOT EXISTS asset_category_details (
  asset_id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  category TEXT NOT NULL,
  details_json TEXT NOT NULL DEFAULT '{}',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (asset_id) REFERENCES client_assets(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_asset_category_details_workspace
  ON asset_category_details(workspace_id, category);
