# Manual de usuario — Responsable (rol RESPONSABLE)

**Empresa:** WCodex  
**Sistema:** Arzen  
**Perfil:** **RESPONSABLE** — **Nivel 1** (gestión) o **Nivel 2** (solo lectura)  
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
| **Cuándo NO aplica** | Casi siempre aplica **§1.4** en el primer ingreso (contraseña temporal). |

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
## 2. Nivel 1 vs Nivel 2

| Acción | Nivel 1 | Nivel 2 |
|--------|---------|----------|
| Ver armas de cartera | Sí | Sí |
| Asignar, fotos, transferencias, Revista | Sí | No (consulta) |
| Editar datos maestros arma | No | No |

---

### 3.1 Menú del responsable

| | |
|---|---|
| **Objetivo** | Confirmar módulos |

#### Qué hacer

1. Tras §1, verifique menú típico Nivel 1: **Inicio**, **Armamento**, **Revista armas**, **Clientes**, **Puestos**, **Trabajadores**, **Mapa**, **Transferencias**.
2. No verá **Usuarios**, **Asignaciones**, **Cargas masivas**, **Reportes**, **Alertas** (salvo cambio de política).

#### Resultado esperado

Menú acorde a nivel.

#### Figura 3.1 — Menú responsable

**[Insertar imagen: fig-03-01-menu-resp.png]**

---

## 4. Armamento en su cartera

### 4.1 Listar armas de su cartera

| | |
|---|---|
| **Menú** | **Armamento** |
| **Ruta** | `/weapons` |
| **Objetivo** | Inventario filtrado |

#### Qué hacer

1. Clic **Armamento**.
2. Filtre por **Cliente** de su cartera.
3. Abra ficha por serie.

#### Resultado esperado

Solo armas autorizadas.

#### Figura 4.1 — Listado cartera

| Ref. | Qué señalar |
|------|-------------|
| **①** | Filtro cliente |

**[Insertar imagen: fig-04-01-weapons.png]**

---

### 4.2 Asignar destino operativo (Nivel 1)

| | |
|---|---|
| **Pantalla** | Ficha — destino |
| **Objetivo** | Cliente y responsable |

#### Qué hacer

1. Abra ficha.
2. Verifique aviso de **transferencia pendiente** si aparece.
3. Clic **Asignar a cliente**.
4. Seleccione cliente de **su cartera**.
5. Confirme.

#### Resultado esperado

Destino actualizado.

#### Figura 4.2 — Destino

**[Insertar imagen: fig-04-02-destino.png]**

#### Errores frecuentes

| Situación | Acción |
|-----------|--------|
| No ve el arma | Fuera de cartera — contacte ADMIN |

---

### 4.3 Asignación interna (Nivel 1)

| | |
|---|---|
| **Pantalla** | Ficha |
| **Objetivo** | Puesto/trabajador |

#### Qué hacer

1. Con cliente operativo activo, elija **puesto** y/o **trabajador**.
2. Pulse **Asignar**.

#### Resultado esperado

Interna vigente.

#### Figura 4.3 — Interna

**[Insertar imagen: fig-04-03-interna.png]**

---

### 4.4 Custodia (Nivel 1)

| | |
|---|---|
| **Pantalla** | Ficha |
| **Objetivo** | Armerillo / mantenimiento / armero |

#### Qué hacer

1. Use **Enviar a mi armerillo**, **Para mantenimiento** o **Enviar a armero**.
2. Complete modal.
3. Confirme.

#### Resultado esperado

Estado custodia actualizado.

#### Figura 4.4 — Custodia

**[Insertar imagen: fig-04-04-custody.png]**

---

### 4.5 Documentos en ficha (Nivel 1)

| | |
|---|---|
| **Pantalla** | Documentos |
| **Objetivo** | Cargar soportes |

#### Qué hacer

1. Suba archivos con **Agregar**.
2. Descargue **permiso**.
3. No verá **Revalidación**.

#### Resultado esperado

Documentos actualizados.

#### Figura 4.5 — Documentos

**[Insertar imagen: fig-04-05-docs.png]**

---

### 4.6 Fotos del arma (Nivel 1) — paso a paso

| | |
|---|---|
| **Pantalla** | Franja Fotos |
| **Objetivo** | Subir fotos oficiales |

#### Qué hacer

1. Active toggle **Editar** (rojo).
2. Clic casilla → modal **Agregar imagen**.
3. **Tomar foto** o **Galería**.
4. **Recortar o mover** → **Guardar** en cropper.
5. Espere **Imagen guardada**.
6. Repita por casilla: derecho, izquierdo, cañón, serie, impronta, permiso frente.
7. Toggle → **Guardar** (verde).

