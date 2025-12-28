# 🧪 TESTING GUIDE - Jobs & Queue Features

## ✅ PRE-REQUISITOS

Antes de empezar el testing, asegúrate de:

- [ ] Servidor Laravel corriendo (`php artisan serve`)
- [ ] Base de datos tiene datos de prueba (`php artisan db:seed`)
- [ ] Admin user existe (`ccdiego.ve@gmail.com`)
- [ ] Productos tienen `low_stock_threshold` configurado

---

## 📋 TEST 1: LOW STOCK NOTIFICATION

### **Objetivo:** Verificar que se envía email cuando un producto queda en stock bajo después del checkout

### **Pasos:**

#### **1. Configurar el sistema**
```bash
# Terminal 1: Iniciar queue worker
php artisan queue:work
```

#### **2. Preparar productos para el test**
```bash
# En otro terminal, abrir tinker
php artisan tinker

# Encontrar un producto y configurar stock bajo
>>> $product = \App\Models\Product::first();
>>> $product->stock_quantity = 6; // Justo arriba del threshold
>>> $product->low_stock_threshold = 5;
>>> $product->save();
>>> exit
```

#### **3. Ejecutar checkout**
1. Ir a http://127.0.0.1:8000
2. Login como `john@example.com` / `password`
3. Agregar el producto del paso 2 al carrito
4. Configurar cantidad que deje stock <= 5 (ej: comprar 2 unidades)
5. Ir a Cart → Proceed to Checkout
6. Completar checkout

#### **4. Verificar Job ejecutado**
```bash
# En el terminal con queue:work, deberías ver:
# [YYYY-MM-DD HH:MM:SS][xxxxx] Processing: App\Jobs\SendLowStockNotification
# [YYYY-MM-DD HH:MM:SS][xxxxx] Processed: App\Jobs\SendLowStockNotification
```

#### **5. Verificar Email enviado**
```bash
# Ver el email en el log
tail -n 50 storage/logs/laravel.log

# Buscar líneas con:
# - "Sending low stock notification"
# - "Subject: ⚠️ Low Stock Alert"
# - El contenido del email HTML
```

### **✅ Resultado Esperado:**
- Job procesado sin errores
- Email visible en laravel.log
- Email contiene:
  - Nombre del producto
  - Stock actual
  - Threshold
  - Mensaje de advertencia

---

## 📋 TEST 2: DAILY SALES REPORT

### **Objetivo:** Verificar que se genera y envía el reporte diario de ventas

### **Pasos:**

#### **1. Crear datos de prueba (órdenes del día)**
```bash
# Asegurarse de tener al menos 2-3 órdenes completadas HOY
# Hacer checkouts como en el TEST 1 o usar tinker:

php artisan tinker

>>> $user = \App\Models\User::where('email', 'john@example.com')->first();
>>> $product = \App\Models\Product::first();

>>> $order = \App\Models\Order::create([
      'user_id' => $user->id,
      'order_number' => \App\Models\Order::generateOrderNumber(),
      'total_amount' => 99.99,
      'status' => 'completed',
      'completed_at' => now(),
    ]);

>>> \App\Models\OrderItem::create([
      'order_id' => $order->id,
      'product_id' => $product->id,
      'product_name' => $product->name,
      'price' => $product->price,
      'quantity' => 2,
    ]);

>>> exit
```

#### **2. Test Manual del Job**
```bash
# Disparar el job manualmente
php artisan tinker

>>> \App\Jobs\SendDailySalesReport::dispatch();
>>> exit
```

#### **3. Verificar Email enviado**
```bash
# Ver el email en el log
tail -n 100 storage/logs/laravel.log

# Buscar:
# - "Sending daily sales report"
# - "Subject: 📊 Daily Sales Report"
# - Tabla HTML con órdenes
# - Estadísticas (total orders, revenue, products sold)
```

### **✅ Resultado Esperado:**
- Job ejecutado sin errores
- Email visible en laravel.log
- Email contiene:
  - Total de órdenes del día
  - Revenue total
  - Productos vendidos
  - Tabla con detalle de órdenes
  - Tabla con resumen de productos

---

## 📋 TEST 3: SCHEDULER (Daily Sales Report Automático)

### **Objetivo:** Verificar que el scheduler ejecuta el reporte automáticamente

### **Pasos:**

#### **1. Verificar configuración del scheduler**
```bash
# Ver tasks programados
php artisan schedule:list

# Deberías ver:
# 0 23:30 ......... App\Jobs\SendDailySalesReport .... daily-sales-report
```

#### **2. Ejecutar el scheduler manualmente**
```bash
# Esto ejecutará todos los jobs programados que deberían correr AHORA
php artisan schedule:run

# Si es antes de las 23:30, dirá:
# No scheduled commands are ready to run.
```

#### **3. Test con schedule:work (simula cron)**
```bash
# Esto revisa cada minuto si hay tasks para ejecutar
php artisan schedule:work

# Dejar corriendo y esperar a las 23:30
# O cambiar la hora en routes/console.php temporalmente para test
```

