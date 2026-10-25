<?php
/**
 * Genera los 4 manuales de usuario con procedimientos paso a paso (estilo §1 login).
 */
declare(strict_types=1);

$dir = __DIR__;
$accessTpl = file_get_contents($dir . '/_bloque-acceso-incrustado.md');

function accessBlock(string $nota): string
{
    global $accessTpl;
    return str_replace('{{NOTA_CAMBIO_OBLIGATORIO}}', $nota, $accessTpl);
}

function proc(
    string $title,
    array $meta,
    array $steps,
    string $result,
    array $figures,
    ?array $errors = null,
    ?array $notes = null
): string {
    $md = "### {$title}\n\n";
    $md .= "| | |\n|---|---|\n";
    foreach ($meta as $k => $v) {
        $md .= '| **' . $k . '** | ' . $v . " |\n";
    }
    $md .= "\n#### Qué hacer\n\n";
    foreach ($steps as $i => $step) {
        $md .= ($i + 1) . '. ' . $step . "\n";
    }
    $md .= "\n#### Resultado esperado\n\n{$result}\n\n";
    foreach ($figures as $fig) {
        $md .= "#### Figura {$fig['id']} — {$fig['title']}\n\n";
        if (! empty($fig['refs'])) {
            $md .= "| Ref. | Qué señalar |\n|------|-------------|\n";
            foreach ($fig['refs'] as $ref => $txt) {
                $md .= "| **{$ref}** | {$txt} |\n";
            }
            $md .= "\n";
        }
        $md .= "**[Insertar imagen: {$fig['file']}]**\n\n";
    }
    if ($notes) {
        $md .= "#### Notas\n\n";
        foreach ($notes as $n) {
            $md .= "- {$n}\n";
        }
        $md .= "\n";
    }
    if ($errors) {
        $md .= "#### Errores frecuentes\n\n| Situación | Acción |\n|-----------|--------|\n";
        foreach ($errors as $e) {
            $md .= "| {$e[0]} | {$e[1]} |\n";
        }
        $md .= "\n";
    }
    $md .= "---\n\n";
    return $md;
}

function chapter(string $num, string $title): string
{
    return "## {$num}. {$title}\n\n";
}

function headerDoc(string $title, string $perfil, string $version = '3.0'): string
{
    return "# {$title}\n\n"
        . "**Empresa:** WCodex  \n"
        . "**Sistema:** Arzen  \n"
        . "**Perfil:** {$perfil}  \n"
        . "**Versión del manual:** {$version}  \n"
        . "**Fecha:** [Completar]\n\n---\n\n";
}

function footerDoc(string $name): string
{
    return "## Control del documento\n\n"
        . "| Versión | Fecha | Elaboró | Aprobó | Cambios |\n"
        . "|---------|--------|---------|--------|----------|\n"
        . "| 3.0 | [fecha] | [nombre] | [nombre] | Procedimientos paso a paso en todas las funciones |\n"
        . "| 3.1 | 2026-10-24 | [nombre] | [nombre] | Ajustes, matriz de módulos, instalaciones, puestos y Mi empresa con mapa |\n\n"
        . "---\n\n*Fin del {$name}.*\n";
}

// --- ADMIN ---
$admin = headerDoc('Manual de usuario — Administrador (rol ADMIN)', 'rol **ADMIN**', '3.1');
$admin .= accessBlock('Administrador inicial de la instalación (`admin@arzen.com`); véase **§2.1**. Sí aplica cambio obligatorio en el primer ingreso.');
$admin .= chapter('2', 'Tipos de cuenta administrador');
$admin .= proc(
    '2.1 Administrador inicial (seed)',
    [
        'Pantalla' => 'Inicio de sesión (`/login`) tras §1.2–1.3',
        'Objetivo' => 'Entrar con la cuenta creada en la instalación del servidor',
    ],
    [
        'Complete **§1.2** (bienvenida) y **§1.3** (login).',
        'En **Correo electrónico**, escriba `admin@arzen.com`.',
        'En **Contraseña**, escriba la contraseña inicial `ArzenAdmin.2026`.',
        'Pulse **Ingresar**.',
        'El sistema pide cambiar la contraseña (§1.4); defina una nueva y guárdela.',
    ],
    'Dashboard **Inicio** con menú completo de ADMIN. Complete después **Mi empresa** (§16).',
    [['id' => '2.1', 'title' => 'Login admin seed', 'file' => 'fig-02-01-login-seed.png', 'refs' => ['①' => 'Correo', '②' => 'Contraseña', '③' => 'Ingresar']]],
    null,
    ['Tras el despliegue, cambie la contraseña desde **Perfil** (§15.3) o cree admins nuevos con contraseña temporal.']
);
$admin .= proc(
    '2.2 Administrador creado por otro ADMIN',
    [
        'Pantalla' => 'Login → Cambio obligatorio → Inicio',
        'Objetivo' => 'Primer ingreso con contraseña temporal',
    ],
    [
        'Reciba por canal seguro el **correo** y la **contraseña temporal** (al crearlo otro admin o con acción **Enviar** en §3.4).',
        'Siga **§1.2** y **§1.3** con esas credenciales.',
        'Tras **Ingresar**, el sistema muestra **§1.4** (cambio obligatorio).',
        'Defina **Nueva contraseña** y **Confirmar** → **Establecer contraseña**.',
        'Verifique que llega al **Inicio** con todos los ítems del menú ADMIN.',
    ],
    'Sesión activa con contraseña propia; ya no se pide cambio obligatorio.',
    [['id' => '2.2', 'title' => 'Secuencia admin nuevo', 'file' => 'fig-02-02-flujo-admin-nuevo.png', 'refs' => []]]
);

