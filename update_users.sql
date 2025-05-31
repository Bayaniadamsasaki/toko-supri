USE toko_sembako_supri;

-- Update existing admin users to cashier
UPDATE users SET role = 'cashier' WHERE role = 'admin';

-- Modify the role column to only allow 'cashier'
ALTER TABLE users MODIFY COLUMN role ENUM('cashier') NOT NULL DEFAULT 'cashier'; 