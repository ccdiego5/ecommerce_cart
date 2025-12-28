# 📋 PLANIFICACIÓN: Low Stock Notification & Daily Sales Report

## 🎯 OBJETIVO

Implementar las 2 características faltantes del proyecto:
1. **Low Stock Notification** - Laravel Job/Queue para notificar stock bajo
2. **Daily Sales Report** - Scheduled Job para reporte diario de ventas

---

## 📊 ANÁLISIS DE REQUISITOS

### **1. Low Stock Notification**

**Requisito original:**
> "When a product's stock is running low, a Laravel Job/Queue should be triggered to send an email to a dummy admin user."

**Interpretación:**
- ✅ Detectar cuando `stock_quantity <= low_stock_threshold`
- ✅ Disparar un Job de Laravel (procesamiento asíncrono)
- ✅ Enviar email al usuario admin
- ✅ Debe usar Queue (no síncrono)

**Pregunta clave:** ¿Cuándo se dispara?
- **Opción A:** Después de cada checkout (cuando el stock disminuye)
- **Opción B:** Periódicamente revisar todos los productos
- **Decisión:** Opción A (después de checkout) ✅

---

### **2. Daily Sales Report**

**Requisito original:**
> "Implement a scheduled job (cron) that runs every evening and sends a report of all products sold that day to the email of the dummy admin user."

**Interpretación:**
- ✅ Job programado (Task Scheduling)
- ✅ Corre automáticamente cada noche
- ✅ Obtiene órdenes del día actual
- ✅ Genera reporte con productos vendidos
- ✅ Envía email al admin

**Pregunta clave:** ¿Qué hora?
- **Decisión:** 11:30 PM (23:30) cada noche ✅

---

## 🏗️ ARQUITECTURA DE LA SOLUCIÓN

### **Componentes Necesarios:**

```
Low Stock Notification:
├── app/Jobs/SendLowStockNotification.php         (Job para queue)
├── app/Mail/LowStockAlert.php                    (Mailable)
├── resources/views/emails/low-stock-alert.blade.php (Vista del email)
└── Trigger: En ShoppingCart::checkout() después de decreaseStock()

Daily Sales Report:
├── app/Jobs/SendDailySalesReport.php             (Job programado)
├── app/Mail/DailySalesReport.php                 (Mailable)
├── resources/views/emails/daily-sales-report.blade.php (Vista del email)
├── app/Console/Kernel.php o routes/console.php   (Scheduler)
└── Trigger: Laravel Scheduler (cron)
```

---

## 📝 CHECKLIST DE REQUISITOS PREVIOS

### **A. Configuración del Sistema**

#### **1. Mail Configuration (.env)**
```env
MAIL_MAILER=log  # Para testing (usar smtp en producción)
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@ecommerce-cart.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Estado actual:** ✅ Ya configurado en .env.example

---

#### **2. Queue Configuration (.env)**
```env
QUEUE_CONNECTION=database  # Usar database driver
```

**Verificar:**
- [ ] Tabla `jobs` existe (migración ya creada ✅)
- [ ] Tabla `failed_jobs` existe (migración ya creada ✅)

**Estado actual:** ✅ Migraciones ya existen (`0001_01_01_000002_create_jobs_table.php`)

---

#### **3. Admin User para Emails**

**Necesitamos:**
- Email del admin: `ccdiego.ve@gmail.com` ✅ (ya existe)
- User con rol admin ✅ (ya existe en UserSeeder)

**Verificar:**
```php
// En UserSeeder.php - Ya existe:
$adminUser = User::create([
    'email' => 'ccdiego.ve@gmail.com',
    'is_admin' => true,
]);
```

**Estado actual:** ✅ Admin user ya existe

---

### **B. Database Schema**

#### **Tabla: products**
**Campos necesarios:**
- `stock_quantity` (int) ✅ Ya existe
- `low_stock_threshold` (int) ✅ Ya existe (default: 5)

**Estado actual:** ✅ Todo listo

---

#### **Tabla: orders**
**Campos necesarios:**
- `created_at` (timestamp) ✅ Ya existe
- `completed_at` (timestamp) ✅ Ya existe
- `status` (string) ✅ Ya existe

**Query para daily report:**
```sql
SELECT o.*, oi.product_name, oi.quantity, oi.price
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE DATE(o.completed_at) = CURRENT_DATE
AND o.status = 'completed'
ORDER BY o.completed_at DESC;
```

**Estado actual:** ✅ Todo listo

---

### **C. Modelos Eloquent**

#### **Product Model**
**Método necesario:**
```php
// Ya existe en app/Models/Product.php
public function isLowStock(): bool
{
    return $this->stock_quantity <= $this->low_stock_threshold;
}
```

**Estado actual:** ✅ Ya implementado

---

#### **Order Model**
**Relaciones necesarias:**
```php
// Ya existen en app/Models/Order.php
public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
```

**Método adicional necesario:**
```php
// NUEVO - Agregar a Order.php
public static function getTodaySales()
{
    return self::with('orderItems.product')
        ->whereDate('completed_at', today())
        ->where('status', 'completed')
        ->get();
}
```

**Estado actual:** ⏳ Agregar método `getTodaySales()`

---

## 🛠️ IMPLEMENTACIÓN PASO A PASO

### **FASE 1: Low Stock Notification**

#### **Step 1.1: Crear Job**
```bash
php artisan make:job SendLowStockNotification
```

**Contenido:**
```php
<?php