$admin .= chapter('3', 'Usuarios del sistema');
$admin .= proc(
    '3.1 Abrir el listado de usuarios',
    ['Menú' => '**Ajustes** → **Usuarios**', 'Ruta' => '`/users`', 'Objetivo' => 'Ver y gestionar cuentas'],
    [
        'Con sesión iniciada, abra el módulo **Ajustes** y la pestaña **Usuarios**.',
        'Espere a que cargue la tabla con columnas: nombre, correo, rol, cargo y estado.',
        'Use la barra de búsqueda o filtros si están visibles para localizar un usuario.',
    ],
    'Listado de usuarios con acciones por fila.',
    [['id' => '3.1', 'title' => 'Listado usuarios', 'file' => 'fig-03-01-users-index.png', 'refs' => ['①' => '**Crear usuario**', '②' => 'Columna **Rol**', '③' => 'Acciones **Editar** / **Enviar**']]]
);
$admin .= proc(
    '3.2 Crear un usuario',
    ['Menú' => '**Ajustes** → **Usuarios** → **Crear usuario**', 'Ruta' => '`/users/create`', 'Objetivo' => 'Alta de cuenta con contraseña temporal, cartera y matriz'],
    [
        'En **Ajustes → Usuarios**, clic **Crear usuario**.',
        'En **Nombre**, escriba el nombre completo.',
        'En **Correo electrónico**, escriba un correo válido (será el login).',
        'En **Rol**, elija **ADMIN**, **RESPONSABLE**, **AUDITOR** o **ALMACEN**. Al cambiar el rol se cargan los permisos por defecto en la matriz.',
        'En **Cargo**, elija un cargo de **Ajustes → Cargos** (opcional).',
        'Si el rol no es **ADMIN**, marque los **clientes** de su cartera. El súper administrador ve todos los clientes.',
        'En la **matriz de módulos**, elija por cada módulo **Nada**, **Ver** o **Gestionar**.',
        'Marque **Activo** según política interna y pulse **Guardar**.',
        'Lea el **mensaje o banner** con la **contraseña temporal** — cópiela de inmediato; **no se vuelve a mostrar**.',
        'Entregue correo + temporal al usuario por canal seguro.',
    ],
    'Usuario creado con cartera y permisos; deberá completar **§1.4** en su primer ingreso.',
    [
        ['id' => '3.2a', 'title' => 'Formulario crear', 'file' => 'fig-03-02-users-create.png', 'refs' => ['①' => 'Correo', '②' => 'Rol', '③' => 'Matriz Nada / Ver / Gestionar', '④' => 'Guardar']],
        ['id' => '3.2b', 'title' => 'Banner contraseña temporal', 'file' => 'fig-03-03-password-banner.png', 'refs' => ['①' => 'Texto temporal (difuminar en impresión)']],
    ],
    [['No guardó la temporal', 'Use §3.4 **Enviar** para generar una nueva']]
);
$admin .= proc(
    '3.3 Editar un usuario',
    ['Menú' => '**Ajustes** → **Usuarios**', 'Ruta' => '`/users/{id}/edit`', 'Objetivo' => 'Modificar datos, cartera o matriz'],
    [
        'En el listado, localice la fila del usuario.',
        'Clic **Editar**.',
        'Modifique nombre, rol, cargo, clientes de la cartera o la matriz **Nada / Ver / Gestionar**.',
        'Si necesita nueva contraseña temporal, marque la opción **Generar contraseña temporal** (si aparece) o use **Enviar** desde el listado (§3.4).',
        'Pulse **Guardar**.',
    ],
    'Datos actualizados; mensaje de confirmación del sistema.',
    [['id' => '3.3', 'title' => 'Edición usuario', 'file' => 'fig-03-04-users-edit.png', 'refs' => []]]
);
$admin .= proc(
    '3.4 Enviar credenciales de acceso',
    ['Menú' => '**Ajustes** → **Usuarios**', 'Objetivo' => 'Reenviar correo y nueva temporal'],
    [
        'En el listado, localice al usuario.',
        'Clic **Enviar** (reenvío de credenciales).',
        'Lea el **modal de confirmación**.',
        'Pulse el botón de **confirmar** envío.',
        'El sistema genera nueva temporal, activa cambio obligatorio y envía correo.',
        'Informe al usuario que debe usar la nueva clave en el próximo login.',
    ],
    'Correo enviado (si SMTP está configurado); usuario verá §1.4 al ingresar.',
    [
        ['id' => '3.4', 'title' => 'Modal Enviar credenciales', 'file' => 'fig-03-05-send-credentials.png', 'refs' => ['①' => 'Confirmar', '②' => 'Cancelar']],
    ],
    [['Correo no llega', 'Revise SMTP; copie temporal si el sistema la muestra en pantalla']]
);
$admin .= proc(
    '3.5 Activar o inactivar usuario',
    ['Menú' => '**Ajustes** → **Usuarios**', 'Objetivo' => 'Bloquear acceso sin borrar historial'],
    [
        'En la fila del usuario, use la acción de **cambio de estado** (activar/inactivar según etiqueta visible).',
        'Confirme en el modal si el sistema lo solicita.',
    ],
    'Estado actualizado; usuario inactivo no puede iniciar sesión.',
    [['id' => '3.5', 'title' => 'Cambio de estado', 'file' => 'fig-03-06-user-status.png', 'refs' => []]]
);

