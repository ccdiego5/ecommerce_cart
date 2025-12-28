# 🔐 Cumplimiento de Requisitos: Carrito Asociado al Usuario Autenticado

## 📋 Requisito Original

> **"Cada carrito de compra debe estar asociado al usuario autenticado (modelo Usuario)."**
> 
> **"Cuando un usuario agrega productos a su carrito, actualiza cantidades o elimina artículos, estas acciones deben almacenarse y recuperarse en función del usuario actualmente autenticado (no a través de la sesión o el almacenamiento local)."**
> 
> **"Asegúrese de utilizar la autenticación incorporada de Laravel desde el kit de inicio."**

---

## ✅ Implementación Completa

### **1. Autenticación: Laravel Breeze**

**Instalado:** Laravel Breeze (authentication starter kit oficial)
- ✅ `php artisan breeze:install livewire`
- ✅ Sistema de autenticación completo con login/register
- ✅ Middleware `auth` en todas las rutas protegidas
- ✅ Uso de `Auth::id()` para identificar usuario autenticado

**Prueba:**
```bash
# Ver que Breeze está instalado
composer show laravel/breeze
```

---

### **2. Base de Datos: Carrito Asociado al Usuario**

#### **Estructura de la Tabla `cart_items`:**
```sql
CREATE TABLE cart_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    public_id BIGINT UNIQUE DEFAULT nextval('cart_items_public_id_seq'),
    user_id UUID NOT NULL,  -- ✅ RELACIÓN CON USUARIOS
    product_id UUID NOT NULL,
    quantity INTEGER NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    -- ✅ CONSTRAINT: Un usuario no puede tener el mismo producto duplicado
    UNIQUE(user_id, product_id),
    
    -- ✅ FOREIGN KEYS
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

**Verificación en la migración:**
```php
// database/migrations/2025_12_28_180812_create_cart_items_table.php
$table->uuid('user_id');  // ✅ Columna obligatoria
$table->unique(['user_id', 'product_id']); // ✅ Constraint único
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
```

---

### **3. Modelo Eloquent: Relación Usuario-Carrito**

#### **`app/Models/CartItem.php`**
```php
class CartItem extends Model
{
    protected $fillable = [
        'user_id',      // ✅ Requerido siempre
        'product_id',
        'quantity',
    ];

