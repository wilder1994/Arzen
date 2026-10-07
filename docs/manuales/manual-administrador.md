# Manual de usuario — Administrador (rol ADMIN)

**Empresa:** WCodex  
**Sistema:** Arzen  
**Perfil:** rol **ADMIN**  
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
| **Cuándo NO aplica** | Administrador inicial del **seed** (`SEED_ADMIN_PASSWORD` en `.env`); véase **§2.1**. No aplica cambio obligatorio en primer ingreso seed. |

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
## 2. Tipos de cuenta administrador

### 2.1 Administrador inicial (seed)

| | |
|---|---|
| **Pantalla** | Inicio de sesión (`/login`) tras §1.2–1.3 |
| **Objetivo** | Entrar con la cuenta creada en la instalación del servidor |

#### Qué hacer

1. Complete **§1.2** (bienvenida) y **§1.3** (login).
2. En **Correo electrónico**, use el correo del admin configurado en base de datos (seeder).
3. En **Contraseña**, use el valor de **`SEED_ADMIN_PASSWORD`** del archivo `.env` del servidor (solo personal autorizado de TI).
4. Pulse **Ingresar**.
5. Si las credenciales son correctas, entra directo al **Inicio** — **no** aparece §1.4 (cambio obligatorio).

#### Resultado esperado

Dashboard **Inicio** con menú completo de ADMIN.

#### Figura 2.1 — Login admin seed

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Contraseña |
| **③** | Ingresar |

**[Insertar imagen: fig-02-01-login-seed.png]**

#### Notas

- Tras el despliegue, cambie la contraseña desde **Perfil** (§17) o cree admins nuevos con contraseña temporal.

---

### 2.2 Administrador creado por otro ADMIN

| | |
|---|---|
| **Pantalla** | Login → Cambio obligatorio → Inicio |
| **Objetivo** | Primer ingreso con contraseña temporal |

#### Qué hacer

1. Reciba por canal seguro el **correo** y la **contraseña temporal** (al crearlo otro admin o con acción **Enviar** en §3.4).
2. Siga **§1.2** y **§1.3** con esas credenciales.
3. Tras **Ingresar**, el sistema muestra **§1.4** (cambio obligatorio).
4. Defina **Nueva contraseña** y **Confirmar** → **Establecer contraseña**.
5. Verifique que llega al **Inicio** con todos los ítems del menú ADMIN.

#### Resultado esperado

Sesión activa con contraseña propia; ya no se pide cambio obligatorio.

#### Figura 2.2 — Secuencia admin nuevo

**[Insertar imagen: fig-02-02-flujo-admin-nuevo.png]**

---

## 3. Usuarios del sistema

### 3.1 Abrir el listado de usuarios

| | |
|---|---|
| **Menú** | **Usuarios** |
| **Ruta** | `/users` |
| **Objetivo** | Ver y gestionar cuentas |

#### Qué hacer

1. Con sesión iniciada, clic en **Usuarios** en la barra superior.
2. Espere a que cargue la tabla con columnas: nombre, correo, rol, nivel (si aplica), estado.
3. Use la barra de búsqueda o filtros si están visibles para localizar un usuario.

#### Resultado esperado

Listado de usuarios con acciones por fila.

#### Figura 3.1 — Listado usuarios

| Ref. | Qué señalar |
|------|-------------|
| **①** | **Crear usuario** |
| **②** | Columna **Rol** |
| **③** | Acciones **Editar** / **Enviar** |

**[Insertar imagen: fig-03-01-users-index.png]**

---

### 3.2 Crear un usuario

| | |
|---|---|
| **Menú** | **Usuarios** → **Crear usuario** |
| **Ruta** | `/users/create` |
| **Objetivo** | Alta de cuenta con contraseña temporal |

#### Qué hacer

1. En `/users`, clic **Crear usuario** (o enlace equivalente).
2. En **Nombre**, escriba el nombre completo.
3. En **Correo electrónico**, escriba un correo válido (será el login).
4. En **Rol**, elija **ADMIN**, **RESPONSABLE** o **AUDITOR**.
5. Si el rol es **RESPONSABLE**, seleccione **Nivel** (1 = gestión, 2 = solo lectura).
6. Complete **Cargo**, **Centro de costo** y marque **Activo** según política interna.
7. Pulse **Guardar**.
8. Lea el **mensaje o banner** con la **contraseña temporal** — cópiela de inmediato; **no se vuelve a mostrar**.
9. Entregue correo + temporal al usuario por canal seguro.

