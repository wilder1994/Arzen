# Manual de usuario — Revista armas

**Empresa:** WCodex  
**Sistema:** Arzen  
**Perfil:** **Staff** (ADMIN / RESPONSABLE niv. 1) y **colaborador temporal**  
**Versión del manual:** 3.0  
**Fecha:** [Completar]

---

## 1. Acceso al sistema

Este capítulo describe cómo entrar a Arzen con su cuenta de usuario (ADMIN, RESPONSABLE o AUDITOR). Cada figura incluye una tabla **«Qué señalar en la captura»** para armar el documento en Word.

### 1.1 Cómo leer las figuras de este manual

| Convención | Uso |
|------------|-----|
| **① ② ③** | Orden sugerido de callouts en la imagen |
| **Flecha** | Desde el recuadro de texto hacia el control (botón, campo, enlace) |
| **Ocultar datos** | Tache contraseñas y correos reales con rectángulo gris si publica el manual |
| **Resolución** | Preferible 1920×1080, navegador al 100 %, sin barra de favoritos recortada |
| **Nombre archivo** | Ejemplo: `fig-01-03-login-campos.png` |

---

### 1.2 Pantalla de bienvenida

| | |
|---|---|
| **Pantalla** | Vista de **bienvenida** |
| **Ruta** | `/` (raíz del sitio; también llega aquí si la sesión expiró) |
| **Objetivo** | Llegar al formulario de inicio de sesión |

#### Qué hacer

1. Abra la URL oficial del sistema en el navegador (la que le entregó su área de sistemas u operaciones).
2. Verá una **imagen institucional** a pantalla completa (fondo oscuro).
3. Localice el botón **Iniciar sesión** (pastilla azul degradada, icono de candado a la izquierda, texto en mayúsculas).
4. Haga **un clic** en ese botón.

#### Resultado esperado

El navegador abre la pantalla **Inicio de sesión** (`/login`).

#### Figura 1.1 — Bienvenida

| Ref. | Qué señalar en la captura |
|------|---------------------------|
| **①** | Botón completo **Iniciar sesión** (esquina inferior derecha en escritorio; abajo centrado en móvil) — **flecha hacia el botón** |
| *(opc.)* | Imagen de fondo / marca SJ (sin anotar si distrae) |

**[Insertar imagen: fig-01-01-bienvenida.png]**

#### Notas

- Si escribe `/login` directamente en la barra de direcciones, también es válido; el flujo recomendado para usuarios nuevos es **bienvenida → Iniciar sesión**.
- Si intentó entrar a un módulo sin sesión, el sistema le devolvió a `/` a propósito.

---

### 1.3 Pantalla de inicio de sesión

| | |
|---|---|
| **Pantalla** | **Inicio de sesión** |
| **Ruta** | `/login` |
| **Objetivo** | Autenticarse con correo y contraseña |

#### Qué hacer

1. En **Correo electrónico**, escriba su usuario asignado (**debe ser un correo válido**, p. ej. `nombre.apellido@empresa.com`).
2. En **Contraseña**, escriba la clave que le entregó el administrador (temporal o definitiva).
3. *(Opcional)* Marque **Recordarme** en equipo personal de confianza.
4. Pulse **Ingresar**.

#### Resultado esperado

- Credenciales correctas y **sin** cambio obligatorio pendiente → **Inicio** (dashboard).
- Debe cambiar contraseña (clave temporal) → §1.4 antes del menú.
- Error → mensaje bajo los campos; use **¿Olvidaste tu contraseña?** si aplica.

#### Figura 1.2 — Login (campos principales)

| Ref. | Qué señalar |
|------|-------------|
| **①** | Campo **Correo electrónico** |
| **②** | Campo **Contraseña** |
| **③** | Botón **Ingresar** |

**[Insertar imagen: fig-01-02-login-campos.png]**

#### Figura 1.3 — Login (opciones secundarias)

