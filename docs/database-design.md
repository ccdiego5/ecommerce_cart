# 🗃️ Diseño de Base de Datos - E-commerce Shopping Cart

## 🐘 PostgreSQL + UUIDs + Public IDs

### **Convención de IDs:**
- **`id`**: UUID (string 36 caracteres) - Primary Key
- **`public_id`**: BIGINT secuencial con formato 0001, 0002, etc. - Para mostrar al usuario

---

## 📊 Diagrama de Relaciones

```
users (Laravel Auth)
  ├── cart_items (carrito actual del usuario)
  ├── orders (compras completadas)
  └── notifications (opcional)

products
  ├── cart_items
  └── order_items

orders
  └── order_items
```

---

## 📋 Tablas Necesarias

### 1️⃣ **users** (Viene con Laravel Breeze - Modificada)
```sql
id                  UUID PRIMARY KEY DEFAULT gen_random_uuid()
public_id           BIGSERIAL UNIQUE NOT NULL
name                VARCHAR(255) NOT NULL
email               VARCHAR(255) UNIQUE NOT NULL
email_verified_at   TIMESTAMP NULL
password            VARCHAR(255) NOT NULL
is_admin            BOOLEAN DEFAULT FALSE
remember_token      VARCHAR(100) NULL
created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
```

**Notas:**
- Usuario admin dummy: `admin@example.com` / `password`
- `gen_random_uuid()` genera UUIDs automáticamente en PostgreSQL
- `BIGSERIAL` auto-incrementa el public_id
- Agregamos `is_admin` para el usuario admin que recibe los emails

---

### 2️⃣ **products** (Nueva)
```sql
id                  UUID PRIMARY KEY DEFAULT gen_random_uuid()
public_id           BIGSERIAL UNIQUE NOT NULL
name                VARCHAR(255) NOT NULL
description         TEXT NULL
price               NUMERIC(10, 2) NOT NULL
stock_quantity      INTEGER NOT NULL DEFAULT 0
image               VARCHAR(255) NULL
low_stock_threshold INTEGER DEFAULT 10
is_active           BOOLEAN DEFAULT TRUE
deleted_at          TIMESTAMP NULL
created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
```

**Campos Explicados:**
- `id`: UUID para referencias internas
- `public_id`: Formato 0001, 0002 para mostrar al usuario (ej: "Producto #0042")
- `price`: NUMERIC en PostgreSQL (10 dígitos totales, 2 decimales)
- `stock_quantity`: Stock actual disponible
- `low_stock_threshold`: Cuando el stock baje de este número, dispara el Job
- `is_active`: Para desactivar productos sin eliminarlos
- `deleted_at`: Soft delete (mantener historial en órdenes)
- `image`: Opcional, ruta de imagen del producto

**Indices:**
```sql
CREATE INDEX idx_products_is_active ON products(is_active);
CREATE INDEX idx_products_stock ON products(stock_quantity);
CREATE INDEX idx_products_public_id ON products(public_id);
CREATE INDEX idx_products_deleted_at ON products(deleted_at);
```

---

### 3️⃣ **cart_items** (Nueva)
```sql
id                  UUID PRIMARY KEY DEFAULT gen_random_uuid()
public_id           BIGSERIAL UNIQUE NOT NULL
user_id             UUID NOT NULL
product_id          UUID NOT NULL
quantity            INTEGER NOT NULL DEFAULT 1 CHECK (quantity > 0)
created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

CONSTRAINT fk_cart_items_user FOREIGN KEY (user_id) 
    REFERENCES users(id) ON DELETE CASCADE,
CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) 
    REFERENCES products(id) ON DELETE CASCADE,
CONSTRAINT unique_user_product UNIQUE (user_id, product_id)
```

**Campos Explicados:**
- `user_id`: Usuario dueño del carrito (UUID)
- `product_id`: Producto en el carrito (UUID)
- `quantity`: Cantidad seleccionada (CHECK constraint > 0)
- **UNIQUE CONSTRAINT**: Un usuario no puede tener el mismo producto duplicado en su carrito

