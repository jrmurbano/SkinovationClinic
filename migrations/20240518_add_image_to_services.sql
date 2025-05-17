-- Add image column to services table
ALTER TABLE services ADD COLUMN image VARCHAR(255) DEFAULT NULL;