| Ref. | Qué señalar |
|------|-------------|
| **④** | **Recordarme** |
| **⑤** | **¿Olvidaste tu contraseña?** |

**[Insertar imagen: fig-01-03-login-opciones.png]**

#### Errores frecuentes (login)

| Mensaje / situación | Causa probable | Acción |
|---------------------|----------------|--------|
| Credenciales incorrectas | Correo o contraseña incorrectos | Verifique mayúsculas; pida **Enviar** credenciales al admin |
| No avanza tras Ingresar | Debe cambiar contraseña | Complete §1.4 |
| Usuario inactivo | Cuenta deshabilitada | Contacte al administrador |

---

### 1.4 Cambio de contraseña obligatorio (primer ingreso)

| | |
|---|---|
| **Pantalla** | **Cambio de contraseña obligatorio** |
| **Cuándo aplica** | Usuario creado en el sistema con **contraseña temporal** o tras acción **Enviar** en el listado de usuarios |
| **Cuándo NO aplica** | Solo **staff** (Parte A). Colaboradores: salte a **§12** (ingreso con código). |

#### Qué hacer

1. Tras login exitoso, esta pantalla aparece **antes** del menú principal.
2. En **Nueva contraseña**, escriba una clave que cumpla la política (mínimo 8 caracteres).
3. En **Confirmar contraseña**, repita la misma clave.
4. Pulse **Establecer contraseña**.

#### Resultado esperado

Acceso al **Inicio** y menú según su rol.

#### Figura 1.4 — Cambio obligatorio

| Ref. | Qué señalar |
|------|-------------|
| **①** | Título **Cambio de contraseña obligatorio** |
| **②** | **Nueva contraseña** |
| **③** | **Confirmar contraseña** |
| **④** | **Establecer contraseña** |

**[Insertar imagen: fig-01-04-cambio-password-obligatorio.png]**

#### Notas

- Hasta completar este paso, no podrá usar Armamento, Reportes, etc.
- Si cierra sesión y vuelve a entrar con la temporal, el sistema volverá a pedir el cambio.

---

### 1.5 Recuperar contraseña olvidada

| | |
|---|---|
| **Ruta** | Desde `/login` → **¿Olvidaste tu contraseña?** |

1. Clic en **¿Olvidaste tu contraseña?**
2. Ingrese su **correo** registrado.
3. Siga el enlace del correo para definir nueva contraseña.

#### Figura 1.5 — Restablecimiento

| Ref. | Qué señalar |
|------|-------------|
| **①** | Campo correo |
| **②** | Botón de envío |

**[Insertar imagen: fig-01-05-olvido-password.png]**

---

### 1.6 Navegación tras ingresar

| Elemento | Función |
|----------|---------|
| **Logo** (izquierda) | Clic → **Inicio** |
| **Inicio** | Dashboard |
| Enlaces del centro | Módulos según **rol** |
| **Campana** *(si aplica)* | Notificaciones |
| **Nombre de usuario** | Perfil, cerrar sesión |

#### Figura 1.6 — Barra de navegación

| Ref. | Qué señalar |
|------|-------------|
| **①** | **Inicio** |
| **②** | Un módulo de su rol (ej. **Armamento**) |
| **③** | Menú del **nombre de usuario** |

**[Insertar imagen: fig-01-06-menu-superior.png]**

> Use una captura del **rol** de este manual (ADMIN tiene más ítems que AUDITOR o RESPONSABLE).

---

### 1.7 Cerrar sesión y sesión expirada

**Cerrar sesión:** nombre de usuario (arriba a la derecha) → **Cerrar sesión** → bienvenida o login.

**Sesión expirada:** tras inactividad puede volver a `/`; repita **Iniciar sesión** → login.

**[Insertar imagen: fig-01-07-cerrar-sesion.png]**

---

### 1.8 Resumen del flujo de acceso

