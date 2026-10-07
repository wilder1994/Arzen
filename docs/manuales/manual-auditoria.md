# Manual de usuario — Auditoría (rol AUDITOR)

**Empresa:** WCodex  
**Sistema:** Arzen  
**Perfil:** rol **AUDITOR** (consulta, reportes y exportaciones)  
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
| **Cuándo NO aplica** | Solo si su cuenta fue creada con **contraseña temporal**; siga **§1.4** en primer ingreso. |

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
## 2. Alcance del auditor

| Puede | No puede |
|-------|----------|
| Ver inventario, fichas, mapa, dashboard | Crear/editar armas, clientes, usuarios |
| Reportes y alertas; exportar relaciones | Asignar, transferir, subir fotos, Revista |
| Descargar documentos permitidos | Ver/descargar fila **Revalidación** (solo ADMIN) |

---

### 3.1 Revisar menú del auditor

| | |
|---|---|
| **Pantalla** | Cualquier vista autenticada |
| **Objetivo** | Confirmar módulos disponibles |

#### Qué hacer

1. Tras **§1**, compare su barra con: **Inicio**, **Armamento**, **Clientes**, **Puestos**, **Trabajadores**, **Reportes**, **Alertas**, **Mapa**, **Transferencias**.
2. Verifique que **no** aparecen: **Usuarios**, **Asignaciones**, **Cargas masivas**, **Revista armas**.

#### Resultado esperado

Menú acorde a rol AUDITOR.

#### Figura 3.1 — Menú AUDITOR

| Ref. | Qué señalar |
|------|-------------|
| **①** | Inicio |
| **②** | Armamento |
| **③** | Reportes/Alertas |

**[Insertar imagen: fig-03-01-menu-auditor.png]**

---

## 4. Consultar armamento

### 4.1 Listar y filtrar armas

| | |
|---|---|
| **Menú** | **Armamento** |
| **Ruta** | `/weapons` |
| **Objetivo** | Buscar en inventario (solo lectura) |

#### Qué hacer

1. Clic **Armamento**.
2. Use filtros del encabezado (cliente, serie, texto).
3. Clic en **serie/código** para abrir ficha.

#### Resultado esperado

Ficha en modo consulta.

#### Figura 4.1 — Listado armas

| Ref. | Qué señalar |
|------|-------------|
| **①** | Filtros |
| **②** | Enlace ficha |

**[Insertar imagen: fig-04-01-weapons.png]**

---

### 4.2 Revisar ficha del arma (solo lectura)

| | |
|---|---|
| **Ruta** | `/weapons/{id}` |
| **Objetivo** | Auditar datos sin modificar |

#### Qué hacer

1. Recorra columna izquierda: datos, **documentos**, **notas**.
2. Columna derecha: **destino**, asignación (sin botones de edición).
3. Franja **Fotos**: visualice; **no** hay toggle **Editar**.
4. Descargue **permiso** u otros documentos permitidos.
5. Confirme que **no** ve fila **Revalidación**.
6. Lea **historial de notas** cronológico.

#### Resultado esperado

Evidencia para informe de auditoría.

#### Figura 4.2a — Ficha completa

**[Insertar imagen: fig-04-02-weapon-show.png]**

#### Figura 4.2b — Sin botón Editar

| Ref. | Qué señalar |
|------|-------------|
| **①** | Ausencia Editar/toggle fotos |

**[Insertar imagen: fig-04-03-readonly.png]**

---

## 5. Mapa

### 5.1 Mapa operativo

| | |
|---|---|
| **Menú** | **Mapa** |
| **Ruta** | `/mapa` |
| **Objetivo** | Ubicar armas |

#### Qué hacer

1. Clic **Mapa**.
2. Cambie **Calles** / **Satélite**.
3. Clic **marcador** → lea popup → enlace a ficha.

#### Resultado esperado

Resumen geográfico.

#### Figura 5.1 — Mapa

**[Insertar imagen: fig-05-01-map.png]**

---

## 6. Inicio (dashboard)

### 6.1 Dashboard

| | |
|---|---|
| **Menú** | **Inicio** |
| **Ruta** | `/dashboard` |
| **Objetivo** | Vista agregada |

#### Qué hacer

1. Clic **Inicio**.
2. Revise KPIs y gráficos.
3. Use como punto de partida antes de listados.

#### Resultado esperado

Indicadores visibles.

#### Figura 6.1 — Dashboard

**[Insertar imagen: fig-06-01-dashboard.png]**

---

## 7. Alertas documentales

### 7.1 Consultar alertas y filtrar meses

| | |
|---|---|
| **Menú** | **Alertas** |
| **Ruta** | `/alerts/documents` |
| **Objetivo** | Vencidos y por vencer |

#### Qué hacer

1. Clic **Alertas**.
2. Revise tarjetas **Vencidos**, **Por vencer**, **Sin alertas**.
3. Clic **Meses** → marque meses → **Filtrar**.
4. Abra tarjeta → modal con tabla.

#### Resultado esperado

Listado en modal.

#### Figura 7.1 — Alertas

| Ref. | Qué señalar |
|------|-------------|
| **①** | Vencidos |
| **②** | Meses |