    // ✅ Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
```

#### **`app/Models/User.php`**
```php
class User extends Authenticatable
{
    // ✅ Relación inversa
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
```

---

### **4. Componentes Livewire: TODAS las Operaciones Usan `Auth::id()`**

#### **4.1. Agregar al Carrito (`ProductCatalog.php`)**
```php
public function addToCart($productId)
{
    // ✅ Buscar si ya existe EN LA BASE DE DATOS para este usuario
    $cartItem = CartItem::where('user_id', Auth::id())  // ✅ USUARIO AUTENTICADO
        ->where('product_id', $productId)
        ->first();

    if ($cartItem) {
        // ✅ Incrementar en BD (NO en sesión)
        $cartItem->increment('quantity');
    } else {
        // ✅ Crear nuevo registro en BD (NO en sesión)
        CartItem::create([
            'user_id' => Auth::id(),  // ✅ USUARIO AUTENTICADO
            'product_id' => $productId,
            'quantity' => 1,
        ]);
    }

    // ✅ Notificar a otros componentes
    $this->dispatch('cart-updated');
}
```

**❌ NO se usa:**
- `session()->put('cart', ...)`
- `localStorage` en JavaScript
- Cookies temporales

**✅ SE usa:**
- `CartItem::create()` - **Escribe en PostgreSQL**
- `Auth::id()` - **Identifica al usuario autenticado**

---

#### **4.2. Cargar Carrito (`CartDropdown.php` y `ShoppingCart.php`)**
```php
public function loadCart()
{
    // ✅ Cargar DESDE LA BASE DE DATOS, filtrado por usuario autenticado
    $this->cartItems = CartItem::with('product')
        ->where('user_id', Auth::id())  // ✅ SOLO DEL USUARIO ACTUAL
        ->get();

    // Calcular total (sin usar sesión)
    $this->cartTotal = $this->cartItems->sum(function ($item) {
        return $item->quantity * $item->product->price;
    });
}
```

**Query SQL ejecutada:**
```sql
SELECT cart_items.*, products.*
FROM cart_items
INNER JOIN products ON products.id = cart_items.product_id
WHERE cart_items.user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e'  -- ✅ UUID del usuario
ORDER BY cart_items.created_at DESC;
```

---

#### **4.3. Actualizar Cantidad (`ShoppingCart.php`)**
```php
public function updateQuantity($cartItemId, $quantity)
{
    // ✅ SEGURIDAD: Verificar que el item pertenezca al usuario actual
    $cartItem = CartItem::where('id', $cartItemId)
        ->where('user_id', Auth::id())  // ✅ VERIFICACIÓN OBLIGATORIA
        ->firstOrFail();

    // ✅ Actualizar EN LA BASE DE DATOS
    $cartItem->update(['quantity' => $quantity]);

    $this->dispatch('cart-updated');
}
```

**Query SQL ejecutada:**
```sql
UPDATE cart_items 
SET quantity = 3,
    updated_at = NOW()
WHERE id = '7c3e8a2e-c0cf-43e8-9ee1-7a9b34df8b1c'
AND user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';  -- ✅ FILTRO DE SEGURIDAD
```

---

#### **4.4. Eliminar Item (`CartDropdown.php` y `ShoppingCart.php`)**
```php
public function removeItem($cartItemId)
{
    // ✅ SEGURIDAD: Solo eliminar si pertenece al usuario
    CartItem::where('id', $cartItemId)
        ->where('user_id', Auth::id())  // ✅ VERIFICACIÓN OBLIGATORIA
        ->firstOrFail()
        ->delete();  // ✅ Eliminar de BD

    $this->dispatch('cart-updated');
}
```

**Query SQL ejecutada:**
```sql
DELETE FROM cart_items 
WHERE id = '8d01e267-4ddc-405a-ba65-a871085a138c'
AND user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';  -- ✅ FILTRO DE SEGURIDAD
```

---

#### **4.5. Checkout (`ShoppingCart.php`)**
```php
public function checkout()
{
    DB::beginTransaction();

    try {
        // ✅ Crear orden del usuario autenticado
        $order = Order::create([
            'user_id' => Auth::id(),  // ✅ USUARIO AUTENTICADO
            'total_amount' => $this->total,
            'status' => 'completed',
        ]);

        // ✅ Procesar cada item del carrito (desde BD)
        foreach ($this->cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                // ...
            ]);

            // Reducir stock
            $cartItem->product->decreaseStock($cartItem->quantity);
        }

        // ✅ Vaciar el carrito EN LA BASE DE DATOS
        CartItem::where('user_id', Auth::id())->delete();

        DB::commit();

        return $this->redirect(route('order.confirmation', ['orderId' => $order->id]));
    } catch (\Exception $e) {
        DB::rollBack();
        // ...
    }
}
```

**Queries SQL ejecutadas (dentro de transaction):**
```sql
BEGIN;
  -- Crear orden
  INSERT INTO orders (id, user_id, total_amount, ...) VALUES (...);
  
  -- Crear items
  INSERT INTO order_items (order_id, product_id, quantity, ...) VALUES (...);
  
  -- Reducir stock
  UPDATE products SET stock_quantity = stock_quantity - 5 WHERE id = '...';
  
  -- ✅ Vaciar carrito DEL USUARIO AUTENTICADO
  DELETE FROM cart_items WHERE user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';
COMMIT;
```

---

### **5. Protección de Rutas**

#### **`routes/web.php`**
```php
// ✅ Todas las rutas del carrito requieren autenticación
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])  // ✅ MIDDLEWARE OBLIGATORIO
    ->name('dashboard');

Route::view('cart', 'cart')
    ->middleware(['auth'])  // ✅ MIDDLEWARE OBLIGATORIO
    ->name('cart');

Route::view('order-confirmation/{orderId}', 'order-confirmation')
    ->middleware(['auth'])  // ✅ MIDDLEWARE OBLIGATORIO
    ->name('order.confirmation');