```text
URL del sistema (/)
    → Clic «Iniciar sesión»
        → /login: correo + contraseña → «Ingresar»
            → ¿Cambio obligatorio? → Sí → Establecer contraseña → Inicio
            → ¿Cambio obligatorio? → No  → Inicio (dashboard)
```

---
## 2. Objetivo y alcance

Colaborador sube **4 fotos técnicas** (derecho, izquierdo, cañón/marca, serie) a **staging**. Staff revisa y **Actualizar** copia a fotos oficiales.

---

## 3. Usuarios temporales (staff)

### 3.1 Listar usuarios temporales

| | |
|---|---|
| **Menú** | **Revista armas** → **Usuarios temporales** |
| **Ruta** | `/revista-armas/usuarios-temporales` |
| **Objetivo** | Ver colaboradores |

#### Qué hacer

1. Clic **Revista armas**.
2. Clic **Usuarios temporales**.
3. Revise correo, estado, dueño.

#### Resultado esperado

Listado cargado.

#### Figura 3.1 — Listado temporales

**[Insertar imagen: fig-03-01-temp-users.png]**

---

### 3.2 Crear usuario temporal

| | |
|---|---|
| **Ruta** | crear temporal |
| **Objetivo** | Alta colaborador |

#### Qué hacer

1. Clic **Crear**.
2. Nombre y **correo** (usado en ingreso §12).
3. Activo.
4. ADMIN: elija **responsable dueño**.
5. RESPONSABLE: dueño es usted.
6. **Guardar**.

#### Resultado esperado

Colaborador listo para acceso.

#### Figura 3.2 — Crear temporal

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Dueño (ADMIN) |

**[Insertar imagen: fig-03-02-temp-create.png]**

---

### 3.3 Desactivar usuario temporal

| | |
|---|---|
| **Objetivo** | Bloquear nuevos ingresos |

#### Qué hacer

1. En fila, **Editar** o desactivar.
2. Confirme.
3. No borra fotos ya en staging.

#### Resultado esperado

Usuario inactivo.

#### Figura 3.3 — Desactivar

**[Insertar imagen: fig-03-03-temp-off.png]**

---

## 4. Asignar acceso temporal (12 h)

### 4.1 Generar código y enlace

| | |
|---|---|
| **Pantalla** | `/revista-armas` |
| **Objetivo** | Acceso campo |

#### Qué hacer

1. Clic **Asignar acceso temporal**.
2. Seleccione **usuario temporal**.
3. Marque **armas** (use buscador si hay muchas).
4. Confirme asignación.
5. Copie **código** y enlace `/revista-armas/ingreso` del modal de éxito.
6. Envíe al colaborador por canal seguro.

#### Resultado esperado

Correo enviado; código válido 12 h.

#### Figura 4.1a — Modal asignar

| Ref. | Qué señalar |
|------|-------------|
| **①** | Usuario |
| **②** | Armas |
| **③** | Confirmar |

**[Insertar imagen: fig-04-01-assign.png]**

#### Figura 4.1b — Código 12h

| Ref. | Qué señalar |
|------|-------------|
| **①** | Código |
| **②** | Copiar |

**[Insertar imagen: fig-04-02-code.png]**

---

### 4.2 Revocar acceso

| | |
|---|---|
| **Objetivo** | Invalidar código |

#### Qué hacer

1. Localice acceso vigente.
2. Use **Revocar**.
3. Confirme.
4. Fotos en staging no se borran.

#### Resultado esperado

Nuevos ingresos bloqueados.

#### Figura 4.2 — Revocar

**[Insertar imagen: fig-04-03-revoke.png]**

---

## 5. Revisar y aprobar fotos (staff)

### 5.1 Filtrar por usuario temporal

| | |
|---|---|
| **Ruta** | `/revista-armas` |
| **Objetivo** | Ver progreso |

#### Qué hacer

1. Desplegable **Usuario temporal** → **Filtrar**.
2. Columna **Realizado**: ✓ = 4/4, ✕ = falta alguna.
3. Sin usuario en filtro no aparece **Ver**.