#### Resultado esperado

Usuario creado; el nuevo usuario deberá completar **§1.4** en su primer ingreso.

#### Figura 3.2a — Formulario crear

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Rol |
| **③** | Nivel |
| **④** | Guardar |

**[Insertar imagen: fig-03-02-users-create.png]**

#### Figura 3.2b — Banner contraseña temporal

| Ref. | Qué señalar |
|------|-------------|
| **①** | Texto temporal (difuminar en impresión) |

**[Insertar imagen: fig-03-03-password-banner.png]**

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| No guardó la temporal | Use §3.4 **Enviar** para generar una nueva |

---

### 3.3 Editar un usuario

| | |
|---|---|
| **Menú** | **Usuarios** |
| **Ruta** | `/users/{id}/edit` |
| **Objetivo** | Modificar datos o regenerar temporal |

#### Qué hacer

1. En el listado, localice la fila del usuario.
2. Clic **Editar**.
3. Modifique los campos permitidos (nombre, rol, nivel, estado, etc.).
4. Si necesita nueva contraseña temporal, marque la opción **Generar contraseña temporal** (si aparece) o use **Enviar** desde el listado (§3.4).
5. Pulse **Guardar**.

#### Resultado esperado

Datos actualizados; mensaje de confirmación del sistema.

#### Figura 3.3 — Edición usuario

**[Insertar imagen: fig-03-04-users-edit.png]**

---

### 3.4 Enviar credenciales de acceso

| | |
|---|---|
| **Menú** | **Usuarios** |
| **Objetivo** | Reenviar correo y nueva temporal |

#### Qué hacer

1. En el listado, localice al usuario.
2. Clic **Enviar** (reenvío de credenciales).
3. Lea el **modal de confirmación**.
4. Pulse el botón de **confirmar** envío.
5. El sistema genera nueva temporal, activa cambio obligatorio y envía correo.
6. Informe al usuario que debe usar la nueva clave en el próximo login.

#### Resultado esperado

Correo enviado (si SMTP está configurado); usuario verá §1.4 al ingresar.

#### Figura 3.4 — Modal Enviar credenciales

| Ref. | Qué señalar |
|------|-------------|
| **①** | Confirmar |
| **②** | Cancelar |

**[Insertar imagen: fig-03-05-send-credentials.png]**

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| Correo no llega | Revise SMTP; copie temporal si el sistema la muestra en pantalla |

---

### 3.5 Activar o inactivar usuario

| | |
|---|---|
| **Menú** | **Usuarios** |
| **Objetivo** | Bloquear acceso sin borrar historial |

#### Qué hacer

1. En la fila del usuario, use la acción de **cambio de estado** (activar/inactivar según etiqueta visible).
2. Confirme en el modal si el sistema lo solicita.

#### Resultado esperado

Estado actualizado; usuario inactivo no puede iniciar sesión.

#### Figura 3.5 — Cambio de estado

**[Insertar imagen: fig-03-06-user-status.png]**

---

## 4. Cartera operativa (Asignaciones)

### 4.1 Listar responsables y su cartera

| | |
|---|---|
| **Menú** | **Asignaciones** |
| **Ruta** | `/portfolios` |
| **Objetivo** | Ver qué clientes tiene cada responsable |

#### Qué hacer

1. Clic **Asignaciones** en el menú.
2. Revise la tabla de **responsables** y cantidad de clientes asignados.

#### Resultado esperado

Listado listo para editar o transferir cartera.

#### Figura 4.1 — Listado carteras

| Ref. | Qué señalar |
|------|-------------|
| **①** | Responsable |
| **②** | Acción editar |

**[Insertar imagen: fig-04-01-portfolios.png]**

---

### 4.2 Asignar clientes a un responsable

| | |
|---|---|
| **Ruta** | `/portfolios/{user}/edit` |
| **Objetivo** | Definir cartera del responsable |

#### Qué hacer

1. En **Asignaciones**, clic **Editar** (o gestionar) en la fila del responsable.
2. Marque los **checkboxes** de los clientes que podrá ver y operar.
3. Desmarque los que debe quitar de su cartera.
4. Pulse **Guardar**.

#### Resultado esperado

Cartera guardada; el responsable solo verá armas/clientes de esos clientes.

#### Figura 4.2 — Edición cartera

