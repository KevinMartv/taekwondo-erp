# Escuela de Taekwondo ERP

Monorepo de cuatro aplicaciones Laravel independientes que comparten una base MySQL (`escuela_taekwondo`) vía Docker Compose:

| Módulo | Carpeta | Puerto local (Docker Compose) | Rol |
| --- | --- | --- | --- |
| Portal | `tkd_portal` | `http://localhost:8000` | Login, panel admin y cuenta del alumno |
| Alumnos | `tkd_alumnos` | `http://localhost:8001` | Expediente de alumnos |
| Pagos | `tkd_pagos` | `http://localhost:8002` | Colegiaturas (API) **y** checkout de la tienda (`/pagar`) |
| POS / tienda | `tkd_pos` | `http://localhost:8003` | Catálogo, carrito, ventas e inventario |

Este documento sirve para **correr el sistema en equipo** y para que otro agente de IA no rompa el handshake entre módulos.

---

## Cómo correr el sistema (compañeros)

Necesitas **Docker Desktop** (Compose v2). No hace falta PHP ni MySQL en el host: todo corre en contenedores.

### 1. Clonar y entrar al repo

```bash
git clone https://github.com/KevinMartv/taekwondo-erp.git
cd taekwondo-erp
git checkout main
```

### 2. Configuración en la raíz (Compose)

Crea el `.env` que usa `docker-compose.yml` (contraseña de MySQL y secretos compartidos):

```bash
cp .env.example .env
```

Valores de desarrollo (puedes dejarlos igual):

| Variable | Para qué |
| --- | --- |
| `DB_ROOT_PASSWORD` | Root de MySQL; **la misma** en los cuatro módulos |
| `PAGO_SECRET` | Firma del cobro POS → Pagos |
| `PORTAL_SECRET` | Firma del portal → Pagos (suscripción) y POS (tienda) |
| `SUSCRIPCION_MONTO` | Mensualidad simulada (por defecto 500) |

### 3. Configuración de cada módulo (`APP_KEY`)

Cada app Laravel necesita su propio `.env` (sobre todo `APP_KEY`). Copia los ejemplos:

```bash
cp tkd_portal/.env.example tkd_portal/.env
cp tkd_alumnos/.env.example tkd_alumnos/.env
cp tkd_pagos/.env.example tkd_pagos/.env
cp tkd_pos/.env.example tkd_pos/.env
```

Docker Compose **sobrescribe** host de BD, puertos y secretos. Con eso basta para el entorno Docker.

### 4. Contenedores a levantar

Hay **cinco** servicios; hay que levantarlos **todos**:

| Contenedor | Puerto en tu PC | Qué es |
| --- | --- | --- |
| `tkd_db` | 3306 | MySQL 8 (`escuela_taekwondo`) |
| `tkd_portal` | **8000** | Bienvenida, login, paneles |
| `tkd_alumnos` | 8001 | API de expedientes, horarios, asistencia |
| `tkd_pagos` | 8002 | Mensualidades y pasarela simulada |
| `tkd_pos` | 8003 | Catálogo / carrito / ventas |

```bash
docker compose up -d
```

La primera vez descarga las imágenes (`mysql:8.0` y `bitnami/laravel`). Espera a que `tkd_db` quede healthy (`docker compose ps`).

### 5. Dependencias, clave y base de datos

Dentro de cada contenedor de app:

```bash
# Dependencias PHP (si vendor/ no está en el volumen)
docker compose exec tkd_portal composer install --no-interaction
docker compose exec tkd_alumnos composer install --no-interaction
docker compose exec tkd_pagos composer install --no-interaction
docker compose exec tkd_pos composer install --no-interaction

# APP_KEY (una vez por módulo)
docker compose exec tkd_portal php artisan key:generate --force
docker compose exec tkd_alumnos php artisan key:generate --force
docker compose exec tkd_pagos php artisan key:generate --force
docker compose exec tkd_pos php artisan key:generate --force

# Migraciones: alumnos ANTES que pagos (hay llave foránea)
docker compose exec tkd_portal php artisan migrate --force
docker compose exec tkd_alumnos php artisan migrate --force
docker compose exec tkd_pagos php artisan migrate --force
docker compose exec tkd_pos php artisan migrate --force

# Catálogos y cuenta admin
docker compose exec tkd_portal php artisan db:seed --force
docker compose exec tkd_alumnos php artisan db:seed --force
docker compose exec tkd_pos php artisan db:seed --force
```