**Indices:**
```sql
CREATE INDEX idx_cart_items_user_id ON cart_items(user_id);
CREATE INDEX idx_cart_items_product_id ON cart_items(product_id);
CREATE INDEX idx_cart_items_public_id ON cart_items(public_id);
```

---

### 4️⃣ **orders** (Nueva)
```sql
id                  UUID PRIMARY KEY DEFAULT gen_random_uuid()
public_id           BIGSERIAL UNIQUE NOT NULL
user_id             UUID NOT NULL
order_number        VARCHAR(50) UNIQUE NOT NULL
total_amount        NUMERIC(10, 2) NOT NULL
status              VARCHAR(20) DEFAULT 'completed' CHECK (status IN ('pending', 'completed', 'cancelled'))
completed_at        TIMESTAMP NULL
created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

CONSTRAINT fk_orders_user FOREIGN KEY (user_id) 
    REFERENCES users(id) ON DELETE CASCADE
```

**Campos Explicados:**
- `public_id`: Formato 0001, 0002 para mostrar (ej: "Orden #0123")
- `order_number`: Número único de orden (ej: ORD-2025-000001)
- `total_amount`: Total pagado en la orden (NUMERIC en PostgreSQL)
- `status`: Estado de la orden (CHECK constraint para valores válidos)
- `completed_at`: Fecha de compra (para el reporte diario)

**Indices:**
```sql
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_public_id ON orders(public_id);
CREATE INDEX idx_orders_completed_at ON orders(completed_at);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_order_number ON orders(order_number);
```

---

### 5️⃣ **order_items** (Nueva)
```sql
id                  UUID PRIMARY KEY DEFAULT gen_random_uuid()
public_id           BIGSERIAL UNIQUE NOT NULL
order_id            UUID NOT NULL
product_id          UUID NOT NULL
product_name        VARCHAR(255) NOT NULL
product_price       NUMERIC(10, 2) NOT NULL
quantity            INTEGER NOT NULL CHECK (quantity > 0)
subtotal            NUMERIC(10, 2) NOT NULL
created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) 
    REFERENCES orders(id) ON DELETE CASCADE,
CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) 
    REFERENCES products(id) ON DELETE CASCADE
```

**Campos Explicados:**
- `order_id`: Orden a la que pertenece (UUID)
- `product_id`: Referencia al producto (UUID, puede ser soft deleted)
- `product_name`: Snapshot del nombre (por si el producto cambia después)
- `product_price`: Snapshot del precio al momento de compra
- `quantity`: Cantidad comprada
- `subtotal`: quantity * product_price (calculado)

**¿Por qué snapshots?**
Si un producto cambia de nombre o precio, las órdenes históricas mantienen los datos originales.

**Indices:**
```sql
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
CREATE INDEX idx_order_items_public_id ON order_items(public_id);
```

---

### 6️⃣ **jobs** (Laravel Queue)
```sql
id                  BIGSERIAL PRIMARY KEY
queue               VARCHAR(255) NOT NULL
payload             TEXT NOT NULL
attempts            SMALLINT NOT NULL DEFAULT 0
reserved_at         INTEGER NULL
available_at        INTEGER NOT NULL
created_at          INTEGER NOT NULL
```

**Notas:**
- Laravel la crea automáticamente con `php artisan queue:table`
- Necesaria para el sistema de Jobs/Queues
- PostgreSQL usa BIGSERIAL en lugar de BIGINT UNSIGNED

**Indices:**
```sql
CREATE INDEX idx_jobs_queue ON jobs(queue);
```

---

### 7️⃣ **failed_jobs** (Laravel Queue)
```sql
id                  BIGSERIAL PRIMARY KEY
uuid                VARCHAR(255) UNIQUE NOT NULL
connection          TEXT NOT NULL
queue               TEXT NOT NULL
payload             TEXT NOT NULL
exception           TEXT NOT NULL
failed_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
```

