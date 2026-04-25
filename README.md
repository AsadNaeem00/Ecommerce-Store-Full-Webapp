# 🏪 Self-Hosted eCommerce Platform

> **Laravel 11 · Tailwind CSS · MySQL 8 · EasyPaisa · JazzCash · Stripe (VISA/Mastercard)**
>
> A complete, production-ready, self-hosted eCommerce solution built for Pakistani businesses.
> Manage everything through a web admin panel — no coding required after setup.

---

## 📋 Table of Contents

1. [System Overview](#1-system-overview)
2. [Requirements & Installation](#2-requirements--installation)
3. [System Architecture](#3-system-architecture)
4. [Database Schema](#4-database-schema)
5. [Admin Panel Guide](#5-admin-panel-guide)
6. [Payment Gateway Setup](#6-payment-gateway-setup)
7. [Project Folder Structure](#7-project-folder-structure)
8. [Security Architecture](#8-security-architecture)
9. [Deployment Guide](#9-deployment-guide)
10. [Troubleshooting & FAQ](#10-troubleshooting--faq)

---

## 1. System Overview

This is a **complete, self-hosted eCommerce platform** built with Laravel 11. It is designed to be installed on any standard PHP hosting environment and managed entirely through a web-based admin panel.

It is specifically tailored for **Pakistani businesses**, with built-in support for EasyPaisa, JazzCash, VISA/Mastercard, and Cash on Delivery, as well as all Pakistani provinces.

### Key Features at a Glance

| Feature | Details | Notes |
|---|---|---|
| Admin Dashboard | Revenue charts, order stats, top products | Real-time from database |
| Product Management | Multi-image, SKU auto-gen, stock tracking | Low stock alerts included |
| Category System | 4-digit auto code, hierarchy support | e.g. code `1042` |
| Order Management | Full lifecycle with status history | Order# = `CatCode-ProdID-Ref` |
| Payment Gateways | EasyPaisa, JazzCash, Stripe (VISA/MC), COD | All encrypted in DB |
| Homepage Builder | Hero slider, section toggle/reorder | Drag & drop order |
| Review System | Star ratings 1–5, admin moderation | XSS-safe, dedup by email |
| Store Settings | Logo, colors, theme, SEO meta | Live CSS variable switching |
| Security | CSRF, XSS, bcrypt, rate limiting | Session timeout: 2 hours |
| Provinces | All 6 Pakistani provinces + ICT | Dropdown on checkout |

---

## 2. Requirements & Installation

### 2.1 Server Requirements

| Requirement | Value / Detail |
|---|---|
| PHP Version | 8.2 or higher (8.3 recommended) |
| PHP Extensions | PDO, PDO_MySQL, Mbstring, OpenSSL, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, GD or Imagick |
| MySQL | 8.0 or higher (MariaDB 10.6+ also works) |
| Composer | 2.x (PHP dependency manager) |
| Web Server | Apache 2.4+ with mod_rewrite **OR** Nginx 1.18+ |
| SSL Certificate | Required for EasyPaisa, JazzCash, and Stripe |
| Disk Space | Minimum 500MB for application + uploads |
| RAM | Minimum 512MB (1GB+ recommended) |
| PHP `memory_limit` | At least `256M` in php.ini |
| PHP `max_execution_time` | At least `60` seconds |

> ⚠️ **SSL Required** — All three online payment gateways (EasyPaisa, JazzCash, Stripe) require HTTPS.
> Cash on Delivery works without SSL but SSL is strongly recommended for customer trust.
> Most shared hosting providers offer free SSL via Let's Encrypt — enable it in your cPanel.

---

### 2.2 Installation — Step by Step

#### Step 1 — Download & Upload Files

- Extract the ZIP file on your computer
- Connect to your hosting via FTP (FileZilla) or use cPanel File Manager
- Upload the entire `ecommerce/` folder contents to your server

**Folder Placement Options:**

```
Option A (main domain):   Upload to /public_html/        →  yoursite.com
Option B (subdomain):     Upload to /public_html/shop/   →  shop.yoursite.com
Option C (VPS/Nginx):     Any directory, point document root to /your-folder/public/
```

> 📁 **Important:** The web server must point to the `/public` subfolder, not the project root!

---

#### Step 2 — Create the Database

Log in to your **cPanel → MySQL Databases** or run these SQL commands:

```sql
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON ecommerce_db.* TO 'db_user'@'localhost';
FLUSH PRIVILEGES;
```

---

#### Step 3 — Configure Environment File

- Find `.env.example` in the project root
- Make a copy and rename it to `.env`
- Open `.env` in a text editor (Notepad++ recommended on Windows)
- Fill in the following values:

| `.env` Key | What to Enter |
|---|---|
| `APP_NAME` | Your store name, e.g. `MyStore` |
| `APP_URL` | Your full URL, e.g. `https://mystore.com` |
| `APP_DEBUG` | Set to `false` for production (`true` only for development) |
| `DB_DATABASE` | The database name you created in Step 2 |
| `DB_USERNAME` | The database username from Step 2 |
| `DB_PASSWORD` | The database password from Step 2 |
| `MAIL_HOST` | Your SMTP server, e.g. `smtp.gmail.com` |
| `MAIL_USERNAME` | Your email address for sending order notifications |
| `MAIL_PASSWORD` | Your email app password (not your login password) |

---

#### Step 4 — Install PHP Dependencies

Via **SSH terminal** (preferred):

```bash
cd /path/to/your/project
composer install --no-dev --optimize-autoloader
```

> 💡 **No SSH Access? (Shared Hosting)**
>
> Run Composer locally on your Windows PC instead:
> 1. Install Composer from https://getcomposer.org/Composer-Setup.exe
> 2. Open Command Prompt in the project folder
> 3. Run: `composer install --no-dev --optimize-autoloader`
> 4. Upload the generated `vendor/` folder via FTP to your server
>    *(vendor/ can be 50–100MB — allow time for upload)*

---

#### Step 5 — Generate Key & Run Migrations

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

**What these commands do:**

| Command | Effect |
|---|---|
| `key:generate` | Creates a unique 32-character encryption key for your app |
| `migrate --seed` | Creates all 13 database tables and inserts default data |
| `storage:link` | Links `public/storage` for serving uploaded images |

> ✅ **What the Seeder Creates Automatically:**
> - Admin user: `admin@store.com` / `Admin@1234` — **change immediately!**
> - All settings with sensible defaults (PKR currency, ₨2,000 free shipping minimum)
> - Payment gateway slots (disabled, awaiting your credentials)
> - 7 homepage sections (Hero, Categories, Featured, Banner, New Arrivals, etc.)
> - 4 static pages (About, Contact, Privacy Policy, Terms of Service)

---

#### Step 6 — Set File Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

- **Windows with XAMPP:** No permissions change needed
- **Shared hosting via cPanel:** Set `storage/` and `bootstrap/cache/` to `755` via File Manager

---

#### Step 7 — Web Server Configuration

**Option A — Apache (most shared hosting)**

Create or verify a `.htaccess` file in the `public/` folder:

```apache
Options -MultiViews -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

In **cPanel**: go to **Domains → Document Root** and point it to `public_html/public` (not just `public_html/`).

**Option B — Nginx (VPS)**

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/ecommerce/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

#### Step 8 — First Login

| Item | Value |
|---|---|
| **Admin URL** | `https://yourdomain.com/admin` |
| **Email** | `admin@store.com` |
| **Password** | `Admin@1234` |
| **First Action** | Change password immediately via Profile → Security |
| **Store URL** | `https://yourdomain.com` (your live customer-facing shop) |

---

## 3. System Architecture

The system uses the **MVC pattern** provided by Laravel, with a clear separation between the admin panel and the customer-facing store.

### 3.1 Request Flow

```
┌─────────────────────────────────────────────┐
│             BROWSER / CLIENT                │
└──────────────────┬──────────────────────────┘
                   │  HTTP Request (GET / POST)
                   ▼
┌─────────────────────────────────────────────┐
│       🌐  Web Server  (Apache / Nginx)       │
│         routes to /public/index.php         │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│    ⚙️  Laravel Application Bootstrap         │
│  Loads .env → AppServiceProvider → Kernel   │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│         🔀  Router  (routes/web.php)         │
│  Matches URL pattern → dispatches to        │
│  Controller — admin/* or store/*            │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  🔐  Middleware Stack                        │
│  auth · admin · maintenance · CSRF · headers│
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│             🎮  Controller                  │
│  Validates input, calls services & models   │
└────────┬──────────────────────┬─────────────┘
         │                      │
         ▼                      ▼
┌─────────────────┐   ┌─────────────────────┐
│  📦  Eloquent   │   │  🛒  Services        │
│  Models (ORM)   │   │  Cart / Payment      │
└────────┬────────┘   └──────────┬──────────┘
         │                       │
         ▼                       ▼
┌─────────────────────────────────────────────┐
│          🗄️  MySQL Database (13 tables)      │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│    🖼️  Blade View  →  HTML  →  Browser       │
└─────────────────────────────────────────────┘
```

---

### 3.2 Application Layers

| Layer | Responsibility / Contents |
|---|---|
| **Routes** `web.php` | Maps every URL to a controller. Admin routes prefixed `/admin` with `auth` + `admin` middleware. Store routes wrapped in `maintenance` middleware |
| **Middleware** | `AdminMiddleware` (role check + session timeout), `SecurityHeaders` (XSS/clickjacking headers), `MaintenanceMode` (site-wide toggle), Laravel CSRF (built-in) |
| **Controllers** | **Admin:** Auth, Dashboard, Product, Category, Order, Review, Settings, PaymentSettings, Homepage, Pages<br>**Store:** Home, Product, Cart, Checkout, PaymentCallback |
| **Models** | `User`, `Category`, `Product`, `ProductImage`, `Order`, `OrderItem`, `OrderStatusHistory`, `Review`, `Setting`, `PaymentConfig`, `SliderImage`, `HomepageSection`, `Page` |
| **Services** | `CartService` (session cart), `PaymentManager` (gateway factory), `EasyPaisaGateway`, `JazzCashGateway`, `StripeGateway`, `CodGateway` |
| **Views** | **Admin:** Tailwind CSS + Alpine.js admin panel<br>**Store:** Responsive storefront with hero slider, product grid, cart, checkout |
| **Storage** | `public/storage/` (symlinked) → `products/`, `categories/`, `slider/`, `branding/` |

---

## 4. Database Schema

The system uses **13 tables** in a normalized MySQL schema. All tables use `utf8mb4` charset for full Unicode/emoji support.

### 4.1 Table Relationships

```
users
  └── products.created_by          (who created each product)

categories
  ├── categories.parent_id         (self-referential hierarchy)
  └── products.category_id         (one category → many products)

products
  ├── product_images.product_id    (gallery images)
  └── reviews.product_id           (customer reviews)

orders
  ├── order_items.order_id         (line items — price snapshot)
  └── order_status_history.order_id (full audit trail)

settings                           (standalone key-value store, cached 1hr)

payment_configs                    (one row per gateway, keys AES-256 encrypted)

homepage_sections
slider_images
pages                              (content management tables)
```

---

### 4.2 All Tables — Column Reference

#### `users`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `name` | varchar(255) | Display name |
| `email` | varchar(255) UNIQUE | Login email |
| `password` | varchar(255) | bcrypt hashed |
| `role` | enum | `super_admin`, `admin`, `manager` |
| `is_active` | boolean | Deactivate without deleting |
| `last_login_at` | timestamp | Security tracking |
| `last_login_ip` | varchar(45) | Security tracking |

#### `categories`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `category_code` | varchar(4) UNIQUE | Auto-generated 4-digit code e.g. `1042` |
| `name` | varchar(255) | Display name |
| `slug` | varchar(255) UNIQUE | URL-friendly name |
| `parent_id` | bigint FK → categories | Null = top-level |
| `is_active` | boolean | |
| `show_in_nav` | boolean | Show in store header menu |

#### `products`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `sku` | varchar UNIQUE | Auto-generated e.g. `PRD-2024-0001` |
| `name` | varchar(255) | |
| `slug` | varchar UNIQUE | |
| `category_id` | bigint FK → categories | |
| `price` | decimal(12,2) | Regular price |
| `sale_price` | decimal(12,2) | Optional sale price |
| `stock_quantity` | integer | |
| `low_stock_threshold` | integer | Default: 5 |
| `is_active` | boolean | Visible in store |
| `is_featured` | boolean | Shown in featured section |
| `track_quantity` | boolean | Disable for unlimited stock |
| `tags` | JSON | Array of tags |

#### `orders`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `order_number` | varchar UNIQUE | Format: `[CatCode]-[ProdID]-[Ref]` |
| `customer_name` | varchar | |
| `customer_email` | varchar | |
| `customer_phone` | varchar(20) | |
| `shipping_address` | text | |
| `shipping_province` | varchar | One of the 6 Pakistani provinces |
| `subtotal` | decimal(12,2) | |
| `shipping_cost` | decimal(12,2) | 0 if free shipping threshold met |
| `total_amount` | decimal(12,2) | |
| `status` | enum | `pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled`, `refunded` |
| `payment_method` | enum | `cod`, `easypaisa`, `jazzcash`, `card` |
| `payment_status` | enum | `unpaid`, `paid`, `partially_paid`, `refunded` |

#### `settings` — Key-Value Store
| Key | Group | Description |
|---|---|---|
| `store_name`, `store_email`, `store_phone` | `general` | Basic store info |
| `currency`, `currency_symbol` | `general` | Defaults: `PKR`, `₨` |
| `logo`, `favicon`, `background_image` | `branding` | File paths |
| `color_primary`, `color_secondary`, `color_accent` | `branding` | Hex colors |
| `theme_style` | `branding` | `modern`, `luxury`, `minimal`, `bold` |
| `free_shipping_min`, `default_shipping_cost` | `shipping` | PKR amounts |
| `facebook_url`, `instagram_url`, `whatsapp_number` | `social` | Social links |
| `maintenance_mode` | `general` | `0` or `1` |

#### `payment_configs`
| Column | Type | Notes |
|---|---|---|
| `gateway` | varchar UNIQUE | `easypaisa`, `jazzcash`, `card`, `cod` |
| `is_enabled` | boolean | Master switch |
| `is_test_mode` | boolean | Sandbox vs live |
| `merchant_id` | varchar | Plain text |
| `api_key` | text | **AES-256 encrypted at rest** |
| `api_secret` | text | **AES-256 encrypted at rest** |
| `extra_config` | JSON | Gateway-specific fields (hash_key, return_url, etc.) |

---

## 5. Admin Panel Guide

### 5.1 Dashboard

- Total revenue, today's revenue, monthly revenue
- Pending orders count, total customers, low stock products
- Revenue bar chart — last 7 days (live from DB)
- Order status breakdown (progress bars)
- Recent 8 orders with links to detail pages
- Top 5 products by quantity sold

---

### 5.2 Product Management

| Action | How to Do It |
|---|---|
| **Add Product** | Admin → Products → Add Product. Fill name, category, price, stock. Upload main image + gallery |
| **SKU** | Auto-generated as `PRD-2024-0001`. Can be overridden manually |
| **Sale Price** | If set lower than regular price, a discount % badge appears on the store |
| **Stock Tracking** | Toggle "Track inventory". When stock hits `low_stock_threshold` (default 5), admin sees a warning |
| **Featured** | Toggle "Featured Product" to appear in the Featured Products homepage section |
| **Gallery Images** | Upload multiple at once. Each can be individually removed from the Edit page |

---

### 5.3 Category Management

- Each category gets an auto-generated unique **4-digit code** (e.g. `1042`)
- Categories can be **nested** (parent/child) for hierarchy — e.g. Clothing → Men's
- Toggle **"Show in Nav"** to control which categories appear in the store header menu
- Deleting a category with products assigned is **blocked** — reassign products first

---

### 5.4 Order Management

**Order Number Format:** `[CategoryCode]-[ProductID]-[RandomRef]`

Example: `1042-0003-A7F2D1` = Category 1042, Product #3, reference A7F2D1

#### Order Statuses

| Status | Meaning |
|---|---|
| `pending` | New order placed, awaiting review |
| `confirmed` | Admin has acknowledged the order |
| `processing` | Being packed / prepared for dispatch |
| `shipped` | Handed to courier, in transit |
| `delivered` | Customer has received the order |
| `cancelled` | Order cancelled by admin or customer |
| `refunded` | Payment refunded to customer |

Every status change is logged in `order_status_history` with the admin's name and an optional note — creating a **full audit trail** viewable on the Order Detail page.

---

### 5.5 Homepage Builder

- Go to **Admin → Homepage Builder** to see all 7 sections
- Toggle sections on/off — changes take effect immediately
- Reorder sections by editing sort order values
- **Hero Slider:** Upload multiple banner images with title, subtitle, and CTA button text/URL
- Recommended slider image size: **1920 × 600 pixels**, JPG or PNG

**Available sections:**

| Section Key | Default State | Description |
|---|---|---|
| `hero` | Enabled | Hero image slider with CTA buttons |
| `categories` | Enabled | Shop by Category grid |
| `featured` | Enabled | Featured Products grid |
| `promo_banner` | Enabled | Promotional banner section |
| `new_arrivals` | Enabled | Latest products |
| `testimonials` | Disabled | Customer testimonials |
| `trust_badges` | Enabled | Free shipping / secure payment badges |

---

### 5.6 Store Settings Reference

| Setting Group | What It Controls | Location |
|---|---|---|
| General | Store name, tagline, email, phone, address, currency | Settings → General |
| Branding | Logo, favicon, primary/secondary/accent colors, theme style | Settings → Branding |
| Background | Upload a background image (supports animated GIF) | Settings → Branding |
| Social | Facebook URL, Instagram URL, WhatsApp number | Settings → Social |
| Shipping | Free shipping minimum (PKR), default shipping cost (PKR) | Settings → Shipping |
| SEO | Default meta title and meta description | Settings → SEO |
| Maintenance | Toggle to hide the store from visitors | Settings → General |

---

## 6. Payment Gateway Setup

Go to **Admin → Payment Setup** to configure each gateway.
Credentials are **AES-256 encrypted** before being stored in the database.

---

### 6.1 EasyPaisa

> Mobile Account (MA) · OTC payments · HMAC-SHA256 signed requests

**Getting Credentials:**
1. Register at https://easypaisa.com.pk/online-payment-gateway/
2. Provide: CNIC, business registration, bank account details
3. You will receive: Merchant/Store ID, Store Password, Hash/Integrity Key, Account Number
4. EasyPaisa provides sandbox credentials for testing

**Admin Fields:**

| Field | Description |
|---|---|
| Store / Merchant ID | Your numeric merchant ID from EasyPaisa portal |
| Store Password (API Key) | Authentication password for the API |
| Hash / Integrity Key | Used to sign requests with HMAC-SHA256 |
| Account Number | Your EasyPaisa mobile number e.g. `03XX-XXXXXXX` |
| Return URL | Auto-filled: `https://yourdomain.com/payment/easypaisa/callback` — register this in EasyPaisa portal |

**How It Works:**
1. Customer selects EasyPaisa at checkout and clicks Place Order
2. System generates an HMAC-SHA256 signed POST request to EasyPaisa servers
3. Customer is redirected to EasyPaisa's page to enter their mobile account PIN
4. EasyPaisa redirects back to your Return URL with a signed response
5. System verifies the hash signature and marks the order as paid
6. Customer sees the Order Confirmation page

---

### 6.2 JazzCash

> Mobile Wallet · Card payments · Hosted checkout · HMAC-SHA256 signed

**Getting Credentials:**
1. Register at https://developer.jazzcash.com.pk/
2. You will receive: Merchant ID, Password, Integrity Salt (Hash Key)
3. Test at: https://sandbox.jazzcash.com.pk/

**Admin Fields:**

| Field | Description |
|---|---|
| Merchant ID | Your JazzCash merchant ID e.g. `MC12345` |
| Password | Merchant API password |
| Integrity Salt | Hash key — treat as secret |
| Account Number | Your JazzCash mobile number |
| Return URL | Auto-filled: `https://yourdomain.com/payment/jazzcash/callback` |

**How It Works:**
1. Customer selects JazzCash at checkout
2. System generates HMAC-SHA256 signed form data (sorted keys)
3. Customer is redirected to JazzCash's hosted checkout page
4. Customer pays via mobile wallet OR bank card on JazzCash's own page
5. JazzCash sends a signed callback to your Return URL
6. System verifies the signature and confirms the order

---

### 6.3 Stripe — VISA / Mastercard

> Stripe Checkout Sessions · PKR currency · PCI compliant

**Getting Credentials:**
1. Register at https://stripe.com (Pakistani businesses supported via partner networks)
2. Get keys from **Dashboard → Developers → API Keys**
3. You will receive: Secret Key (`sk_live_...`), Publishable Key (`pk_live_...`)

**Admin Fields:**

| Field | Description |
|---|---|
| Secret Key | Starts with `sk_live_...` or `sk_test_...` — never expose publicly |
| Publishable Key | Starts with `pk_live_...` — safe for frontend |
| Merchant ID | Your Stripe Account ID (`acct_...`) |
| Webhook Secret | From Stripe Dashboard → Webhooks, starts with `whsec_...` |
| Webhook URL | Register in Stripe: `https://yourdomain.com/payment/card/webhook` |

**How It Works:**
1. Customer selects Credit/Debit Card at checkout
2. System creates a Stripe Checkout Session with order line items
3. Customer is redirected to Stripe's hosted payment page (PCI compliant)
4. Customer enters VISA/Mastercard details on Stripe's secure page
5. Stripe redirects to your success URL with a `session_id`
6. System retrieves the session, verifies `payment_status === "paid"`
7. Order is confirmed and customer sees the confirmation page

---

### 6.4 Cash on Delivery (COD)

> No integration required · Enabled by default

- Enable in **Admin → Payment Setup → Cash on Delivery → toggle Enabled**
- No credentials required — no API calls are made
- Order is placed with `payment_status = unpaid` and `status = pending`
- Admin manually marks payment as **"Paid"** when cash is collected
- Customer receives a WhatsApp tracking link after confirmation

---

## 7. Project Folder Structure

```
ecommerce/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AuthController.php           → Login, logout, profile
│   │   │   │   ├── DashboardController.php      → Stats, charts, recent orders
│   │   │   │   ├── ProductController.php        → Full product CRUD
│   │   │   │   └── AdminControllers.php         → Category, Order, Review,
│   │   │   │                                       Settings, Payment, Homepage, Pages
│   │   │   └── Store/
│   │   │       └── StoreControllers.php         → Home, Products, Cart,
│   │   │                                           Checkout, PaymentCallback
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php              → Role check + session timeout
│   │       ├── SecurityHeaders.php              → XSS/clickjacking headers
│   │       └── MaintenanceMode.php             → Site-wide maintenance toggle
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php                         → Auto 4-digit code, hierarchy
│   │   ├── Product.php                          → Auto SKU, computed price/discount
│   │   ├── ProductImage.php
│   │   ├── Order.php                            → Status labels/colors
│   │   ├── OrderItem.php
│   │   ├── OrderStatusHistory.php
│   │   ├── Review.php
│   │   ├── Setting.php                          → get/set with 1hr cache
│   │   ├── PaymentConfig.php                   → AES-256 encrypted keys
│   │   └── StoreFront.php                       → SliderImage, HomepageSection, Page
│   │
│   ├── Services/
│   │   ├── CartService.php                      → Session cart (add/update/remove)
│   │   └── Payment/
│   │       ├── PaymentManager.php               → Gateway factory & registry
│   │       ├── Contracts/
│   │       │   └── PaymentGatewayInterface.php  → initiatePayment, verifyPayment,
│   │       │                                       callbackHandler
│   │       └── Gateways/
│   │           ├── BaseGateway.php              → Shared logging, response helpers
│   │           ├── EasyPaisaGateway.php         → HMAC-SHA256, MA/OTC payments
│   │           ├── JazzCashGateway.php          → Sorted hash, hosted checkout
│   │           ├── StripeGateway.php            → Checkout Sessions, PKR
│   │           └── CodGateway.php              → No-op, redirect to confirmation
│   │
│   └── Providers/
│       └── AppServiceProvider.php               → CartService singleton binding
│
├── bootstrap/
│   └── app.php                                  → Middleware registration
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_users_table.php
│   │   ├── ..._create_categories_table.php
│   │   ├── ..._create_products_table.php
│   │   └── ..._create_ecommerce_tables.php      → 10 remaining tables
│   └── seeders/
│       └── DatabaseSeeder.php                   → Admin user, settings, gateways, pages
│
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── layouts/app.blade.php            → Master layout with sidebar nav
│       │   ├── auth/login.blade.php             → Login page
│       │   ├── dashboard/index.blade.php        → Charts, stats, recent orders
│       │   ├── products/
│       │   │   ├── index.blade.php              → List with search/filter
│       │   │   └── create.blade.php             → Create + Edit form
│       │   ├── categories/index.blade.php       → List + inline edit modal
│       │   ├── orders/
│       │   │   ├── index.blade.php              → Orders table with filters
│       │   │   └── show.blade.php               → Detail + status update
│       │   ├── reviews/index.blade.php          → Approve/reject/delete
│       │   ├── settings/index.blade.php         → Full store settings form
│       │   ├── payments/index.blade.php         → Gateway config cards
│       │   ├── homepage/index.blade.php         → Section toggle + slider
│       │   └── pages/
│       │       ├── index.blade.php
│       │       └── edit.blade.php
│       │
│       └── store/
│           ├── layouts/app.blade.php            → Master with header/footer/nav
│           ├── home/index.blade.php             → Hero slider + all sections
│           ├── products/
│           │   ├── index.blade.php              → Grid with sidebar filters
│           │   ├── show.blade.php               → Gallery, reviews, add to cart
│           │   └── _card.blade.php              → Reusable product card partial
│           ├── cart/index.blade.php             → AJAX-powered cart
│           ├── checkout/
│           │   ├── index.blade.php              → Full checkout form
│           │   ├── confirmation.blade.php       → Order confirmed + WhatsApp
│           │   └── payment-redirect.blade.php   → Auto POST to gateway
│           └── pages/
│               ├── contact.blade.php
│               ├── show.blade.php               → Static pages
│               └── ../maintenance.blade.php     → 503 maintenance page
│
├── routes/
│   └── web.php                                  → All admin + store routes
│
├── public/                                      ← Web server document root
│   ├── index.php                                → Laravel entry point
│   └── storage/                                 → Symlink → storage/app/public/
│
├── storage/
│   └── app/public/
│       ├── products/                            → Product images
│       ├── categories/                          → Category images
│       ├── slider/                              → Hero banner images
│       └── branding/                            → Logo, favicon, backgrounds
│
├── .env                                         ← Your config (NEVER commit to Git!)
├── .env.example                                 → Template
├── composer.json                                → PHP dependencies
└── README.md                                    → This file
```

---

## 8. Security Architecture

| Threat | Protection Method | Where Implemented |
|---|---|---|
| **SQL Injection** | Laravel Eloquent ORM uses PDO prepared statements — no raw string interpolation | All Model queries |
| **CSRF Attacks** | Laravel's built-in CSRF middleware validates `@csrf` token on every POST/PUT/DELETE | All Blade forms |
| **XSS** | Blade `{{ }}` auto-escapes all output. `e()` helper used in JS contexts | All views + controllers |
| **Password Theft** | `bcrypt` hashing via Laravel `Hash` facade. Never stored in plain text | Auth system |
| **Session Hijacking** | Session regenerated on login. 2-hour inactivity timeout enforced | `AdminMiddleware` |
| **Brute Force** | 5 failed attempts per IP per minute triggers 60-second lockout | `AuthController` |
| **Payment Key Exposure** | API keys encrypted with AES-256-CBC via `Crypt::encryptString()` before DB storage | `PaymentConfig` model |
| **File Upload Attacks** | Strict MIME type validation (jpg/png/webp only), 4MB max, filenames sanitized | All upload handlers |
| **Clickjacking** | `X-Frame-Options: SAMEORIGIN` header on all responses | `SecurityHeaders` middleware |
| **MIME Sniffing** | `X-Content-Type-Options: nosniff` header on all responses | `SecurityHeaders` middleware |
| **Unauthorized Admin** | `auth` middleware checks authentication. `admin` middleware checks role + active status | Route middleware |
| **Maintenance Bypass** | Only `/admin` routes accessible in maintenance mode. Visitors see 503 | `MaintenanceMode` middleware |

---

## 9. Deployment Guide

### 9.1 Shared Hosting (cPanel) — Most Common

1. **Upload files** — Upload to `public_html/` via cPanel File Manager or FTP
2. **Set document root** — cPanel → Domains → set root to `public_html/public` (the `/public` subfolder)
3. **Create database** — cPanel → MySQL Databases → Create DB → Create User → Add User with ALL privileges
4. **Edit .env** — Via cPanel File Manager. Set `APP_ENV=production`, `APP_DEBUG=false`
5. **Run via cPanel Terminal:**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```
6. **No terminal?** — Create a temporary `run-setup.php`, visit it once to run migrations, then delete it immediately

> 🚫 **Never on Production:**
> - Never set `APP_DEBUG=true` on a live site — exposes credentials and code
> - Never commit `.env` to Git — contains your secret keys
> - Never run `migrate:fresh --seed` on production — deletes all data
> - Never leave default admin credentials — change immediately

---

### 9.2 VPS / Cloud (Ubuntu)

```bash
# Install required packages
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
     php8.2-gd php8.2-curl nginx mysql-server composer -y

# Clone / upload project
cd /var/www
git clone your-repo ecommerce
cd ecommerce

# Install dependencies
composer install --no-dev --optimize-autoloader

# Configure
cp .env.example .env
nano .env                       # fill in your values
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 9.3 Local Development — Windows (XAMPP)

1. **Install XAMPP** — https://www.apachefriends.org (includes PHP, MySQL, Apache)
2. **Install Composer** — https://getcomposer.org/Composer-Setup.exe
3. **Copy project** — Place `ecommerce/` folder inside `C:\xampp\htdocs\`
4. **Create database** — Open `http://localhost/phpmyadmin` → New → Create `ecommerce_db`
5. **Configure .env:**
   ```
   DB_HOST=127.0.0.1
   DB_DATABASE=ecommerce_db
   DB_USERNAME=root
   DB_PASSWORD=
   APP_URL=http://localhost/ecommerce/public
   ```
6. **Open terminal** — `Win+R` → `cmd` → `cd C:\xampp\htdocs\ecommerce`
7. **Run setup:**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```
8. **Access** — `http://localhost/ecommerce/public`

**XAMPP Virtual Host (Recommended):**

Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName store.test
    DocumentRoot "C:/xampp/htdocs/ecommerce/public"
    <Directory "C:/xampp/htdocs/ecommerce/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1   store.test
```

Access at: **http://store.test**

---

## 10. Troubleshooting & FAQ

| Problem | Likely Cause | Solution |
|---|---|---|
| White screen / 500 error | `APP_DEBUG=false` hiding errors | Temporarily set `APP_DEBUG=true`, reload, fix the shown error, then set back to `false` |
| "No application encryption key" | `APP_KEY` is empty | Run: `php artisan key:generate` |
| "Access denied for user" (DB error) | Wrong DB credentials in `.env` | Check `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE` match your cPanel MySQL settings |
| Images not showing | `storage:link` not created | Run: `php artisan storage:link`. On shared hosting, set `FILESYSTEM_DISK=public` |
| Admin login redirect loop | Session/cookie misconfiguration | Set `SESSION_DRIVER=file` in `.env`. Ensure `storage/framework/sessions/` is writable |
| "Class not found" errors | Composer autoload not generated | Run: `composer dump-autoload` |
| Payment callback not working | Wrong Return URL in gateway portal | Copy the exact URL from Admin → Payment Setup and paste it into your gateway portal |
| Stripe "No such payment_intent" | Test vs live mode mismatch | Ensure both keys (secret + publishable) are from the same mode (both test OR both live) |
| EasyPaisa hash mismatch | Wrong hash key | Double-check the Hash/Integrity Key in Admin → Payment Setup — must match exactly |
| CSS not loading | CDN blocked or unavailable | Tailwind is loaded from CDN. Check internet connectivity on server or self-host Tailwind |
| File upload failing | PHP upload limits too low | In `php.ini`: set `upload_max_filesize=20M` and `post_max_size=25M`. Restart Apache |
| Slow admin panel | No caching configured | Run: `php artisan config:cache && php artisan route:cache`. Use Redis for sessions |

---

### Useful Artisan Commands

| Command | What It Does |
|---|---|
| `php artisan cache:clear` | Clears all cached settings and routes |
| `php artisan config:clear` | Clears config cache (run after editing `.env`) |
| `php artisan route:list` | Lists all registered routes with their middleware |
| `php artisan tinker` | Interactive PHP shell for debugging |
| `php artisan queue:work` | Starts the job queue (for email notifications) |
| `php artisan migrate:status` | Shows which migrations have been run |
| `php artisan storage:link` | Re-creates the `public/storage` symlink |
| `php artisan optimize` | Caches config, routes, and views for production |
| `php artisan down` | Puts the site into maintenance mode |
| `php artisan up` | Brings the site back from maintenance mode |

---

## Quick Reference Card

```
┌────────────────────────────────────────────────────┐
│              ADMIN CREDENTIALS                     │
│  URL:       https://yourdomain.com/admin           │
│  Email:     admin@store.com                        │
│  Password:  Admin@1234   ← CHANGE IMMEDIATELY      │
├────────────────────────────────────────────────────┤
│              ORDER NUMBER FORMAT                   │
│  [CategoryCode] - [ProductID] - [RandomRef]        │
│  Example:   1042-0003-A7F2D1                       │
├────────────────────────────────────────────────────┤
│              PAYMENT CALLBACK URLS                 │
│  EasyPaisa: /payment/easypaisa/callback            │
│  JazzCash:  /payment/jazzcash/callback             │
│  Stripe:    /payment/card/webhook  (webhook)       │
├────────────────────────────────────────────────────┤
│              SUPPORTED PROVINCES                   │
│  Islamabad ICT · Punjab · KPK                      │
│  Sindh · Balochistan · Tribal Areas                │
└────────────────────────────────────────────────────┘
```

---

> 🚀 **Ready to Go Live?**
>
> 1. Complete installation steps 1–8
> 2. Login to Admin and change the default password
> 3. Add your logo and brand colors in **Settings → Branding**
> 4. Create your product categories and add products
> 5. Enable and configure payment methods
> 6. Add hero slider images via **Homepage Builder**
>
> **Your store is ready! 🎉**

---

*Built with Laravel 11 · Tailwind CSS · Alpine.js · Font Awesome · EasyPaisa · JazzCash · Stripe*
