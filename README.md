# 🛒 E-Commerce Shopping Cart System

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=flat&logo=livewire&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat&logo=postgresql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38B2AC?style=flat&logo=tailwind-css&logoColor=white)

A full-featured e-commerce shopping cart system built with Laravel 11, Livewire 3, and PostgreSQL. Features include user authentication, product catalog, shopping cart management, checkout process, multi-language support (English/Spanish), and complete order tracking.

## 🤖 Development with AI

This project was developed using **Cursor IDE** with **Claude Sonnet 4.5**, leveraging AI-assisted development to optimize workflow and productivity. Development was completed over several days with carefully crafted prompts and comprehensive supervision to ensure code quality, best practices, and project requirements adherence.

**Development Approach:**
- ✅ AI-assisted code generation with human supervision
- ✅ Iterative prompt engineering for optimal results
- ✅ Manual review and validation of all generated code
- ✅ Strategic use of AI for architecture decisions
- ✅ Quality control and testing oversight

---

## ✨ Features

### 🔐 Authentication & Authorization
- User registration with complete address details
- Login/Logout functionality
- Role-based access control (Admin/Customer) using Spatie Laravel Permission
- Password reset functionality
- Email verification support

### 🛍️ Product Management
- Product catalog with search functionality
- Product listings with images, prices, and stock levels
- Real product data from DummyJSON API
- Product pagination
- Active/Inactive product status
- Soft deletes for products

### 🛒 Shopping Cart
- User-specific cart stored in database
- Add/Remove items from cart
- Update quantities
- Cart dropdown in navigation bar
- Dedicated cart page
- Real-time cart updates using Livewire events
- Cart persistence across sessions

### 💳 Checkout Process
- Multi-step checkout flow
- Shipping address collection (pre-filled from user profile)
- Fake credit card payment form (for demonstration)
- Order confirmation page
- Order number generation
- Complete order history

### 🌐 Multi-Language Support
- English and Spanish languages
- Language switcher component
- Persistent language selection across sessions
- All UI elements fully translated
- Easy to extend with additional languages

### 📊 Database Features
- PostgreSQL with UUID primary keys
- BIGSERIAL `public_id` fields with 4-digit zero-padding
- Complete relational database design
- Database transactions for checkout
- Proper foreign key relationships
- Soft deletes where appropriate

### 📧 Jobs & Automation
- **Low Stock Notification:** Automated email alerts when product inventory is low
  - Triggered after checkout when stock <= threshold
  - Uses Laravel Queue for async processing
  - Sends detailed email to admin with product information
- **Daily Sales Report:** Automated end-of-day sales summary
  - Runs every evening at 11:30 PM via Laravel Scheduler
  - Comprehensive statistics (orders, revenue, products sold)
  - Detailed breakdown of all daily transactions
  - Sends formatted HTML email to admin

---

## 🏗️ Technology Stack

| Category | Technology |
|----------|-----------|
| **Backend** | Laravel 11 |
| **Frontend** | Livewire 3, Tailwind CSS, Alpine.js |
| **Database** | PostgreSQL 16 |
| **Authentication** | Laravel Breeze |
| **Permissions** | Spatie Laravel Permission |
| **API Integration** | DummyJSON API |
| **Server** | Laragon (Development) |

---

## 📋 Requirements

- PHP 8.2 or higher
- PostgreSQL 16 or higher
- Composer
- Node.js & NPM
- Git

---

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/ccdiego5/ecommerce_cart.git
cd ecommerce_cart
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit your `.env` file with your PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce_cart
DB_USERNAME=postgres
DB_PASSWORD=your_password_here
```

**Create the database:**

```sql
-- Connect to PostgreSQL and run:
CREATE DATABASE ecommerce_cart;
```

### 5. Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Seed the database with test data
php artisan db:seed
```

**Seeded Data Includes:**
- 1 Admin user
- 4 Test customer users
- 30 Products from DummyJSON API

**Default Admin Credentials:**
- **Email:** ccdiego.ve@gmail.com
- **Password:** [Ver database/seeders/UserSeeder.php]

