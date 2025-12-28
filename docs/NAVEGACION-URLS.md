# 🔗 Mapa de URLs y Navegación

## 📍 Todas las Rutas del Sistema

### **Rutas Públicas (Sin autenticación)**
```
┌─────────────────────────────────────────┐
│ /                                       │
│ Landing Page - Productos destacados    │
│ → CTA: "Comprar Ahora" lleva a /login  │
└─────────────────────────────────────────┘
```

### **Rutas de Autenticación**
```
/login          → Iniciar sesión
/register       → Registrarse
/forgot-password → Recuperar contraseña
```

### **Rutas Protegidas (Requieren autenticación)**
```
┌────────────────────────────────────────────────────┐
│ /dashboard                                         │
│ Catálogo de productos + búsqueda + agregar carrito│
└────────────────────────────────────────────────────┘
                    │
                    ├─→ Click en "Agregar al carrito"
                    │   ├─ CartItem creado en BD
                    │   └─ Evento "cart-updated" disparado
                    │
                    ├─→ Click en ícono carrito (navbar)
                    │   └─ Dropdown abre (últimos 5 items)
                    │       ├─ Botón "Ver Carrito" → /cart
                    │       └─ Botón "Checkout" → /cart
                    │
┌───────────────────▼────────────────────────────────┐
│ /cart                                              │
│ Página completa del carrito                        │
│ - Ver todos los items                              │
│ - Modificar cantidades                             │
│ - Eliminar productos                               │
│ - Botón "Proceder al Checkout"                     │
└────────────────────┬───────────────────────────────┘
                     │
                     ├─→ Click en "Proceder al Checkout"
                     │   ├─ Validación de stock
                     │   ├─ DB Transaction inicia
                     │   ├─ Order creada
                     │   ├─ OrderItems creados
                     │   ├─ Stock reducido
                     │   ├─ CartItems eliminados
                     │   └─ Transaction commit
                     │
┌───────────────────▼────────────────────────────────┐
│ /order-confirmation/{orderId}                      │
│ Página de confirmación                             │
│ - Número de orden                                  │
│ - Detalles de compra                               │
│ - Total pagado                                     │
│ - Botones: "Continuar Comprando" → /dashboard     │
└────────────────────────────────────────────────────┘

/profile         → Perfil del usuario
```

---

## 🔄 Flujo de Navegación Completo

### **Usuario No Autenticado:**
```
Landing (/)
   │
   └─→ Click en cualquier CTA
        └─→ /login
             │
             ├─→ Login exitoso → /dashboard
             └─→ No tiene cuenta → /register → /dashboard
```

### **Usuario Autenticado - Flujo de Compra:**
```
/dashboard (Catálogo)
   │
   ├─→ Agrega productos al carrito
   │   └─→ Toast: "Producto agregado al carrito"
   │       └─→ Badge del carrito se actualiza (navbar)
   │
   ├─→ Click en ícono carrito (navbar)
   │   └─→ Dropdown abre
   │       ├─→ "Ver Carrito" → /cart
   │       └─→ "Checkout" → /cart
   │
   └─→ Click en "Ver Carrito" (navbar o dropdown)
        └─→ /cart
             │
             ├─→ Modifica cantidades
             ├─→ Elimina items
             │
             └─→ Click "Proceder al Checkout"
                  │
                  ├─→ Stock suficiente
                  │   └─→ /order-confirmation/{orderId}
                  │        └─→ "Continuar Comprando" → /dashboard
                  │
                  └─→ Stock insuficiente
                      └─→ Toast error → Queda en /cart
```

---

## 🗂️ Componentes Livewire por Ruta

| Ruta | Vista | Componente Livewire |
|------|-------|---------------------|
| `/` | `welcome.blade.php` | `LandingPage` |
| `/dashboard` | `dashboard.blade.php` | `ProductCatalog` |
| `/cart` | `cart.blade.php` | `ShoppingCart` |
| `/order-confirmation/{orderId}` | `order-confirmation.blade.php` | `OrderConfirmation` |
| Navbar | `navigation.blade.php` | `CartDropdown` |

---

## 🎯 Eventos Livewire (Comunicación entre componentes)

### **`cart-updated`**
- **Disparado por:** `ProductCatalog`, `ShoppingCart`, `CartDropdown`
- **Escuchado por:** `CartDropdown`
- **Propósito:** Refrescar el contador y dropdown del carrito

### **`show-toast`**
- **Disparado por:** Todos los componentes
- **Escuchado por:** Layout principal (Alpine.js)
- **Propósito:** Mostrar notificaciones al usuario

---

## 📊 Base de Datos - Estados del Carrito

### **1. Usuario agrega producto:**
```sql
-- CartItem creado
INSERT INTO cart_items (user_id, product_id, quantity) VALUES (...);
```

### **2. Usuario ve el carrito:**
```sql
-- CartItems permanecen en BD
SELECT * FROM cart_items WHERE user_id = ?;
```

### **3. Usuario hace checkout:**
```sql
-- Transaction:
BEGIN;
  INSERT INTO orders (...);           -- Orden creada
  INSERT INTO order_items (...);      -- Items guardados (snapshot)
  UPDATE products SET stock = ...;    -- Stock reducido
  DELETE FROM cart_items WHERE ...;   -- Carrito vaciado ✅
COMMIT;
```

### **4. Usuario ve confirmación:**
```sql
-- Order ya existe (permanente)
SELECT * FROM orders WHERE id = ?;
SELECT * FROM order_items WHERE order_id = ?;
```

---

## 🛡️ Seguridad Implementada

### **Protección de Rutas:**
- ✅ Middleware `auth` en todas las rutas protegidas
- ✅ Verificación `user_id` en todas las queries de carrito/órdenes
- ✅ UUIDs para prevenir enumeración de IDs

### **Validación de Stock:**
- ✅ Al agregar al carrito
- ✅ Al modificar cantidad
- ✅ Al hacer checkout (con transaction)

### **Transacciones de Base de Datos:**
- ✅ `DB::beginTransaction()` para checkout
- ✅ Rollback automático si falla cualquier paso
- ✅ Garantía de consistencia de datos

---

## 🧪 Testing Manual

### **Test 1: Flujo completo de compra**
1. Ir a `/` (landing)
2. Click en "Comprar Ahora"
3. Login con: `ccdiego.ve@gmail.com` / `GodAleGO##85`
4. Agregar 3 productos al carrito
5. Click en ícono carrito (navbar)
6. Click en "Ver Carrito"
7. Modificar cantidad de un producto
8. Click en "Proceder al Checkout"
9. Verificar página de confirmación
10. Click en "Continuar Comprando"
11. Verificar que estás en `/dashboard`

### **Test 2: Stock insuficiente**
1. En `/dashboard`, agregar un producto al carrito
2. Ir a base de datos y reducir stock a 0
3. Intentar hacer checkout
4. Verificar toast de error
5. Verificar que sigues en `/cart`

### **Test 3: Carrito vacío**
1. Vaciar el carrito manualmente en BD
2. Ir a `/cart`
3. Verificar mensaje "Tu carrito está vacío"
4. Click en "Proceder al Checkout"
5. Verificar toast de error

---

## 📝 Comandos Útiles

### **Ver todas las rutas:**
```bash
php artisan route:list --except-vendor
```

### **Limpiar cache de rutas:**
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### **Ver datos en la base de datos:**
```bash
php artisan tinker
>>> App\Models\CartItem::with('product')->where('user_id', auth()->id())->get();
>>> App\Models\Order::with('items')->latest()->first();
```

---

**✅ Todas las URLs están correctamente configuradas y el flujo de navegación es coherente.**

