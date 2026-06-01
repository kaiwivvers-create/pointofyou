-- Seed inventory_categories table
INSERT OR IGNORE INTO inventory_categories (name, description, type, created_at, updated_at) VALUES
('Flour', 'Various types of flour for baking', 'ingredient', datetime('now'), datetime('now')),
('Dairy', 'Milk, butter, cream, and other dairy products', 'ingredient', datetime('now'), datetime('now')),
('Sweeteners', 'Sugar, honey, and other sweeteners', 'ingredient', datetime('now'), datetime('now')),
('Spices', 'Spices and flavorings', 'ingredient', datetime('now'), datetime('now')),
('Fruits', 'Fresh and preserved fruits', 'ingredient', datetime('now'), datetime('now')),
('Beverages', 'Coffee, tea, and other beverage bases', 'ingredient', datetime('now'), datetime('now'));
