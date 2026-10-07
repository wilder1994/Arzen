<?php
/** Parte 2: auditoría, responsable, revista armas */
declare(strict_types=1);

$dir = __DIR__;

// --- AUDITOR ---
$aud = headerDoc('Manual de usuario — Auditoría (rol AUDITOR)', 'rol **AUDITOR** (consulta, reportes y exportaciones)');
$aud .= accessBlock('Solo si su cuenta fue creada con **contraseña temporal**; siga **§1.4** en primer ingreso.');
$aud .= chapter('2', 'Alcance del auditor');
$aud .= "| Puede | No puede |\n|-------|----------|\n";
$aud .= "| Ver inventario, fichas, mapa, dashboard | Crear/editar armas, clientes, usuarios |\n";
$aud .= "| Reportes y alertas; exportar relaciones | Asignar, transferir, subir fotos, Revista |\n";
$aud .= "| Descargar documentos permitidos | Ver/descargar fila **Revalidación** (solo ADMIN) |\n\n---\n\n";
$aud .= proc(
    '3.1 Revisar menú del auditor',
    ['Pantalla' => 'Cualquier vista autenticada', 'Objetivo' => 'Confirmar módulos disponibles'],
    [
        'Tras **§1**, compare su barra con: **Inicio**, **Armamento**, **Clientes**, **Puestos**, **Trabajadores**, **Reportes**, **Alertas**, **Mapa**, **Transferencias**.',
        'Verifique que **no** aparecen: **Usuarios**, **Asignaciones**, **Cargas masivas**, **Revista armas**.',
    ],
    'Menú acorde a rol AUDITOR.',
    [['id' => '3.1', 'title' => 'Menú AUDITOR', 'file' => 'fig-03-01-menu-auditor.png', 'refs' => ['①' => 'Inicio', '②' => 'Armamento', '③' => 'Reportes/Alertas']]]
);
$aud .= chapter('4', 'Consultar armamento');
$aud .= proc(
    '4.1 Listar y filtrar armas',
    ['Menú' => '**Armamento**', 'Ruta' => '`/weapons`', 'Objetivo' => 'Buscar en inventario (solo lectura)'],
    [
        'Clic **Armamento**.',
        'Use filtros del encabezado (cliente, serie, texto).',
        'Clic en **serie/código** para abrir ficha.',
    ],
    'Ficha en modo consulta.',
    [['id' => '4.1', 'title' => 'Listado armas', 'file' => 'fig-04-01-weapons.png', 'refs' => ['①' => 'Filtros', '②' => 'Enlace ficha']]]
);
$aud .= proc(
    '4.2 Revisar ficha del arma (solo lectura)',
    ['Ruta' => '`/weapons/{id}`', 'Objetivo' => 'Auditar datos sin modificar'],
    [
        'Recorra columna izquierda: datos, **documentos**, **notas**.',
        'Columna derecha: **destino**, asignación (sin botones de edición).',
        'Franja **Fotos**: visualice; **no** hay toggle **Editar**.',
        'Descargue **permiso** u otros documentos permitidos.',
        'Confirme que **no** ve fila **Revalidación**.',
        'Lea **historial de notas** cronológico.',
    ],
    'Evidencia para informe de auditoría.',
    [
        ['id' => '4.2a', 'title' => 'Ficha completa', 'file' => 'fig-04-02-weapon-show.png', 'refs' => []],
        ['id' => '4.2b', 'title' => 'Sin botón Editar', 'file' => 'fig-04-03-readonly.png', 'refs' => ['①' => 'Ausencia Editar/toggle fotos']],
    ]
);
$aud .= chapter('5', 'Mapa');
$aud .= proc('5.1 Mapa operativo', ['Menú' => '**Mapa**', 'Ruta' => '`/mapa`', 'Objetivo' => 'Ubicar armas'], [
    'Clic **Mapa**.', 'Cambie **Calles** / **Satélite**.', 'Clic **marcador** → lea popup → enlace a ficha.',
], 'Resumen geográfico.', [['id' => '5.1', 'title' => 'Mapa', 'file' => 'fig-05-01-map.png', 'refs' => []]]);
$aud .= chapter('6', 'Inicio (dashboard)');
$aud .= proc('6.1 Dashboard', ['Menú' => '**Inicio**', 'Ruta' => '`/dashboard`', 'Objetivo' => 'Vista agregada'], [
    'Clic **Inicio**.', 'Revise KPIs y gráficos.', 'Use como punto de partida antes de listados.',
], 'Indicadores visibles.', [['id' => '6.1', 'title' => 'Dashboard', 'file' => 'fig-06-01-dashboard.png', 'refs' => []]]);
$aud .= chapter('7', 'Alertas documentales');
$aud .= proc(
    '7.1 Consultar alertas y filtrar meses',
    ['Menú' => '**Alertas**', 'Ruta' => '`/alerts/documents`', 'Objetivo' => 'Vencidos y por vencer'],
    [
        'Clic **Alertas**.',
        'Revise tarjetas **Vencidos**, **Por vencer**, **Sin alertas**.',
        'Clic **Meses** → marque meses → **Filtrar**.',
        'Abra tarjeta → modal con tabla.',
    ],
    'Listado en modal.',
    [['id' => '7.1', 'title' => 'Alertas', 'file' => 'fig-07-01-alerts.png', 'refs' => ['①' => 'Vencidos', '②' => 'Meses']]]
);
$aud .= proc(
    '7.2 Filtrar columnas y exportar',
    ['Pantalla' => 'Modal alertas', 'Objetivo' => 'Relación DOCX/PDF'],
    [
        'Use **buscar** y **Excluir no revalidables**.',
        'Clic **▼** en columna → checkboxes → **Aplicar**.',
        'Seleccione filas → **Vista previa PDF** o **Descargar relación**.',
    ],
    'Archivo descargado.',
    [['id' => '7.2', 'title' => 'Filtro columna', 'file' => 'fig-07-02-filter.png', 'refs' => []]]
);
$aud .= chapter('8', 'Reportes');
$aud .= proc(
    '8.1 Reporte auditoría del sistema',
    ['Menú' => '**Reportes** → Auditoría', 'Ruta' => '`/reports/audit`', 'Objetivo' => 'Trazabilidad'],
    [
        'Clic **Reportes**.', 'Entre **Auditoría**.', 'Elija rango de fechas.', 'Revise filas (usuario, acción, módulo).',
    ],
    'Evidencia de accesos y cambios.',
    [['id' => '8.1', 'title' => 'Reporte auditoría', 'file' => 'fig-08-01-audit-report.png', 'refs' => ['①' => 'Filtro fechas']]]
);
$aud .= proc(
    '8.2 Novedades operativas',
    ['Ruta' => '`/reports/weapon-incidents`', 'Objetivo' => 'Hurtos, pérdidas, etc.'],
    [
        'Abra **Novedades operativas**.', 'Revise gráficos.', 'Clic **Lista** en un tipo → modal tabla.',
    ],
    'Detalle por tipo.',
    [['id' => '8.2', 'title' => 'Novedades', 'file' => 'fig-08-02-incidents.png', 'refs' => []]]
);
$aud .= proc(
    '8.3 Custodia y taller',
    ['Ruta' => '`/reports/weapon-custody`', 'Objetivo' => 'Armas en custodia'],
    ['Abra **Custodia y taller**.', 'Filtre por responsable si aplica.', 'Exporte si hay botón.'],
    'Listado de custodia.',
    [['id' => '8.3', 'title' => 'Custodia', 'file' => 'fig-08-03-custody.png', 'refs' => []]]
);
$aud .= proc(
    '8.4 Otros reportes',
    ['Menú' => '**Reportes**', 'Objetivo' => 'Armas por cliente, sin destino, historial'],
    [
        'Elija **Armas por cliente**, **Sin destino** o **Historial por arma**.',
        'Aplique filtros de pantalla.', 'Documente criterios en su informe.',
    ],
    'Datos para auditoría.',
    [['id' => '8.4', 'title' => 'Otros reportes', 'file' => 'fig-08-04-reports.png', 'refs' => []]]
);
$aud .= chapter('9', 'Transferencias (consulta)');
$aud .= proc(
    '9.1 Ver transferencias',
    ['Menú' => '**Transferencias**', 'Ruta' => '`/transfers`', 'Objetivo' => 'Consultar estados'],
    [
        'Clic **Transferencias**.', 'Revise columnas estado, remitente, destinatario.', 'No use **Nueva** si no está habilitada.',
    ],
    'Trazabilidad de movimientos.',
    [['id' => '9.1', 'title' => 'Transferencias consulta', 'file' => 'fig-09-01-transfers.png', 'refs' => []]]
);
$aud .= chapter('10', 'Clientes, puestos y trabajadores (consulta)');
$aud .= proc('10.1 Consultar clientes', ['Menú' => '**Clientes**', 'Ruta' => '`/clients`', 'Objetivo' => 'Solo lectura'], [
    'Clic **Clientes**.', 'Filtre y abra edición solo si el sistema lo permite en su rol (normalmente consulta listado).',
], 'Datos de clientes visibles.', [['id' => '10.1', 'title' => 'Clientes', 'file' => 'fig-10-01-clients.png', 'refs' => []]]);
$aud .= proc('10.2 Consultar puestos y trabajadores', ['Menú' => '**Puestos** / **Trabajadores**', 'Objetivo' => 'Consulta'], [
    'Repita flujo de listado y filtros como en Armamento.', 'No espere botones de alta si no tiene permiso.',
], 'Listados de apoyo.', [['id' => '10.2', 'title' => 'Puestos/trabajadores', 'file' => 'fig-10-02-posts-workers.png', 'refs' => []]]);
$aud .= chapter('11', 'Perfil y cierre');
$aud .= proc('11.1 Perfil y cerrar sesión', ['Menú' => 'Pie del sidebar (nombre de usuario / **Cerrar sesión**)', 'Objetivo' => 'Cuenta propia'], [
    'Use **§1.7** para cerrar sesión.', '**Perfil** para cambiar contraseña propia.',
], 'Sesión cerrada o perfil actualizado.', [['id' => '11.1', 'title' => 'Perfil', 'file' => 'fig-11-01-profile.png', 'refs' => []]]);
$aud .= footerDoc('manual de auditoría');
file_put_contents($dir . '/manual-auditoria.md', $aud);
echo 'Wrote manual-auditoria.md (' . strlen($aud) . " bytes)\n";