$admin .= chapter('4', 'Cartera operativa (Asignaciones)');
$admin .= proc(
    '4.1 Asignar clientes en el usuario',
    ['Menú' => '**Ajustes** → **Usuarios**', 'Ruta' => '`/users/{id}/edit`', 'Objetivo' => 'Definir qué clientes opera cada usuario'],
    [
        'Abra **Ajustes → Usuarios** y **Editar** del usuario.',
        'En el bloque de **clientes**, marque los que podrá ver y operar.',
        'Desmarque los que debe quitar de su cartera.',
        'Pulse **Guardar**.',
    ],
    'Cartera guardada; el usuario solo ve armas y operación de esos clientes. El **ADMIN** ve todos.',
    [['id' => '4.1', 'title' => 'Clientes en el usuario', 'file' => 'fig-04-01-user-clients.png', 'refs' => ['①' => 'Lista de clientes', '②' => 'Guardar']]],
    null,
    ['Asignar un arma a un **cliente** solo lo hace el súper administrador. La asignación interna (puesto y trabajador) la hacen quienes tienen ese cliente y esas armas en su cartera.']
);
$admin .= proc(
    '4.2 Listar responsables y su cartera',
    ['Menú' => '**Asignaciones**', 'Ruta' => '`/portfolios`', 'Objetivo' => 'Ver qué clientes tiene cada responsable'],
    [
        'Abra **Asignaciones**.',
        'Revise la tabla de **responsables** y la cantidad de clientes asignados.',
    ],
    'Listado listo para consultar o transferir cartera.',
    [['id' => '4.2', 'title' => 'Listado carteras', 'file' => 'fig-04-02-portfolios.png', 'refs' => ['①' => 'Responsable', '②' => 'Acción editar']]]
);
$admin .= proc(
    '4.3 Transferir clientes entre responsables',
    ['Ruta' => '`/portfolios`', 'Objetivo' => 'Mover clientes de un responsable a otro'],
    [
        'En la pantalla de cartera, abra la opción **Transferir** (panel o botón según interfaz).',
        'Seleccione el **responsable origen** y **destino**.',
        'Marque los **clientes** a transferir.',
        'Confirme la operación.',
    ],
    'Clientes reasignados; revise armas y asignaciones operativas afectadas.',
    [['id' => '4.3', 'title' => 'Transferir cartera', 'file' => 'fig-04-03-portfolio-transfer.png', 'refs' => []]],
    null,
    ['La edición diaria de la cartera se hace en **Ajustes → Usuarios**. Esta pantalla sirve para mover varios clientes de un responsable a otro.']
);

// Continue with more sections - clients, weapons, etc.
$admin .= chapter('5', 'Clientes');
$admin .= proc(
    '5.1 Listar clientes',
    ['Menú' => '**Clientes**', 'Ruta' => '`/clients`', 'Objetivo' => 'Consultar clientes operativos'],
    ['Clic **Clientes**.', 'Use filtros y búsqueda del encabezado si necesita acotar.', 'Clic en una fila o en **Editar** para modificar.'],
    'Tabla de clientes visible.',
    [['id' => '5.1', 'title' => 'Listado clientes', 'file' => 'fig-05-01-clients.png', 'refs' => ['①' => '**Nuevo cliente**', '②' => 'Filtros']]]
);
$admin .= proc(
    '5.2 Crear cliente',
    ['Ruta' => '`/clients/create`', 'Objetivo' => 'Alta de cliente con ubicación e instalación principal'],
    [
        'Clic **Nuevo cliente**.',
        'Complete identificación, contacto y tipo de persona (natural o jurídica), alineados al formato de Zentra.',
        'En la ubicación, use el icono de **mapa** para fijar la sede (dirección, departamento, municipio y coordenadas).',
        'Pulse **Guardar**.',
    ],
    'Cliente creado. El sistema genera su **instalación principal** con la misma ubicación.',
    [
        ['id' => '5.2a', 'title' => 'Formulario cliente', 'file' => 'fig-05-02-client-create.png', 'refs' => []],
        ['id' => '5.2b', 'title' => 'Mapa en cliente', 'file' => 'fig-05-03-client-map.png', 'refs' => []],
    ]
);
$admin .= proc(
    '5.3 Editar o archivar cliente',
    ['Ruta' => '`/clients/{id}/edit`', 'Objetivo' => 'Actualizar o dar de baja lógica'],
    [
        'Abra **Editar** en la fila del cliente.',
        'Modifique campos necesarios; en ediciones el sistema puede pedir **nota** de historial.',
        'Para archivo, use la acción **Archivar** / estado inactivo según botón visible.',
        'Guarde cambios.',
    ],
    'Cliente actualizado o archivado según acción. La instalación principal se sincroniza con la ubicación del cliente.',
    [['id' => '5.3', 'title' => 'Editar cliente', 'file' => 'fig-05-04-client-edit.png', 'refs' => []]]
);
$admin .= proc(
    '5.4 Instalaciones del cliente',
    ['Menú' => '**Clientes** → ficha o edición', 'Ruta' => '`/clients/{id}/installations/create`', 'Objetivo' => 'Agregar sedes distintas de la principal'],
    [
        'Abra el cliente y localice el bloque **Instalaciones**.',
        'La instalación marcada como sede del cliente ya existe: es la principal.',
        'Para otra sede, clic **Nueva instalación**.',
        'Escriba el nombre. Si la sede es la misma del cliente, marque **Misma ubicación que el cliente**; si no, abra el mapa y fije otra dirección.',
        'Pulse **Guardar**.',
    ],
    'La instalación queda bajo el cliente y podrá recibir puestos.',
    [['id' => '5.4', 'title' => 'Instalaciones', 'file' => 'fig-05-05-installations.png', 'refs' => ['①' => 'Instalación principal', '②' => 'Nueva instalación']]],
    null,
    ['**Mi empresa** también es un cliente del sistema: su sede principal aloja el armerillo.']
);

