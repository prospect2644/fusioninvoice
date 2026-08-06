CREATE TABLE IF NOT EXISTS document_folders (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  parent_id TEXT,
  name TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES document_folders(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS documents (
  id TEXT PRIMARY KEY,
  workspace_id TEXT NOT NULL,
  folder_id TEXT,
  title TEXT NOT NULL,
  content_html TEXT NOT NULL DEFAULT '',
  created_by_email TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
  FOREIGN KEY (folder_id) REFERENCES document_folders(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_document_folders_workspace ON document_folders(workspace_id, name);
CREATE INDEX IF NOT EXISTS idx_documents_workspace_folder ON documents(workspace_id, folder_id, updated_at DESC);