**Notas:**
- Laravel la crea automáticamente con `php artisan queue:failed-table`
- Registra jobs que fallaron para debugging
- PostgreSQL usa BIGSERIAL en lugar de BIGINT UNSIGNED

---

## 🎯 Funcionalidades por Tabla

### **Carrito de Compras (cart_items)**
- ✅ Añadir producto al carrito
- ✅ Actualizar cantidad
- ✅ Eliminar item
- ✅ Ver carrito completo con totales
- ✅ Persistencia por usuario autenticado

### **Completar Compra (Process: cart_items → orders + order_items)**
1. Crear registro en `orders`
2. Copiar items de `cart_items` a `order_items`
3. Reducir `stock_quantity` de cada producto
4. Si stock < threshold → Disparar `LowStockNotificationJob`
5. Vaciar `cart_items` del usuario
6. Mostrar confirmación

### **Reporte Diario (Scheduled Command)**
```sql
SELECT 
    p.public_id,
    p.name,
    SUM(oi.quantity) as total_sold,
    SUM(oi.subtotal) as revenue
FROM order_items oi
JOIN orders o ON o.id = oi.order_id
JOIN products p ON p.id = oi.product_id
WHERE DATE(o.completed_at) = CURRENT_DATE
GROUP BY p.id, p.public_id, p.name
ORDER BY total_sold DESC
```

**Nota PostgreSQL:** Usar `CURRENT_DATE` en lugar de `CURDATE()`

---

## 📦 Seeders a Crear

### **1. UserSeeder**
```php
use Illuminate\Support\Str;

// Usuario Admin (dummy para emails)
User::create([
    'id' => Str::uuid(),
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true,
    'email_verified_at' => now(),
]);

// Usuarios de prueba (el factory generará UUIDs automáticamente)
User::factory(5)->create();
```

**Nota:** Necesitarás configurar el factory para usar UUIDs:
```php
// database/factories/UserFactory.php
'id' => Str::uuid(),
```

### **2. ProductSeeder**
```php
use Illuminate\Support\Str;

// 15-20 productos de ejemplo con:
Product::create([
    'id' => Str::uuid(),
    'name' => 'Laptop HP',
    'description' => 'Laptop de alto rendimiento',
    'price' => 899.99,
    'stock_quantity' => 5, // Stock bajo para probar el Job
    'low_stock_threshold' => 10,
    'image' => 'https://placehold.co/300x300/3b82f6/ffffff?text=Laptop',
    'is_active' => true,
]);

// Más productos con stock variado (algunos bajos, otros altos)
// El public_id se genera automáticamente (BIGSERIAL)
```

### **3. CartItemSeeder (Opcional)**
```php
use Illuminate\Support\Str;

// Algunos carritos pre-cargados para testing
CartItem::create([
    'id' => Str::uuid(),
    'user_id' => User::where('is_admin', false)->first()->id,
    'product_id' => Product::first()->id,
    'quantity' => 2,
]);
```

### **4. OrderSeeder (Opcional)**
```php
use Illuminate\Support\Str;

// Órdenes de días anteriores para testing del reporte
Order::create([
    'id' => Str::uuid(),
    'user_id' => User::first()->id,
    'order_number' => 'ORD-' . now()->format('Y') . '-' . str_pad(1, 6, '0', STR_PAD_LEFT),
    'total_amount' => 1799.98,
    'status' => 'completed',
    'completed_at' => now()->subDays(1), // Ayer
]);

// OrderItems correspondientes
OrderItem::create([
    'id' => Str::uuid(),
    'order_id' => $order->id,
    'product_id' => $product->id,
    'product_name' => $product->name,
    'product_price' => $product->price,
    'quantity' => 2,
    'subtotal' => $product->price * 2,
]);
```

---

## 🚀 Orden de Migración

```bash
# Existentes (Laravel Breeze)
2014_10_12_000000_create_users_table.php          # Modificar para UUID + public_id
2014_10_12_100000_create_password_reset_tokens_table.php
2019_08_19_000000_create_failed_jobs_table.php    # Modificar para PostgreSQL

# Nuevas a crear
2025_12_28_000001_create_products_table.php
2025_12_28_000002_create_cart_items_table.php
2025_12_28_000003_create_orders_table.php
2025_12_28_000004_create_order_items_table.php
2025_12_28_000005_create_jobs_table.php           # php artisan queue:table
```