| Ref. | Qué señalar |
|------|-------------|
| **①** | Lista clientes |
| **②** | Guardar |

**[Insertar imagen: fig-04-02-portfolio-edit.png]**

---

### 4.3 Transferir clientes entre responsables

| | |
|---|---|
| **Ruta** | `/portfolios` |
| **Objetivo** | Mover clientes de un responsable a otro |

#### Qué hacer

1. En la pantalla de cartera, abra la opción **Transferir** (panel o botón según interfaz).
2. Seleccione el **responsable origen** y **destino**.
3. Marque los **clientes** a transferir.
4. Confirme la operación.

#### Resultado esperado

Clientes reasignados; revise armas y asignaciones operativas afectadas.

#### Figura 4.3 — Transferir cartera

**[Insertar imagen: fig-04-03-portfolio-transfer.png]**

---

## 5. Clientes

### 5.1 Listar clientes

| | |
|---|---|
| **Menú** | **Clientes** |
| **Ruta** | `/clients` |
| **Objetivo** | Consultar clientes operativos |

#### Qué hacer

1. Clic **Clientes**.
2. Use filtros y búsqueda del encabezado si necesita acotar.
3. Clic en una fila o en **Editar** para modificar.

#### Resultado esperado

Tabla de clientes visible.

#### Figura 5.1 — Listado clientes

| Ref. | Qué señalar |
|------|-------------|
| **①** | **Nuevo cliente** |
| **②** | Filtros |

**[Insertar imagen: fig-05-01-clients.png]**

---

### 5.2 Crear cliente

| | |
|---|---|
| **Ruta** | `/clients/create` |
| **Objetivo** | Alta de cliente con ubicación |

#### Qué hacer

1. Clic **Nuevo cliente**.
2. Complete datos de identificación, contacto y dirección.
3. Si la pantalla incluye **mapa**, puede buscar dirección o marcar coordenadas (opcional).
4. Pulse **Guardar**.

#### Resultado esperado

Cliente creado y visible en listado.

#### Figura 5.2a — Formulario cliente

**[Insertar imagen: fig-05-02-client-create.png]**

#### Figura 5.2b — Mapa en cliente

**[Insertar imagen: fig-05-03-client-map.png]**

---

### 5.3 Editar o archivar cliente

| | |
|---|---|
| **Ruta** | `/clients/{id}/edit` |
| **Objetivo** | Actualizar o dar de baja lógica |

#### Qué hacer

1. Abra **Editar** en la fila del cliente.
2. Modifique campos necesarios; en ediciones el sistema puede pedir **nota** de historial.
3. Para archivo, use la acción **Archivar** / estado inactivo según botón visible.
4. Guarde cambios.

#### Resultado esperado

Cliente actualizado o archivado según acción.

#### Figura 5.3 — Editar cliente

**[Insertar imagen: fig-05-04-client-edit.png]**

---

## 6. Puestos y trabajadores

### 6.1 Gestionar puestos

| | |
|---|---|
| **Menú** | **Puestos** |
| **Ruta** | `/posts` |
| **Objetivo** | CRUD de puestos operativos |

#### Qué hacer

1. Clic **Puestos**.
2. Para crear: **Nuevo puesto** → complete datos → **Guardar**.
3. Para editar: **Editar** en la fila → modifique → **Guardar** (nota si aplica).
4. Para historial: use enlace **Historial** de la fila si está disponible.
5. Para reactivar archivo: acción **Restaurar** según listado.

#### Resultado esperado

Puestos actualizados en el sistema.

#### Figura 6.1 — Puestos

**[Insertar imagen: fig-06-01-posts.png]**

---

### 6.2 Gestionar trabajadores

| | |
|---|---|
| **Menú** | **Trabajadores** |
| **Ruta** | `/workers` |
| **Objetivo** | CRUD de trabajadores |

#### Qué hacer

1. Clic **Trabajadores**.
2. Mismo flujo que puestos: **Nuevo**, **Editar**, **Historial**, **Restaurar**.
3. Revise el **total** en barra de filtros si aparece.

#### Resultado esperado

Trabajadores registrados para asignación interna de armas.

#### Figura 6.2 — Trabajadores

**[Insertar imagen: fig-06-02-workers.png]**

---

## 7. Armamento

### 7.1 Listar y filtrar armas