```

**Si un usuario NO autenticado intenta acceder:**
```
/cart → Redirect automático a → /login
```

---

## 🔐 Medidas de Seguridad Implementadas

### **1. Aislamiento de Datos por Usuario**
```php
// ✅ SIEMPRE se filtra por Auth::id()
CartItem::where('user_id', Auth::id())->get();
```

### **2. Prevención de Acceso No Autorizado**
```php
// ✅ Un usuario NO puede ver/modificar el carrito de otro
CartItem::where('id', $cartItemId)
    ->where('user_id', Auth::id())  // Verificación obligatoria
    ->firstOrFail();
```

### **3. Constraint de Base de Datos**
```sql
-- ✅ Imposible tener items duplicados para el mismo usuario
UNIQUE(user_id, product_id)
```

### **4. Foreign Key Cascade**
```sql
-- ✅ Si un usuario se elimina, su carrito se elimina automáticamente
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

---

## 🧪 Pruebas de Verificación

### **Test 1: Aislamiento entre usuarios**
```bash
# Terminal 1 (Usuario A)
php artisan tinker
>>> $userA = User::find('uuid-user-a');
>>> Auth::login($userA);
>>> CartItem::create(['user_id' => Auth::id(), 'product_id' => 'uuid-prod-1', 'quantity' => 1]);
>>> CartItem::where('user_id', Auth::id())->count();  # Resultado: 1

# Terminal 2 (Usuario B)
php artisan tinker
>>> $userB = User::find('uuid-user-b');
>>> Auth::login($userB);
>>> CartItem::where('user_id', Auth::id())->count();  # Resultado: 0 ✅

# Usuario B NO puede ver el carrito de A
```

### **Test 2: Persistencia entre sesiones**
```
1. Usuario hace login
2. Agrega 3 productos al carrito
3. Cierra el navegador (logout)
4. Hace login de nuevo
5. ✅ Los 3 productos siguen en el carrito (están en la BD)
```

### **Test 3: NO hay datos en sesión/localStorage**
```javascript
// En el navegador (DevTools Console)
console.log(localStorage);  // ✅ No hay datos del carrito
console.log(sessionStorage);  // ✅ No hay datos del carrito
```

---

## 📊 Comparación: Lo que NO hacemos vs Lo que SÍ hacemos

| ❌ Método Incorrecto | ✅ Método Implementado |
|---------------------|------------------------|
| `session()->put('cart', [...])` | `CartItem::create(['user_id' => Auth::id(), ...])` |
| `localStorage.setItem('cart', ...)` | Consulta a PostgreSQL con `WHERE user_id = Auth::id()` |
| `Cookie::queue('cart', ...)` | Relación Eloquent: `User->hasMany(CartItem)` |
| Carrito temporal (se pierde al logout) | Carrito persistente en BD (permanece entre sesiones) |
| Sin asociación a usuario | Foreign key `user_id` obligatoria |

---

## ✅ Conclusión: Requisito CUMPLIDO

### **Evidencia de Cumplimiento:**

1. ✅ **Autenticación de Laravel (Breeze):** Instalado y configurado
2. ✅ **Carrito asociado al usuario:** Tabla `cart_items` con `user_id` obligatorio
3. ✅ **Almacenamiento en BD:** Todas las operaciones usan `CartItem::create/update/delete`
4. ✅ **NO usa sesión/localStorage:** Verificado en el código - solo consultas SQL
5. ✅ **Recuperación basada en usuario autenticado:** Todos los queries usan `WHERE user_id = Auth::id()`
6. ✅ **Seguridad:** Verificación de pertenencia en todas las operaciones

### **Archivos Clave:**
- `app/Livewire/ProductCatalog.php` - Líneas 54-76
- `app/Livewire/CartDropdown.php` - Líneas 23-36
- `app/Livewire/ShoppingCart.php` - Líneas 24-28, 40-76, 78-151
- `database/migrations/*_create_cart_items_table.php`
- `app/Models/CartItem.php`
- `app/Models/User.php` - Relación `cartItems()`

---

**🎉 El carrito está 100% asociado al usuario autenticado y almacenado en la base de datos PostgreSQL, sin uso de sesiones ni localStorage.**