**Test User Credentials:**
- **Email:** john@example.com, jane@example.com, bob@example.com, alice@example.com
- **Password:** password

### 6. Install Spatie Permissions

```bash
# Publish Spatie configuration and migrations (if needed)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 7. Build Assets

```bash
# Compile assets for development
npm run dev

# OR compile for production
npm run build
```

### 8. Start the Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite (if using npm run dev)
npm run dev

# In another terminal, start Queue Worker (for low stock notifications)
php artisan queue:work

# OPTIONAL: In another terminal, start Scheduler (for daily sales report)
php artisan schedule:work
```

**Note:** For production, use:
- Supervisor or similar to manage `queue:work`
- Cron job for `schedule:run` (see below)

**Production Cron Setup:**
```bash
# Add to crontab (crontab -e):
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Access the Application

Open your browser and navigate to:

```
http://127.0.0.1:8000
```

---

## 🎯 Application Flow

### Public Users (Not Logged In)
1. **Landing Page** (`/`) - Displays featured products and call-to-action
2. **Click "Shop Now"** → Redirects to Login
3. **Login Page** (`/login`) - Login or click "Create New Account"
4. **Register Page** (`/register`) - Complete registration with full address details

### Authenticated Users
1. **Dashboard** (`/dashboard`) - Full product catalog with search
2. **Add to Cart** - Products added to database-backed cart
3. **Cart Dropdown** - Quick view of cart items in navigation
4. **Cart Page** (`/cart`) - Full cart view with update/remove options
5. **Checkout** - Enter shipping address and fake payment details
6. **Order Confirmation** (`/order-confirmation/{orderId}`) - Complete order details

---

## 📁 Project Structure

```
ecommerce_cart/
├── app/
│   ├── Http/
│   │   └── Middleware/
│   │       └── SetLocale.php          # Language middleware
│   ├── Livewire/
│   │   ├── CartDropdown.php           # Cart dropdown component
│   │   ├── LandingPage.php            # Landing page component
│   │   ├── LanguageSwitcher.php       # Language switcher
│   │   ├── OrderConfirmation.php      # Order confirmation
│   │   ├── ProductCatalog.php         # Product catalog with search
│   │   └── ShoppingCart.php           # Full cart & checkout
│   ├── Models/
│   │   ├── User.php                   # User model
│   │   ├── Product.php                # Product model
│   │   ├── CartItem.php               # Cart item model
│   │   ├── Order.php                  # Order model
│   │   └── OrderItem.php              # Order item model
│   └── Traits/
│       ├── HasUuid.php                # UUID trait
│       └── HasPublicId.php            # Public ID trait
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2025_12_28_180745_create_products_table.php
│   │   ├── 2025_12_28_180812_create_cart_items_table.php
│   │   ├── 2025_12_28_180813_create_orders_table.php
│   │   ├── 2025_12_28_180814_create_order_items_table.php
│   │   ├── 2025_12_28_190816_add_address_fields_to_users_table.php
│   │   └── 2025_12_28_190908_add_payment_and_shipping_to_orders_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       └── ProductSeeder.php
├── lang/
│   ├── en/
│   │   └── messages.php               # English translations
│   └── es/
│       └── messages.php               # Spanish translations
├── resources/
│   └── views/
│       ├── livewire/
│       │   ├── cart-dropdown.blade.php
│       │   ├── landing-page.blade.php
│       │   ├── language-switcher.blade.php
│       │   ├── order-confirmation.blade.php
│       │   ├── product-catalog.blade.php
│       │   └── shopping-cart.blade.php
│       ├── cart.blade.php
│       ├── dashboard.blade.php
│       ├── order-confirmation.blade.php
│       └── welcome.blade.php
└── routes/
    └── web.php                        # Application routes
