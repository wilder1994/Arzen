# Manual de usuario — Administrador (rol ADMIN)

**Empresa:** WCodex  
**Sistema:** Arzen  
**Perfil:** rol **ADMIN**  
**Versión del manual:** 3.1  
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
| **Cuándo NO aplica** | Administrador inicial de la instalación (`admin@arzen.com`); véase **§2.1**. Sí aplica cambio obligatorio en el primer ingreso. |

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
## 2. Tipos de cuenta administrador

### 2.1 Administrador inicial (seed)

| | |
|---|---|
| **Pantalla** | Inicio de sesión (`/login`) tras §1.2–1.3 |
| **Objetivo** | Entrar con la cuenta creada en la instalación del servidor |

#### Qué hacer

1. Complete **§1.2** (bienvenida) y **§1.3** (login).
2. En **Correo electrónico**, escriba `admin@arzen.com`.
3. En **Contraseña**, escriba la contraseña inicial `ArzenAdmin.2026`.
4. Pulse **Ingresar**.
5. El sistema pide cambiar la contraseña (§1.4); defina una nueva y guárdela.

#### Resultado esperado

Dashboard **Inicio** con menú completo de ADMIN. Complete después **Mi empresa** (§16).

#### Figura 2.1 — Login admin seed

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Contraseña |
| **③** | Ingresar |

**[Insertar imagen: fig-02-01-login-seed.png]**

#### Notas

- Tras el despliegue, cambie la contraseña desde **Perfil** (§15.3) o cree admins nuevos con contraseña temporal.

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
| **Menú** | **Ajustes** → **Usuarios** |
| **Ruta** | `/users` |
| **Objetivo** | Ver y gestionar cuentas |

#### Qué hacer

1. Con sesión iniciada, abra el módulo **Ajustes** y la pestaña **Usuarios**.
2. Espere a que cargue la tabla con columnas: nombre, correo, rol, cargo y estado.
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
| **Menú** | **Ajustes** → **Usuarios** → **Crear usuario** |
| **Ruta** | `/users/create` |
| **Objetivo** | Alta de cuenta con contraseña temporal, cartera y matriz |

#### Qué hacer

1. En **Ajustes → Usuarios**, clic **Crear usuario**.
2. En **Nombre**, escriba el nombre completo.
3. En **Correo electrónico**, escriba un correo válido (será el login).
4. En **Rol**, elija **ADMIN**, **RESPONSABLE**, **AUDITOR** o **ALMACEN**. Al cambiar el rol se cargan los permisos por defecto en la matriz.
5. En **Cargo**, elija un cargo de **Ajustes → Cargos** (opcional).
6. Si el rol no es **ADMIN**, marque los **clientes** de su cartera. El súper administrador ve todos los clientes.
7. En la **matriz de módulos**, elija por cada módulo **Nada**, **Ver** o **Gestionar**.
8. Marque **Activo** según política interna y pulse **Guardar**.
9. Lea el **mensaje o banner** con la **contraseña temporal** — cópiela de inmediato; **no se vuelve a mostrar**.
10. Entregue correo + temporal al usuario por canal seguro.

#### Resultado esperado

Usuario creado con cartera y permisos; deberá completar **§1.4** en su primer ingreso.

#### Figura 3.2a — Formulario crear

| Ref. | Qué señalar |
|------|-------------|
| **①** | Correo |
| **②** | Rol |
| **③** | Matriz Nada / Ver / Gestionar |
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
| **Menú** | **Ajustes** → **Usuarios** |
| **Ruta** | `/users/{id}/edit` |
| **Objetivo** | Modificar datos, cartera o matriz |

#### Qué hacer

1. En el listado, localice la fila del usuario.
2. Clic **Editar**.
3. Modifique nombre, rol, cargo, clientes de la cartera o la matriz **Nada / Ver / Gestionar**.
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
| **Menú** | **Ajustes** → **Usuarios** |
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
| **Menú** | **Ajustes** → **Usuarios** |
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

### 4.1 Asignar clientes en el usuario