### 6. Entrar al sistema

| URL | Uso |
| --- | --- |
| http://localhost:8000 | **Portal** (punto de entrada del equipo) |
| http://localhost:8001/alumnos | UI de alumnos (también la consume el portal) |
| http://localhost:8002 | Módulo de pagos |
| http://localhost:8003 | Tienda POS |

Cuenta de administrador (seeder del portal):

- Correo: `admin@dojang.test`
- Contraseña: `Admin123!`

Un alumno se registra desde **Crear cuenta** en el portal. Tarjeta simulada: `4242 4242 4242 4242`, vencimiento `MM/AA`, CVV 3 dígitos.

### 7. Parar / resetear

```bash
docker compose down          # apaga contenedores
docker compose down -v       # además borra el volumen de MySQL (base limpia)
```

Si un módulo no arranca: `docker compose logs -f tkd_portal` (cambia el nombre del servicio). Cookies distintas (`SESSION_COOKIE`) evitan que los cuatro `localhost` se pisen la sesión.

---

### `tkd_portal` — bienvenida, niveles de acceso e integración

Punto de entrada público en `http://localhost:8000`. El visitante ve un portal de bienvenida con **Iniciar sesión** y **Crear cuenta**. El registro siempre nace con rol `alumno` y crea el expediente en `tkd_alumnos`. El administrador se siembra (`admin@dojang.test` / `Admin123!`).

Tras el login, `GET /dashboard` redirige según el nivel:

| Nivel | Aterrizaje | Privilegio |
| --- | --- | --- |
| Visitante | `/` | Sólo bienvenida, login y registro |
| Alumno | `/mi-cuenta` | Edita **su** expediente, horarios, fechas de asistencia, renovación mensual y tienda |
| Administrador | `/admin/alumnos` | Ve, edita, suspende, activa y borra cualquier alumno; cobra y confirma mensualidades; ve inventario POS |

**Alumno** (el id de expediente sale de la sesión, nunca de la URL):

- Perfil: `PUT` a `tkd_alumnos` sin `nivel_id` ni `activo`.
- Horarios y asistencias: catálogo y reservas en `tkd_alumnos`.
- Contador de vencimiento: `GET /api/alumnos/{id}/estado-cuenta` en `tkd_pagos` (ciclo mensual).
- Renovar: redirect firmado a `tkd_pagos/suscripcion` (pasarela simulada).
- Tienda: redirect firmado a `tkd_pos/?alumno_id&alumno&token`; el checkout cierra en `/pagar`.

**Administrador:**

- Listado cruzado con indicador **Pagado / No ha pagado / Sin pagos**.
- Editar grado, horarios y datos.
- Suspender o activar (`PATCH /api/alumnos/{id}/estado`).
- Borrar sólo si no hay historial de cobros; si hay pagos, debe suspender.
- Registrar o confirmar mensualidades en `tkd_pagos`.

Gates: `is-admin` e `is-alumno` en `AppServiceProvider`. Middleware `expediente` obliga a completar el padrón si el módulo estaba caído al registrarse.

Variables: `ALUMNOS_URL`, `PAGOS_URL`, `POS_URL`, `*_INTERNAL_URL`, `PORTAL_SECRET`.

---

## Qué se agregó en esta iteración

### `tkd_pos` — tienda estilo Mercado Libre

El POS dejó de ser solo API de ventas y ahora es una tienda web:

1. **Catálogo** de productos de taekwondo (uniforme, protección, entrenamiento, cinturones, calzado, accesorios).
2. Cada producto muestra **id**, **precio MXN**, **cantidad/stock** e **imagen de autoría original** en `tkd_pos/public/images/productos/`.
3. **Carrito de sesión** (no persiste en BD hasta el checkout).
4. **Checkout**: crea una `venta` en estado `pendiente_pago`, descuenta stock y **redirige al módulo de pagos**.
5. **Pantalla de compra** (`/compra/{venta}`) para ver si el pago ya fue confirmado.

Archivos clave:

| Pieza | Ubicación |
| --- | --- |
| Rutas web | `tkd_pos/routes/web.php` |
| API de ventas/pago | `tkd_pos/routes/api.php` |
| Catálogo | `app/Http/Controllers/CatalogoController.php` |
| Carrito HTTP | `app/Http/Controllers/CarritoController.php` |
| Carrito sesión | `app/Support/Carrito.php` |
| Checkout | `app/Http/Controllers/CheckoutController.php` |
| Alta de venta + stock | `app/Services/RegistrarVenta.php` |
| Confirmación de cobro | `VentaController::confirmarPago` |
| Token HMAC + URL de pagos | `app/Models/Venta.php` (`tokenPago()`, `urlModuloPago()`) |
| UI | `resources/views/{layouts,catalogo,carrito,compra}` + `public/css/tienda.css` |
| Seed del catálogo | `database/seeders/ProductoSeeder.php` |
| Tests | `tests/Feature/CatalogoTest.php` |

Campos nuevos:

- `productos.descripcion`, `productos.imagen_url`
- `ventas.estado` (`pendiente_pago` \| `pagada`)
- `ventas.referencia` (formato `TKD-000001`)

### `tkd_pagos` — colegiaturas + cobro de tienda

El módulo ya tenía API de **mensualidades** (`PagoController`, tabla `pagos`, estado de cuenta). Encima se agregó el checkout de la tienda **sin reemplazar esa API**:

1. Recibe la orden de la tienda por query string en `GET /pagar` (`CheckoutTiendaController`).
2. Valida el **token HMAC** y que `return_url` pertenezca al POS.
3. Muestra un checkout (tarjeta o transferencia). **No guarda PAN/CVV**.
4. Confirma el cobro llamando al POS: `POST {POS_URL}/api/ventas/{id}/pagar`.
5. Redirige al comprador a `return_url` (pantalla de compra del POS).

La API existente sigue en `routes/api.php`:

- `POST /api/pagos` — registro de colegiatura
- `GET /api/alumnos/{alumno_id}/estado-cuenta` — estado de cuenta

Archivos clave del checkout de tienda:

| Pieza | Ubicación |
| --- | --- |
| Checkout tienda | `app/Http/Controllers/CheckoutTiendaController.php` |
| Colegiaturas (no tocar el contrato) | `app/Http/Controllers/PagoController.php` |
| Cliente HTTP al POS | `app/Services/ConfirmarPagoPos.php` |
| UI tienda | `resources/views/pagos/*`, `public/css/pagos.css` |
| Tests handshake | `tests/Feature/PagoTest.php` |

### Docker Compose

- Servicio `tkd_pos` en el puerto **8003** y `tkd_pagos` en **8002**.
- `tkd_pos` recibe `PAGOS_URL=http://localhost:8002` (URL que usa el **navegador**).
- `tkd_pagos` llama al POS por red Docker: `POS_URL=http://tkd_pos:8000`.
- `POS_PUBLIC_URL=http://localhost:8003` permite validar el `return_url` del navegador aunque la API interna use el hostname del contenedor.

---

## Cómo se conecta el POS con el módulo de pagos

Los módulos **no comparten sesión**. Se comunican con:

1. Un **redirect del navegador** POS → Pagos (inicio del cobro).
2. Un **HTTP server-to-server** Pagos → POS (cierre de la venta).
3. Un **redirect del navegador** Pagos → POS (regreso a la compra).