| | |
|---|---|
| **Menú** | **Armamento** |
| **Ruta** | `/weapons` |
| **Objetivo** | Buscar armas en inventario |

#### Qué hacer

1. Clic **Armamento**.
2. Use filtros del **encabezado de tabla** (cliente, serie, estado, etc.).
3. Escriba en **búsqueda** si está disponible.
4. Clic en **serie/código** de una fila para abrir la ficha.

#### Resultado esperado

Listado filtrado; enlace a detalle operativo.

#### Figura 7.1 — Listado armas

| Ref. | Qué señalar |
|------|-------------|
| **①** | Filtros |
| **②** | **Nueva arma** |
| **③** | Enlace ficha |

**[Insertar imagen: fig-07-01-weapons.png]**

---

### 7.2 Crear arma

| | |
|---|---|
| **Ruta** | `/weapons/create` |
| **Objetivo** | Alta de arma en inventario |

#### Qué hacer

1. En listado, clic **Nueva arma**.
2. Complete datos obligatorios (tipo, serie, marca, etc.).
3. Pulse **Guardar**.
4. El sistema redirige a la **ficha** del arma creada.

#### Resultado esperado

Arma registrada; puede cargar documentos y fotos en la ficha.

#### Figura 7.2 — Alta arma

**[Insertar imagen: fig-07-02-weapon-create.png]**

---

### 7.3 Consultar ficha y notas del arma

| | |
|---|---|
| **Ruta** | Ficha `/weapons/{id}` |
| **Objetivo** | Revisar trazabilidad antes de editar |

#### Qué hacer

1. Abra la ficha desde el listado.
2. Recorra **datos** (izquierda), **documentos**, bloque **Notas** (historial cronológico).
3. Recorra **destino** y **asignación interna** (derecha).
4. Baje a franja **Fotos**.
5. Si hay transferencia pendiente, lea el **aviso** (ADMIN ve remitente y destinatario).

#### Resultado esperado

Visión completa del arma para decidir acciones siguientes.

#### Figura 7.3 — Ficha y notas

| Ref. | Qué señalar |
|------|-------------|
| **①** | Notas |
| **②** | Destino |
| **③** | Fotos |

**[Insertar imagen: fig-07-03-weapon-show.png]**

---

### 7.4 Editar datos maestros del arma

| | |
|---|---|
| **Ruta** | Ficha `/weapons/{id}` |
| **Objetivo** | Corregir datos técnicos (solo ADMIN) |

#### Qué hacer

1. Abra la ficha del arma.
2. Clic **Editar** en la cabecera de la ficha.
3. Modifique campos en el formulario.
4. Pulse **Guardar**.
5. Verifique en la ficha que los datos se actualizaron.

#### Resultado esperado

Datos maestros actualizados; evento puede quedar en **Notas**.

#### Figura 7.4 — Editar arma

| Ref. | Qué señalar |
|------|-------------|
| **①** | Botón **Editar** |

**[Insertar imagen: fig-07-04-weapon-edit.png]**

---

### 7.5 Gestionar documentos en la ficha

| | |
|---|---|
| **Pantalla** | Ficha del arma — bloque **Documentos** |
| **Objetivo** | Subir, descargar o eliminar soportes |

#### Qué hacer

1. En la ficha, localice la tabla **Documentos**.
2. Para subir: clic **Agregar** / **Subir** → elija archivo → confirme.
3. Para descargar **Permiso**: use el enlace de descarga de la fila permiso (PDF frente + reverso si aplica).
4. Fila **Revalidación**: visible solo para ADMIN; descargue o cargue según proceso interno.
5. Para eliminar un documento permitido: icono **Eliminar** → confirme en modal.

#### Resultado esperado

Documentos asociados al arma; descargas según permisos.

#### Figura 7.5 — Documentos y revalidación

| Ref. | Qué señalar |
|------|-------------|
| **①** | Fila revalidación |
| **②** | Descargar permiso |

**[Insertar imagen: fig-07-05-documents.png]**

---

### 7.6 Editar fotos del arma (modo edición)

| | |
|---|---|
| **Pantalla** | Ficha — franja **Fotos** |
| **Objetivo** | Subir o reemplazar fotos oficiales |

#### Qué hacer

