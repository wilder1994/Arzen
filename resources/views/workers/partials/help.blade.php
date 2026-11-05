<h3>{{ __('¿Para qué sirve?') }}</h3>
<p>{{ __('Aquí se registra la ficha del personal: identificación, contacto, cargo, vinculación y seguridad social.') }}</p>

<h3>{{ __('Identificación') }}</h3>
<dl>
    <div>
        <dt>{{ __('Tipo y número de documento') }}</dt>
        <dd>{{ __('Cédula, cédula de extranjería, pasaporte o PPT. Con el número se reconoce al trabajador en las cargas masivas.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Lugar de nacimiento y de expedición') }}</dt>
        <dd>{{ __('Primero el departamento y después la ciudad de ese departamento.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Nacionalidad') }}</dt>
        <dd>{{ __('Queda en Colombiana. Puede cambiarla si el trabajador es de otro país.') }}</dd>
    </div>
</dl>

<h3>{{ __('Vinculación') }}</h3>
<dl>
    <div>
        <dt>{{ __('Cargo') }}</dt>
        <dd>{{ __('Se elige del catálogo de Ajustes → Cargos, que es el mismo para usuarios y trabajadores.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Tipo de vinculación y tipo de contrato') }}</dt>
        <dd>{{ __('Cada empresa define los suyos en Ajustes. Si una carga trae uno que no existe, el sistema lo crea.') }}</dd>
    </div>
</dl>

<h3>{{ __('Armas') }}</h3>
<p>{{ __('Un arma se puede asignar directamente al trabajador (por ejemplo, a un escolta) desde la ficha del arma, en Asignación interna.') }}</p>