```mermaid
sequenceDiagram
    actor Usuario
    participant POS as tkd_pos :8003
    participant Pagos as tkd_pagos :8002

    Usuario->>POS: Agrega productos y POST /checkout
    POS->>POS: RegistrarVenta (estado=pendiente_pago, descuenta stock)
    POS->>Usuario: 302 a PAGOS_URL/pagar?...
    Usuario->>Pagos: GET /pagar (venta_id, total, token, return_url)
    Pagos->>Pagos: Verifica HMAC y return_url
    Usuario->>Pagos: POST /pagar (metodo_pago)
    Pagos->>POS: POST /api/ventas/{id}/pagar {metodo_pago, token}
    POS->>POS: Si token válido, estado=pagada
    POS-->>Pagos: JSON venta
    Pagos->>Usuario: 302 a return_url
    Usuario->>POS: GET /compra/{venta}
```

### 1. POS crea la intención de pago

`CheckoutController` llama a `RegistrarVenta` y redirige a `Venta::urlModuloPago()`.

URL generada:

```text
{PAGOS_URL}/pagar?venta_id={id}&referencia={TKD-00000N}&total={0.00}&token={hmac}&return_url={APP_URL}/compra/{id}
```

Query params (todos obligatorios):

| Param | Origen | Notas |
| --- | --- | --- |
| `venta_id` | `ventas.id` | Entero |
| `referencia` | `ventas.referencia` | `TKD-` + id con padding 6 |
| `total` | `number_format(total, 2, '.', '')` | Siempre `"123.45"`, **nunca** `"123.4"` ni `"123"` |
| `token` | HMAC-SHA256 | Ver fórmula abajo |
| `return_url` | `route('compra.show', $venta)` | Debe empezar por `POS_URL` o `POS_PUBLIC_URL` |

### 2. Fórmula del token (no cambiar el formato)

Ambos módulos deben calcular **exactamente**:

```text
token = hash_hmac('sha256', "{venta_id}|{total_formateado}", PAGO_SECRET)
```

Ejemplo: venta `7` con total `250.00` y secreto `test-secret`:

```text
payload = "7|250.00"
token   = HMAC-SHA256(payload, secreto)
```

Implementaciones:

- POS: `App\Models\Venta::tokenPago()` usando `config('services.pagos.secret')` (`PAGO_SECRET`).
- Pagos: `PagoController::validarIntencion()` usando `config('services.pos.secret')` (`PAGO_SECRET`).

**El secreto debe ser el mismo valor en los dos `.env`.** Si el total se serializa distinto (`200` vs `200.00`) el token no coincide y Pagos responde 403.

### 3. Pagos confirma en el POS

`ConfirmarPagoPos` hace:

```http
POST {POS_URL}/api/ventas/{venta_id}/pagar
Accept: application/json
Content-Type: application/json

{
  "metodo_pago": "tarjeta" | "transferencia",
  "token": "<mismo HMAC>"
}
```

`VentaController::confirmarPago`:

- 403 si el token no coincide (`hash_equals`).
- Si ya está `pagada`, es idempotente (no vuelve a cambiar método/estado de forma destructiva más allá del early return).
- Si estaba `pendiente_pago`, actualiza `metodo_pago` y `estado = pagada`.
- **No vuelve a descontar stock** (eso ocurrió al crear la venta).

Prefijo Laravel: las rutas de `routes/api.php` viven bajo `/api`.

### 4. Variables de entorno del contrato

**`tkd_pos/.env`**

| Variable | Uso |
| --- | --- |
| `APP_URL` | Base de `return_url` (`http://localhost:8003`) |
| `PAGOS_URL` | Destino del redirect del navegador (`http://localhost:8002`) |
| `PAGO_SECRET` | Secreto HMAC compartido |

**`tkd_pagos/.env`**