1. En la franja **Fotos**, localice el toggle **Editar** / **Guardar** (arriba a la derecha).
2. Clic para activar modo **Editar** (indicador rojo / texto «Editar»).
3. Clic en una **casilla** (vacía o con imagen).
4. En el modal **Agregar imagen**: elija **Tomar foto**, **Elegir de galería** o arrastre archivo.
5. Si aparece editor **Recortar o mover**, ajuste y pulse **Guardar** en el modal.
6. Espere mensaje **Imagen guardada** (sin recargar toda la página).
7. Repita para cada casilla: derecho, izquierdo, cañón/marca, serie, impronta, permiso frente.
8. Al terminar, clic toggle → **Guardar** (verde).

#### Resultado esperado

Fotos oficiales actualizadas en inventario.

#### Figura 7.6a — Toggle Editar

**[Insertar imagen: fig-07-06-toggle.png]**

#### Figura 7.6b — Modal y Cropper

**[Insertar imagen: fig-07-07-cropper.png]**

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| Toggle no pasa a Guardar | Cierre modal Cropper o espere fin de subida |

---

### 7.7 Asignar arma a cliente (destino operativo)

| | |
|---|---|
| **Pantalla** | Ficha — columna derecha **Destino** |
| **Objetivo** | Asignación operativa |

#### Qué hacer

1. Verifique que no haya **transferencia pendiente** (aviso en ficha si aplica).
2. En bloque destino, clic **Asignar a cliente** o equivalente.
3. Seleccione **cliente** y **responsable** según formulario.
4. Confirme **Guardar** / **Asignar**.

#### Resultado esperado

Destino operativo actualizado; nota en historial.

#### Figura 7.7 — Asignación cliente

**[Insertar imagen: fig-07-08-assign-client.png]**

---

### 7.8 Asignación interna (puesto / trabajador)

| | |
|---|---|
| **Pantalla** | Ficha — **Asignación interna** |
| **Objetivo** | Ubicar arma en puesto o trabajador |

#### Qué hacer

1. Confirme que el arma tiene **cliente operativo** activo.
2. Elija **puesto** y/o **trabajador** en los selectores.
3. Pulse **Asignar** / **Guardar**.

#### Resultado esperado

Asignación interna vigente; mapa prioriza puesto si existe.

#### Figura 7.8 — Asignación interna

**[Insertar imagen: fig-07-09-internal-assign.png]**

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| No puede asignar | Asigne cliente operativo primero |

---

### 7.9 Custodia (armerillo, mantenimiento, armero)

| | |
|---|---|
| **Pantalla** | Ficha — botones custodia |
| **Objetivo** | Mover arma a custodia |

#### Qué hacer

1. Revise estado actual en la ficha.
2. Elija **Enviar a mi armerillo**, **Para mantenimiento** o **Enviar a armero** según procedimiento.
3. Complete modal (puesto de custodia, notas si pide).
4. Confirme.

#### Resultado esperado

Arma en custodia; listado muestra estado alineado.

#### Figura 7.9 — Custodia

**[Insertar imagen: fig-07-10-custody.png]**

---

### 7.10 Registrar novedad operativa

| | |
|---|---|
| **Pantalla** | Ficha o módulo novedades |
| **Objetivo** | Reportar hurto, pérdida, incautación o baja |

#### Qué hacer

1. Abra el flujo **Novedad** / **Incidente** desde la ficha (según botón visible).
2. Seleccione **tipo** reportable (hurtada, perdida, incautada, dar de baja).
3. Complete fecha, descripción y adjuntos si aplica.
4. Guarde.

#### Resultado esperado

Novedad registrada; aparece en reportes de novedades.

#### Figura 7.10 — Novedad

**[Insertar imagen: fig-07-11-incident.png]**

---

### 7.11 Exportar inventario a Excel

| | |
|---|---|
| **Menú** | **Armamento** |
| **Ruta** | exportación desde listado |
| **Objetivo** | Descargar XLSX filtrado |

#### Qué hacer

1. Aplique filtros deseados en el listado.
2. Clic **Exportar** (o **Vista previa exportación** si existe).
3. Confirme selección si el sistema pregunta.
4. Descargue el archivo `.xlsx` generado.

#### Resultado esperado

Archivo Excel con filas coloreadas según completitud de fotos (y leyenda si aplica).

#### Figura 7.11 — Exportar armas

**[Insertar imagen: fig-07-12-export.png]**

---

## 8. Transferencias

### 8.1 Crear transferencia

| | |
|---|---|
| **Menú** | **Transferencias** |
| **Ruta** | `/transfers` |
| **Objetivo** | Enviar armas a otro responsable |