$admin .= chapter('6', 'Puestos y trabajadores');
$admin .= proc(
    '6.1 Gestionar puestos',
    ['Menú' => '**Puestos**', 'Ruta' => '`/posts`', 'Objetivo' => 'CRUD de puestos dentro de una instalación'],
    [
        'Clic **Puestos**.',
        'Para crear: **Nuevo puesto** → elija **Cliente e instalación** → nombre y nota → **Guardar**.',
        'El puesto toma la dirección y las coordenadas de esa instalación; no se georreferencia aparte.',
        'Para editar: **Editar** en la fila → modifique → **Guardar** (la nota queda en el historial).',
        'Para historial o archivo: use **Historial** o **Restaurar** de la fila.',
    ],
    'Puesto creado dentro de la instalación elegida.',
    [['id' => '6.1', 'title' => 'Puestos', 'file' => 'fig-06-01-posts.png', 'refs' => []]]
);
$admin .= proc(
    '6.2 Gestionar trabajadores',
    ['Menú' => '**Trabajadores**', 'Ruta' => '`/workers`', 'Objetivo' => 'CRUD de trabajadores con cargo y un puesto activo'],
    [
        'Clic **Trabajadores** → **Nuevo trabajador**.',
        'Elija el **cliente**, el documento y el **cargo** (catálogo de **Ajustes → Cargos**).',
        'Opcional: asigne **un puesto** de ese cliente. Un cambio de puesto queda en el historial.',
        'Pulse **Guardar**. Use **Editar**, **Historial** o **Restaurar** desde el listado.',
    ],
    'Trabajador listo para la asignación interna de armas.',
    [['id' => '6.2', 'title' => 'Trabajadores', 'file' => 'fig-06-02-workers.png', 'refs' => []]]
);