namespace App\Jobs;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLowStockNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Product $product
    ) {}

    public function handle(): void
    {
        // Obtener admin user
        $admin = User::where('is_admin', true)->first();
        
        if ($admin && $this->product->isLowStock()) {
            Mail::to($admin->email)
                ->send(new LowStockAlert($this->product));
        }
    }
}
```

---

#### **Step 1.2: Crear Mailable**
```bash
php artisan make:mail LowStockAlert
```

**Contenido:**
```php
<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Low Stock Alert: ' . $this->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-alert',
        );
    }
}
```

---

#### **Step 1.3: Crear Vista del Email**
**Archivo:** `resources/views/emails/low-stock-alert.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .alert { background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; }
        .product-info { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .warning { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>⚠️ Low Stock Alert</h2>
        
        <div class="alert">
            <p><strong>Warning:</strong> A product in your inventory is running low on stock.</p>
        </div>

        <div class="product-info">
            <h3>{{ $product->name }}</h3>
            <p><strong>Product ID:</strong> {{ $product->formatted_public_id }}</p>
            <p><strong>Current Stock:</strong> <span class="warning">{{ $product->stock_quantity }} units</span></p>
            <p><strong>Low Stock Threshold:</strong> {{ $product->low_stock_threshold }} units</p>
            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
        </div>

        <p><strong>Action Required:</strong> Please restock this product as soon as possible to avoid stockouts.</p>
        
        <p style="color: #6c757d; font-size: 12px; margin-top: 30px;">
            This is an automated notification from {{ config('app.name') }}.
        </p>
    </div>
</body>
</html>
```

---

#### **Step 1.4: Integrar en Checkout**
**Modificar:** `app/Livewire/ShoppingCart.php`

**En el método `checkout()`, después de `decreaseStock()`:**

```php
// Dentro de DB::transaction() en checkout()
foreach ($this->cartItems as $cartItem) {
    OrderItem::create([...]);
    
    // Decrease product stock
    $cartItem->product->decreaseStock($cartItem->quantity);
    
    // NUEVO: Check if product is low stock and dispatch job
    if ($cartItem->product->isLowStock()) {
        \App\Jobs\SendLowStockNotification::dispatch($cartItem->product);
    }
}
```

---

### **FASE 2: Daily Sales Report**

#### **Step 2.1: Agregar método a Order Model**
**Modificar:** `app/Models/Order.php`

```php
public static function getTodaySales()
{
    return self::with(['orderItems', 'user'])
        ->whereDate('completed_at', today())
        ->where('status', 'completed')
        ->get();
}

public static function getTodaySalesStats()
{
    $orders = self::getTodaySales();
    
    return [
        'total_orders' => $orders->count(),
        'total_revenue' => $orders->sum('total_amount'),
        'products_sold' => $orders->flatMap->orderItems->sum('quantity'),
        'orders' => $orders,
    ];
}
```

---

#### **Step 2.2: Crear Job**
```bash
php artisan make:job SendDailySalesReport
```

**Contenido:**
```php
<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $salesData = Order::getTodaySalesStats();
        
        // Obtener admin user
        $admin = User::where('is_admin', true)->first();
        
        if ($admin) {
            Mail::to($admin->email)
                ->send(new DailySalesReport($salesData));
        }
    }
}
```

---

#### **Step 2.3: Crear Mailable**
```bash
php artisan make:mail DailySalesReport
```

**Contenido:**
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $salesData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📊 Daily Sales Report - ' . now()->format('F j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-sales-report',
        );
    }
}
```