| | |
|---|---|
| **Menú** | **Ajustes** → **Usuarios** |
| **Ruta** | `/users/{id}/edit` |
| **Objetivo** | Definir qué clientes opera cada usuario |

#### Qué hacer

1. Abra **Ajustes → Usuarios** y **Editar** del usuario.
2. En el bloque de **clientes**, marque los que podrá ver y operar.
3. Desmarque los que debe quitar de su cartera.
4. Pulse **Guardar**.

#### Resultado esperado

Cartera guardada; el usuario solo ve armas y operación de esos clientes. El **ADMIN** ve todos.

#### Figura 4.1 — Clientes en el usuario

| Ref. | Qué señalar |
|------|-------------|
| **①** | Lista de clientes |
| **②** | Guardar |

**[Insertar imagen: fig-04-01-user-clients.png]**

#### Notas

- Asignar un arma a un **cliente** solo lo hace el súper administrador. La asignación interna (puesto y trabajador) la hacen quienes tienen ese cliente y esas armas en su cartera.

---

### 4.2 Listar responsables y su cartera

| | |
|---|---|
| **Menú** | **Asignaciones** |
| **Ruta** | `/portfolios` |
| **Objetivo** | Ver qué clientes tiene cada responsable |

#### Qué hacer

1. Abra **Asignaciones**.
2. Revise la tabla de **responsables** y la cantidad de clientes asignados.

#### Resultado esperado

Listado listo para consultar o transferir cartera.

#### Figura 4.2 — Listado carteras

| Ref. | Qué señalar |
|------|-------------|
| **①** | Responsable |
| **②** | Acción editar |

**[Insertar imagen: fig-04-02-portfolios.png]**

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

#### Notas

- La edición diaria de la cartera se hace en **Ajustes → Usuarios**. Esta pantalla sirve para mover varios clientes de un responsable a otro.

---

## 5. Clientes

### 5.1 Listar clientes

| | |
|---|---|
| **Menú** | **Plataforma** → **Clientes** |
| **Ruta** | `/clients` |
| **Objetivo** | Consultar clientes y cuántas sedes tiene cada uno |

#### Qué hacer

1. Clic **Clientes**.
2. Use el buscador del encabezado si necesita acotar.
3. En la columna **Instalaciones**, **1 misma ubicación** indica que el cliente solo tiene su sede principal. **2 instalaciones**, **3 instalaciones**, etc. indica que tiene sedes adicionales.
4. **Ver** abre la ficha. **Editar** abre el formulario. **Archivar** deja la fila en el listado.

#### Resultado esperado

Tabla con Cliente, NIT, Contacto, Ubicación, Instalaciones y Acciones. Un cliente archivado queda en degradado rojo-naranja y el botón pasa a **Reactivar**.

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
| **Objetivo** | Alta de cliente con ubicación e instalación principal |

#### Qué hacer

1. Clic **Nuevo cliente**.
2. Complete identificación, contacto y tipo de persona (natural o jurídica), alineados al formato de Zentra.
3. En la ubicación, use el icono de **mapa** para fijar la sede (dirección, departamento, municipio y coordenadas).
4. Pulse **Guardar**.

#### Resultado esperado

Cliente creado. El sistema genera su **instalación principal** con la misma ubicación.

#### Figura 5.2a — Formulario cliente

**[Insertar imagen: fig-05-02-client-create.png]**

#### Figura 5.2b — Mapa en cliente

**[Insertar imagen: fig-05-03-client-map.png]**

---

### 5.3 Editar o archivar cliente

| | |
|---|---|
| **Ruta** | `/clients/{id}/edit` |
| **Objetivo** | Actualizar datos o archivar sin borrar |

#### Qué hacer

1. Abra **Editar** en la fila del cliente y guarde los cambios. La instalación principal toma el nombre y la ubicación del cliente.
2. Para archivar, use **Archivar** en la fila. Confirme.
3. Si el cliente tiene otras instalaciones activas, archívelas primero en **Instalaciones**. Si tiene armas asignadas, el sistema no lo archiva.
4. La empresa propia no tiene **Archivar**. Sus datos se editan en **Mi empresa**.