$admin .= chapter('7', 'Armamento');
$admin .= proc(
    '7.1 Listar y filtrar armas',
    ['Menú' => '**Armamento**', 'Ruta' => '`/weapons`', 'Objetivo' => 'Buscar armas en inventario'],
    [
        'Clic **Armamento**.',
        'Use filtros del **encabezado de tabla** (cliente, serie, estado, etc.).',
        'Escriba en **búsqueda** si está disponible.',
        'Clic en **serie/código** de una fila para abrir la ficha.',
    ],
    'Listado filtrado; enlace a detalle operativo.',
    [['id' => '7.1', 'title' => 'Listado armas', 'file' => 'fig-07-01-weapons.png', 'refs' => ['①' => 'Filtros', '②' => '**Nueva arma**', '③' => 'Enlace ficha']]]
);
$admin .= proc(
    '7.2 Crear arma',
    ['Ruta' => '`/weapons/create`', 'Objetivo' => 'Alta de arma en inventario'],
    [
        'En listado, clic **Nueva arma**.',
        'Complete datos obligatorios (tipo, serie, marca, etc.).',
        'Pulse **Guardar**.',
        'El sistema redirige a la **ficha** del arma creada.',
    ],
    'Arma registrada; puede cargar documentos y fotos en la ficha.',
    [['id' => '7.2', 'title' => 'Alta arma', 'file' => 'fig-07-02-weapon-create.png', 'refs' => []]]
);
$admin .= proc(
    '7.3 Consultar ficha y notas del arma',
    ['Ruta' => 'Ficha `/weapons/{id}`', 'Objetivo' => 'Revisar trazabilidad antes de editar'],
    [
        'Abra la ficha desde el listado.',
        'Recorra **datos** (izquierda), **documentos**, bloque **Notas** (historial cronológico).',
        'Recorra **destino** y **asignación interna** (derecha).',
        'Baje a franja **Fotos**.',
        'Si hay transferencia pendiente, lea el **aviso** (ADMIN ve remitente y destinatario).',
    ],
    'Visión completa del arma para decidir acciones siguientes.',
    [['id' => '7.3', 'title' => 'Ficha y notas', 'file' => 'fig-07-03-weapon-show.png', 'refs' => ['①' => 'Notas', '②' => 'Destino', '③' => 'Fotos']]]
);
$admin .= proc(
    '7.4 Editar datos maestros del arma',
    ['Ruta' => 'Ficha `/weapons/{id}`', 'Objetivo' => 'Corregir datos técnicos (solo ADMIN)'],
    [
        'Abra la ficha del arma.',
        'Clic **Editar** en la cabecera de la ficha.',
        'Modifique campos en el formulario.',
        'Pulse **Guardar**.',
        'Verifique en la ficha que los datos se actualizaron.',
    ],
    'Datos maestros actualizados; evento puede quedar en **Notas**.',
    [['id' => '7.4', 'title' => 'Editar arma', 'file' => 'fig-07-04-weapon-edit.png', 'refs' => ['①' => 'Botón **Editar**']]]
);
$admin .= proc(
    '7.5 Gestionar documentos en la ficha',
    ['Pantalla' => 'Ficha del arma — bloque **Documentos**', 'Objetivo' => 'Subir, descargar o eliminar soportes'],
    [
        'En la ficha, localice la tabla **Documentos**.',
        'Para subir: clic **Agregar** / **Subir** → elija archivo → confirme.',
        'Para descargar **Permiso**: use el enlace de descarga de la fila permiso (PDF frente + reverso si aplica).',
        'Fila **Revalidación**: visible solo para ADMIN; descargue o cargue según proceso interno.',
        'Para eliminar un documento permitido: icono **Eliminar** → confirme en modal.',
    ],
    'Documentos asociados al arma; descargas según permisos.',
    [['id' => '7.5', 'title' => 'Documentos y revalidación', 'file' => 'fig-07-05-documents.png', 'refs' => ['①' => 'Fila revalidación', '②' => 'Descargar permiso']]]
);
$admin .= proc(
    '7.6 Editar fotos del arma (modo edición)',
    ['Pantalla' => 'Ficha — franja **Fotos**', 'Objetivo' => 'Subir o reemplazar fotos oficiales'],
    [
        'En la franja **Fotos**, localice el toggle **Editar** / **Guardar** (arriba a la derecha).',
        'Clic para activar modo **Editar** (indicador rojo / texto «Editar»).',
        'Clic en una **casilla** (vacía o con imagen).',
        'En el modal **Agregar imagen**: elija **Tomar foto**, **Elegir de galería** o arrastre archivo.',
        'Si aparece editor **Recortar o mover**, ajuste y pulse **Guardar** en el modal.',
        'Espere mensaje **Imagen guardada** (sin recargar toda la página).',
        'Repita para cada casilla: derecho, izquierdo, cañón/marca, serie, impronta, permiso frente.',
        'Al terminar, clic toggle → **Guardar** (verde).',
    ],
    'Fotos oficiales actualizadas en inventario.',
    [
        ['id' => '7.6a', 'title' => 'Toggle Editar', 'file' => 'fig-07-06-toggle.png', 'refs' => []],
        ['id' => '7.6b', 'title' => 'Modal y Cropper', 'file' => 'fig-07-07-cropper.png', 'refs' => []],
    ],
    [['Toggle no pasa a Guardar', 'Cierre modal Cropper o espere fin de subida']]
);
$admin .= proc(
    '7.7 Asignar arma a cliente (destino operativo)',
    ['Pantalla' => 'Ficha — columna derecha **Destino**', 'Objetivo' => 'Asignación operativa'],
    [
        'Verifique que no haya **transferencia pendiente** (aviso en ficha si aplica).',
        'En bloque destino, clic **Asignar a cliente** o equivalente.',
        'Seleccione **cliente** y **responsable** según formulario.',
        'Confirme **Guardar** / **Asignar**.',
    ],
    'Destino operativo actualizado; nota en historial.',
    [['id' => '7.7', 'title' => 'Asignación cliente', 'file' => 'fig-07-08-assign-client.png', 'refs' => []]]
);
$admin .= proc(
    '7.8 Asignación interna (puesto / trabajador)',
    ['Pantalla' => 'Ficha — **Asignación interna**', 'Objetivo' => 'Ubicar arma en puesto o trabajador'],
    [
        'Confirme que el arma tiene **cliente operativo** activo.',
        'Elija **puesto** y/o **trabajador** en los selectores.',
        'Pulse **Asignar** / **Guardar**.',
    ],
    'Asignación interna vigente; mapa prioriza puesto si existe.',
    [['id' => '7.8', 'title' => 'Asignación interna', 'file' => 'fig-07-09-internal-assign.png', 'refs' => []]],
    [['No puede asignar', 'Asigne cliente operativo primero']]
);
$admin .= proc(
    '7.9 Custodia (armerillo, mantenimiento, armero)',
    ['Pantalla' => 'Ficha — botones custodia', 'Objetivo' => 'Mover arma a custodia'],
    [
        'Revise estado actual en la ficha.',
        'Elija **Enviar a mi armerillo**, **Para mantenimiento** o **Enviar a armero** según procedimiento.',
        'Complete modal (puesto de custodia, notas si pide).',
        'Confirme.',
    ],
    'Arma en custodia; listado muestra estado alineado.',
    [['id' => '7.9', 'title' => 'Custodia', 'file' => 'fig-07-10-custody.png', 'refs' => []]]
);
$admin .= proc(
    '7.10 Registrar novedad operativa',
    ['Pantalla' => 'Ficha o módulo novedades', 'Objetivo' => 'Reportar hurto, pérdida, incautación o baja'],
    [
        'Abra el flujo **Novedad** / **Incidente** desde la ficha (según botón visible).',
        'Seleccione **tipo** reportable (hurtada, perdida, incautada, dar de baja).',
        'Complete fecha, descripción y adjuntos si aplica.',
        'Guarde.',
    ],
    'Novedad registrada; aparece en reportes de novedades.',
    [['id' => '7.10', 'title' => 'Novedad', 'file' => 'fig-07-11-incident.png', 'refs' => []]]
);
$admin .= proc(
    '7.11 Exportar inventario a Excel',
    ['Menú' => '**Armamento**', 'Ruta' => 'exportación desde listado', 'Objetivo' => 'Descargar XLSX filtrado'],
    [
        'Aplique filtros deseados en el listado.',
        'Clic **Exportar** (o **Vista previa exportación** si existe).',
        'Confirme selección si el sistema pregunta.',
        'Descargue el archivo `.xlsx` generado.',
    ],
    'Archivo Excel con filas coloreadas según completitud de fotos (y leyenda si aplica).',
    [['id' => '7.11', 'title' => 'Exportar armas', 'file' => 'fig-07-12-export.png', 'refs' => []]]
);

$admin .= chapter('8', 'Transferencias');
$admin .= proc(
    '8.1 Crear transferencia',
    ['Menú' => '**Transferencias**', 'Ruta' => '`/transfers`', 'Objetivo' => 'Enviar armas a otro responsable'],
    [
        'Clic **Transferencias**.',
        'Clic **Nueva transferencia** (o equivalente).',
        'Seleccione **armas** del listado (búsqueda si hay muchas).',
        'Elija **destinatario** (usuario responsable).',
        'Opcional: complete **munición** / **proveedores** si el formulario lo muestra.',
        'Confirme envío.',
    ],
    'Transferencia en estado **Pendiente**; el arma no cambia hasta que acepten.',
    [['id' => '8.1', 'title' => 'Nueva transferencia', 'file' => 'fig-08-01-transfer-new.png', 'refs' => []]]
);
$admin .= proc(
    '8.2 Aceptar transferencia (como destinatario)',
    ['Ruta' => '`/transfers`', 'Objetivo' => 'Recibir armas pendientes'],
    [
        'En listado, localice fila **Pendiente** donde usted es destinatario.',
        'Clic **Aceptar**.',
        'En el modal/formulario, elija **cliente** de su cartera, **puesto** y/o **trabajador**.',
        'Confirme **Aceptar**.',
    ],
    'Transferencia completada; arma bajo su responsabilidad.',
    [['id' => '8.2', 'title' => 'Aceptar transferencia', 'file' => 'fig-08-02-transfer-accept.png', 'refs' => []]]
);
$admin .= proc(
    '8.3 Cancelar transferencia',
    ['Ruta' => '`/transfers`', 'Objetivo' => 'Anular envío pendiente'],
    [
        'Localice la transferencia **Pendiente**.',
        'Clic **Cancelar**.',
        'Lea el **modal de confirmación** del sistema.',
        'Confirme cancelación.',
    ],
    'Transferencia cancelada; restauración según reglas del sistema.',
    [['id' => '8.3', 'title' => 'Cancelar transferencia', 'file' => 'fig-08-03-transfer-cancel.png', 'refs' => []]]
);