---

#### **Step 2.4: Crear Vista del Email**
**Archivo:** `resources/views/emails/daily-sales-report.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; border-radius: 5px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { background-color: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #007bff; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #dee2e6; }
        tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Daily Sales Report</h1>
            <p>{{ now()->format('l, F j, Y') }}</p>
        </div>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-value">{{ $salesData['total_orders'] }}</div>
                <div>Total Orders</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">${{ number_format($salesData['total_revenue'], 2) }}</div>
                <div>Total Revenue</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $salesData['products_sold'] }}</div>
                <div>Products Sold</div>
            </div>
        </div>

        @if($salesData['orders']->count() > 0)
            <h2>Order Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesData['orders'] as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->orderItems->count() }} items</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>{{ $order->completed_at->format('g:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3>Products Sold Today</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $productsSummary = [];
                        foreach($salesData['orders'] as $order) {
                            foreach($order->orderItems as $item) {
                                $key = $item->product_name;
                                if (!isset($productsSummary[$key])) {
                                    $productsSummary[$key] = [
                                        'quantity' => 0,
                                        'price' => $item->price,
                                        'total' => 0,
                                    ];
                                }
                                $productsSummary[$key]['quantity'] += $item->quantity;
                                $productsSummary[$key]['total'] += $item->quantity * $item->price;
                            }
                        }
                    @endphp

                    @foreach($productsSummary as $productName => $data)
                        <tr>
                            <td>{{ $productName }}</td>
                            <td>{{ $data['quantity'] }}</td>
                            <td>${{ number_format($data['price'], 2) }}</td>
                            <td>${{ number_format($data['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #6c757d; padding: 40px;">
                No sales recorded for today.
            </p>
        @endif

        <p style="color: #6c757d; font-size: 12px; margin-top: 30px;">
            This is an automated daily report from {{ config('app.name') }}.
        </p>
    </div>
</body>
</html>
```

---

#### **Step 2.5: Configurar Scheduler**

**Opción A: Laravel 11+ (routes/console.php)**
```php
<?php

use App\Jobs\SendDailySalesReport;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SendDailySalesReport)
    ->dailyAt('23:30')
    ->timezone('America/New_York')
    ->name('daily-sales-report')
    ->onSuccess(function () {
        \Log::info('Daily sales report sent successfully');
    })
    ->onFailure(function () {
        \Log::error('Daily sales report failed to send');
    });
```

**Opción B: Laravel 10 (app/Console/Kernel.php)**
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->job(new \App\Jobs\SendDailySalesReport)
        ->dailyAt('23:30')
        ->timezone('America/New_York');
}
```

---

## 🧪 TESTING PLAN

### **Test 1: Low Stock Notification**

```bash
# 1. Configurar queue
php artisan queue:table  # Ya existe
php artisan migrate      # Ya ejecutado

# 2. Configurar mail
# En .env: MAIL_MAILER=log

# 3. Ejecutar queue worker en background
php artisan queue:work

# 4. Hacer un checkout que deje un producto en stock bajo
# - Login como john@example.com
# - Agregar producto al carrito con cantidad que deje stock <= threshold
# - Completar checkout

# 5. Verificar job fue ejecutado
# - Check storage/logs/laravel.log para el email
# - Verificar tabla jobs está vacía (job procesado)
# - Verificar email en log
```

---

### **Test 2: Daily Sales Report**

```bash
# 1. Test manual (simular el schedule)
php artisan tinker
>>> \App\Jobs\SendDailySalesReport::dispatch();
>>> exit

# 2. Verificar email en log
# - Check storage/logs/laravel.log

# 3. Test con scheduler (para ver si corre)
php artisan schedule:work

