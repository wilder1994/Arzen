<h3>{{ __('¿Qué es una instalación?') }}</h3>
<p>{{ __('Es una sede del cliente. En el listado, Editar abre esa sede: el mapa, sus datos, sus puestos y las personas y armas de cada puesto.') }}</p>

<h3>{{ __('Puestos y trabajadores') }}</h3>
<p>{{ __('Arriba se lee la sede. Editar instalación abre el formulario del nombre y la ubicación. Si la sede es la del mismo cliente, el sistema avisa y lleva a la ficha del cliente; al guardar, regresa aquí. Agregar puesto pide solo el nombre. Cada puesto tiene personas y armas. El historial guarda la nota del cambio y los movimientos de los puestos: alta, nombre, personas, armas, archivo y reactivación.') }}</p>

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
<p>{{ __('Una instalación solo se archiva cuando no tiene puestos activos. La fila queda en el listado, marcada como archivada, y el botón pasa a Reactivar. La instalación principal no se archiva. Un puesto archivado sigue en la ficha: las personas y las armas quedan sin puesto, y el botón pasa a Reactivar.') }}</p>
