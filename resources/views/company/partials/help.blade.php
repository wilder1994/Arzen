<h3>{{ __('¿Para qué sirve esta vista?') }}</h3>
<p>{{ __('Aquí se registran los datos de su empresa. El sistema los usa para generar las cartas de revalidación de permisos, mostrar su logo y numerar las armas nuevas.') }}</p>
<p>{{ __('Nada se aplica hasta que haga clic en') }} <strong>{{ __('Guardar cambios') }}</strong>.</p>

<h3>{{ __('Empresa') }}</h3>
<dl>
    <div>
        <dt>{{ __('Razón social y NIT') }}</dt>
        <dd>{{ __('Nombre legal y NIT tal como aparecen en el RUT. Salen en el texto de la carta de revalidación. Son obligatorios para generar cartas.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Teléfono, correo y sitio web') }}</dt>
        <dd>{{ __('Datos de contacto de la empresa. Son opcionales y quedan guardados como referencia.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Prefijo de códigos internos') }}</dt>
        <dd>{{ __('Cada arma nueva recibe un código interno con este prefijo y un consecutivo, por ejemplo ARM-0001. Admite hasta 10 caracteres: letras, números, guion, guion bajo y punto. Cambiarlo no modifica los códigos que ya tienen las armas.') }}</dd>
    </div>
</dl>

<h3>{{ __('Ubicación') }}</h3>
<ul>
    <li>{{ __('Es la sede principal de la empresa. Su empresa también funciona como un cliente más del sistema: con esta ubicación se crea su instalación principal, donde quedan su armerillo, sus puestos y sus trabajadores.') }}</li>
    <li>{{ __('Haga clic en el ícono de mapa junto a la dirección para marcar el punto exacto, o escriba la dirección, el departamento y el municipio y el sistema calcula la ubicación.') }}</li>
    <li>{{ __('Debajo de los campos se ven las coordenadas guardadas.') }}</li>
</ul>
<dl class="mt-2">
    <div>
        <dt>{{ __('Ciudad para la carta') }}</dt>
        <dd>{{ __('Se llena sola con el municipio elegido y se puede editar, por ejemplo para escribir "Santiago de Cali". Sale en la fecha de las cartas de revalidación y es obligatoria para generarlas.') }}</dd>
    </div>
</dl>

<h3>{{ __('Representante legal') }}</h3>
<p>{{ __('Nombre completo, cédula y ciudad de expedición de quien firma la carta de revalidación. Los tres datos son obligatorios para generar cartas.') }}</p>

<h3>{{ __('Persona autorizada para trámites') }}</h3>
<p>{{ __('Es opcional. Complétela solo si los trámites ante la autoridad los hace otra persona: la carta la autoriza a ella. Si escribe el nombre, también debe escribir la cédula y la ciudad de expedición. Si deja la sección vacía, la carta autoriza al representante legal.') }}</p>

<h3>{{ __('Logo') }}</h3>
<ul>
    <li>{{ __('Formato PNG, JPG o WEBP, de hasta 2 MB.') }}</li>
    <li>{{ __('Se muestra en el menú lateral, en el inicio de sesión y en la revista mensual.') }}</li>
    <li>{{ __('Para cargarlo, haga clic en el recuadro, arrastre la imagen sobre él, o copie la imagen y péguela con Ctrl+V con el cursor sobre el recuadro.') }}</li>
    <li>{{ __('Marque "Quitar logo" y guarde para volver al logo predeterminado de Arzen.') }}</li>
</ul>

<h3>{{ __('Membrete (Word)') }}</h3>
<ul>
    <li>{{ __('Archivo de Word (.docx) de hasta 10 MB.') }}</li>
    <li>{{ __('El membrete debe ir como imagen dentro del encabezado del documento (y del pie, si lo tiene). En Word: Insertar > Encabezado > Editar encabezado, y allí inserte la imagen.') }}</li>
    <li>{{ __('Se aplica a todas las páginas de las cartas de revalidación.') }}</li>
    <li>{{ __('Después de guardar, el recuadro muestra el encabezado y el pie que el sistema encontró en el Word.') }}</li>
    <li>{{ __('Se carga igual que el logo: clic, arrastrar o pegar sobre el recuadro.') }}</li>
</ul>

<h3>{{ __('Problemas frecuentes') }}</h3>
<dl>
    <div>
        <dt>{{ __('Aparece un aviso amarillo con datos faltantes') }}</dt>
        <dd>{{ __('Las cartas de revalidación no se pueden generar hasta completar los datos que lista el aviso. Llénelos y guarde.') }}</dd>
    </div>
    <div>
        <dt>{{ __('"El Word no tiene una imagen en el encabezado"') }}</dt>
        <dd>{{ __('El membrete está en el cuerpo del documento o como texto. Muévalo al encabezado como imagen y vuelva a cargarlo.') }}</dd>
    </div>
    <div>
        <dt>{{ __('El prefijo no se guarda') }}</dt>
        <dd>{{ __('Revise que no tenga espacios ni caracteres especiales y que no pase de 10 caracteres.') }}</dd>
    </div>
</dl>
