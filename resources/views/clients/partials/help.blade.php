<h3>{{ __('¿Para qué sirve?') }}</h3>
<p>{{ __('Aquí se registran los clientes a los que su empresa presta servicio. Cada cliente tiene instalaciones; cada instalación tiene puestos; y los trabajadores del cliente se asignan a esos puestos.') }}</p>
<p>{{ __('Su propia empresa aparece como un cliente más, marcado "Mi empresa". Sus datos se editan en Mi empresa.') }}</p>

<h3>{{ __('Datos básicos') }}</h3>
<dl>
    <div>
        <dt>{{ __('Tipo de cliente') }}</dt>
        <dd>{{ __('Persona jurídica (empresa) o persona natural.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Nombre comercial y razón social') }}</dt>
        <dd>{{ __('El nombre comercial es el que se ve en listados, mapa y reportes. La razón social es el nombre legal del RUT; si se deja vacía se usa el nombre comercial.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Tipo y número de documento') }}</dt>
        <dd>{{ __('NIT para empresas o el documento de la persona natural. Usa los mismos códigos que Zentra (NIT, CC, CE, PA, PPT).') }}</dd>
    </div>
    <div>
        <dt>{{ __('Correo, teléfono, contacto y representante') }}</dt>
        <dd>{{ __('Datos de contacto del cliente y de su representante legal. Son opcionales.') }}</dd>
    </div>
</dl>

<h3>{{ __('Ubicación') }}</h3>
<ul>
    <li>{{ __('Departamento y municipio son obligatorios.') }}</li>
    <li>{{ __('Use el ícono de mapa junto a la dirección para marcar el punto exacto, o escriba la dirección y el sistema calcula la ubicación.') }}</li>
    <li>{{ __('Con esta ubicación se crea la instalación principal del cliente, y se actualiza cuando cambia.') }}</li>
</ul>

<h3>{{ __('Instalaciones') }}</h3>
<ul>
    <li>{{ __('Al guardar el cliente se crea su instalación principal con el mismo nombre y ubicación.') }}</li>
    <li>{{ __('Si el cliente tiene otras sedes, use "Nueva instalación". Puede usar la misma ubicación del cliente o marcar otra en el mapa.') }}</li>
    <li>{{ __('Desde cada instalación puede agregar sus puestos. Los puestos toman la ubicación de su instalación.') }}</li>
</ul>

<h3>{{ __('Carga masiva') }}</h3>
<p>{{ __('En Cargas masivas puede subir el Excel de clientes descargado desde Zentra, sin modificarlo.') }}</p>