#### Resultado esperado

Fotos oficiales guardadas.

#### Figura 4.6a — Toggle

**[Insertar imagen: fig-04-06-toggle.png]**

#### Figura 4.6b — Cropper

**[Insertar imagen: fig-04-07-cropper.png]**

---

### 4.7 Novedad operativa (Nivel 1)

| | |
|---|---|
| **Objetivo** | Hurto, pérdida, etc. |

#### Qué hacer

1. Desde ficha o módulo novedades, elija tipo reportable.
2. Complete formulario.
3. Guarde.

#### Resultado esperado

Novedad registrada.

#### Figura 4.7 — Novedad

**[Insertar imagen: fig-04-08-incident.png]**

---

## 5. Transferencias (Nivel 1)

### 5.1 Enviar transferencia

| | |
|---|---|
| **Menú** | **Transferencias** |
| **Ruta** | `/transfers` |
| **Objetivo** | Enviar armas |

#### Qué hacer

1. **Nueva transferencia**.
2. Seleccione armas y **destinatario**.
3. Opcional munición/proveedores.
4. Confirme.

#### Resultado esperado

Estado **Pendiente**.

#### Figura 5.1 — Nueva transferencia

**[Insertar imagen: fig-05-01-transfer-new.png]**

---

### 5.2 Aceptar transferencia

| | |
|---|---|
| **Objetivo** | Recibir armas |

#### Qué hacer

1. Fila **Pendiente** como destinatario → **Aceptar**.
2. Elija cliente de **su cartera**, puesto/trabajador.
3. Confirme.

#### Resultado esperado

Transferencia completada.

#### Figura 5.2 — Aceptar

**[Insertar imagen: fig-05-02-accept.png]**

---

### 5.3 Cancelar transferencia

| | |
|---|---|
| **Objetivo** | Anular pendiente |

#### Qué hacer

1. **Cancelar** → confirme en modal del sistema.

#### Resultado esperado

Cancelada.

#### Figura 5.3 — Cancelar

**[Insertar imagen: fig-05-03-cancel.png]**

---

## 6. Revista armas (Nivel 1)

Procedimiento detallado en **manual-revista-armas.md** (Parte A).

### 6.1 Resumen Revista para responsable

| | |
|---|---|
| **Menú** | **Revista armas** |
| **Ruta** | `/revista-armas` |
| **Objetivo** | Fotos de campo |

#### Qué hacer

1. Cree **usuario temporal** (dueño automático usted).
2. **Asignar acceso temporal** → armas → código 12 h al colaborador.
3. Filtre por usuario → **Ver** → **Actualizar** si 4/4.

#### Resultado esperado

Fotos oficiales tras aprobar.

#### Figura 6.1 — Revista

**[Insertar imagen: fig-06-01-revista.png]**

---

## 7. Nivel 2 (solo lectura)

### 7.1 Consultar sin editar

| | |
|---|---|
| **Objetivo** | Solo lectura |

#### Qué hacer

1. Siga §4.1 para listar y abrir fichas.
2. Verifique que **no** hay toggle fotos ni botones asignar/custodia activos.
3. Use mapa y transferencias solo en consulta.

#### Resultado esperado

Vista de auditoría operativa sin cambios.

#### Figura 7.1 — Solo lectura

**[Insertar imagen: fig-07-01-readonly.png]**

---

## 8. Mapa, Inicio y perfil

### 8.1 Mapa

| | |
|---|---|
| **Menú** | **Mapa** |
| **Ruta** | `/mapa` |
| **Objetivo** | Ubicación |

#### Qué hacer

1. Igual que §13 admin: capas, marcador, enlace ficha.

#### Resultado esperado

Mapa consultado.

#### Figura 8.1 — Mapa

**[Insertar imagen: fig-08-01-map.png]**

---

### 8.2 Inicio y perfil

| | |
|---|---|
| **Menú** | **Inicio** / Perfil |
| **Objetivo** | Dashboard y cuenta |

#### Qué hacer

1. Revise KPIs en **Inicio**.
2. **Perfil** para contraseña.
3. §1.7 para salir.

#### Resultado esperado

Listo.

#### Figura 8.2 — Dashboard

**[Insertar imagen: fig-08-02-dashboard.png]**

---

## Control del documento

| Versión | Fecha | Elaboró | Aprobó | Cambios |
|---------|--------|---------|--------|----------|
| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |

---

*Fin del manual de responsable.*