// --- RESPONSABLE ---
$resp = headerDoc('Manual de usuario — Responsable (rol RESPONSABLE)', '**RESPONSABLE** — **Nivel 1** (gestión) o **Nivel 2** (solo lectura)');
$resp .= accessBlock('Casi siempre aplica **§1.4** en el primer ingreso (contraseña temporal).');
$resp .= chapter('2', 'Nivel 1 vs Nivel 2');
$resp .= "| Acción | Nivel 1 | Nivel 2 |\n|--------|---------|----------|\n";
$resp .= "| Ver armas de cartera | Sí | Sí |\n| Asignar, fotos, transferencias, Revista | Sí | No (consulta) |\n| Editar datos maestros arma | No | No |\n\n---\n\n";
$resp .= proc(
    '3.1 Menú del responsable',
    ['Objetivo' => 'Confirmar módulos'],
    [
        'Tras §1, verifique menú típico Nivel 1: **Inicio**, **Armamento**, **Revista armas**, **Clientes**, **Puestos**, **Trabajadores**, **Mapa**, **Transferencias**.',
        'No verá **Usuarios**, **Asignaciones**, **Cargas masivas**, **Reportes**, **Alertas** (salvo cambio de política).',
    ],
    'Menú acorde a nivel.',
    [['id' => '3.1', 'title' => 'Menú responsable', 'file' => 'fig-03-01-menu-resp.png', 'refs' => []]]
);
$resp .= chapter('4', 'Armamento en su cartera');
$resp .= proc(
    '4.1 Listar armas de su cartera',
    ['Menú' => '**Armamento**', 'Ruta' => '`/weapons`', 'Objetivo' => 'Inventario filtrado'],
    [
        'Clic **Armamento**.', 'Filtre por **Cliente** de su cartera.', 'Abra ficha por serie.',
    ],
    'Solo armas autorizadas.',
    [['id' => '4.1', 'title' => 'Listado cartera', 'file' => 'fig-04-01-weapons.png', 'refs' => ['①' => 'Filtro cliente']]]
);
$resp .= proc(
    '4.2 Asignar destino operativo (Nivel 1)',
    ['Pantalla' => 'Ficha — destino', 'Objetivo' => 'Cliente y responsable'],
    [
        'Abra ficha.', 'Verifique aviso de **transferencia pendiente** si aparece.',
        'Clic **Asignar a cliente**.', 'Seleccione cliente de **su cartera**.', 'Confirme.',
    ],
    'Destino actualizado.',
    [['id' => '4.2', 'title' => 'Destino', 'file' => 'fig-04-02-destino.png', 'refs' => []]],
    [['No ve el arma', 'Fuera de cartera — contacte ADMIN']]
);
$resp .= proc(
    '4.3 Asignación interna (Nivel 1)',
    ['Pantalla' => 'Ficha', 'Objetivo' => 'Puesto/trabajador'],
    [
        'Con cliente operativo activo, elija **puesto** y/o **trabajador**.', 'Pulse **Asignar**.',
    ],
    'Interna vigente.',
    [['id' => '4.3', 'title' => 'Interna', 'file' => 'fig-04-03-interna.png', 'refs' => []]]
);
$resp .= proc(
    '4.4 Custodia (Nivel 1)',
    ['Pantalla' => 'Ficha', 'Objetivo' => 'Armerillo / mantenimiento / armero'],
    [
        'Use **Enviar a mi armerillo**, **Para mantenimiento** o **Enviar a armero**.', 'Complete modal.', 'Confirme.',
    ],
    'Estado custodia actualizado.',
    [['id' => '4.4', 'title' => 'Custodia', 'file' => 'fig-04-04-custody.png', 'refs' => []]]
);
$resp .= proc(
    '4.5 Documentos en ficha (Nivel 1)',
    ['Pantalla' => 'Documentos', 'Objetivo' => 'Cargar soportes'],
    [
        'Suba archivos con **Agregar**.', 'Descargue **permiso**.', 'No verá **Revalidación**.',
    ],
    'Documentos actualizados.',
    [['id' => '4.5', 'title' => 'Documentos', 'file' => 'fig-04-05-docs.png', 'refs' => []]]
);
$resp .= proc(
    '4.6 Fotos del arma (Nivel 1) — paso a paso',
    ['Pantalla' => 'Franja Fotos', 'Objetivo' => 'Subir fotos oficiales'],
    [
        'Active toggle **Editar** (rojo).',
        'Clic casilla → modal **Agregar imagen**.',
        '**Tomar foto** o **Galería**.',
        '**Recortar o mover** → **Guardar** en cropper.',
        'Espere **Imagen guardada**.',
        'Repita por casilla: derecho, izquierdo, cañón, serie, impronta, permiso frente.',
        'Toggle → **Guardar** (verde).',
    ],
    'Fotos oficiales guardadas.',
    [
        ['id' => '4.6a', 'title' => 'Toggle', 'file' => 'fig-04-06-toggle.png', 'refs' => []],
        ['id' => '4.6b', 'title' => 'Cropper', 'file' => 'fig-04-07-cropper.png', 'refs' => []],
    ]
);
$resp .= proc(
    '4.7 Novedad operativa (Nivel 1)',
    ['Objetivo' => 'Hurto, pérdida, etc.'],
    [
        'Desde ficha o módulo novedades, elija tipo reportable.', 'Complete formulario.', 'Guarde.',
    ],
    'Novedad registrada.',
    [['id' => '4.7', 'title' => 'Novedad', 'file' => 'fig-04-08-incident.png', 'refs' => []]]
);
$resp .= chapter('5', 'Transferencias (Nivel 1)');
$resp .= proc(
    '5.1 Enviar transferencia',
    ['Menú' => '**Transferencias**', 'Ruta' => '`/transfers`', 'Objetivo' => 'Enviar armas'],
    [
        '**Nueva transferencia**.', 'Seleccione armas y **destinatario**.', 'Opcional munición/proveedores.', 'Confirme.',
    ],
    'Estado **Pendiente**.',
    [['id' => '5.1', 'title' => 'Nueva transferencia', 'file' => 'fig-05-01-transfer-new.png', 'refs' => []]]
);
$resp .= proc(
    '5.2 Aceptar transferencia',
    ['Objetivo' => 'Recibir armas'],
    [
        'Fila **Pendiente** como destinatario → **Aceptar**.', 'Elija cliente de **su cartera**, puesto/trabajador.', 'Confirme.',
    ],
    'Transferencia completada.',
    [['id' => '5.2', 'title' => 'Aceptar', 'file' => 'fig-05-02-accept.png', 'refs' => []]]
);
$resp .= proc(
    '5.3 Cancelar transferencia',
    ['Objetivo' => 'Anular pendiente'],
    ['**Cancelar** → confirme en modal del sistema.'],
    'Cancelada.',
    [['id' => '5.3', 'title' => 'Cancelar', 'file' => 'fig-05-03-cancel.png', 'refs' => []]]
);
$resp .= chapter('6', 'Revista armas (Nivel 1)');
$resp .= "Procedimiento detallado en **manual-revista-armas.md** (Parte A).\n\n";
$resp .= proc(
    '6.1 Resumen Revista para responsable',
    ['Menú' => '**Revista armas**', 'Ruta' => '`/revista-armas`', 'Objetivo' => 'Fotos de campo'],
    [
        'Cree **usuario temporal** (dueño automático usted).',
        '**Asignar acceso temporal** → armas → código 12 h al colaborador.',
        'Filtre por usuario → **Ver** → **Actualizar** si 4/4.',
    ],
    'Fotos oficiales tras aprobar.',
    [['id' => '6.1', 'title' => 'Revista', 'file' => 'fig-06-01-revista.png', 'refs' => []]]
);
$resp .= chapter('7', 'Nivel 2 (solo lectura)');
$resp .= proc(
    '7.1 Consultar sin editar',
    ['Objetivo' => 'Solo lectura'],
    [
        'Siga §4.1 para listar y abrir fichas.',
        'Verifique que **no** hay toggle fotos ni botones asignar/custodia activos.',
        'Use mapa y transferencias solo en consulta.',
    ],
    'Vista de auditoría operativa sin cambios.',
    [['id' => '7.1', 'title' => 'Solo lectura', 'file' => 'fig-07-01-readonly.png', 'refs' => []]]
);
$resp .= chapter('8', 'Mapa, Inicio y perfil');
$resp .= proc('8.1 Mapa', ['Menú' => '**Mapa**', 'Ruta' => '`/mapa`', 'Objetivo' => 'Ubicación'], [
    'Igual que §13 admin: capas, marcador, enlace ficha.',
], 'Mapa consultado.', [['id' => '8.1', 'title' => 'Mapa', 'file' => 'fig-08-01-map.png', 'refs' => []]]);
$resp .= proc('8.2 Inicio y perfil', ['Menú' => '**Inicio** / Perfil', 'Objetivo' => 'Dashboard y cuenta'], [
    'Revise KPIs en **Inicio**.', '**Perfil** para contraseña.', '§1.7 para salir.',
], 'Listo.', [['id' => '8.2', 'title' => 'Dashboard', 'file' => 'fig-08-02-dashboard.png', 'refs' => []]]);
$resp .= footerDoc('manual de responsable');
file_put_contents($dir . '/manual-responsable.md', $resp);
echo 'Wrote manual-responsable.md (' . strlen($resp) . " bytes)\n";