# 4. Test específico de un comando
php artisan schedule:test
```

---

## 📦 ARCHIVOS A CREAR/MODIFICAR

### **Archivos Nuevos (8):**
1. ✅ `app/Jobs/SendLowStockNotification.php`
2. ✅ `app/Jobs/SendDailySalesReport.php`
3. ✅ `app/Mail/LowStockAlert.php`
4. ✅ `app/Mail/DailySalesReport.php`
5. ✅ `resources/views/emails/low-stock-alert.blade.php`
6. ✅ `resources/views/emails/daily-sales-report.blade.php`
7. ✅ `routes/console.php` (modificar para schedule)
8. ✅ `tests/Feature/LowStockNotificationTest.php` (opcional)

### **Archivos a Modificar (3):**
1. ✅ `app/Livewire/ShoppingCart.php` - Agregar dispatch de job
2. ✅ `app/Models/Order.php` - Agregar métodos getTodaySales()
3. ✅ `.env.example` - Documentar configuración de mail/queue

---

## ⚙️ CONFIGURACIÓN NECESARIA

### **En .env:**
```env
# Mail Configuration (para testing)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@ecommerce-cart.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=database

# Timezone para scheduler
APP_TIMEZONE=UTC
```

---

## 🚀 COMANDOS PARA EJECUTAR

### **Durante Desarrollo:**
```bash
# 1. Crear Jobs
php artisan make:job SendLowStockNotification
php artisan make:job SendDailySalesReport

# 2. Crear Mailables
php artisan make:mail LowStockAlert
php artisan make:mail DailySalesReport

# 3. Ejecutar queue worker (en terminal separada)
php artisan queue:work

# 4. Test scheduler
php artisan schedule:work

# 5. Test manual de jobs
php artisan tinker
>>> \App\Jobs\SendLowStockNotification::dispatch(\App\Models\Product::first());
>>> \App\Jobs\SendDailySalesReport::dispatch();
```

### **En Producción:**
```bash
# 1. Queue worker con supervisor
php artisan queue:work --daemon

# 2. Cron entry (agregar a crontab)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### **Pre-requisitos:**
- [x] Admin user existe en DB
- [x] Tabla `jobs` existe
- [x] Tabla `failed_jobs` existe
- [x] Product model tiene `isLowStock()` method
- [x] Order model tiene relaciones
- [ ] Configurar .env para mail
- [ ] Configurar .env para queue

### **Low Stock Notification:**
- [ ] Crear Job `SendLowStockNotification`
- [ ] Crear Mailable `LowStockAlert`
- [ ] Crear vista `low-stock-alert.blade.php`
- [ ] Modificar `ShoppingCart::checkout()`
- [ ] Test: Hacer checkout que active notificación
- [ ] Verificar email enviado

### **Daily Sales Report:**
- [ ] Agregar métodos a `Order` model
- [ ] Crear Job `SendDailySalesReport`
- [ ] Crear Mailable `DailySalesReport`
- [ ] Crear vista `daily-sales-report.blade.php`
- [ ] Configurar scheduler en `routes/console.php`
- [ ] Test: Dispatch manual
- [ ] Test: Schedule:work

### **Testing:**
- [ ] Test Low Stock con queue:work
- [ ] Test Daily Report con tinker
- [ ] Verificar emails en log
- [ ] Test con diferentes escenarios
- [ ] Documentar en README

### **Documentation:**
- [ ] Actualizar README.md con instrucciones
- [ ] Documentar comandos necesarios
- [ ] Agregar ejemplos de uso
- [ ] Update QUICKSTART.md

---

## ⏱️ ESTIMACIÓN DE TIEMPO

| Tarea | Tiempo Estimado |
|-------|-----------------|
| Low Stock Notification | 30-45 minutos |
| Daily Sales Report | 45-60 minutos |
| Testing | 15-20 minutos |
| Documentación | 10-15 minutos |
| **TOTAL** | **1.5 - 2 horas** |

---

## ✅ VERIFICACIÓN FINAL

### **Low Stock debe:**
- ✅ Dispararse después de checkout si stock <= threshold
- ✅ Usar queue (procesamiento asíncrono)
- ✅ Enviar email al admin
- ✅ Incluir detalles del producto
- ✅ Funcionar con `queue:work`

### **Daily Sales debe:**
- ✅ Correr automáticamente cada noche (23:30)
- ✅ Obtener órdenes del día actual
- ✅ Calcular estadísticas (total orders, revenue, products sold)
- ✅ Enviar email al admin con reporte completo
- ✅ Funcionar con `schedule:work` o cron

---

## 🎯 READY TO IMPLEMENT

Con esta planificación:
- ✅ Sabemos exactamente qué crear
- ✅ Tenemos el código de cada archivo
- ✅ Conocemos las dependencias
- ✅ Tenemos plan de testing
- ✅ Estimamos el tiempo necesario

**¿Procedemos con la implementación?** 🚀