#### Qué hacer

1. Clic **Transferencias**.
2. Clic **Nueva transferencia** (o equivalente).
3. Seleccione **armas** del listado (búsqueda si hay muchas).
4. Elija **destinatario** (usuario responsable).
5. Opcional: complete **munición** / **proveedores** si el formulario lo muestra.
6. Confirme envío.

#### Resultado esperado

Transferencia en estado **Pendiente**; el arma no cambia hasta que acepten.

#### Figura 8.1 — Nueva transferencia

**[Insertar imagen: fig-08-01-transfer-new.png]**

---

### 8.2 Aceptar transferencia (como destinatario)

| | |
|---|---|
| **Ruta** | `/transfers` |
| **Objetivo** | Recibir armas pendientes |

#### Qué hacer

1. En listado, localice fila **Pendiente** donde usted es destinatario.
2. Clic **Aceptar**.
3. En el modal/formulario, elija **cliente** de su cartera, **puesto** y/o **trabajador**.
4. Confirme **Aceptar**.

#### Resultado esperado

Transferencia completada; arma bajo su responsabilidad.

#### Figura 8.2 — Aceptar transferencia

**[Insertar imagen: fig-08-02-transfer-accept.png]**

---

### 8.3 Cancelar transferencia

| | |
|---|---|
| **Ruta** | `/transfers` |
| **Objetivo** | Anular envío pendiente |

#### Qué hacer

1. Localice la transferencia **Pendiente**.
2. Clic **Cancelar**.
3. Lea el **modal de confirmación** del sistema.
4. Confirme cancelación.

#### Resultado esperado

Transferencia cancelada; restauración según reglas del sistema.

#### Figura 8.3 — Cancelar transferencia

**[Insertar imagen: fig-08-03-transfer-cancel.png]**

---

## 9. Alertas documentales

### 9.1 Revisar tarjetas y filtrar por meses

| | |
|---|---|
| **Menú** | **Alertas** |
| **Ruta** | `/alerts/documents` |
| **Objetivo** | Ver vencidos y por vencer |

#### Qué hacer

1. Clic **Alertas**.
2. Revise tarjetas: **Vencidos**, **Por vencer**, **Sin alertas**.
3. Clic **Meses** → marque uno o varios **meses/años** → **Filtrar**.
4. Clic en una tarjeta para abrir el **modal** con tabla de armas.

#### Resultado esperado

Modal con listado filtrado por criterio de tarjeta.

#### Figura 9.1 — Alertas y meses

| Ref. | Qué señalar |
|------|-------------|
| **①** | Tarjetas |
| **②** | Meses |

**[Insertar imagen: fig-09-01-alerts.png]**

---

### 9.2 Filtrar columnas y exportar relación

| | |
|---|---|
| **Pantalla** | Modal alertas |
| **Objetivo** | Exportar DOCX o PDF |

#### Qué hacer

1. En el modal, use **buscar** para texto libre.
2. Active **Excluir no revalidables** si aplica a su proceso.
3. Clic **▼** en encabezado de columna (ej. **Cliente**) → marque valores → **Aplicar**.
4. Seleccione filas con checkboxes.
5. Clic **Vista previa PDF** o **Descargar relación** (`.docx`).
6. Guarde archivo (`Revalidacion_{mes}_{año}` en PDF).

#### Resultado esperado

Archivo generado con armas seleccionadas.

#### Figura 9.2a — Filtro columna

**[Insertar imagen: fig-09-02-filter-col.png]**

#### Figura 9.2b — Exportar

**[Insertar imagen: fig-09-03-export-alerts.png]**

---

## 10. Cargas masivas

### 10.1 Importar armas desde Excel

| | |
|---|---|
| **Menú** | **Cargas masivas** |
| **Ruta** | `/subir-armas` |
| **Objetivo** | Carga por lotes |

#### Qué hacer

1. Clic **Cargas masivas**.
2. Descargue o use la **plantilla** indicada en pantalla.
3. Clic **Subir** / seleccione archivo Excel.
4. Revise pantalla de **vista previa** y errores de validación.
5. Corrija el archivo si hay filas rechazadas.
6. Pulse **Ejecutar** / iniciar procesamiento por lotes.
7. Espere barra de progreso hasta **completado**.
8. Revise resumen del lote.

#### Resultado esperado

Armas importadas o reporte de errores por fila.