#### Resultado esperado

El cliente archivado sigue en el listado. **Reactivar** lo devuelve a la operación. No se elimina el registro.

#### Figura 5.3 — Editar cliente

**[Insertar imagen: fig-05-04-client-edit.png]**

---

### 5.4 Instalaciones del cliente

| | |
|---|---|
| **Menú** | **Plataforma** → **Instalaciones** |
| **Ruta** | `/installations` y `/installations/{id}/edit` |
| **Objetivo** | Abrir la sede, sus puestos y las personas y armas de cada puesto |

#### Qué hacer

1. Clic **Instalaciones**. La primera columna es la sede; el cliente va después.
2. **Editar** abre la ficha: mapa, datos y los puestos.
3. **Agregar puesto** pide solo el nombre. En la tarjeta del puesto se agregan personas y armas.
4. **Historial** muestra los cambios de la sede y los movimientos del puesto: alta, nombre, persona, arma, archivo y reactivación.
5. **Archivar** un puesto lo deja en la ficha, suelta personas y armas de ese puesto, y el botón pasa a **Reactivar**.
6. **Archivar** una instalación solo si no es la principal y no tiene puestos activos. La fila queda en el listado.
7. Si la sede es la principal, **Editar instalación** avisa y lleva al cliente. Al guardar, regresa a la ficha.

#### Resultado esperado

Cada sede se opera desde su ficha. La principal no se archiva.

#### Figura 5.4 — Instalaciones

| Ref. | Qué señalar |
|------|-------------|
| **①** | Instalación principal |
| **②** | Nueva instalación |

**[Insertar imagen: fig-05-05-installations.png]**

#### Notas

- **Mi empresa** también es un cliente del sistema: su sede principal aloja el armerillo.

---

## 6. Puestos y trabajadores

### 6.1 Gestionar puestos

| | |
|---|---|
| **Menú** | **Plataforma** → **Instalaciones** → ficha de la sede |
| **Ruta** | `/installations/{id}/edit` |
| **Objetivo** | Crear, renombrar y archivar puestos de una sede |

#### Qué hacer

1. Abra la ficha de la instalación.
2. **Agregar puesto** y escriba el nombre. El puesto toma la ubicación de la sede.
3. **Editar** cambia el nombre. **Archivar** pide confirmación: las personas quedan sin puesto y las armas sin ubicación interna. La persona conserva el cliente. El arma conserva el cliente y, si tenía responsable, también lo conserva.
4. El puesto archivado sigue en la ficha. **Reactivar** lo devuelve.

#### Resultado esperado

El puesto queda en la sede y cada movimiento aparece en el historial de esa instalación.

#### Figura 6.1 — Puestos

**[Insertar imagen: fig-06-01-posts.png]**

---

### 6.1.1 Crear un armerillo en la sede

| | |
|---|---|
| **Menú** | **Instalaciones** → ficha de la sede |
| **Objetivo** | Tener una caja del armerillo en esa instalación |

#### Qué hacer

1. En la ficha, pulse **Agregar armerillo** (junto a **Agregar puesto**).
2. Escriba el nombre. La ubicación es la de la sede.
3. En la tarjeta, la puerta inicia cerrada. Pulse la chapa o la manija para abrirla.
4. Dentro hay tres compartimentos: **Listas para asignar**, **Para mantenimiento** y **Munición**. Al elegir uno, la tabla sale a la derecha, sin pasar la altura de la caja.
5. Con la puerta abierta, **Cerrar** queda al lado de la manija. **Archivar** está en la misma tarjeta.

#### Resultado esperado

El armerillo no aparece como puesto de campo. El conteo de puestos de la sede no lo incluye.

#### Notas

- Enviar un arma desde su ficha con **Enviar a mi armerillo**, **Para mantenimiento** o **Enviar a armero** sigue usando la custodia del responsable. Esa custodia no elige todavía esta caja de la sede.