| Variable | Uso |
| --- | --- |
| `POS_URL` | Base HTTP **interna** para confirmar (`http://tkd_pos:8000` en Docker, `http://localhost:8003` en local) |
| `POS_PUBLIC_URL` | Origen permitido de `return_url` (`http://localhost:8003`) |
| `PAGO_SECRET` | El mismo secreto que el POS |

Config Laravel:

- POS: `config/services.php` → `services.pagos.url`, `services.pagos.secret`
- Pagos: `config/services.php` → `services.pos.url`, `services.pos.public_url`, `services.pos.secret`

### 5. Estados de `ventas.estado`

| Estado | Cuándo |
| --- | --- |
| `pendiente_pago` | Checkout web, o `POST /api/ventas` con `metodo_pago=pendiente` |
| `pagada` | Cobro confirmado por Pagos, o alta API con método distinto de `pendiente` |

`metodo_pago` en checkout web nace como `pendiente` y se reemplaza por `tarjeta` o `transferencia`.

---

## Rutas que un agente no debe romper

### POS web

| Método | Ruta | Nombre |
| --- | --- | --- |
| GET | `/` | `catalogo.index` |
| GET | `/producto/{producto}` | `catalogo.show` |
| GET | `/carrito` | `carrito.index` |
| POST | `/carrito` | `carrito.store` |
| PATCH | `/carrito/{producto}` | `carrito.update` |
| DELETE | `/carrito/{producto}` | `carrito.destroy` |
| POST | `/checkout` | `checkout.store` |
| GET | `/compra/{venta}` | `compra.show` |

### POS API

| Método | Ruta | Acción |
| --- | --- | --- |
| POST | `/api/ventas` | Alta de venta (POS interno / otros módulos) |
| POST | `/api/ventas/{venta}/pagar` | Cierre de cobro (solo módulo de pagos) |

### Pagos web

| Método | Ruta | Nombre |
| --- | --- | --- |
| GET | `/` | Landing informativa |
| GET | `/pagar` | Formulario checkout tienda (`CheckoutTiendaController`) |
| POST | `/pagar` | Procesa cobro de tienda y llama al POS |

Separado (no mezclar con el catálogo):

| Método | Ruta | Acción |
| --- | --- | --- |
| POST | `/api/pagos` | Alta de colegiatura de alumno |
| GET | `/api/alumnos/{id}/estado-cuenta` | Estado de cuenta |

---

## Cómo levantar el entorno

Sigue la sección **Cómo correr el sistema** al inicio de este README (`.env` raíz, `.env` de cada módulo, `docker compose up -d`, migrate/seed).

### Tests

```bash
docker compose exec tkd_portal php artisan test
docker compose exec tkd_alumnos php artisan test
docker compose exec tkd_pagos php artisan test
docker compose exec tkd_pos php artisan test
```
---

## Reglas para agentes de IA que toquen este flujo

1. **No cambien el payload del HMAC** (`id|total` con dos decimales y punto).
2. **Mantengan `PAGO_SECRET` idéntico** en POS y Pagos.
3. Distingan **URL de navegador** (`PAGOS_URL`, `POS_PUBLIC_URL`, `APP_URL`) de **URL interna Docker** (`POS_URL=http://tkd_pos:8000`).
4. El stock se reserva al crear la venta; el pago solo cambia estado. Si se cancela una venta pendiente en el futuro, hay que **reponer stock** (hoy no hay cancelación).
5. `Carrito` es singleton de sesión (`AppServiceProvider`); no mezclarlo con BD.
6. Imágenes del catálogo son assets locales de autoría original; no sustituir por URLs de stock con copyright.
7. `tkd_pagos` no tiene tabla de ventas: la fuente de verdad de la orden es `tkd_pos`.
9. En `tkd_pagos`, `PagoController` es **colegiaturas**. El cobro de catálogo vive en `CheckoutTiendaController` y rutas `/pagar`. No mezclar payloads.