#### Figura 10.1a — Subir plantilla

**[Insertar imagen: fig-10-01-import.png]**

#### Figura 10.1b — Preview

**[Insertar imagen: fig-10-02-preview.png]**

---

### 10.2 Plantillas permiso autenticado (porte / tenencia)

| | |
|---|---|
| **Ruta** | `/subir-armas` |
| **Objetivo** | Configurar reverso PDF global |

#### Qué hacer

1. En **Cargas masivas**, localice sección **plantillas** permiso autenticado.
2. Elija **porte** o **tenencia**.
3. Suba imagen de reverso según instrucciones.
4. Guarde.

#### Resultado esperado

Plantillas usadas en PDF de permiso y ficha.

#### Figura 10.2 — Plantillas permiso

**[Insertar imagen: fig-10-03-permit-templates.png]**

---

## 11. Revista armas (resumen ADMIN)

Detalle completo en **manual-revista-armas.md**. Como ADMIN: usuarios temporales globales, asignar dueño, accesos 12 h, revisar staging, **Actualizar** o **Rechazar**.

### 11.1 Abrir Revista armas

| | |
|---|---|
| **Menú** | **Revista armas** |
| **Ruta** | `/revista-armas` |
| **Objetivo** | Gestionar fotos de campo |

#### Qué hacer

1. Clic **Revista armas**.
2. Opcional: **Usuarios temporales** para crear colaborador.
3. **Asignar acceso temporal** → usuario + armas → copiar **código** 12 h.
4. Filtre por **Usuario temporal** → **Filtrar** → **Ver** → **Actualizar** si 4/4 fotos.

#### Resultado esperado

Fotos oficiales actualizadas tras aprobar.

#### Figura 11.1 — Revista índice

**[Insertar imagen: fig-11-01-revista.png]**

---

## 12. Reportes

### 12.1 Abrir menú de reportes

| | |
|---|---|
| **Menú** | **Reportes** |
| **Ruta** | `/reports` |
| **Objetivo** | Elegir reporte |

#### Qué hacer

1. Clic **Reportes**.
2. Elija el reporte en la lista (auditoría, novedades, custodia, armas por cliente, etc.).
3. Aplique filtros de fecha o cliente según pantalla.
4. Exporte o imprima si hay botón.

#### Resultado esperado

Datos del reporte en pantalla.

#### Figura 12.1 — Índice reportes

**[Insertar imagen: fig-12-01-reports.png]**

---

### 12.2 Reporte de auditoría del sistema

| | |
|---|---|
| **Ruta** | `/reports/audit` |
| **Objetivo** | Trazabilidad de acciones |

#### Qué hacer

1. Entre a **Auditoría**.
2. Seleccione **rango de fechas** (30 / 90 días u opciones).
3. Revise tabla: usuario, fecha, acción, módulo.
4. Use búsqueda si está disponible.

#### Resultado esperado

Listado de eventos para control interno.

#### Figura 12.2 — Auditoría

**[Insertar imagen: fig-12-02-audit.png]**

---

### 12.3 Novedades operativas

| | |
|---|---|
| **Ruta** | `/reports/weapon-incidents` |
| **Objetivo** | Ver hurtos, pérdidas, etc. |

#### Qué hacer

1. Abra **Novedades operativas**.
2. Revise gráficos y totales.
3. Clic **Lista** en un tipo para abrir modal con tabla filtrable.

#### Resultado esperado

Detalle de armas por tipo de novedad.

#### Figura 12.3 — Novedades

**[Insertar imagen: fig-12-03-incidents.png]**

---

### 12.4 Custodia y taller

| | |
|---|---|
| **Ruta** | `/reports/weapon-custody` |
| **Objetivo** | Armas en armerillo/mantenimiento/armero |

#### Qué hacer

1. En **Reportes**, abra **Custodia y taller**.
2. Filtre por responsable si la pantalla lo permite.
3. Revise tabla y totales.

#### Resultado esperado

Listado de armas en custodia por responsable.

#### Figura 12.4 — Custodia reporte

**[Insertar imagen: fig-12-04-custody-report.png]**

---

### 12.5 Armas por cliente

| | |
|---|---|
| **Ruta** | `/reports/assignments` |
| **Objetivo** | Asignaciones por cliente |

#### Qué hacer

1. Abra **Armas por cliente** (o **Asignaciones** en menú de reportes).
2. Seleccione **cliente** o filtros disponibles.
3. Pulse **Buscar** / **Filtrar**.
4. Revise tabla de armas asignadas.