$admin .= chapter('9', 'Alertas documentales');
$admin .= proc(
    '9.1 Revisar tarjetas y filtrar por meses',
    ['Menú' => '**Alertas**', 'Ruta' => '`/alerts/documents`', 'Objetivo' => 'Ver vencidos y por vencer'],
    [
        'Clic **Alertas**.',
        'Revise tarjetas: **Vencidos**, **Por vencer**, **Sin alertas**.',
        'Clic **Meses** → marque uno o varios **meses/años** → **Filtrar**.',
        'Clic en una tarjeta para abrir el **modal** con tabla de armas.',
    ],
    'Modal con listado filtrado por criterio de tarjeta.',
    [['id' => '9.1', 'title' => 'Alertas y meses', 'file' => 'fig-09-01-alerts.png', 'refs' => ['①' => 'Tarjetas', '②' => 'Meses']]]
);
$admin .= proc(
    '9.2 Filtrar columnas y exportar relación',
    ['Pantalla' => 'Modal alertas', 'Objetivo' => 'Exportar DOCX o PDF'],
    [
        'En el modal, use **buscar** para texto libre.',
        'Active **Excluir no revalidables** si aplica a su proceso.',
        'Clic **▼** en encabezado de columna (ej. **Cliente**) → marque valores → **Aplicar**.',
        'Seleccione filas con checkboxes.',
        'Clic **Vista previa PDF** o **Descargar relación** (`.docx`).',
        'Guarde archivo (`Revalidacion_{mes}_{año}` en PDF).',
    ],
    'Archivo generado con armas seleccionadas.',
    [
        ['id' => '9.2a', 'title' => 'Filtro columna', 'file' => 'fig-09-02-filter-col.png', 'refs' => []],
        ['id' => '9.2b', 'title' => 'Exportar', 'file' => 'fig-09-03-export-alerts.png', 'refs' => []],
    ]
);

$admin .= chapter('10', 'Cargas masivas');
$admin .= proc(
    '10.1 Importar armas desde Excel',
    ['Menú' => '**Cargas masivas**', 'Ruta' => '`/subir-armas`', 'Objetivo' => 'Carga por lotes'],
    [
        'Clic **Cargas masivas**.',
        'Descargue o use la **plantilla** indicada en pantalla.',
        'Clic **Subir** / seleccione archivo Excel.',
        'Revise pantalla de **vista previa** y errores de validación.',
        'Corrija el archivo si hay filas rechazadas.',
        'Pulse **Ejecutar** / iniciar procesamiento por lotes.',
        'Espere barra de progreso hasta **completado**.',
        'Revise resumen del lote.',
    ],
    'Armas importadas o reporte de errores por fila.',
    [
        ['id' => '10.1a', 'title' => 'Subir plantilla', 'file' => 'fig-10-01-import.png', 'refs' => []],
        ['id' => '10.1b', 'title' => 'Preview', 'file' => 'fig-10-02-preview.png', 'refs' => []],
    ]
);
$admin .= proc(
    '10.2 Plantillas permiso autenticado (porte / tenencia)',
    ['Ruta' => '`/subir-armas`', 'Objetivo' => 'Configurar reverso PDF global'],
    [
        'En **Cargas masivas**, localice sección **plantillas** permiso autenticado.',
        'Elija **porte** o **tenencia**.',
        'Suba imagen de reverso según instrucciones.',
        'Guarde.',
    ],
    'Plantillas usadas en PDF de permiso y ficha.',
    [['id' => '10.2', 'title' => 'Plantillas permiso', 'file' => 'fig-10-03-permit-templates.png', 'refs' => []]]
);

$admin .= chapter('11', 'Revista armas (resumen ADMIN)');
$admin .= "Detalle completo en **manual-revista-armas.md**. Como ADMIN: usuarios temporales globales, asignar dueño, accesos 12 h, revisar staging, **Actualizar** o **Rechazar**.\n\n";
$admin .= proc(
    '11.1 Abrir Revista armas',
    ['Menú' => '**Revista armas**', 'Ruta' => '`/revista-armas`', 'Objetivo' => 'Gestionar fotos de campo'],
    [
        'Clic **Revista armas**.',
        'Opcional: **Usuarios temporales** para crear colaborador.',
        '**Asignar acceso temporal** → usuario + armas → copiar **código** 12 h.',
        'Filtre por **Usuario temporal** → **Filtrar** → **Ver** → **Actualizar** si 4/4 fotos.',
    ],
    'Fotos oficiales actualizadas tras aprobar.',
    [['id' => '11.1', 'title' => 'Revista índice', 'file' => 'fig-11-01-revista.png', 'refs' => []]]
);

