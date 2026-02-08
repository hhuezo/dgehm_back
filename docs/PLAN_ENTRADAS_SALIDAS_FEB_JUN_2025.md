# Plan de entradas y salidas (Febrero – Junio 2025)

## Criterios

- **PEPS**: Toda salida consume primero el lote más antiguo (menor `purchase_order_id`). Si una solicitud pide 10 unidades de un producto y hay 6 u. a $5 (OC antigua) y 8 u. a $6 (OC nueva), se registran **6 a $5 + 4 a $6** en kárdex (2 líneas de salida).
- **Entradas**: ~4 órdenes de compra por mes (Feb–Jun). Proveedores distintos; cada OC con un subconjunto de productos, no todos en cada una.
- **Salidas**: Solicitudes de oficinas; no todas las oficinas piden todos los meses. Solo se puede despachar si hay existencia; al despachar se aplica PEPS automáticamente.
- **Orden cronológico**: Primero entradas del mes, luego salidas del mes, para que siempre haya stock cuando corresponda.

---

## Proveedores (existentes + nuevos)

| ID (ref) | Nombre |
|----------|--------|
| 1 | PROVEEDORA DE ALIMENTOS EL SALVADOR S.A. DE C.V. |
| 2 | SUMINISTROS DE OFICINA CENTRAL S.A. DE C.V. |
| 3 | DISTRIBUIDORA DE PAPEL Y LIMPIEZA S.A. |
| 4 | INSUMOS QUÍMICOS Y LABORATORIO S.A. |
| 5 | TECNOLOGÍA Y SUMINISTROS C.A. |
| 6 | PROVEEDORA NACIONAL DE CAFÉ Y ALIMENTOS |

*(Los IDs reales dependerán del orden de creación; aquí son referencia.)*

---

## Oficinas (existentes)

| ID (ref) | Nombre |
|----------|--------|
| 1 | GERENCIA ADMINISTRATIVA / SERVICIOS GENERALES |
| 2 | LABORATORIO DE ACAJUTLA |
| 3 | UNIDAD DE TECNOLOGIA E INFORMACION |
| 4 | GERENCIA FINANCIERA |
| 5 | GERENCIA LEGAL |

---

## Productos (referencia)

Se asume que ya existen productos (por seeder y/o importación). En el plan se referencian por “nombre corto” para no depender de IDs:

- **Alimentos**: Azúcar 1 lb, Café Majada Oro, Cremora, Sal, Té canela, Té manzanilla, Té menta, Té negro, Té verde, Azúcar Splenda, etc.
- **Papel/limpieza**: Papel aluminio, materiales de oficina (resmas, etc.).
- **Otros**: Materiales informáticos, eléctricos, químicos (si existen).

Los IDs de producto se resuelven en ejecución según la base actual.

---

## FEBRERO 2025

### Entradas (4 órdenes de compra)

| OC   | Proveedor (ref) | Fecha recepción | Productos (ejemplo) | Notas |
|------|------------------|------------------|----------------------|-------|
| 0002 | 1 (Alimentos)   | 05-feb-2025      | Café Majada Oro 20 u @ 3.50, Cremora 50 u @ 0.25, Azúcar 1 lb 30 u @ 1.20 | |
| 0003 | 2 (Suministros) | 10-feb-2025      | Resmas papel 10 u @ 4.00, Papel aluminio 5 u @ 8.00 | Solo papel/ofi |
| 0004 | 6 (Café/Alimentos) | 15-feb-2025   | Té canela 15 u @ 2.00, Té negro 15 u @ 2.00, Sal 100 u @ 0.15 | |
| 0005 | 3 (Papel/Limpieza) | 25-feb-2025  | Papel aluminio 3 u @ 7.50, Bolsas café 10 u @ 2.80 | Mismo producto (papel aluminio) en otra OC = otro precio PEPS |

### Salidas (solicitudes)