#### Resultado esperado

Reporte filtrado listo para exportar o captura.

#### Figura 12.5 — Por cliente

**[Insertar imagen: fig-12-05-by-client.png]**

---

### 12.6 Armas sin destino

| | |
|---|---|
| **Ruta** | `/reports/no-destination` |
| **Objetivo** | Inventario sin destino operativo |

#### Qué hacer

1. Abra **Armas sin destino**.
2. Revise listado y totales.
3. Clic en serie para ir a ficha y asignar (§7.6) si corresponde operación.

#### Resultado esperado

Identificación de pendientes de destino.

#### Figura 12.6 — Sin destino

**[Insertar imagen: fig-12-06-no-dest.png]**

---

### 12.7 Historial por arma

| | |
|---|---|
| **Ruta** | `/reports/history` |
| **Objetivo** | Trazabilidad de un arma |

#### Qué hacer

1. Abra **Historial por arma**.
2. Busque arma por **serie** o selector.
3. Revise línea de tiempo o tabla de eventos.

#### Resultado esperado

Historial consolidado del arma seleccionada.

#### Figura 12.7 — Historial arma

**[Insertar imagen: fig-12-07-history.png]**

---

## 13. Mapa

### 13.1 Consultar mapa operativo

| | |
|---|---|
| **Menú** | **Mapa** |
| **Ruta** | `/mapa` |
| **Objetivo** | Ubicación de armas |

#### Qué hacer

1. Clic **Mapa**.
2. Cambie capa **Calles** / **Satélite** si necesita.
3. Clic en un **marcador**.
4. Lea el resumen en popup.
5. Clic enlace a **ficha del arma** si desea detalle.

#### Resultado esperado

Ubicación visual según reglas (puesto / cliente / trabajador).

#### Figura 13.1 — Mapa

**[Insertar imagen: fig-13-01-map.png]**

---

## 14. Inicio (dashboard)

### 14.1 Usar el tablero Inicio

| | |
|---|---|
| **Menú** | **Inicio** |
| **Ruta** | `/dashboard` |
| **Objetivo** | KPIs operativos |

#### Qué hacer

1. Clic **Inicio** o logo.
2. Revise tarjetas KPI y gráficos.
3. Espere actualización si broadcasting está activo.

#### Resultado esperado

Vista resumen del estado del inventario.

#### Figura 14.1 — Dashboard

**[Insertar imagen: fig-14-01-dashboard.png]**

---

## 15. Notificaciones y perfil

### 15.1 Campana de notificaciones

| | |
|---|---|
| **Pantalla** | Barra superior |
| **Objetivo** | Ver alertas no leídas |

#### Qué hacer

1. Clic icono **campana** (si está visible).
2. Lea lista de notificaciones no leídas.
3. Clic en una para marcar leída e ir al enlace si aplica.
4. Use **Marcar todas leídas** si aparece.

#### Resultado esperado

Bandeja al día.

#### Figura 15.1 — Notificaciones

**[Insertar imagen: fig-15-01-notify.png]**

---

### 15.2 Historial de notificaciones

| | |
|---|---|
| **Menú** | Nombre usuario → **Historial de notificaciones** |
| **Objetivo** | Ver leídas y no leídas |

#### Qué hacer

1. Clic su **nombre** arriba a la derecha.
2. Elija **Historial de notificaciones**.
3. Revise modal con `?history=1`.

#### Resultado esperado

Historial completo visible.

#### Figura 15.2 — Historial notify

**[Insertar imagen: fig-15-02-notify-history.png]**

---

### 15.3 Editar perfil y contraseña

| | |
|---|---|
| **Menú** | Nombre usuario → **Perfil** |
| **Ruta** | `/profile` |
| **Objetivo** | Actualizar datos propios |

#### Qué hacer

1. Abra **Perfil**.
2. Modifique nombre o correo si está permitido.
3. Para cambiar contraseña, complete sección **Contraseña** → **Guardar**.

#### Resultado esperado

Perfil actualizado.

#### Figura 15.3 — Perfil

**[Insertar imagen: fig-15-03-profile.png]**

---

## Control del documento

| Versión | Fecha | Elaboró | Aprobó | Cambios |
|---------|--------|---------|--------|----------|
| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |

---

*Fin del manual de administrador.*