---

### 6.2 Gestionar trabajadores

| | |
|---|---|
| **Menú** | **Trabajadores** |
| **Ruta** | `/workers` |
| **Objetivo** | Registrar la ficha del trabajador, sin asignarlo a un puesto |

#### Qué hacer

1. Clic **Trabajadores** → **Nuevo trabajador**.
2. Complete identificación, lugar de nacimiento y de expedición, y en **Vinculación** el cargo, el tipo de vinculación y el tipo de contrato.
3. Esos catálogos se administran en **Ajustes**. El alta no pide cliente ni puesto: eso se hace en la ficha de la instalación.
4. Pulse **Guardar**. En el listado use **Editar**, **Historial** o **Archivar**.

#### Resultado esperado

Trabajador listo para la asignación interna de armas.

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
2. Complete tipo, serie, marca y calibre. Marca y calibre salen de **Ajustes**. El arma ya no pide tipo de propiedad.
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

1. Descargue la plantilla en **Plataforma** → **Formatos**. **Cargas masivas** solo recibe el archivo.
2. Use esa plantilla. El formato de clientes no trae accesos ni supervisión. El tipo de estructura es opcional.
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

Detalle completo en **manual-revista-armas.md**. El administrador crea el **usuario de campo** en **Usuarios**, envía armas desde **Revista armas** y revisa las cinco fotos. No toma fotos ni genera un código de 12 horas.

### 11.1 Abrir Revista armas

| | |
|---|---|
| **Menú** | **Revista armas** |
| **Ruta** | `/revista-armas` |
| **Objetivo** | Gestionar fotos de campo |

#### Qué hacer

1. En **Usuarios**, cree el **usuario de campo** (correo, zona, responsable y días del mes).
2. En **Revista armas**, pulse **Enviar a campo**, elija ese usuario y marque las armas en la tabla.
3. Cuando el campo haya tomado las fotos, ábralas. **Actualizar** las copia al arma. **Rechazar** borra el borrador. **Cancelar** cierra sin cambiar nada.

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
| **Menú** | Pie del sidebar → **Historial de notificaciones** |
| **Objetivo** | Ver leídas y no leídas |

#### Qué hacer

1. En el pie del sidebar, clic en **Historial de notificaciones**.
2. Revise modal con `?history=1`.

#### Resultado esperado

Historial completo visible.

#### Figura 15.2 — Historial notify

**[Insertar imagen: fig-15-02-notify-history.png]**

---

### 15.3 Editar perfil y contraseña

| | |
|---|---|
| **Menú** | Pie del sidebar → clic en su **nombre** |
| **Ruta** | `/profile` |
| **Objetivo** | Actualizar datos propios |

#### Qué hacer

1. Clic en su **nombre** al pie del sidebar para abrir **Perfil**.
2. Modifique nombre o correo si está permitido.
3. Para cambiar contraseña, complete sección **Contraseña** → **Guardar**.

#### Resultado esperado

Perfil actualizado.

#### Figura 15.3 — Perfil

**[Insertar imagen: fig-15-03-profile.png]**

---

## 16. Mi empresa

### 16.1 Datos de la empresa, logo y membrete

| | |
|---|---|
| **Menú** | Sidebar → **Mi empresa** → **Datos de la empresa** |
| **Ruta** | `/empresa` |
| **Objetivo** | Registrar datos legales, sede, logo y membrete en una sola tarjeta |

#### Qué hacer

1. Abra **Mi empresa**. El icono de ayuda del encabezado abre la guía del módulo.
2. Complete **Razón social**, **NIT** y los datos del **Representante legal**.
3. En la ubicación, abra el **mapa** y fije la sede (dirección, departamento y municipio). Esa sede es la instalación principal del cliente «Mi empresa» y aloja el armerillo.
4. En **Ciudad para la carta**, escriba el texto que debe salir en las cartas (puede diferir del municipio del mapa).
5. Si los trámites los hace otra persona, complete **Persona autorizada para trámites**.
6. En **Logo** y **Membrete**, use el recuadro: clic para elegir, arrastrar el archivo o pegar con Ctrl+V. No hay botón «Seleccionar archivo».
7. Defina el **Prefijo** de los códigos internos de armas y pulse **Guardar cambios**.

