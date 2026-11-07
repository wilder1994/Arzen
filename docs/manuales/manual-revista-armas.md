# Manual de usuario — Revista armas

**Empresa:** WCodex  
**Sistema:** Arzen  
**Perfil:** **Staff** (ADMIN o RESPONSABLE con Revista en Gestionar) y **usuario de campo**  
**Versión del manual:** 3.2  
**Fecha:** 2026-11-06

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
| *(opc.)* | Imagen de fondo / logo de la empresa (sin anotar si distrae) |

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
| **Cuándo NO aplica** | Solo **staff** (Parte A). El usuario de campo entra por **§6** con correo y contraseña. |

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
| **Logo** (sidebar, arriba) | Clic → **Inicio** |
| **Sidebar** (izquierda) | Módulos según **rol** |
| Pestañas superiores | Secciones del módulo activo |
| **Tema** (sol/luna, arriba a la derecha) | Modo claro u oscuro |
| **Campana** *(si aplica, arriba a la derecha)* | Notificaciones no leídas |
| **Idioma** (pie del sidebar) | Español / Inglés |
| **Historial de notificaciones** (pie del sidebar) | Leídas y no leídas |
| **Nombre de usuario** (pie del sidebar) | Abre el **Perfil** |
| **Cerrar sesión** (pie del sidebar) | Salir del sistema |

#### Figura 1.6 — Barra de navegación

| Ref. | Qué señalar |
|------|-------------|
| **①** | **Inicio** |
| **②** | Un módulo de su rol (ej. **Armamento**) |
| **③** | Pie del sidebar (**Idioma**, **nombre de usuario**, **Cerrar sesión**) |

**[Insertar imagen: fig-01-06-menu-superior.png]**

> Use una captura del **rol** de este manual (ADMIN tiene más ítems que AUDITOR o RESPONSABLE).

---

### 1.7 Cerrar sesión y sesión expirada

**Cerrar sesión:** pie del sidebar → **Cerrar sesión** → bienvenida o login.

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

El usuario de campo toma **cinco fotos** (lado derecho, lado izquierdo, cañón/disparador/marca, serie e impronta). El staff solo revisa: **Actualizar** pasa las fotos al inventario y **Rechazar** borra el borrador. El staff no toma fotos.

---

## 3. Usuario de campo

### 3.1 Crear el usuario

| | |
|---|---|
| **Menú** | **Ajustes** → **Usuarios** → **Crear usuario** |
| **Ruta** | `/users/create` |
| **Objetivo** | Dar una cuenta de campo, no una cuenta de plataforma |

#### Qué hacer

1. En **Tipo de usuario**, elija **Usuario de campo**.
2. Escriba el **correo** y la **zona**. La zona es el nombre que se muestra.
3. Elija el **responsable** dueño.
4. Si varios responsables deben usarlo, marque el uso compartido y elija esos responsables.
5. Indique los **días de cada mes** en los que puede entrar. Por defecto, del 1 al 5.
6. **Guardar** y entregue el correo y la contraseña por un canal seguro.

#### Resultado esperado

La cuenta existe. Fuera de esos días del mes el ingreso de campo no abre.

#### Figura 3.1 — Usuario de campo

| Ref. | Qué señalar |
|------|-------------|
| **①** | Tipo de usuario |
| **②** | Zona y responsable |
| **③** | Días del mes |

**[Insertar imagen: fig-03-01-field-user.png]**

---

### 3.2 Desactivar

| | |
|---|---|
| **Objetivo** | Impedir el ingreso |

#### Qué hacer

1. En **Usuarios**, edite la cuenta y déjela inactiva, o archívela según la política de la empresa.
2. Las fotos ya tomadas en revisión no se borran solas.

#### Resultado esperado

Esa persona no vuelve a entrar a Mis armas.

---

## 4. Enviar armas a campo

| | |
|---|---|
| **Pantalla** | `/revista-armas` |
| **Objetivo** | Dejar armas en la cuenta de campo |

#### Qué hacer

1. Clic **Enviar a campo**.
2. Elija el **usuario de campo**.
3. Busque por serie, marca, cliente, puesto o trabajador.
4. Marque las filas en la tabla: **Cliente**, **Puesto o trabajador**, **Tipo**, **Serie** y **Marca**.
5. Confirme.

#### Resultado esperado

Esas armas aparecen en Mis armas de ese usuario mientras su ventana de días esté abierta.

#### Figura 4.1 — Enviar a campo

| Ref. | Qué señalar |
|------|-------------|
| **①** | Usuario de campo |
| **②** | Tabla de armas |
| **③** | Confirmar |

**[Insertar imagen: fig-04-01-enviar-campo.png]**

---

## 5. Revisar fotos (staff)

| | |
|---|---|
| **Ruta** | `/revista-armas` |
| **Objetivo** | Aprobar o descartar el borrador |

#### Qué hacer

1. Abra el arma del usuario de campo.
2. Revise las cinco casillas. Si no hay foto, se lee **Sin foto**. No hay botón de cámara ni de archivo.
3. **Actualizar** copia el borrador a las fotos oficiales del arma.
4. **Rechazar** elimina solo ese borrador. Las fotos oficiales no cambian.
5. **Cancelar** cierra la revisión.

#### Resultado esperado

La ficha del arma queda con las fotos aprobadas y una nota en el historial.

#### Figura 5.1 — Revisión

**[Insertar imagen: fig-05-01-revision.png]**

---

## 6. Parte B — Usuario de campo

### 6.1 Ingreso

| | |
|---|---|
| **Pantalla** | Ingreso de campo |
| **Ruta** | `/revista-armas/ingreso` |
| **Objetivo** | Entrar solo a las armas asignadas |

#### Qué hacer

1. Abra `/revista-armas/ingreso`.
2. Escriba el **correo** y la **contraseña**.
3. Pulse **Entrar**.

#### Resultado esperado

Se abre **Mis armas**. Si el día del mes está fuera de la ventana, el ingreso no continúa.

#### Figura 6.1 — Ingreso de campo

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Contraseña |
| **③** | Entrar |

**[Insertar imagen: fig-06-01-field-login.png]**

---

### 6.2 Tomar las cinco fotos

| | |
|---|---|
| **Objetivo** | Completar las casillas sin salir de la pantalla |

#### Qué hacer

1. En **Mis armas**, abra el arma.
2. Toque la casilla: lado derecho, lado izquierdo, cañón/disparador/marca, serie o impronta.
3. Tome la foto. El recorte, el disparo y **Cancelar** quedan en la misma pantalla.
4. Repita hasta las cinco casillas.
5. Avise al responsable para que revise.

#### Resultado esperado

El borrador queda listo. El staff lo aprueba o lo rechaza.

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| No deja entrar | Confirme que hoy está entre los días configurados y que la cuenta sigue activa |
| Falta una casilla | Vuelva a abrir el arma y tome solo esa foto |

---

### 6.3 Salir

1. Use **Salir** en la barra de Revista.

La sesión de campo queda cerrada.

---

## Control del documento

| Versión | Fecha | Elaboró | Aprobó | Cambios |
|---------|--------|---------|--------|----------|
| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |
| 3.1 | 2026-10-24 | [nombre] | [nombre] | Ajustes, matriz de módulos, instalaciones, puestos y Mi empresa con mapa |
| 3.2 | 2026-11-06 | [nombre] | [nombre] | Usuario de campo con ventana de días, cinco fotos y revisión sin cámara para el staff |

---

*Fin del manual de Revista armas.*
