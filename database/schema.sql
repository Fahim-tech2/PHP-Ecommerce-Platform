-- =============================================
-- বাংলা ই-কমার্স SQLite Schema
-- =============================================

PRAGMA journal_mode=WAL;
PRAGMA foreign_keys=ON;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    icon_url TEXT DEFAULT '',
    image_url TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    images TEXT DEFAULT '[]',
    buying_price REAL DEFAULT 0,
    selling_price REAL NOT NULL,
    old_price REAL DEFAULT 0,
    stock_qty INTEGER DEFAULT 0,
    category_id INTEGER,
    sku TEXT DEFAULT '',
    is_featured INTEGER DEFAULT 0,
    variants TEXT DEFAULT '[]',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tracking_code TEXT NOT NULL UNIQUE,
    customer_name TEXT NOT NULL,
    customer_phone TEXT NOT NULL,
    delivery_address TEXT NOT NULL,
    region TEXT NOT NULL DEFAULT 'inside_dhaka',
    items_json TEXT NOT NULL DEFAULT '[]',
    subtotal REAL DEFAULT 0,
    shipping_fee REAL DEFAULT 0,
    discount REAL DEFAULT 0,
    coupon_code TEXT DEFAULT '',
    total_amount REAL DEFAULT 0,
    cost_total REAL DEFAULT 0,
    net_profit REAL DEFAULT 0,
    payment_method TEXT DEFAULT 'cod',
    status TEXT DEFAULT 'pending',
    note TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Shipping Zones Table
CREATE TABLE IF NOT EXISTS shipping_zones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    zone_name TEXT NOT NULL,
    zone_key TEXT NOT NULL UNIQUE,
    delivery_fee REAL DEFAULT 0,
    free_shipping_min_amount REAL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT NOT NULL UNIQUE,
    setting_value TEXT DEFAULT ''
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT DEFAULT '',
    phone TEXT DEFAULT '',
    password_hash TEXT NOT NULL,
    role TEXT DEFAULT 'customer',
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Coupons Table
CREATE TABLE IF NOT EXISTS coupons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    discount_type TEXT DEFAULT 'percent',
    discount_value REAL DEFAULT 0,
    min_order_amount REAL DEFAULT 0,
    max_uses INTEGER DEFAULT 0,
    used_count INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    reviewer_name TEXT NOT NULL,
    rating INTEGER DEFAULT 5,
    comment TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =============================================
-- Seed Data
-- =============================================

-- Default Shipping Zones
INSERT OR IGNORE INTO shipping_zones (zone_name, zone_key, delivery_fee, free_shipping_min_amount) VALUES
('ঢাকার ভিতরে', 'inside_dhaka', 70, 1000),
('ঢাকার বাইরে', 'outside_dhaka', 130, 2000);

-- Default Site Settings
INSERT OR IGNORE INTO site_settings (setting_key, setting_value) VALUES
('site_title', 'বাংলা শপ'),
('tagline', 'বাংলাদেশের সেরা অনলাইন শপ'),
('logo_url', ''),
('favicon_url', ''),
('currency_symbol', '৳'),
('primary_color', '#6C63FF'),
('accent_color', '#FF6584'),
('contact_email', 'info@banglashop.com'),
('contact_phone', '01700000000'),
('facebook_url', 'https://facebook.com/banglashop'),
('instagram_url', ''),
('address', 'ঢাকা, বাংলাদেশ'),
('meta_pixel_id', ''),
('gtm_container_id', ''),
('server_side_token', ''),
('server_side_url', ''),
('pixel_enabled', '0'),
('gtm_enabled', '0'),
('server_tracking_enabled', '0'),
('free_delivery_notice', 'এই অর্ডারে ফ্রি ডেলিভারি নেই'),
('hero_title', 'বাংলাদেশের সেরা অনলাইন শপিং'),
('hero_subtitle', 'সারা দেশে ক্যাশ অন ডেলিভারি • দ্রুত ডেলিভারি • ১০০% অরিজিনাল প্রোডাক্ট');

-- Default Admin User (password: admin123)
INSERT OR IGNORE INTO users (name, email, phone, password_hash, role, status) VALUES
('Admin', 'admin@banglashop.com', '01700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Sample Categories
INSERT OR IGNORE INTO categories (name, slug, icon_url, image_url, sort_order) VALUES
('পোশাক ও ফ্যাশন', 'fashion', '👗', '', 1),
('ইলেকট্রনিক্স', 'electronics', '📱', '', 2),
('সৌন্দর্য চর্চা', 'beauty', '💄', '', 3),
('খাদ্য ও পানীয়', 'food', '🍎', '', 4),
('গৃহস্থালি', 'home', '🏠', '', 5),
('শিশু পণ্য', 'kids', '👶', '', 6);

-- Sample Products
INSERT OR IGNORE INTO products (title, description, images, buying_price, selling_price, old_price, stock_qty, category_id, sku, is_featured) VALUES
('কটন টি-শার্ট (সাদা)', '১০০% কটন, টেকসই এবং আরামদায়ক টি-শার্ট। গরমে পরার জন্য আদর্শ।', '[]', 200, 450, 600, 50, 1, 'TS-001', 1),
('স্মার্টফোন কভার', 'প্রিমিয়াম মানের স্মার্টফোন কভার, সব মডেলের জন্য উপলব্ধ।', '[]', 80, 250, 350, 100, 2, 'SC-001', 1),
('ফেস ক্রিম (নাইট)', 'রাতের ময়শ্চারাইজার ক্রিম, ত্বককে উজ্জ্বল ও মসৃণ রাখে।', '[]', 150, 380, 500, 30, 3, 'FC-001', 1),
('অর্গানিক মধু (৫০০গ্রাম)', '১০০% অর্গানিক ও বিশুদ্ধ মধু। সরাসরি সুন্দরবন থেকে সংগ্রহ করা।', '[]', 300, 650, 800, 25, 4, 'HN-001', 1),
('ডিজিটাল ওয়াচ', 'ওয়াটারপ্রুফ ডিজিটাল ওয়াচ, স্পোর্টস ডিজাইন।', '[]', 400, 890, 1200, 15, 2, 'DW-001', 0),
('বাঁশের ট্রে সেট', 'হাতে তৈরি বাঁশের ট্রে সেট, ৩ পিস। ঘর সাজানোর জন্য আদর্শ।', '[]', 250, 550, 700, 20, 5, 'HT-001', 0);

-- Sample Reviews
INSERT OR IGNORE INTO reviews (product_id, reviewer_name, rating, comment) VALUES
(1, 'রহিম মিয়া', 5, 'অনেক ভালো মানের পণ্য। দ্রুত ডেলিভারি পেয়েছি।'),
(1, 'করিম সাহেব', 4, 'ভালো, তবে সাইজ একটু বড় হলে আরো ভালো হতো।'),
(2, 'সুমাইয়া বেগম', 5, 'দারুণ! একদম পারফেক্ট ফিট হয়েছে।'),
(3, 'তাহমিনা আক্তার', 5, 'ত্বক অনেক ভালো হয়েছে। আবার অর্ডার করব।');