```

---

## 🗄️ Database Schema

### Key Tables

**users**
- `id` (UUID, Primary Key)
- `public_id` (BIGSERIAL, Unique, formatted with 4 leading zeros)
- `name`, `email`, `password`
- `phone`, `address`, `city`, `state`, `zip_code`, `country`
- `is_admin` (Boolean)
- `email_verified_at`, `remember_token`
- `created_at`, `updated_at`

**products**
- `id` (UUID, Primary Key)
- `public_id` (BIGSERIAL, Unique)
- `name`, `description`, `price`, `stock_quantity`, `image`
- `low_stock_threshold`, `is_active`
- `created_at`, `updated_at`, `deleted_at`

**cart_items**
- `id` (UUID, Primary Key)
- `public_id` (BIGSERIAL, Unique)
- `user_id` (UUID, Foreign Key → users)
- `product_id` (UUID, Foreign Key → products)
- `quantity` (Integer)
- `created_at`, `updated_at`
- **UNIQUE CONSTRAINT:** `(user_id, product_id)`

**orders**
- `id` (UUID, Primary Key)
- `public_id` (BIGSERIAL, Unique)
- `user_id` (UUID, Foreign Key → users)
- `order_number` (String, e.g., ORD-2025-000001)
- `total_amount` (Decimal)
- `status` (String: pending, completed, cancelled)
- Shipping details: `shipping_name`, `shipping_phone`, `shipping_address`, etc.
- Payment details: `card_last_four`, `card_brand`, `card_expiration`
- `completed_at` (Timestamp)
- `created_at`, `updated_at`

**order_items** (Snapshot)
- `id` (UUID, Primary Key)
- `public_id` (BIGSERIAL, Unique)
- `order_id` (UUID, Foreign Key → orders)
- `product_id` (UUID, Nullable, Foreign Key → products)
- `product_name` (String, snapshot)
- `price` (Decimal, snapshot)
- `quantity` (Integer)
- `created_at`, `updated_at`

---

## 🔑 Key Features Implementation

### UUID & Public ID System
- All primary keys use UUID (36-character string)
- Each table has a `public_id` (BIGSERIAL) for user-friendly IDs
- `public_id` formatted with 4 leading zeros via `HasPublicId` trait
- Example: `public_id: 1` → `formatted_public_id: 0001`

### Cart Management
- Cart is **stored in the database** (`cart_items` table)
- Each cart item is associated with the authenticated user
- Cart persists across sessions
- Real-time updates using Livewire events
- Security: Cart operations verify ownership before modification

### Multi-Language System
- Laravel's built-in localization system
- `SetLocale` middleware sets language from session
- Translation files in `lang/en/messages.php` and `lang/es/messages.php`
- Language switcher component available on all pages
- Language selection persists in session

### Checkout Flow
1. **Cart Review** - User views cart items
2. **Proceed to Checkout** - Transition to checkout form
3. **Shipping Information** - Pre-filled from user profile, editable
4. **Payment Information** - Fake credit card details (demonstration only)
5. **Place Order** - Database transaction creates order and order items
6. **Cart Cleanup** - Cart items cleared after successful order
7. **Confirmation** - Redirect to order confirmation page with details

---

## 🧪 Testing the Application

### Test Scenarios

#### 1. User Registration
- Navigate to `/register`
- Fill in all required fields including address
- Submit form
- Verify redirect to dashboard

#### 2. Product Browsing
- Login as any user
- View products on dashboard
- Use search functionality
- Verify product details display correctly

#### 3. Shopping Cart
- Add products to cart
- Check cart dropdown shows correct items
- Navigate to cart page
- Update quantities
- Remove items
- Verify cart total calculations

#### 4. Checkout Process
- Add items to cart
- Go to cart page
- Click "Proceder al Checkout"
- Fill shipping information
- Fill fake payment details (any 16-digit number)
- Submit order
- Verify order confirmation page displays

#### 5. Language Switching
- Click "English" or "Español" in navigation
- Verify all UI elements change language
- Verify language persists on page reload

#### 6. Admin Access
- Login as admin (ccdiego.ve@gmail.com)
- Note: Admin-specific features can be added later

---

## 🎨 Customization

### Adding New Languages

1. Create new language file:
```bash
mkdir lang/fr
cp lang/en/messages.php lang/fr/messages.php
```

2. Translate all keys in `lang/fr/messages.php`

3. Update `LanguageSwitcher.php`:
```php
public function setLocale($locale)
{
    if (in_array($locale, ['en', 'es', 'fr'])) { // Add 'fr'
        // ...
    }
}
```

4. Add button in `language-switcher.blade.php`

### Modifying Checkout Flow

Edit `app/Livewire/ShoppingCart.php`:
- Modify validation rules in `checkout()` method
- Add/remove form fields
- Update order creation logic

### Changing Product Source

Edit `database/seeders/ProductSeeder.php`:
- Modify API endpoint
- Change data mapping
- Add custom product attributes

---

## 📝 Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | Laravel |
| `APP_ENV` | Environment | local |
| `APP_DEBUG` | Debug mode | true |
| `APP_URL` | Application URL | http://localhost |
| `DB_CONNECTION` | Database driver | pgsql |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 5432 |
| `DB_DATABASE` | Database name | ecommerce_cart |
| `DB_USERNAME` | Database user | postgres |
| `DB_PASSWORD` | Database password | - |

---

## 🐛 Troubleshooting

### Database Connection Issues
```bash
# Clear config cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Verify PostgreSQL is running
# Check credentials in .env match your PostgreSQL setup
```

### Migration Errors
```bash
# Drop all tables and re-migrate
php artisan migrate:fresh --seed

