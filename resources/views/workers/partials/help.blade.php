<h3>{{ __('¿Para qué sirve?') }}</h3>
<p>{{ __('Aquí se registra el personal de cada cliente que recibe armas o dotación: escoltas, guardas, mensajeros, recaudadores, supervisores.') }}</p>

<h3>{{ __('Identificación') }}</h3>
<dl>
    <div>
        <dt>{{ __('Tipo y número de documento') }}</dt>
        <dd>{{ __('Cédula, cédula de extranjería, pasaporte o PPT. Con el número se reconoce al trabajador en las cargas masivas.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Cargo') }}</dt>
        <dd>{{ __('Se elige del catálogo de Ajustes → Cargos, que es el mismo para usuarios y trabajadores.') }}</dd>
    </div>
</dl>

<h3>{{ __('Asignación') }}</h3>
<dl>
    <div>
        <dt>{{ __('Cliente') }}</dt>
        <dd>{{ __('Cliente para el que trabaja. Su propia empresa aparece como "Mi empresa".') }}</dd>
    </div>
    <div>
        <dt>{{ __('Puesto') }}</dt>
        <dd>{{ __('Opcional. Solo muestra los puestos del cliente elegido. El trabajador queda en un solo puesto a la vez, y cada cambio de puesto queda en su historial.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Responsable') }}</dt>
        <dd>{{ __('Usuario de su empresa que responde por el trabajador.') }}</dd>
    </div>
</dl>

<h3>{{ __('Armas') }}</h3>
<p>{{ __('Un arma se puede asignar directamente al trabajador (por ejemplo, a un escolta) desde la ficha del arma, en Asignación interna.') }}</p>
