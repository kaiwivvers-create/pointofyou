-- Seed menu_items table
INSERT OR IGNORE INTO menu_items (name, description, category, price, emoji, created_at, updated_at) VALUES
('Country Sourdough', '72-hour ferment, crackly crust', 'food', 8.00, '🍞', datetime('now'), datetime('now')),
('Butter Croissant', 'Flaky layers, European butter', 'food', 4.00, '🥐', datetime('now'), datetime('now')),
('Cinnamon Roll', 'Cream cheese frosting', 'food', 5.00, '🌀', datetime('now'), datetime('now')),
('Berry Tart', 'Seasonal fruit, vanilla custard', 'food', 7.00, '🫐', datetime('now'), datetime('now')),
('Iced Latte', 'Espresso over ice with milk', 'drinks', 4.50, '☕', datetime('now'), datetime('now')),
('Hot Chocolate', 'Rich cocoa, whipped cream', 'drinks', 3.50, '🍫', datetime('now'), datetime('now')),
('Lemonade', 'Fresh squeezed, lightly sweet', 'drinks', 3.00, '🍋', datetime('now'), datetime('now')),
('Matcha Latte', 'Ceremonial grade matcha', 'drinks', 5.00, '🍵', datetime('now'), datetime('now'));
