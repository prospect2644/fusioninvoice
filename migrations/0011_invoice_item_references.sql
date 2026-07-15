ALTER TABLE invoice_items ADD COLUMN source_type TEXT CHECK (source_type IN ('item','subscription'));
ALTER TABLE invoice_items ADD COLUMN source_id TEXT;

CREATE INDEX IF NOT EXISTS idx_invoice_items_source ON invoice_items(workspace_id, source_type, source_id);