$admin .= chapter('12', 'Reportes');
$admin .= proc(
    '12.1 Abrir menú de reportes',
    ['Menú' => '**Reportes**', 'Ruta' => '`/reports`', 'Objetivo' => 'Elegir reporte'],
    ['Clic **Reportes**.', 'Elija el reporte en la lista (auditoría, novedades, custodia, armas por cliente, etc.).', 'Aplique filtros de fecha o cliente según pantalla.', 'Exporte o imprima si hay botón.'],
    'Datos del reporte en pantalla.',
    [['id' => '12.1', 'title' => 'Índice reportes', 'file' => 'fig-12-01-reports.png', 'refs' => []]]
);
$admin .= proc(
    '12.2 Reporte de auditoría del sistema',
    ['Ruta' => '`/reports/audit`', 'Objetivo' => 'Trazabilidad de acciones'],
    [
        'Entre a **Auditoría**.',
        'Seleccione **rango de fechas** (30 / 90 días u opciones).',
        'Revise tabla: usuario, fecha, acción, módulo.',
        'Use búsqueda si está disponible.',
    ],
    'Listado de eventos para control interno.',
    [['id' => '12.2', 'title' => 'Auditoría', 'file' => 'fig-12-02-audit.png', 'refs' => []]]
);
$admin .= proc(
    '12.3 Novedades operativas',
    ['Ruta' => '`/reports/weapon-incidents`', 'Objetivo' => 'Ver hurtos, pérdidas, etc.'],
    [
        'Abra **Novedades operativas**.',
        'Revise gráficos y totales.',
        'Clic **Lista** en un tipo para abrir modal con tabla filtrable.',
    ],
    'Detalle de armas por tipo de novedad.',
    [['id' => '12.3', 'title' => 'Novedades', 'file' => 'fig-12-03-incidents.png', 'refs' => []]]
);
$admin .= proc(
    '12.4 Custodia y taller',
    ['Ruta' => '`/reports/weapon-custody`', 'Objetivo' => 'Armas en armerillo/mantenimiento/armero'],
    [
        'En **Reportes**, abra **Custodia y taller**.',
        'Filtre por responsable si la pantalla lo permite.',
        'Revise tabla y totales.',
    ],
    'Listado de armas en custodia por responsable.',
    [['id' => '12.4', 'title' => 'Custodia reporte', 'file' => 'fig-12-04-custody-report.png', 'refs' => []]]
);
$admin .= proc(
    '12.5 Armas por cliente',
    ['Ruta' => '`/reports/assignments`', 'Objetivo' => 'Asignaciones por cliente'],
    [
        'Abra **Armas por cliente** (o **Asignaciones** en menú de reportes).',
        'Seleccione **cliente** o filtros disponibles.',
        'Pulse **Buscar** / **Filtrar**.',
        'Revise tabla de armas asignadas.',
    ],
    'Reporte filtrado listo para exportar o captura.',
    [['id' => '12.5', 'title' => 'Por cliente', 'file' => 'fig-12-05-by-client.png', 'refs' => []]]
);
$admin .= proc(
    '12.6 Armas sin destino',
    ['Ruta' => '`/reports/no-destination`', 'Objetivo' => 'Inventario sin destino operativo'],
    [
        'Abra **Armas sin destino**.',
        'Revise listado y totales.',
        'Clic en serie para ir a ficha y asignar (§7.6) si corresponde operación.',
    ],
    'Identificación de pendientes de destino.',
    [['id' => '12.6', 'title' => 'Sin destino', 'file' => 'fig-12-06-no-dest.png', 'refs' => []]]
);
$admin .= proc(
    '12.7 Historial por arma',
    ['Ruta' => '`/reports/history`', 'Objetivo' => 'Trazabilidad de un arma'],
    [
        'Abra **Historial por arma**.',
        'Busque arma por **serie** o selector.',
        'Revise línea de tiempo o tabla de eventos.',
    ],
    'Historial consolidado del arma seleccionada.',
    [['id' => '12.7', 'title' => 'Historial arma', 'file' => 'fig-12-07-history.png', 'refs' => []]]
);

$admin .= chapter('13', 'Mapa');
$admin .= proc(
    '13.1 Consultar mapa operativo',
    ['Menú' => '**Mapa**', 'Ruta' => '`/mapa`', 'Objetivo' => 'Ubicación de armas'],
    [
        'Clic **Mapa**.',
        'Cambie capa **Calles** / **Satélite** si necesita.',
        'Clic en un **marcador**.',
        'Lea el resumen en popup.',
        'Clic enlace a **ficha del arma** si desea detalle.',
    ],
    'Ubicación visual según reglas (puesto / cliente / trabajador).',
    [['id' => '13.1', 'title' => 'Mapa', 'file' => 'fig-13-01-map.png', 'refs' => []]]
);

$admin .= chapter('14', 'Inicio (dashboard)');
$admin .= proc(
    '14.1 Usar el tablero Inicio',
    ['Menú' => '**Inicio**', 'Ruta' => '`/dashboard`', 'Objetivo' => 'KPIs operativos'],
    [
        'Clic **Inicio** o logo.',
        'Revise tarjetas KPI y gráficos.',
        'Espere actualización si broadcasting está activo.',
    ],
    'Vista resumen del estado del inventario.',
    [['id' => '14.1', 'title' => 'Dashboard', 'file' => 'fig-14-01-dashboard.png', 'refs' => []]]
);