#### Resultado esperado

Logo en sidebar, login y revista; cartas con membrete y ciudad propia; la empresa queda como cliente con instalación principal.

#### Figura 16.1 — Datos de la empresa

| Ref. | Qué señalar |
|------|-------------|
| **①** | Datos legales y mapa de la sede |
| **②** | Ciudad para la carta |
| **③** | Zonas de logo y membrete |

**[Insertar imagen: fig-16-01-company.png]**

#### Notas

- Cambiar el prefijo no modifica los códigos de armas ya registradas. Los cargos y los tipos de novedad están en **Ajustes** (§17), no en esta pantalla.

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| Alertas muestra «faltan datos en Mi empresa» | Complete los campos indicados y guarde. |
| El Word se rechaza | Inserte el membrete como imagen dentro del encabezado del documento. |

---

## 17. Ajustes

### 17.1 Cargos

| | |
|---|---|
| **Menú** | **Ajustes** → **Cargos** |
| **Ruta** | `/ajustes?tab=cargos` |
| **Objetivo** | Un solo catálogo de cargos para usuarios y trabajadores |

#### Qué hacer

1. Abra **Ajustes** y la pestaña **Cargos**.
2. Escriba el nombre (escolta, guarda, mensajero, recaudador, etc.) y una descripción opcional → **Agregar**.
3. Edite y **Guardar**, o **Eliminar** si el cargo no está en uso.

#### Resultado esperado

El cargo aparece en usuarios y en trabajadores. En la misma pantalla están **Tipos de vinculación**, **Tipos de contrato** y **Tipos de estructura**. Si una carga trae un nombre que no existe, el sistema lo crea.

#### Figura 17.1 — Cargos

**[Insertar imagen: fig-17-01-positions.png]**

---

### 17.2 Tipos de novedad

| | |
|---|---|
| **Menú** | **Ajustes** → **Tipos de novedad** |
| **Ruta** | `/ajustes?tab=novedades` |
| **Objetivo** | Mantener los tipos y modalidades que usa el reporte de novedades |

#### Qué hacer

1. Abra la pestaña **Tipos de novedad**. Hay una sola tabla. Mantenimiento y armerillo no aparecen.
2. Clic **Agregar**. Elija **Tipo** (nombre y color) o **Modalidad** (un tipo ya creado y el nombre) → **Guardar**.
3. En la fila, edite el nombre o el color, marque **Activo** y pulse **Guardar**. Las flechas suben o bajan el orden.
4. **Eliminar** solo está disponible si ninguna arma usa ese tipo o esa modalidad. Si ya está en uso, desactívelo: los reportes nuevos no lo ofrecen y el arma que ya lo tiene lo conserva.

#### Resultado esperado

El reporte de novedades ofrece los mismos tipos y modalidades activos.

#### Figura 17.2 — Tipos de novedad

**[Insertar imagen: fig-17-02-incident-types.png]**

#### Notas

- El adjunto del reporte siempre es opcional. La nota de cierre siempre es obligatoria. Esas reglas no se configuran en esta pantalla.

---

## Control del documento

| Versión | Fecha | Elaboró | Aprobó | Cambios |
|---------|--------|---------|--------|----------|
| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |
| 3.1 | 2026-10-24 | [nombre] | [nombre] | Ajustes, matriz de módulos, instalaciones, puestos y Mi empresa con mapa |
| 3.2 | 2026-11-05 | [nombre] | [nombre] | Ficha de instalación, archivo visible de cliente y puesto, columna Instalaciones e historial de movimientos |
| 3.3 | 2026-11-06 | [nombre] | [nombre] | Usuario de campo en Revista, armerillo en la sede, marcas y calibres de catálogo |

---

*Fin del manual de administrador.*