**[Insertar imagen: fig-07-01-alerts.png]**

---

### 7.2 Filtrar columnas y exportar

| | |
|---|---|
| **Pantalla** | Modal alertas |
| **Objetivo** | Relación DOCX/PDF |

#### Qué hacer

1. Use **buscar** y **Excluir no revalidables**.
2. Clic **▼** en columna → checkboxes → **Aplicar**.
3. Seleccione filas → **Vista previa PDF** o **Descargar relación**.

#### Resultado esperado

Archivo descargado.

#### Figura 7.2 — Filtro columna

**[Insertar imagen: fig-07-02-filter.png]**

---

## 8. Reportes

### 8.1 Reporte auditoría del sistema

| | |
|---|---|
| **Menú** | **Reportes** → Auditoría |
| **Ruta** | `/reports/audit` |
| **Objetivo** | Trazabilidad |

#### Qué hacer

1. Clic **Reportes**.
2. Entre **Auditoría**.
3. Elija rango de fechas.
4. Revise filas (usuario, acción, módulo).

#### Resultado esperado

Evidencia de accesos y cambios.

#### Figura 8.1 — Reporte auditoría

| Ref. | Qué señalar |
|------|-------------|
| **①** | Filtro fechas |

**[Insertar imagen: fig-08-01-audit-report.png]**

---

### 8.2 Novedades operativas

| | |
|---|---|
| **Ruta** | `/reports/weapon-incidents` |
| **Objetivo** | Hurtos, pérdidas, etc. |

#### Qué hacer

1. Abra **Novedades operativas**.
2. Revise gráficos.
3. Clic **Lista** en un tipo → modal tabla.

#### Resultado esperado

Detalle por tipo.

#### Figura 8.2 — Novedades

**[Insertar imagen: fig-08-02-incidents.png]**

---

### 8.3 Custodia y taller

| | |
|---|---|
| **Ruta** | `/reports/weapon-custody` |
| **Objetivo** | Armas en custodia |

#### Qué hacer

1. Abra **Custodia y taller**.
2. Filtre por responsable si aplica.
3. Exporte si hay botón.

#### Resultado esperado

Listado de custodia.

#### Figura 8.3 — Custodia

**[Insertar imagen: fig-08-03-custody.png]**

---

### 8.4 Otros reportes

| | |
|---|---|
| **Menú** | **Reportes** |
| **Objetivo** | Armas por cliente, sin destino, historial |

#### Qué hacer

1. Elija **Armas por cliente**, **Sin destino** o **Historial por arma**.
2. Aplique filtros de pantalla.
3. Documente criterios en su informe.

#### Resultado esperado

Datos para auditoría.

#### Figura 8.4 — Otros reportes

**[Insertar imagen: fig-08-04-reports.png]**

---

## 9. Transferencias (consulta)

### 9.1 Ver transferencias

| | |
|---|---|
| **Menú** | **Transferencias** |
| **Ruta** | `/transfers` |
| **Objetivo** | Consultar estados |

#### Qué hacer

1. Clic **Transferencias**.
2. Revise columnas estado, remitente, destinatario.
3. No use **Nueva** si no está habilitada.

#### Resultado esperado

Trazabilidad de movimientos.

#### Figura 9.1 — Transferencias consulta

**[Insertar imagen: fig-09-01-transfers.png]**

---

## 10. Clientes, puestos y trabajadores (consulta)

### 10.1 Consultar clientes

| | |
|---|---|
| **Menú** | **Clientes** |
| **Ruta** | `/clients` |
| **Objetivo** | Solo lectura |

#### Qué hacer

1. Clic **Clientes**.
2. Filtre y abra edición solo si el sistema lo permite en su rol (normalmente consulta listado).

#### Resultado esperado

Datos de clientes visibles.

#### Figura 10.1 — Clientes

**[Insertar imagen: fig-10-01-clients.png]**

---

### 10.2 Consultar puestos y trabajadores

| | |
|---|---|
| **Menú** | **Puestos** / **Trabajadores** |
| **Objetivo** | Consulta |

#### Qué hacer

1. Repita flujo de listado y filtros como en Armamento.
2. No espere botones de alta si no tiene permiso.

#### Resultado esperado

Listados de apoyo.

#### Figura 10.2 — Puestos/trabajadores

**[Insertar imagen: fig-10-02-posts-workers.png]**

---

## 11. Perfil y cierre

### 11.1 Perfil y cerrar sesión

| | |
|---|---|
| **Menú** | Pie del sidebar (nombre de usuario / **Cerrar sesión**) |
| **Objetivo** | Cuenta propia |

#### Qué hacer

1. Use **§1.7** para cerrar sesión.
2. **Perfil** para cambiar contraseña propia.

#### Resultado esperado

Sesión cerrada o perfil actualizado.

#### Figura 11.1 — Perfil

**[Insertar imagen: fig-11-01-profile.png]**

---

## Control del documento

| Versión | Fecha | Elaboró | Aprobó | Cambios |
|---------|--------|---------|--------|----------|
| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |

---

*Fin del manual de auditoría.*