#### Resultado esperado

Tabla filtrada.

#### Figura 5.1 — Filtros

**[Insertar imagen: fig-05-01-filters.png]**

---

### 5.2 Modal Ver (revisión 4 fotos)

| | |
|---|---|
| **Objetivo** | Inspeccionar staging |

#### Qué hacer

1. Clic **Ver** en fila.
2. Revise 4 casillas en modal 2×2.
3. Cierre o continúe.

#### Resultado esperado

Evidencia revisada.

#### Figura 5.2 — Modal Ver

| Ref. | Qué señalar |
|------|-------------|
| **①** | Casilla vacía |
| **②** | Con imagen |

**[Insertar imagen: fig-05-02-review.png]**

---

### 5.3 Actualizar fotos oficiales

| | |
|---|---|
| **Objetivo** | Aprobar staging |

#### Qué hacer

1. Clic **Actualizar**.
2. Si faltan fotos: lea **aviso** con cantidad — no actualiza.
3. Si 4/4: confirme en **modal de confirmación**.
4. Valide en **ficha del arma** fotos y **Notas**.

#### Resultado esperado

Oficiales actualizadas.

#### Figura 5.3a — Aviso faltan

**[Insertar imagen: fig-05-03-missing.png]**

#### Figura 5.3b — Confirmar

**[Insertar imagen: fig-05-04-confirm.png]**

---

### 5.4 Rechazar staging

| | |
|---|---|
| **Objetivo** | Borrar borrador |

#### Qué hacer

1. Clic **Rechazar**.
2. Confirme modal.
3. Colaborador debe volver a capturar.

#### Resultado esperado

Staging eliminado; oficiales sin cambio.

#### Figura 5.4 — Rechazar

**[Insertar imagen: fig-05-05-reject.png]**

---

## 6. Parte B — Colaborador temporal

### 6.1 Ingreso con código

| | |
|---|---|
| **Pantalla** | Ingreso Revista |
| **Ruta** | `/revista-armas/ingreso` |
| **Objetivo** | Entrar sin cuenta Arzen |

#### Qué hacer

1. Abra enlace del correo o `/revista-armas/ingreso`.
2. **Correo** registrado por el responsable.
3. **Código** 12 h (copie exacto).
4. Envíe formulario → **Mis armas**.

#### Resultado esperado

Listado de armas asignadas.

#### Figura 6.1 — Ingreso

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Código |
| **③** | Entrar |

**[Insertar imagen: fig-06-01-guest-login.png]**

---

### 6.2 Subir las 4 fotos por arma

| | |
|---|---|
| **Pantalla** | Modal captura |
| **Objetivo** | Completar 4/4 |

#### Qué hacer

1. En **Mis armas**, clic **Ver**.
2. Toque casilla pendiente.
3. **Tomar foto** o **Galería**.
4. Cropper → **Guardar**.
5. Espere **Imagen guardada**.
6. Repita hasta ✓ en las cuatro.
7. Avise al responsable para **Actualizar**.

#### Resultado esperado

Staging completo; staff aprueba.

#### Figura 6.2a — Cuadrícula

**[Insertar imagen: fig-06-02-grid.png]**

#### Figura 6.2b — Cropper móvil

**[Insertar imagen: fig-06-03-cropper.png]**

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| Código inválido | Solicite nuevo acceso al responsable |

---

### 6.3 Cerrar sesión colaborador

| | |
|---|---|
| **Objetivo** | Salir en dispositivo compartido |

#### Qué hacer

1. Use **Salir** en barra del módulo Revista.

#### Resultado esperado

Sesión invitado cerrada.

#### Figura 6.3 — Salir

**[Insertar imagen: fig-06-04-logout.png]**

---

## Control del documento

| Versión | Fecha | Elaboró | Aprobó | Cambios |
|---------|--------|---------|--------|----------|
| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |

---

*Fin del manual de Revista armas.*
