<h3>{{ __('¿Qué es una instalación?') }}</h3>
<p>{{ __('Es una sede del cliente: un edificio, una planta, una oficina o un conjunto. Cada instalación agrupa sus puestos de servicio, y los puestos toman su ubicación.') }}</p>

<h3>{{ __('Instalación principal') }}</h3>
<p>{{ __('Se crea sola al registrar el cliente, con su mismo nombre y ubicación. Si el cliente tiene una sola sede, no hace falta crear más.') }}</p>

<h3>{{ __('Campos') }}</h3>
<dl>
    <div>
        <dt>{{ __('Nombre') }}</dt>
        <dd>{{ __('Cómo se reconoce la sede, por ejemplo "Planta Yumbo" o "Sede norte".') }}</dd>
    </div>
    <div>
        <dt>{{ __('Código y notas') }}</dt>
        <dd>{{ __('Opcionales, para identificar la sede o dejar indicaciones.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Es la misma ubicación del cliente') }}</dt>
        <dd>{{ __('Márquelo si la sede queda en la misma dirección del cliente. Si no, marque la ubicación en el mapa o escriba la dirección, el departamento y el municipio.') }}</dd>
    </div>
</dl>

<h3>{{ __('Archivar') }}</h3>
<p>{{ __('Una instalación solo se puede archivar cuando no tiene puestos activos. La instalación principal no se archiva.') }}</p>