### **Modificaciones a Migraciones de Laravel:**

**users table:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->bigInteger('public_id')->generatedAs()->always();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->boolean('is_admin')->default(false);
    $table->rememberToken();
    $table->timestamps();
    
    $table->unique('public_id');
});

// Crear secuencia para public_id
DB::statement('CREATE SEQUENCE users_public_id_seq START 1');
DB::statement('ALTER TABLE users ALTER COLUMN public_id SET DEFAULT nextval(\'users_public_id_seq\')');
```

**O más simple con BIGSERIAL:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->bigInteger('public_id')->nullable(); // Lo manejamos manualmente
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->boolean('is_admin')->default(false);
    $table->rememberToken();
    $table->timestamps();
    
    $table->unique('public_id');
    $table->index('public_id');
});

// Crear secuencia para public_id con formato personalizado
DB::statement('CREATE SEQUENCE users_public_id_seq START 1 MINVALUE 1');
```

---

## ⚙️ Configuraciones Adicionales

### **.env**
```env
# Database PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce_cart
DB_USERNAME=postgres
DB_PASSWORD=

# Queue (para local usamos database)
QUEUE_CONNECTION=database

# Mail (Mailtrap o Log)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@ecommerce.test
MAIL_FROM_NAME="E-commerce Cart"
```

### **config/database.php**
Asegurar que PostgreSQL esté configurado:
```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'schema' => 'public',
    'sslmode' => 'prefer',
],
```

### **Model Trait para UUIDs**
Crear trait para los modelos:
```php
// app/Traits/HasUuid.php
namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
```

### **Model Trait para Public IDs**
```php
// app/Traits/HasPublicId.php
namespace App\Traits;

trait HasPublicId
{
    protected static function bootHasPublicId()
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $sequenceName = $model->getTable() . '_public_id_seq';
                $model->public_id = \DB::raw("nextval('{$sequenceName}')");
            }
        });
    }
    
    public function getFormattedPublicIdAttribute()
    {
        return str_pad($this->public_id, 4, '0', STR_PAD_LEFT);
    }
}
```

**Uso en Modelos:**
```php
use App\Traits\HasUuid;
use App\Traits\HasPublicId;

class Product extends Model
{
    use HasUuid, HasPublicId, SoftDeletes;
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $appends = ['formatted_public_id'];
    
    // Ahora puedes usar: $product->formatted_public_id // "0042"
}
```

### **Scheduled Command**
En `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('report:daily-sales')
             ->dailyAt('20:00'); // 8 PM cada día
}
```

---

## 📊 Estimación de Registros (Testing)

| Tabla | Registros Iniciales | Formato Public ID |
|-------|---------------------|-------------------|
| users | 6 (1 admin + 5 test) | 0001, 0002, 0003... |
| products | 20 | 0001, 0002, 0003... |
| cart_items | 10-15 | 0001, 0002, 0003... |
| orders | 5-10 (fechas variadas) | 0001, 0002, 0003... |
| order_items | 15-30 | 0001, 0002, 0003... |

**Ejemplo de visualización:**
- Usuario: `Admin User #0001`
- Producto: `Laptop HP #0042`
- Orden: `Orden #0123` (o usar order_number: `ORD-2025-000123`)

---

## ✅ Validaciones de Negocio

### **Al añadir al carrito:**
- ✅ Producto existe y está activo
- ✅ Stock disponible >= cantidad solicitada
- ✅ Si producto ya está en carrito, sumar cantidades
- ✅ No exceder stock disponible

### **Al actualizar cantidad:**
- ✅ Nueva cantidad > 0 (si es 0, eliminar)
- ✅ Nueva cantidad <= stock disponible

