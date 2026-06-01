-- Seed products table (ingredients)
-- Note: inventory_category_id values will be set after categories are seeded
-- Flour category (id 1)
INSERT OR IGNORE INTO products (name, sku, inventory_category_id, purchase_price, selling_price, stock_quantity, min_stock_level, unit, description, created_at, updated_at) VALUES
('All-purpose Flour', 'ING-FLOUR-AP', 1, 0.50, 0.00, 50000, 10000, 'g', 'Standard all-purpose flour', datetime('now'), datetime('now')),
('Bread Flour', 'ING-FLOUR-BR', 1, 0.60, 0.00, 30000, 5000, 'g', 'High-protein bread flour', datetime('now'), datetime('now'));

-- Dairy category (id 2)
INSERT OR IGNORE INTO products (name, sku, inventory_category_id, purchase_price, selling_price, stock_quantity, min_stock_level, unit, description, created_at, updated_at) VALUES
('European Butter', 'ING-DAIRY-BT', 2, 0.02, 0.00, 10000, 2000, 'g', 'European-style butter', datetime('now'), datetime('now')),
('Whole Milk', 'ING-DAIRY-MK', 2, 0.001, 0.00, 50000, 10000, 'ml', 'Fresh whole milk', datetime('now'), datetime('now')),
('Heavy Cream', 'ING-DAIRY-CR', 2, 0.003, 0.00, 20000, 5000, 'ml', 'Heavy whipping cream', datetime('now'), datetime('now')),
('Cream Cheese', 'ING-DAIRY-CC', 2, 0.03, 0.00, 5000, 1000, 'g', 'Cream cheese for frosting', datetime('now'), datetime('now'));

-- Sweeteners category (id 3)
INSERT OR IGNORE INTO products (name, sku, inventory_category_id, purchase_price, selling_price, stock_quantity, min_stock_level, unit, description, created_at, updated_at) VALUES
('Granulated Sugar', 'ING-SUGAR-GR', 3, 0.001, 0.00, 30000, 5000, 'g', 'White granulated sugar', datetime('now'), datetime('now')),
('Powdered Sugar', 'ING-SUGAR-PW', 3, 0.002, 0.00, 10000, 2000, 'g', 'Powdered sugar for dusting', datetime('now'), datetime('now')),
('Honey', 'ING-SUGAR-HN', 3, 0.005, 0.00, 5000, 1000, 'ml', 'Natural honey', datetime('now'), datetime('now'));

-- Spices category (id 4)
INSERT OR IGNORE INTO products (name, sku, inventory_category_id, purchase_price, selling_price, stock_quantity, min_stock_level, unit, description, created_at, updated_at) VALUES
('Cinnamon', 'ING-SPICE-CN', 4, 0.01, 0.00, 2000, 500, 'g', 'Ground cinnamon', datetime('now'), datetime('now')),
('Vanilla Extract', 'ING-SPICE-VN', 4, 0.02, 0.00, 1000, 200, 'ml', 'Pure vanilla extract', datetime('now'), datetime('now')),
('Salt', 'ING-SPICE-SL', 4, 0.001, 0.00, 5000, 1000, 'g', 'Table salt', datetime('now'), datetime('now'));

-- Fruits category (id 5)
INSERT OR IGNORE INTO products (name, sku, inventory_category_id, purchase_price, selling_price, stock_quantity, min_stock_level, unit, description, created_at, updated_at) VALUES
('Mixed Berries', 'ING-FRUIT-BR', 5, 0.02, 0.00, 5000, 1000, 'g', 'Seasonal mixed berries', datetime('now'), datetime('now')),
('Lemon', 'ING-FRUIT-LM', 5, 0.10, 0.00, 100, 20, 'piece', 'Fresh lemons', datetime('now'), datetime('now'));

-- Beverages category (id 6)
INSERT OR IGNORE INTO products (name, sku, inventory_category_id, purchase_price, selling_price, stock_quantity, min_stock_level, unit, description, created_at, updated_at) VALUES
('Espresso Beans', 'ING-BEV-EB', 6, 0.03, 0.00, 10000, 2000, 'g', 'Coffee beans for espresso', datetime('now'), datetime('now')),
('Matcha Powder', 'ING-BEV-MT', 6, 0.10, 0.00, 2000, 500, 'g', 'Ceremonial grade matcha', datetime('now'), datetime('now')),
('Cocoa Powder', 'ING-BEV-CP', 6, 0.02, 0.00, 5000, 1000, 'g', 'Premium cocoa powder', datetime('now'), datetime('now'));