- **Solicitud 1** (oficina: Gerencia Administrativa, ej. 20-feb-2025): Cremora 20 u, Café 5 u, Té canela 5 u.  
  - PEPS: Cremora y Café salen del lote de OC 0002; Té canela de 0004.
- **Solicitud 2** (oficina: Laboratorio Acajutla, ej. 25-feb-2025): Té negro 5 u, Sal 30 u.  
  - PEPS: Todo de OC 0004.
- Gerencia Financiera, Legal y Tecnología: sin solicitud en febrero.

---

## MARZO 2025

### Entradas (4 órdenes de compra)

| OC   | Proveedor (ref) | Fecha recepción | Productos (ejemplo) |
|------|------------------|------------------|----------------------|
| 0006 | 4 (Químicos/Lab) | 05-mar-2025      | Productos químicos/lab (si existen), ej. 10 u @ 5.00 |
| 0007 | 1 (Alimentos)    | 12-mar-2025      | Azúcar 1 lb 40 u @ 1.25, Cremora 30 u @ 0.28 |
| 0008 | 5 (Tecnología)   | 18-mar-2025      | Material informático (ej. 5 u @ 15.00) |
| 0009 | 2 (Suministros)  | 25-mar-2025      | Resmas 8 u @ 4.20, Té menta 10 u @ 2.10 |

### Salidas

- **Solicitud 3** (Unidad de Tecnología, ej. 10-mar-2025): Resmas 2 u.  
  - PEPS: Del lote de OC 0003 (febrero).
- **Solicitud 4** (Gerencia Administrativa, ej. 20-mar-2025): Café 8 u, Cremora 15 u, Azúcar 1 lb 10 u.  
  - PEPS: Café y Cremora primero de OC 0002 (lo que quede) y si alcanza de 0007; Azúcar de 0002 y/o 0007 según existencia.
- **Solicitud 5** (Laboratorio Acajutla, ej. 28-mar-2025): Sal 20 u, Té negro 5 u.  
  - PEPS: De OC 0004 (febrero).
- Gerencia Financiera y Legal: sin solicitud en marzo.

---

## ABRIL 2025

### Entradas (4 órdenes de compra)

| OC   | Proveedor (ref) | Fecha recepción | Productos (ejemplo) |
|------|------------------|------------------|----------------------|
| 0010 | 1 (Alimentos)   | 05-abr-2025      | Café 25 u @ 3.60, Té verde 20 u @ 2.05 |
| 0011 | 3 (Papel/Limpieza) | 10-abr-2025   | Papel aluminio 4 u @ 8.50 |
| 0012 | 6 (Café/Alimentos) | 18-abr-2025  | Té canela 20 u @ 2.10, Azúcar Splenda 5 u @ 4.00 |
| 0013 | 2 (Suministros) | 25-abr-2025      | Resmas 12 u @ 4.00, Material oficina (ej. 10 u @ 1.50) |

### Salidas

- **Solicitud 6** (Gerencia Financiera, ej. 08-abr-2025): Resmas 3 u, Té canela 5 u.  
  - PEPS: Resmas de 0003 o 0009 (marzo); Té canela de 0004 (feb) y si falta de 0012.
- **Solicitud 7** (Gerencia Administrativa, ej. 15-abr-2025): Cremora 25 u, Papel aluminio 2 u.  
  - PEPS: Cremora de 0007 (marzo); Papel aluminio primero 0003, luego 0005, luego 0011 según stock.
- Laboratorio, Tecnología y Legal: sin solicitud en abril (o una sola si se desea).

---

## MAYO 2025

### Entradas (4 órdenes de compra)

| OC   | Proveedor (ref) | Fecha recepción | Productos (ejemplo) |
|------|------------------|------------------|----------------------|
| 0014 | 4 (Químicos/Lab) | 05-may-2025      | Insumos lab 8 u @ 6.00 |
| 0015 | 6 (Café/Alimentos) | 12-may-2025   | Café 30 u @ 3.55, Té manzanilla 15 u @ 2.20 |
| 0016 | 2 (Suministros)  | 20-may-2025      | Resmas 10 u @ 4.10, Té negro 15 u @ 2.00 |
| 0017 | 1 (Alimentos)    | 28-may-2025      | Azúcar 1 lb 25 u @ 1.22, Sal 80 u @ 0.16 |

