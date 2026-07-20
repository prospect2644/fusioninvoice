ALTER TABLE subscriptions ADD COLUMN managed_it INTEGER NOT NULL DEFAULT 0 CHECK (managed_it IN (0, 1));
ALTER TABLE subscriptions ADD COLUMN hourly_allotment_minutes INTEGER NOT NULL DEFAULT 0 CHECK (hourly_allotment_minutes >= 0);
ALTER TABLE tickets ADD COLUMN board TEXT NOT NULL DEFAULT 'technical_support';

CREATE INDEX IF NOT EXISTS idx_subscriptions_managed_it ON subscriptions(workspace_id, client_id, managed_it, status);
CREATE INDEX IF NOT EXISTS idx_tickets_board ON tickets(workspace_id, board);