# If sequence errors occur
# Connect to PostgreSQL and manually drop sequences:
DROP SEQUENCE IF EXISTS users_public_id_seq CASCADE;
DROP SEQUENCE IF EXISTS products_public_id_seq CASCADE;
# ... etc for all tables
```

### Livewire Not Updating
```bash
# Clear all caches
php artisan optimize:clear

# Restart development server
# Ctrl+C to stop, then:
php artisan serve
```

### Language Not Switching
```bash
# Clear session
php artisan session:clear

# Verify middleware is registered in bootstrap/app.php
# Check that lang files exist in lang/en/ and lang/es/
```

### Assets Not Loading
```bash
# Rebuild assets
npm run build

# OR for development with hot reload
npm run dev
```

---

## 📚 Additional Documentation

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Livewire 3 Documentation](https://livewire.laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6/introduction)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

---

## 🎓 Learning Resources

This project demonstrates:
- ✅ Modern Laravel development practices
- ✅ Livewire component architecture
- ✅ PostgreSQL with advanced features (UUIDs, sequences)
- ✅ Database relationships and transactions
- ✅ Multi-language application development
- ✅ E-commerce cart and checkout flows
- ✅ Role-based access control
- ✅ API integration
- ✅ AI-assisted development workflow

---

## ✅ Completed Features

- [x] **Low stock notification job (Laravel Queue)** - Automated email alerts when inventory is low
- [x] **Daily sales report (Laravel Scheduler)** - End-of-day sales summary sent to admin

## 📦 Future Enhancements

- [ ] Admin dashboard for product management
- [ ] Order tracking and status updates
- [ ] Real-time email notifications for orders (beyond daily reports)
- [ ] Product reviews and ratings
- [ ] Wishlist functionality
- [ ] Payment gateway integration (Stripe/PayPal)
- [ ] Advanced inventory management
- [ ] Sales analytics dashboard with charts

---

## 👥 Credits

**Developer:** Diego Cardenas  
**Email:** ccdiego.ve@gmail.com  
**GitHub:** [@ccdiego5](https://github.com/ccdiego5)

**Collaborator:** Dylan Michael Ryan (@dylanmichaelryan)

**AI Assistant:** Claude Sonnet 4.5 (via Cursor IDE)

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

## 🤝 Contributing

This is an educational project. Feel free to fork, modify, and use it for learning purposes.

---

## 📞 Support

For questions or issues:
1. Check the Troubleshooting section above
2. Review Laravel/Livewire documentation
3. Contact: ccdiego.ve@gmail.com

---

**Built with ❤️ using Laravel, Livewire, and AI-assisted development**

*Last Updated: December 28, 2025*
