<h3>{{ __('¿Qué es un puesto?') }}</h3>
<p>{{ __('Es un punto de servicio dentro de una instalación del cliente: una portería, una garita, una ruta de escolta. A un puesto se le asignan armas y trabajadores.') }}</p>

<h3>{{ __('Campos') }}</h3>
<dl>
    <div>
        <dt>{{ __('Cliente e instalación') }}</dt>
        <dd>{{ __('Elija la instalación donde queda el puesto. El puesto toma su ubicación en el mapa, así que no hay que marcarla de nuevo.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Nombre del puesto') }}</dt>
        <dd>{{ __('No se puede repetir dentro de la misma instalación.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Nota del cambio') }}</dt>
        <dd>{{ __('Al editar, es obligatorio explicar el cambio. Queda en el historial del puesto.') }}</dd>
    </div>
</dl>

<h3>{{ __('Archivar') }}</h3>
<p>{{ __('Al archivar un puesto, las armas que tenía asignadas quedan sin asignación interna activa.') }}</p>
