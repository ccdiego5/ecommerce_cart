# 🗄️ Cómo Funciona la Base de Datos del Carrito

## 📊 Flujo de Datos: Del Click al Checkout

### **1. Usuario Agrega Producto al Carrito**

**Acción:** Click en "Agregar al Carrito"

**Query que se ejecuta:**
```sql
-- Primero verifica si el producto ya está en el carrito
SELECT * FROM cart_items 
WHERE user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e' 
AND product_id = '52ddc179-7f20-4915-b23b-78889552be33';

-- Si NO existe, INSERT:
INSERT INTO cart_items (id, public_id, user_id, product_id, quantity, created_at, updated_at)
VALUES (
    'gen_random_uuid()',  -- UUID generado automáticamente
    nextval('cart_items_public_id_seq'),  -- Auto-incrementa: 0001, 0002, etc.
    'e453bee3-11fa-4f4e-a09f-2026db45dd7e',  -- ID del usuario logueado
    '52ddc179-7f20-4915-b23b-78889552be33',  -- ID del producto
    1,  -- Cantidad inicial
    NOW(),
    NOW()
);

-- Si SÍ existe, UPDATE:
UPDATE cart_items 
SET quantity = quantity + 1,
    updated_at = NOW()
WHERE id = '7c3e8a2e-c0cf-43e8-9ee1-7a9b34df8b1c';
```

**Tabla `cart_items` después:**
```
┌──────────────────────────────────────┬───────────┬──────────────────────────────────────┬──────────────────────────────────────┬──────────┐
│ id (UUID)                            │ public_id │ user_id (UUID)                       │ product_id (UUID)                    │ quantity │
├──────────────────────────────────────┼───────────┼──────────────────────────────────────┼──────────────────────────────────────┼──────────┤
│ 7c3e8a2e-c0cf-43e8-9ee1-7a9b34df8b1c │      0001 │ e453bee3-11fa-4f4e-a09f-2026db45dd7e │ 52ddc179-7f20-4915-b23b-78889552be33 │        5 │
│ 8d01e267-4ddc-405a-ba65-a871085a138c │      0002 │ e453bee3-11fa-4f4e-a09f-2026db45dd7e │ a9bb7126-efe4-4aa9-9b54-04ef84f76831 │        1 │
│ 4592bd4b-9741-4fef-9e51-3fe72425e476 │      0003 │ e453bee3-11fa-4f4e-a09f-2026db45dd7e │ 16f3c5cd-013f-42c8-bfda-ea2628c425f7 │        1 │
└──────────────────────────────────────┴───────────┴──────────────────────────────────────┴──────────────────────────────────────┴──────────┘
```

---

### **2. Usuario Ve el Carrito Dropdown**

**Query que se ejecuta:**
```sql
-- Obtiene los últimos 5 items con información del producto
SELECT 
    ci.id,
    ci.public_id,
    ci.quantity,
    p.id as product_id,
    p.name as product_name,
    p.price,
    p.image,
    p.stock_quantity
FROM cart_items ci
INNER JOIN products p ON p.id = ci.product_id
WHERE ci.user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e'
ORDER BY ci.created_at DESC
LIMIT 5;

-- Calcula el total del carrito
SELECT 
    SUM(ci.quantity) as total_items,
    SUM(ci.quantity * p.price) as total_amount
FROM cart_items ci
INNER JOIN products p ON p.id = ci.product_id
WHERE ci.user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';
```

---

### **3. Usuario Va a la Página del Carrito (`/cart`)**

**Query que se ejecuta:**
```sql
-- Obtiene TODOS los items del carrito
SELECT 
    ci.id,
    ci.public_id,
    ci.quantity,
    ci.created_at,
    p.*
FROM cart_items ci
INNER JOIN products p ON p.id = ci.product_id
WHERE ci.user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e'
ORDER BY ci.created_at DESC;
```

---

### **4. Usuario Modifica Cantidad**

**Query que se ejecuta:**
```sql
-- Actualiza la cantidad
UPDATE cart_items 
SET quantity = 3,  -- Nueva cantidad
    updated_at = NOW()
WHERE id = '7c3e8a2e-c0cf-43e8-9ee1-7a9b34df8b1c'
AND user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';  -- Seguridad: verifica que sea del usuario
```

---

### **5. Usuario Elimina un Item**

**Query que se ejecuta:**
```sql
-- Elimina el item del carrito
DELETE FROM cart_items 
WHERE id = '8d01e267-4ddc-405a-ba65-a871085a138c'
AND user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';
```

---

### **6. Usuario Hace Checkout (¡La Magia!)**

**Transaction completa que se ejecuta:**
```sql
BEGIN TRANSACTION;

-- PASO 1: Crear la orden
INSERT INTO orders (id, public_id, user_id, order_number, total_amount, status, completed_at, created_at, updated_at)
VALUES (
    'gen_random_uuid()',
    nextval('orders_public_id_seq'),  -- Ej: 0001
    'e453bee3-11fa-4f4e-a09f-2026db45dd7e',
    'ORD-2025-000001',  -- Generado por Order::generateOrderNumber()
    133.39,  -- Total calculado
    'completed',
    NOW(),
    NOW(),
    NOW()
);

-- PASO 2: Crear items de la orden (snapshot de los productos)
INSERT INTO order_items (id, public_id, order_id, product_id, product_name, product_price, quantity, subtotal, created_at, updated_at)
VALUES 
(
    'gen_random_uuid()',
    nextval('order_items_public_id_seq'),
    'f1234567-89ab-cdef-0123-456789abcdef',  -- ID de la orden recién creada
    '52ddc179-7f20-4915-b23b-78889552be33',  -- ID del producto
    'Fish Steak',  -- Snapshot del nombre (por si cambia después)
    14.99,  -- Snapshot del precio (por si cambia después)
    8,  -- Cantidad comprada
    119.92,  -- Subtotal
    NOW(),
    NOW()
);
-- ... (Se repite para cada producto en el carrito)

-- PASO 3: Reducir el stock de cada producto
UPDATE products 
SET stock_quantity = stock_quantity - 8,  -- Resta la cantidad comprada
    updated_at = NOW()
WHERE id = '52ddc179-7f20-4915-b23b-78889552be33';

-- PASO 4: Vaciar el carrito del usuario
DELETE FROM cart_items 
WHERE user_id = 'e453bee3-11fa-4f4e-a09f-2026db45dd7e';

COMMIT;
```

