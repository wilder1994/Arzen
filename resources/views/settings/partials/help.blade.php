<h3>{{ __('¿Qué hay en Ajustes?') }}</h3>
<p>{{ __('Las listas y la configuración propias de su empresa: usuarios con sus permisos, cargos, tipos de vinculación, tipos de contrato, marcas, calibres y tipos de novedad.') }}</p>

<h3>{{ __('Cargos') }}</h3>
<ul>
    <li>{{ __('Es un solo catálogo para usuarios y trabajadores: escolta, guarda, supervisor, mensajero, recaudador, etc.') }}</li>
    <li>{{ __('Las flechas cambian el orden en las listas. Un cargo inactivo deja de ofrecerse en los formularios, pero quien ya lo tiene lo conserva.') }}</li>
    <li>{{ __('Un cargo en uso no se puede eliminar; desactívelo.') }}</li>
    <li>{{ __('En las cargas masivas, si llega un cargo que no existe, se crea automáticamente.') }}</li>
</ul>

<h3>{{ __('Tipos de estructura') }}</h3>
<p>{{ __('Opcional en el cliente. Un tipo en uso no se elimina; se desactiva. En la carga de clientes, si llega un tipo que no existe, se crea.') }}</p>

<h3>{{ __('Tipos de vinculación y de contrato') }}</h3>
<ul>
    <li>{{ __('Cada empresa arma sus propias listas. El sistema trae unas semillas para que el formulario no quede vacío: puede dejarlas, borrarlas o agregar las suyas.') }}</li>
    <li>{{ __('Un tipo en uso no se puede eliminar; desactívelo. En la carga desde Zentra, si llega un tipo que no existe, se crea y la fila sigue.') }}</li>
</ul>

<h3>{{ __('Marcas y calibres') }}</h3>
<ul>
    <li>{{ __('El alta de un arma ofrece estas listas en un buscador. El usuario escribe y elige un valor.') }}</li>
    <li>{{ __('Puede agregar, editar, ordenar o eliminar. Un valor en uso no se elimina; se desactiva y deja de ofrecerse.') }}</li>
    <li>{{ __('En la carga de armas, si llega una marca o un calibre que no existe, se crea y la fila sigue.') }}</li>
</ul>

<h3>{{ __('Tipos de novedad') }}</h3>
<ul>
    <li>{{ __('El sistema trae Hurtada, Perdida, Incautada y Dar de baja. Puede crear otros tipos y sus modalidades. Mantenimiento y armerillo no se configuran aquí.') }}</li>
    <li>{{ __('Agregar abre un cuadro para un tipo nuevo (nombre y color) o una modalidad de un tipo ya creado.') }}</li>
    <li>{{ __('Las flechas cambian el orden. Activo oculta el tipo o la modalidad en reportes nuevos.') }}</li>
    <li>{{ __('Si un arma ya usa el tipo o la modalidad, no se elimina: se desactiva. El reporte guardado conserva el nombre.') }}</li>
    <li>{{ __('El adjunto del reporte siempre es opcional y la nota de cierre siempre es obligatoria.') }}</li>
</ul>

<h3>{{ __('Usuarios') }}</h3>
<p>{{ __('En Ajustes → Usuarios se crean los accesos al sistema con su rol, cargo, clientes y matriz de permisos.') }}</p>