#### **4. Modificar hora temporalmente para test**
```php
// En routes/console.php, cambiar temporalmente:
Schedule::job(new SendDailySalesReport)
    ->everyMinute()  // ← Cambiar solo para testing
    ->timezone(config('app.timezone'))
    // ...
```

```bash
# Ejecutar schedule:work
php artisan schedule:work

# Esperar 1 minuto y verificar que se ejecutó
```

### **✅ Resultado Esperado:**
- Scheduler detecta el job
- Job se ejecuta automáticamente
- Email se envía sin intervención manual

---

## 📋 TEST 4: MULTIPLE LOW STOCK PRODUCTS

### **Objetivo:** Verificar que se envían múltiples notificaciones si varios productos quedan bajos

### **Pasos:**

#### **1. Configurar múltiples productos en stock bajo**
```bash
php artisan tinker

>>> $products = \App\Models\Product::limit(3)->get();
>>> foreach ($products as $p) {
      $p->stock_quantity = 6;
      $p->low_stock_threshold = 5;
      $p->save();
    }
>>> exit
```

#### **2. Hacer checkout con múltiples productos**
1. Agregar los 3 productos al carrito
2. Completar checkout

#### **3. Verificar múltiples jobs**
```bash
# En queue:work deberías ver 3 jobs procesados
# En el log deberías ver 3 emails
```

### **✅ Resultado Esperado:**
- Un job por cada producto en stock bajo
- Un email por cada producto
- Todos procesados correctamente

---

## 📋 TEST 5: NO HAY VENTAS HOY

### **Objetivo:** Verificar que el reporte se envía aunque no haya ventas

### **Pasos:**

#### **1. Limpiar órdenes de hoy (solo para test)**
```bash
php artisan tinker

>>> \App\Models\Order::whereDate('completed_at', today())->delete();
>>> exit
```

#### **2. Disparar el job**
```bash
php artisan tinker
>>> \App\Jobs\SendDailySalesReport::dispatch();
>>> exit
```

#### **3. Verificar email**
```bash
tail -n 50 storage/logs/laravel.log

# Buscar mensaje: "No Sales Today"
```

### **✅ Resultado Esperado:**
- Email se envía
- Muestra mensaje "No sales today"
- Estadísticas en 0

---

## 🔧 TROUBLESHOOTING

### **Problema: Jobs no se procesan**
```bash
# Verificar que hay jobs en la cola
php artisan queue:table  # Ver migración existe
php artisan tinker
>>> \DB::table('jobs')->count();
>>> exit

# Si hay jobs pero no se procesan:
# 1. Detener queue:work (Ctrl+C)
# 2. Reiniciar
php artisan queue:work
```

### **Problema: No veo emails en el log**
```bash
# Verificar configuración en .env
grep MAIL .env

# Debe ser MAIL_MAILER=log

# Si no existe el archivo de log:
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

### **Problema: Scheduler no ejecuta**
```bash
# Verificar timezone
php artisan tinker
>>> config('app.timezone');
>>> now(); // Ver hora actual del sistema
>>> exit

# Para producción, agregar a crontab:
crontab -e
# Agregar:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### **Problema: Product no tiene isLowStock()**
```bash
# Verificar el método existe
php artisan tinker
>>> $p = \App\Models\Product::first();
>>> $p->isLowStock();
>>> exit

# Si da error, verificar app/Models/Product.php
```

---

## ✅ CHECKLIST DE VERIFICACIÓN

### **Low Stock Notification:**
- [ ] Job se crea en la tabla `jobs`
- [ ] Queue worker procesa el job
- [ ] Email aparece en `laravel.log`
- [ ] Email tiene el formato correcto HTML
- [ ] Incluye todos los detalles del producto
- [ ] Se envía al admin user correcto

### **Daily Sales Report:**
- [ ] Se puede disparar manualmente con `tinker`
- [ ] Email aparece en `laravel.log`
- [ ] Email tiene formato HTML correcto
- [ ] Muestra estadísticas correctas
- [ ] Tabla de órdenes completa
- [ ] Tabla de productos correcta
- [ ] Scheduler está configurado (11:30 PM)
- [ ] `schedule:list` muestra el job

---

## 📊 COMANDOS ÚTILES

```bash
# Ver jobs en cola
php artisan queue:monitor

# Ver jobs fallidos
php artisan queue:failed

# Reintentar job fallido
php artisan queue:retry all

# Limpiar jobs fallidos
php artisan queue:flush

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Test rápido de mail
php artisan tinker
>>> Mail::raw('Test', function($msg) {
      $msg->to('admin@test.com')->subject('Test');
    });
>>> exit
```

---

## 🎯 TESTING COMPLETADO

Si todos los tests pasan:
- ✅ Low Stock Notification funciona
- ✅ Daily Sales Report funciona
- ✅ Scheduler está configurado
- ✅ Emails se envían correctamente
- ✅ Features listas para producción

**¡Excelente trabajo!** 🎉