### **Al completar compra:**
- ✅ Carrito no vacío
- ✅ Stock suficiente de todos los productos
- ✅ Transaction para atomicidad
- ✅ Si falla, rollback completo

### **Low Stock Job:**
- ✅ Disparar solo si stock < threshold
- ✅ No duplicar emails (verificar si ya se envió hoy)
- ✅ Queue para no bloquear request

---

## 🎯 Alcance Final

**Tablas Core:** 5 (products, cart_items, orders, order_items, jobs)

**IDs:**
- **Primary Keys:** UUIDs (36 caracteres) para todas las relaciones internas
- **Public IDs:** BIGINT con formato 0001, 0002, etc. para mostrar al usuario

**Relationships:**
- User hasMany CartItems (via UUID)
- User hasMany Orders (via UUID)
- Product hasMany CartItems (via UUID)
- Product hasMany OrderItems (via UUID)
- Order belongsTo User (via UUID)
- Order hasMany OrderItems (via UUID)
- CartItem belongsTo User, Product (via UUID)
- OrderItem belongsTo Order, Product (via UUID)

**Jobs:** 1 (LowStockNotificationJob)

**Commands:** 1 (DailySalesReportCommand)

**Mailable:** 2 (LowStockNotification, DailySalesReport)

**Traits:** 2 (HasUuid, HasPublicId)

---

## ❓ Decisiones Pendientes

1. ~~**¿Campo is_admin en users o tabla roles separada?**~~
   - ✅ **Decidido:** Simple boolean `is_admin` (KISS principle)

2. **¿Imágenes reales o placeholders?**
   - Recomendación: Placeholders (https://placehold.co/300x300)

3. **¿Sistema de cupones/descuentos?**
   - Recomendación: NO (fuera de alcance)

4. ~~**¿Soft deletes en products?**~~
   - ✅ **Decidido:** SÍ (para mantener historial en orders)

5. ~~**¿UUIDs o BIGINT?**~~
   - ✅ **Decidido:** UUIDs para PKs + public_id BIGINT para UI

6. ~~**¿Base de datos?**~~
   - ✅ **Decidido:** PostgreSQL

7. **¿Manejo de public_id?**
   - Opción A: BIGSERIAL nativo de PostgreSQL (más simple)
   - Opción B: Secuencias manuales con más control
   - **Recomendación:** Opción A con trait `HasPublicId`

---

## 📝 Próximo Paso

Con esta estructura clara adaptada a **PostgreSQL + UUIDs + Public IDs**, podemos:
1. ✅ Instalar Laravel + Breeze + Livewire
2. ✅ Configurar PostgreSQL en Laragon
3. ✅ Crear los Traits (HasUuid, HasPublicId)
4. ✅ Crear/modificar las migraciones
5. ✅ Crear los modelos con traits
6. ✅ Crear los seeders
7. ✅ Comenzar con los componentes Livewire

## 🔑 Puntos Clave PostgreSQL vs MySQL

| Aspecto | MySQL | PostgreSQL |
|---------|-------|------------|
| Primary Key Auto | BIGINT UNSIGNED | BIGSERIAL / UUID |
| UUID Generator | Extensión | `gen_random_uuid()` nativo |
| Decimales | DECIMAL(10,2) | NUMERIC(10,2) |
| ENUM | ENUM('val1','val2') | VARCHAR + CHECK constraint |
| Texto largo | LONGTEXT | TEXT |
| Unsigned | INTEGER UNSIGNED | INTEGER + CHECK >= 0 |
| Current Date | CURDATE() | CURRENT_DATE |

## 💡 Ventajas de UUIDs

✅ **Seguridad:** No se puede enumerar registros  
✅ **Escalabilidad:** Generación distribuida sin colisiones  
✅ **Migraciones:** Fácil merge de datos de diferentes fuentes  
✅ **APIs:** URLs no predecibles  

**Desventaja:** Tamaño (36 bytes vs 8 bytes BIGINT)  
**Solución:** public_id para mostrar al usuario (ej: "Orden #0123")

**¿Te parece bien esta estructura o quieres ajustar algo?**

