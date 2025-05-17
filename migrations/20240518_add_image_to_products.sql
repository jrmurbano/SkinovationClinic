-- Add image column to products table if not present
ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL;