### Salidas

- **Solicitud 8** (Gerencia Legal, ej. 10-may-2025): Resmas 2 u, Té negro 3 u.  
  - PEPS: Resmas del lote más antiguo con stock; Té negro del más antiguo (ej. 0004, 0016).
- **Solicitud 9** (Laboratorio Acajutla, ej. 22-may-2025): Sal 25 u, Insumos lab 2 u.  
  - PEPS: Sal de 0004, 0017; Insumos de 0006 o 0014.
- **Solicitud 10** (Unidad de Tecnología, ej. 28-may-2025): Material informático 2 u.  
  - PEPS: De OC 0008 (marzo).
- Gerencia Administrativa y Financiera: sin solicitud en mayo (o ajustar si se quiere más movimiento).

---

## JUNIO 2025

### Entradas (4 órdenes de compra)

| OC   | Proveedor (ref) | Fecha recepción | Productos (ejemplo) |
|------|------------------|------------------|----------------------|
| 0018 | 5 (Tecnología)  | 05-jun-2025      | Material informático 6 u @ 14.00 |
| 0019 | 1 (Alimentos)   | 12-jun-2025      | Café 20 u @ 3.65, Cremora 40 u @ 0.26 |
| 0020 | 3 (Papel/Limpieza) | 18-jun-2025   | Papel aluminio 5 u @ 8.00 |
| 0021 | 2 (Suministros) | 25-jun-2025      | Resmas 15 u @ 4.15, Té verde 10 u @ 2.10 |

### Salidas

- **Solicitud 11** (Gerencia Administrativa, ej. 10-jun-2025): Café 10 u, Cremora 20 u, Resmas 2 u.  
  - PEPS: Café de lotes antiguos (0002, 0007, 0010, 0015, 0019); Cremora igual; Resmas del más antiguo con stock.
- **Solicitud 12** (Gerencia Financiera, ej. 20-jun-2025): Té canela 5 u, Azúcar 1 lb 5 u.  
  - PEPS: Por orden de OC (0004, 0012, etc.).
- **Solicitud 13** (Laboratorio Acajutla, ej. 25-jun-2025): Sal 15 u, Insumos lab 1 u.  
  - PEPS: Sal e insumos de los lotes correspondientes más antiguos.
- Legal y Tecnología: sin solicitud en junio (o añadir una más si se desea).

---

## Resumen PEPS en salidas

- Cada **línea de detalle** de una solicitud (ej. “10 unidades de Café”) puede generar **varias líneas en kárdex (tipo 2)** si hay varios lotes con stock: primero se consumen las unidades del lote con menor `purchase_order_id`, luego del siguiente, etc., cada uno con su `unit_price`.
- El sistema ya hace esto en `resolveKardexStock`: agrupa por `purchase_order_id`, `product_id`, `unit_price`, ordena por `purchase_order_id` y va restando. Al implementar las salidas (por script/import/API), no hace falta recalcular PEPS a mano; solo asegurar que las cantidades solicitadas no superen el stock disponible después de las entradas planificadas.

---

## Orden de ejecución recomendado

1. Crear proveedores nuevos (3–6) si no existen.
2. Por mes (Feb → Jun):
   - Crear las 4 órdenes de compra del mes con sus fechas y detalles (kárdex tipo 1).
   - Crear las solicitudes del mes (cabecera + detalles con cantidades).
   - Aprobar y “entregar” cada solicitud para que se generen las salidas en kárdex (tipo 2) con PEPS ya aplicado por el sistema.

Con esto se cumple: ~4 compras por mes, varios proveedores, distintos productos por OC, salidas solo con existencia y PEPS respetado en cada entrega.
