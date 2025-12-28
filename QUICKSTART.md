# E-Commerce Shopping Cart - Quick Start Guide

## 📋 Prerequisites Checklist

Before starting, make sure you have:

- [ ] PHP 8.2 or higher installed
- [ ] PostgreSQL 16 or higher installed and running
- [ ] Composer installed globally
- [ ] Node.js (v18+) and NPM installed
- [ ] Git installed
- [ ] A code editor (VS Code, Cursor, etc.)

---

## ⚡ Quick Start (5 Minutes)

### 1. Clone & Setup
```bash
git clone https://github.com/ccdiego5/ecommerce_cart.git
cd ecommerce_cart
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup
```bash
# Create database in PostgreSQL
createdb ecommerce_cart

# OR via psql:
psql -U postgres
CREATE DATABASE ecommerce_cart;
\q
```

### 3. Configure .env
Edit `.env` file:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce_cart
DB_USERNAME=postgres
DB_PASSWORD=your_password_here
```

### 4. Migrate & Seed
```bash
php artisan migrate --seed
```

### 5. Build & Run
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite (assets)
npm run dev
```

### 6. Access Application
Open browser: **http://127.0.0.1:8000**

---

## 🔑 Test Credentials

### Admin User
- **Email:** ccdiego.ve@gmail.com
- **Password:** GodAleGO##85

### Customer Users
- **Email:** john@example.com
- **Password:** password

- **Email:** jane@example.com
- **Password:** password

---

## 🧪 Quick Test Flow

1. **Landing Page** → Click "Shop Now" → Login
2. **Login** → Use credentials above
3. **Dashboard** → Browse products
4. **Add to Cart** → Click cart icon (top right)
5. **Cart** → View items → "Proceder al Checkout"
6. **Checkout** → Fill details → "Realizar Pedido"
7. **Confirmation** → View order details
8. **Language** → Click "English" to switch language

---

## 🔧 Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild database
php artisan migrate:fresh --seed

# Rebuild assets
npm run build

# Run in production mode
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🆘 Quick Troubleshooting

### Problem: "Connection refused" error
**Solution:** Check PostgreSQL is running
```bash
# Windows (Laragon)
# Start PostgreSQL from Laragon control panel

# Linux/Mac
sudo systemctl start postgresql
```

### Problem: "Class not found" errors
**Solution:** Regenerate autoload
```bash
composer dump-autoload
php artisan clear-compiled
```

### Problem: Assets not loading
**Solution:** Rebuild assets
```bash
npm run build
# or
npm run dev
```

### Problem: "Key too long" error
**Solution:** Run key:generate
```bash
php artisan key:generate
```

---

## 📱 Access URLs

| Page | URL |
|------|-----|
| Landing | http://127.0.0.1:8000 |
| Login | http://127.0.0.1:8000/login |
| Register | http://127.0.0.1:8000/register |
| Dashboard | http://127.0.0.1:8000/dashboard |
| Cart | http://127.0.0.1:8000/cart |

---

## 🎯 What You'll See

- **Landing Page:** 8 featured products with "Shop Now" CTA
- **Dashboard:** Full product catalog with search
- **Cart Dropdown:** Quick view of cart items (top-right nav)
- **Cart Page:** Full cart with update/remove options
- **Checkout:** Shipping address + fake payment form
- **Order Confirmation:** Complete order details
- **Language Switcher:** Toggle English/Spanish

---

For detailed documentation, see **README.md**

