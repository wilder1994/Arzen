<h3>{{ __('¿Para qué sirve?') }}</h3>
<p>{{ __('Aquí se crean las personas que ingresan al sistema y se define qué pueden ver y hacer. La foto, el documento y el correo corporativo identifican a quien maneja armas. La cartera se despliega para marcar los clientes, y el buscador filtra esa lista.') }}</p>

<h3>{{ __('Rol') }}</h3>
<dl>
    <div>
        <dt>{{ __('Súper administrador') }}</dt>
        <dd>{{ __('Acceso total a todos los módulos y clientes. Es el único que asigna armas a clientes y crea otros súper administradores.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Responsable') }}</dt>
        <dd>{{ __('Opera las armas, puestos y trabajadores de los clientes de su cartera: asignación interna, custodia y transferencias.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Auditor') }}</dt>
        <dd>{{ __('Consulta sin modificar: inventario, reportes y alertas.') }}</dd>
    </div>
    <div>
        <dt>{{ __('Almacén (Chalecos)') }}</dt>
        <dd>{{ __('Solo trabaja con el módulo de Dotación (chalecos) y sus cargas masivas.') }}</dd>
    </div>
</dl>

<h3>{{ __('Cargo') }}</h3>
<p>{{ __('Cargo del usuario dentro de su empresa. El catálogo se administra en Ajustes → Cargos.') }}</p>

<h3>{{ __('Clientes (cartera)') }}</h3>
<p>{{ __('Marque los clientes con los que trabaja el usuario. Un responsable solo ve y opera las armas, puestos y trabajadores de esos clientes. La asignación interna (a puesto o trabajador) solo la hacen usuarios con ese cliente en su cartera, además del súper administrador.') }}</p>

<h3>{{ __('Permisos por módulo') }}</h3>
<ul>
    <li><strong>{{ __('Nada') }}</strong>: {{ __('el módulo no aparece en el menú y no se puede abrir.') }}</li>
    <li><strong>{{ __('Ver') }}</strong>: {{ __('consulta sin crear, editar ni eliminar.') }}</li>
    <li><strong>{{ __('Gestionar') }}</strong>: {{ __('puede crear, editar y eliminar dentro de lo que su rol y su cartera le permiten.') }}</li>
    <li>{{ __('Al elegir el rol se cargan sus permisos por defecto; después puede ajustarlos uno por uno. "Restablecer según el rol" los vuelve a cargar.') }}</li>
</ul>

<h3>{{ __('Contraseña') }}</h3>
<p>{{ __('Al crear el usuario el sistema genera una contraseña y la muestra una sola vez. En plataforma, el primer ingreso obliga a cambiarla. Un usuario de campo no entra a la plataforma: usa esa contraseña en Revista armas, dentro de los días del mes indicados en su ficha.') }}</p>