---

## 🔍 Para Ver los Datos en PostgreSQL

### **Ver todos los carritos actuales:**
```sql
SELECT 
    u.name as usuario,
    p.name as producto,
    ci.quantity as cantidad,
    p.price as precio_unitario,
    (ci.quantity * p.price) as subtotal
FROM cart_items ci
JOIN users u ON u.id = ci.user_id
JOIN products p ON p.id = ci.product_id
ORDER BY u.name, ci.created_at;
```

### **Ver todas las órdenes completadas:**
```sql
SELECT 
    o.public_id,
    o.order_number,
    u.name as cliente,
    o.total_amount,
    o.completed_at,
    COUNT(oi.id) as total_productos
FROM orders o
JOIN users u ON u.id = o.user_id
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id, u.name
ORDER BY o.completed_at DESC;
```

### **Ver detalle de una orden específica:**
```sql
SELECT 
    o.order_number,
    oi.product_name,
    oi.product_price,
    oi.quantity,
    oi.subtotal
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.order_number = 'ORD-2025-000001';
```

### **Ver productos con stock bajo:**
```sql
SELECT 
    public_id,
    name,
    stock_quantity,
    low_stock_threshold,
    (stock_quantity <= low_stock_threshold) as es_stock_bajo
FROM products
WHERE stock_quantity <= low_stock_threshold
ORDER BY stock_quantity ASC;
```

---

## 📦 Estructura de Tablas Relacionadas

```
┌─────────────────┐
│     USERS       │
│  (Usuarios)     │
├─────────────────┤
│ id (UUID) PK    │
│ public_id       │
│ name            │
│ email           │
└────────┬────────┘
         │
         │ user_id (FK)
         │
    ┌────┴─────────────┐
    │                  │
┌───▼───────────┐  ┌──▼──────────┐
│  CART_ITEMS   │  │   ORDERS    │
│  (Temporal)   │  │ (Permanente)│
├───────────────┤  ├─────────────┤
│ id (UUID) PK  │  │ id (UUID) PK│
│ user_id FK    │  │ user_id FK  │
│ product_id FK │  │ order_number│
│ quantity      │  │ total_amount│
└───────┬───────┘  └──────┬──────┘
        │                 │
        │ product_id      │ order_id
        │                 │
    ┌───▼─────────────────▼───┐
    │      PRODUCTS           │
    │   (Catálogo)            │
    ├─────────────────────────┤
    │ id (UUID) PK            │
    │ public_id               │
    │ name                    │
    │ price                   │
    │ stock_quantity          │
    │ low_stock_threshold     │
    └────────┬────────────────┘
             │
             │ product_id (FK)
             │
    ┌────────▼────────────┐
    │   ORDER_ITEMS       │
    │   (Histórico)       │
    ├─────────────────────┤
    │ id (UUID) PK        │
    │ order_id FK         │
    │ product_id FK       │
    │ product_name        │ ← Snapshot
    │ product_price       │ ← Snapshot
    │ quantity            │
    │ subtotal            │
    └─────────────────────┘
```

---

## 🎯 Ventajas de esta Arquitectura

### ✅ **UUIDs como Primary Keys:**
- No se pueden enumerar (seguridad)
- Únicos globalmente
- Perfectos para sistemas distribuidos

### ✅ **Public IDs para Usuarios:**
- Formato amigable: #0001, #0042
- Fácil de recordar y compartir
- No expone el UUID real

### ✅ **Snapshots en order_items:**
- Si el producto cambia de precio → las órdenes antiguas mantienen el precio original
- Si el producto se elimina → el historial se conserva
- Auditoría perfecta

### ✅ **Separación cart_items vs order_items:**
- `cart_items` = Temporal (se borra después del checkout)
- `order_items` = Permanente (historial de compras)
- No hay confusión entre "lo que quiero comprar" vs "lo que compré"

---

## 🔐 Seguridad Implementada

1. **Validación de Usuario:**
   - Todos los queries verifican `WHERE user_id = Auth::id()`
   - Un usuario no puede ver/modificar el carrito de otro

2. **Validación de Stock:**
   - Se verifica antes de agregar al carrito
   - Se verifica antes de hacer checkout
   - Se usa `DB::transaction()` para atomicidad

3. **UUIDs:**
   - No se pueden adivinar IDs
   - URLs no predecibles

---

**Para ver tus datos actuales, ejecuta:**
```bash
cd c:\laragon\www\testing
php artisan tinker --execute="echo 'Cart Items: ' . App\Models\CartItem::count(); echo PHP_EOL . 'Orders: ' . App\Models\Order::count();"
```

¿Quieres que ahora probemos hacer un checkout completo para ver cómo se crean las órdenes? 🛒