$admin .= chapter('15', 'Notificaciones y perfil');
$admin .= proc(
    '15.1 Campana de notificaciones',
    ['Pantalla' => 'Barra superior', 'Objetivo' => 'Ver alertas no leídas'],
    [
        'Clic icono **campana** (si está visible).',
        'Lea lista de notificaciones no leídas.',
        'Clic en una para marcar leída e ir al enlace si aplica.',
        'Use **Marcar todas leídas** si aparece.',
    ],
    'Bandeja al día.',
    [['id' => '15.1', 'title' => 'Notificaciones', 'file' => 'fig-15-01-notify.png', 'refs' => []]]
);
$admin .= proc(
    '15.2 Historial de notificaciones',
    ['Menú' => 'Pie del sidebar → **Historial de notificaciones**', 'Objetivo' => 'Ver leídas y no leídas'],
    [
        'En el pie del sidebar, clic en **Historial de notificaciones**.',
        'Revise modal con `?history=1`.',
    ],
    'Historial completo visible.',
    [['id' => '15.2', 'title' => 'Historial notify', 'file' => 'fig-15-02-notify-history.png', 'refs' => []]]
);
$admin .= proc(
    '15.3 Editar perfil y contraseña',
    ['Menú' => 'Pie del sidebar → clic en su **nombre**', 'Ruta' => '`/profile`', 'Objetivo' => 'Actualizar datos propios'],
    [
        'Clic en su **nombre** al pie del sidebar para abrir **Perfil**.',
        'Modifique nombre o correo si está permitido.',
        'Para cambiar contraseña, complete sección **Contraseña** → **Guardar**.',
    ],
    'Perfil actualizado.',
    [['id' => '15.3', 'title' => 'Perfil', 'file' => 'fig-15-03-profile.png', 'refs' => []]]
);

$admin .= chapter('16', 'Mi empresa');
$admin .= proc(
    '16.1 Datos de la empresa, logo y membrete',
    ['Menú' => 'Sidebar → **Mi empresa** → **Datos de la empresa**', 'Ruta' => '`/empresa`', 'Objetivo' => 'Registrar datos legales, sede, logo y membrete en una sola tarjeta'],
    [
        'Abra **Mi empresa**. El icono de ayuda del encabezado abre la guía del módulo.',
        'Complete **Razón social**, **NIT** y los datos del **Representante legal**.',
        'En la ubicación, abra el **mapa** y fije la sede (dirección, departamento y municipio). Esa sede es la instalación principal del cliente «Mi empresa» y aloja el armerillo.',
        'En **Ciudad para la carta**, escriba el texto que debe salir en las cartas (puede diferir del municipio del mapa).',
        'Si los trámites los hace otra persona, complete **Persona autorizada para trámites**.',
        'En **Logo** y **Membrete**, use el recuadro: clic para elegir, arrastrar el archivo o pegar con Ctrl+V. No hay botón «Seleccionar archivo».',
        'Defina el **Prefijo** de los códigos internos de armas y pulse **Guardar cambios**.',
    ],
    'Logo en sidebar, login y revista; cartas con membrete y ciudad propia; la empresa queda como cliente con instalación principal.',
    [['id' => '16.1', 'title' => 'Datos de la empresa', 'file' => 'fig-16-01-company.png', 'refs' => [
        '①' => 'Datos legales y mapa de la sede',
        '②' => 'Ciudad para la carta',
        '③' => 'Zonas de logo y membrete',
    ]]],
    [
        ['Alertas muestra «faltan datos en Mi empresa»', 'Complete los campos indicados y guarde.'],
        ['El Word se rechaza', 'Inserte el membrete como imagen dentro del encabezado del documento.'],
    ],
    ['Cambiar el prefijo no modifica los códigos de armas ya registradas. Los cargos y los tipos de novedad están en **Ajustes** (§17), no en esta pantalla.']
);

$admin .= chapter('17', 'Ajustes');
$admin .= proc(
    '17.1 Cargos',
    ['Menú' => '**Ajustes** → **Cargos**', 'Ruta' => '`/ajustes?tab=cargos`', 'Objetivo' => 'Un solo catálogo de cargos para usuarios y trabajadores'],
    [
        'Abra **Ajustes** y la pestaña **Cargos**.',
        'Escriba el nombre (escolta, guarda, mensajero, recaudador, etc.) y una descripción opcional → **Agregar**.',
        'Edite y **Guardar**, o **Eliminar** si el cargo no está en uso.',
    ],
    'El cargo aparece en usuarios y en trabajadores.',
    [['id' => '17.1', 'title' => 'Cargos', 'file' => 'fig-17-01-positions.png', 'refs' => []]]
);
$admin .= proc(
    '17.2 Tipos de novedad',
    ['Menú' => '**Ajustes** → **Tipos de novedad**', 'Ruta' => '`/ajustes?tab=novedades`', 'Objetivo' => 'Mantener los tipos y modalidades que usa el reporte de novedades'],
    [
        'Abra la pestaña **Tipos de novedad**. Hay una sola tabla. Mantenimiento y armerillo no aparecen.',
        'Clic **Agregar**. Elija **Tipo** (nombre y color) o **Modalidad** (un tipo ya creado y el nombre) → **Guardar**.',
        'En la fila, edite el nombre o el color, marque **Activo** y pulse **Guardar**. Las flechas suben o bajan el orden.',
        '**Eliminar** solo está disponible si ninguna arma usa ese tipo o esa modalidad. Si ya está en uso, desactívelo: los reportes nuevos no lo ofrecen y el arma que ya lo tiene lo conserva.',
    ],
    'El reporte de novedades ofrece los mismos tipos y modalidades activos.',
    [['id' => '17.2', 'title' => 'Tipos de novedad', 'file' => 'fig-17-02-incident-types.png', 'refs' => []]],
    null,
    ['El adjunto del reporte siempre es opcional. La nota de cierre siempre es obligatoria. Esas reglas no se configuran en esta pantalla.']
);

$admin .= footerDoc('manual de administrador');
file_put_contents($dir . '/manual-administrador.md', $admin);
echo 'Wrote manual-administrador.md (' . strlen($admin) . " bytes)\n";

// Include auditor, responsable, revista generators in part 2
require $dir . '/build-manuals-part2.php';