// --- REVISTA ARMAS ---
$rev = headerDoc('Manual de usuario — Revista armas', '**Staff** (ADMIN / RESPONSABLE niv. 1) y **colaborador temporal**');
$rev .= accessBlock('Solo **staff** (Parte A). Colaboradores: salte a **§12** (ingreso con código).');
$rev .= chapter('2', 'Objetivo y alcance');
$rev .= "Colaborador sube **4 fotos técnicas** (derecho, izquierdo, cañón/marca, serie) a **staging**. Staff revisa y **Actualizar** copia a fotos oficiales.\n\n---\n\n";
$rev .= chapter('3', 'Usuarios temporales (staff)');
$rev .= proc(
    '3.1 Listar usuarios temporales',
    ['Menú' => '**Revista armas** → **Usuarios temporales**', 'Ruta' => '`/revista-armas/usuarios-temporales`', 'Objetivo' => 'Ver colaboradores'],
    [
        'Clic **Revista armas**.', 'Clic **Usuarios temporales**.', 'Revise correo, estado, dueño.',
    ],
    'Listado cargado.',
    [['id' => '3.1', 'title' => 'Listado temporales', 'file' => 'fig-03-01-temp-users.png', 'refs' => []]]
);
$rev .= proc(
    '3.2 Crear usuario temporal',
    ['Ruta' => 'crear temporal', 'Objetivo' => 'Alta colaborador'],
    [
        'Clic **Crear**.', 'Nombre y **correo** (usado en ingreso §12).', 'Activo.',
        'ADMIN: elija **responsable dueño**.', 'RESPONSABLE: dueño es usted.', '**Guardar**.',
    ],
    'Colaborador listo para acceso.',
    [['id' => '3.2', 'title' => 'Crear temporal', 'file' => 'fig-03-02-temp-create.png', 'refs' => ['①' => 'Correo', '②' => 'Dueño (ADMIN)']]]
);
$rev .= proc(
    '3.3 Desactivar usuario temporal',
    ['Objetivo' => 'Bloquear nuevos ingresos'],
    [
        'En fila, **Editar** o desactivar.', 'Confirme.', 'No borra fotos ya en staging.',
    ],
    'Usuario inactivo.',
    [['id' => '3.3', 'title' => 'Desactivar', 'file' => 'fig-03-03-temp-off.png', 'refs' => []]]
);
$rev .= chapter('4', 'Asignar acceso temporal (12 h)');
$rev .= proc(
    '4.1 Generar código y enlace',
    ['Pantalla' => '`/revista-armas`', 'Objetivo' => 'Acceso campo'],
    [
        'Clic **Asignar acceso temporal**.',
        'Seleccione **usuario temporal**.',
        'Marque **armas** (use buscador si hay muchas).',
        'Confirme asignación.',
        'Copie **código** y enlace `/revista-armas/ingreso` del modal de éxito.',
        'Envíe al colaborador por canal seguro.',
    ],
    'Correo enviado; código válido 12 h.',
    [
        ['id' => '4.1a', 'title' => 'Modal asignar', 'file' => 'fig-04-01-assign.png', 'refs' => ['①' => 'Usuario', '②' => 'Armas', '③' => 'Confirmar']],
        ['id' => '4.1b', 'title' => 'Código 12h', 'file' => 'fig-04-02-code.png', 'refs' => ['①' => 'Código', '②' => 'Copiar']],
    ]
);
$rev .= proc(
    '4.2 Revocar acceso',
    ['Objetivo' => 'Invalidar código'],
    [
        'Localice acceso vigente.', 'Use **Revocar**.', 'Confirme.', 'Fotos en staging no se borran.',
    ],
    'Nuevos ingresos bloqueados.',
    [['id' => '4.2', 'title' => 'Revocar', 'file' => 'fig-04-03-revoke.png', 'refs' => []]]
);
$rev .= chapter('5', 'Revisar y aprobar fotos (staff)');
$rev .= proc(
    '5.1 Filtrar por usuario temporal',
    ['Ruta' => '`/revista-armas`', 'Objetivo' => 'Ver progreso'],
    [
        'Desplegable **Usuario temporal** → **Filtrar**.',
        'Columna **Realizado**: ✓ = 4/4, ✕ = falta alguna.',
        'Sin usuario en filtro no aparece **Ver**.',
    ],
    'Tabla filtrada.',
    [['id' => '5.1', 'title' => 'Filtros', 'file' => 'fig-05-01-filters.png', 'refs' => []]]
);
$rev .= proc(
    '5.2 Modal Ver (revisión 4 fotos)',
    ['Objetivo' => 'Inspeccionar staging'],
    [
        'Clic **Ver** en fila.', 'Revise 4 casillas en modal 2×2.', 'Cierre o continúe.',
    ],
    'Evidencia revisada.',
    [['id' => '5.2', 'title' => 'Modal Ver', 'file' => 'fig-05-02-review.png', 'refs' => ['①' => 'Casilla vacía', '②' => 'Con imagen']]]
);
$rev .= proc(
    '5.3 Actualizar fotos oficiales',
    ['Objetivo' => 'Aprobar staging'],
    [
        'Clic **Actualizar**.',
        'Si faltan fotos: lea **aviso** con cantidad — no actualiza.',
        'Si 4/4: confirme en **modal de confirmación**.',
        'Valide en **ficha del arma** fotos y **Notas**.',
    ],
    'Oficiales actualizadas.',
    [
        ['id' => '5.3a', 'title' => 'Aviso faltan', 'file' => 'fig-05-03-missing.png', 'refs' => []],
        ['id' => '5.3b', 'title' => 'Confirmar', 'file' => 'fig-05-04-confirm.png', 'refs' => []],
    ]
);
$rev .= proc(
    '5.4 Rechazar staging',
    ['Objetivo' => 'Borrar borrador'],
    [
        'Clic **Rechazar**.', 'Confirme modal.', 'Colaborador debe volver a capturar.',
    ],
    'Staging eliminado; oficiales sin cambio.',
    [['id' => '5.4', 'title' => 'Rechazar', 'file' => 'fig-05-05-reject.png', 'refs' => []]]
);
$rev .= chapter('6', 'Parte B — Colaborador temporal');
$rev .= proc(
    '6.1 Ingreso con código',
    ['Pantalla' => 'Ingreso Revista', 'Ruta' => '`/revista-armas/ingreso`', 'Objetivo' => 'Entrar sin cuenta Arzen'],
    [
        'Abra enlace del correo o `/revista-armas/ingreso`.',
        '**Correo** registrado por el responsable.',
        '**Código** 12 h (copie exacto).',
        'Envíe formulario → **Mis armas**.',
    ],
    'Listado de armas asignadas.',
    [['id' => '6.1', 'title' => 'Ingreso', 'file' => 'fig-06-01-guest-login.png', 'refs' => ['①' => 'Correo', '②' => 'Código', '③' => 'Entrar']]]
);
$rev .= proc(
    '6.2 Subir las 4 fotos por arma',
    ['Pantalla' => 'Modal captura', 'Objetivo' => 'Completar 4/4'],
    [
        'En **Mis armas**, clic **Ver**.',
        'Toque casilla pendiente.',
        '**Tomar foto** o **Galería**.',
        'Cropper → **Guardar**.',
        'Espere **Imagen guardada**.',
        'Repita hasta ✓ en las cuatro.',
        'Avise al responsable para **Actualizar**.',
    ],
    'Staging completo; staff aprueba.',
    [
        ['id' => '6.2a', 'title' => 'Cuadrícula', 'file' => 'fig-06-02-grid.png', 'refs' => []],
        ['id' => '6.2b', 'title' => 'Cropper móvil', 'file' => 'fig-06-03-cropper.png', 'refs' => []],
    ],
    [['Código inválido', 'Solicite nuevo acceso al responsable']]
);
$rev .= proc(
    '6.3 Cerrar sesión colaborador',
    ['Objetivo' => 'Salir en dispositivo compartido'],
    [
        'Use **Salir** en barra del módulo Revista.',
    ],
    'Sesión invitado cerrada.',
    [['id' => '6.3', 'title' => 'Salir', 'file' => 'fig-06-04-logout.png', 'refs' => []]]
);
$rev .= footerDoc('manual de Revista armas');
file_put_contents($dir . '/manual-revista-armas.md', $rev);
echo 'Wrote manual-revista-armas.md (' . strlen($rev) . " bytes)\n";
