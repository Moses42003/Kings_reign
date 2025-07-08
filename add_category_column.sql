-- Add category column to phones and clothes tables
ALTER TABLE phones ADD COLUMN category VARCHAR(100) DEFAULT '';
ALTER TABLE clothes ADD COLUMN category VARCHAR(100) DEFAULT '';
