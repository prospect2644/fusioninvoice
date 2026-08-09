ALTER TABLE tickets ADD COLUMN subscription_covered_minutes INTEGER NOT NULL DEFAULT 0 CHECK (subscription_covered_minutes >= 0);
