<?php
$pageTitle = 'Gestión de usuarios | CapacitaLíder';
$sectionTitle = 'Gestión de usuarios';
$activePage = 'usuarios';
$basePath = '../';
$pageVendorStyles = ['DataTables/datatables.min.css'];
$pageVendorScripts = ['DataTables/datatables.min.js'];
$pageStyle = 'css/gestionUsuarios.css';
$pageScript = 'js/gestionUsuarios.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="user-management-page">
            <section class="user-management-hero" aria-labelledby="user-management-title">
                <div class="user-hero-copy">
                    <span class="user-hero-eyebrow"><i class="fa-solid fa-users-gear" aria-hidden="true"></i> Administración de personal</span>
                    <h2 id="user-management-title">Gestión de usuarios</h2>
                    <p>Consulta y administra visualmente las cuentas, roles y datos laborales del personal que utiliza la plataforma.</p>
                    <div class="user-hero-actions">
                        <button id="add-user-button" class="button user-primary-button" type="button"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Agregar usuario</button>
                        <span class="user-demo-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Gestión simulada sin cambios permanentes</span>
                    </div>
                </div>

                <div class="user-hero-summary" aria-label="Resumen de usuarios">
                    <div class="user-hero-visual" aria-hidden="true">
                        <span class="user-visual-card visual-card-back"></span>
                        <span class="user-visual-card visual-card-front"><i class="fa-solid fa-id-card"></i></span>
                        <span class="user-visual-check"><i class="fa-solid fa-user-check"></i></span>
                    </div>
                    <div class="user-summary-chips">
                        <span><strong id="total-users-count">8</strong> usuarios</span>
                        <span><strong id="active-users-count">7</strong> activos</span>
                        <span><strong>4</strong> roles</span>
                    </div>
                </div>
            </section>

            <section class="user-filters-card" aria-labelledby="user-filters-title">
                <div class="user-section-heading compact-heading">
                    <span class="user-heading-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                    <div><h2 id="user-filters-title">Buscar usuarios</h2><p>Usa uno o varios filtros para localizar rápidamente una cuenta.</p></div>
                </div>

                <form id="user-filter-form" class="user-filter-form" action="#" method="get">
                    <div class="user-filter-field user-search-field">
                        <label for="user-search">Nombre o número de empleado</label>
                        <div class="user-filter-control"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="user-search" class="form-control" type="search" placeholder="Ej. Ana Martínez o EMP014"></div>
                    </div>
                    <div class="user-filter-field">
                        <label for="user-role-filter">Rol</label>
                        <div class="user-filter-control"><i class="fa-solid fa-user-shield" aria-hidden="true"></i><select id="user-role-filter" class="form-select"><option value="">Todos</option><option value="lider-operador">Líder/operador</option><option value="capacitador">Capacitador</option><option value="supervisor">Supervisor</option><option value="administrador">Administrador</option></select></div>
                    </div>
                    <div class="user-filter-field">
                        <label for="user-position-filter">Puesto</label>
                        <div class="user-filter-control"><i class="fa-solid fa-briefcase" aria-hidden="true"></i><select id="user-position-filter" class="form-select"><option value="">Todos</option><option value="operador-produccion">Operador de producción</option><option value="operador-ensamble">Operador de ensamble</option><option value="lider-celula">Líder de célula</option><option value="tecnico-mantenimiento">Técnico de mantenimiento</option><option value="inspector-calidad">Inspector de calidad</option><option value="auxiliar-logistica">Auxiliar de logística</option><option value="supervisor-produccion">Supervisor de producción</option><option value="administrador-plataforma">Administrador de plataforma</option></select></div>
                    </div>
                    <div class="user-filter-field">
                        <label for="user-area-filter">Área</label>
                        <div class="user-filter-control"><i class="fa-solid fa-building" aria-hidden="true"></i><select id="user-area-filter" class="form-select"><option value="">Todas</option><option value="produccion">Producción</option><option value="ensamble">Ensamble</option><option value="mantenimiento">Mantenimiento</option><option value="calidad">Calidad</option><option value="logistica">Logística</option><option value="capacitacion">Capacitación</option><option value="administracion">Administración</option></select></div>
                    </div>
                    <div class="user-filter-field">
                        <label for="user-status-filter">Estatus</label>
                        <div class="user-filter-control"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><select id="user-status-filter" class="form-select"><option value="">Todos</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                    </div>
                    <div class="user-filter-actions">
                        <button id="search-users-button" class="button user-search-button" type="button"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Buscar</button>
                        <button class="button user-clear-button" type="reset"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Limpiar</button>
                    </div>
                </form>
            </section>

            <section class="registered-users-section" aria-labelledby="registered-users-title">
                <div class="user-section-heading user-list-heading">
                    <div>
                        <span class="user-section-label">Directorio administrativo</span>
                        <h2 id="registered-users-title">Usuarios registrados</h2>
                        <p>Revisa los datos asignados y selecciona la acción que deseas realizar.</p>
                    </div>
                    <span id="visible-users-count" class="visible-user-count"><i class="fa-solid fa-users" aria-hidden="true"></i> 8 usuarios</span>
                </div>

                <div class="user-table-panel">
                    <table id="users-management-table" class="table align-middle users-management-table">
                        <caption class="visually-hidden">Listado administrativo de usuarios registrados</caption>
                        <thead>
                            <tr>
                                <th scope="col">Foto</th>
                                <th scope="col">No. empleado</th>
                                <th scope="col">Nombre completo</th>
                                <th scope="col">Usuario o correo</th>
                                <th scope="col">Rol</th>
                                <th scope="col">Puesto</th>
                                <th scope="col">Área</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Último acceso</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="user-record" data-user-employee="EMP001" data-user-name="Carlos Mendoza Ruiz" data-user-email="carlos.mendoza@empresa.com" data-user-role="lider-operador" data-user-role-label="Líder/operador" data-user-position="lider-celula" data-user-position-label="Líder de célula" data-user-area="produccion" data-user-area-label="Producción" data-user-supervisor="Roberto Cruz" data-user-shift="Matutino" data-user-status="activo" data-user-last-access="Hoy, 08:42">
                                <td data-label="Foto"><span class="user-avatar avatar-blue" aria-hidden="true">CM</span></td>
                                <td data-label="No. empleado"><strong class="employee-number">EMP001</strong></td>
                                <td data-label="Nombre"><strong class="table-user-name">Carlos Mendoza Ruiz</strong></td>
                                <td data-label="Usuario o correo"><span class="user-email">carlos.mendoza@empresa.com</span></td>
                                <td data-label="Rol"><span class="user-role role-operator"><i class="fa-solid fa-user" aria-hidden="true"></i> Líder/operador</span></td>
                                <td data-label="Puesto">Líder de célula</td>
                                <td data-label="Área"><span class="user-area area-production">Producción</span></td>
                                <td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td>
                                <td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> Hoy, 08:42</span></td>
                                <td data-label="Acciones"><div class="user-table-actions">
                                    <button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Carlos Mendoza Ruiz"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button>
                                    <button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Carlos Mendoza Ruiz"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                    <button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Carlos Mendoza Ruiz"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button>
                                    <button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Carlos Mendoza Ruiz"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button>
                                    <button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Carlos Mendoza Ruiz"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                </div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="EMP014" data-user-name="Ana Martínez López" data-user-email="ana.martinez@empresa.com" data-user-role="lider-operador" data-user-role-label="Líder/operador" data-user-position="operador-produccion" data-user-position-label="Operador de producción" data-user-area="produccion" data-user-area-label="Producción" data-user-supervisor="Roberto Cruz" data-user-shift="Vespertino" data-user-status="activo" data-user-last-access="Ayer, 16:18">
                                <td data-label="Foto"><span class="user-avatar avatar-cyan" aria-hidden="true">AM</span></td><td data-label="No. empleado"><strong class="employee-number">EMP014</strong></td><td data-label="Nombre"><strong class="table-user-name">Ana Martínez López</strong></td><td data-label="Usuario o correo"><span class="user-email">ana.martinez@empresa.com</span></td><td data-label="Rol"><span class="user-role role-operator"><i class="fa-solid fa-user" aria-hidden="true"></i> Líder/operador</span></td><td data-label="Puesto">Operador de producción</td><td data-label="Área"><span class="user-area area-production">Producción</span></td><td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> Ayer, 16:18</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Ana Martínez López"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Ana Martínez López"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Ana Martínez López"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Ana Martínez López"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Ana Martínez López"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="EMP027" data-user-name="Luis Hernández Vega" data-user-email="luis.hernandez@empresa.com" data-user-role="capacitador" data-user-role-label="Capacitador" data-user-position="tecnico-mantenimiento" data-user-position-label="Técnico de mantenimiento" data-user-area="mantenimiento" data-user-area-label="Mantenimiento" data-user-supervisor="Laura Gómez" data-user-shift="Mixto" data-user-status="activo" data-user-last-access="12 jul, 10:05">
                                <td data-label="Foto"><span class="user-avatar avatar-purple" aria-hidden="true">LH</span></td><td data-label="No. empleado"><strong class="employee-number">EMP027</strong></td><td data-label="Nombre"><strong class="table-user-name">Luis Hernández Vega</strong></td><td data-label="Usuario o correo"><span class="user-email">luis.hernandez@empresa.com</span></td><td data-label="Rol"><span class="user-role role-trainer"><i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i> Capacitador</span></td><td data-label="Puesto">Técnico de mantenimiento</td><td data-label="Área"><span class="user-area area-maintenance">Mantenimiento</span></td><td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> 12 jul, 10:05</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Luis Hernández Vega"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Luis Hernández Vega"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Luis Hernández Vega"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Luis Hernández Vega"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Luis Hernández Vega"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="EMP039" data-user-name="Fernanda Ruiz Soto" data-user-email="fernanda.ruiz@empresa.com" data-user-role="supervisor" data-user-role-label="Supervisor" data-user-position="inspector-calidad" data-user-position-label="Inspector de calidad" data-user-area="calidad" data-user-area-label="Calidad" data-user-supervisor="Marta Silva" data-user-shift="Matutino" data-user-status="activo" data-user-last-access="11 jul, 14:32">
                                <td data-label="Foto"><span class="user-avatar avatar-orange" aria-hidden="true">FR</span></td><td data-label="No. empleado"><strong class="employee-number">EMP039</strong></td><td data-label="Nombre"><strong class="table-user-name">Fernanda Ruiz Soto</strong></td><td data-label="Usuario o correo"><span class="user-email">fernanda.ruiz@empresa.com</span></td><td data-label="Rol"><span class="user-role role-supervisor"><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor</span></td><td data-label="Puesto">Inspector de calidad</td><td data-label="Área"><span class="user-area area-quality">Calidad</span></td><td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> 11 jul, 14:32</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Fernanda Ruiz Soto"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Fernanda Ruiz Soto"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Fernanda Ruiz Soto"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Fernanda Ruiz Soto"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Fernanda Ruiz Soto"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="EMP052" data-user-name="Jorge Castillo Mora" data-user-email="jorge.castillo@empresa.com" data-user-role="lider-operador" data-user-role-label="Líder/operador" data-user-position="operador-ensamble" data-user-position-label="Operador de ensamble" data-user-area="ensamble" data-user-area-label="Ensamble" data-user-supervisor="Roberto Cruz" data-user-shift="Nocturno" data-user-status="inactivo" data-user-last-access="28 jun, 21:10">
                                <td data-label="Foto"><span class="user-avatar avatar-pink" aria-hidden="true">JC</span></td><td data-label="No. empleado"><strong class="employee-number">EMP052</strong></td><td data-label="Nombre"><strong class="table-user-name">Jorge Castillo Mora</strong></td><td data-label="Usuario o correo"><span class="user-email">jorge.castillo@empresa.com</span></td><td data-label="Rol"><span class="user-role role-operator"><i class="fa-solid fa-user" aria-hidden="true"></i> Líder/operador</span></td><td data-label="Puesto">Operador de ensamble</td><td data-label="Área"><span class="user-area area-assembly">Ensamble</span></td><td data-label="Estatus"><span class="user-status status-inactive"><i class="fa-solid fa-circle-minus" aria-hidden="true"></i> Inactivo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> 28 jun, 21:10</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Jorge Castillo Mora"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Jorge Castillo Mora"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Jorge Castillo Mora"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Activar usuario" aria-label="Activar Jorge Castillo Mora"><i class="fa-solid fa-user-check" aria-hidden="true"></i><span>Activar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Jorge Castillo Mora"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="EMP061" data-user-name="Daniela Flores Reyes" data-user-email="daniela.flores@empresa.com" data-user-role="capacitador" data-user-role-label="Capacitador" data-user-position="auxiliar-logistica" data-user-position-label="Auxiliar de logística" data-user-area="logistica" data-user-area-label="Logística" data-user-supervisor="Andrés Navarro" data-user-shift="Matutino" data-user-status="activo" data-user-last-access="Hoy, 07:55">
                                <td data-label="Foto"><span class="user-avatar avatar-success" aria-hidden="true">DF</span></td><td data-label="No. empleado"><strong class="employee-number">EMP061</strong></td><td data-label="Nombre"><strong class="table-user-name">Daniela Flores Reyes</strong></td><td data-label="Usuario o correo"><span class="user-email">daniela.flores@empresa.com</span></td><td data-label="Rol"><span class="user-role role-trainer"><i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i> Capacitador</span></td><td data-label="Puesto">Auxiliar de logística</td><td data-label="Área"><span class="user-area area-logistics">Logística</span></td><td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> Hoy, 07:55</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Daniela Flores Reyes"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Daniela Flores Reyes"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Daniela Flores Reyes"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Daniela Flores Reyes"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Daniela Flores Reyes"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="EMP008" data-user-name="Roberto Cruz Salgado" data-user-email="roberto.cruz@empresa.com" data-user-role="supervisor" data-user-role-label="Supervisor" data-user-position="supervisor-produccion" data-user-position-label="Supervisor de producción" data-user-area="produccion" data-user-area-label="Producción" data-user-supervisor="Marta Silva" data-user-shift="Mixto" data-user-status="activo" data-user-last-access="Hoy, 09:12">
                                <td data-label="Foto"><span class="user-avatar avatar-gold" aria-hidden="true">RC</span></td><td data-label="No. empleado"><strong class="employee-number">EMP008</strong></td><td data-label="Nombre"><strong class="table-user-name">Roberto Cruz Salgado</strong></td><td data-label="Usuario o correo"><span class="user-email">roberto.cruz@empresa.com</span></td><td data-label="Rol"><span class="user-role role-supervisor"><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor</span></td><td data-label="Puesto">Supervisor de producción</td><td data-label="Área"><span class="user-area area-production">Producción</span></td><td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> Hoy, 09:12</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Roberto Cruz Salgado"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Roberto Cruz Salgado"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Roberto Cruz Salgado"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Roberto Cruz Salgado"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Roberto Cruz Salgado"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>

                            <tr class="user-record" data-user-employee="ADM001" data-user-name="Diana Flores Ortega" data-user-email="diana.flores@empresa.com" data-user-role="administrador" data-user-role-label="Administrador" data-user-position="administrador-plataforma" data-user-position-label="Administrador de plataforma" data-user-area="administracion" data-user-area-label="Administración" data-user-supervisor="Dirección general" data-user-shift="Matutino" data-user-status="activo" data-user-last-access="Hoy, 08:05">
                                <td data-label="Foto"><span class="user-avatar avatar-dark" aria-hidden="true">DF</span></td><td data-label="No. empleado"><strong class="employee-number">ADM001</strong></td><td data-label="Nombre"><strong class="table-user-name">Diana Flores Ortega</strong></td><td data-label="Usuario o correo"><span class="user-email">diana.flores@empresa.com</span></td><td data-label="Rol"><span class="user-role role-admin"><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Administrador</span></td><td data-label="Puesto">Administrador de plataforma</td><td data-label="Área"><span class="user-area area-administration">Administración</span></td><td data-label="Estatus"><span class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td><td data-label="Último acceso"><span class="last-access"><i class="fa-regular fa-clock" aria-hidden="true"></i> Hoy, 08:05</span></td>
                                <td data-label="Acciones"><div class="user-table-actions"><button class="user-action user-detail-action" type="button" title="Ver detalle" aria-label="Ver detalle de Diana Flores Ortega"><i class="fa-regular fa-eye" aria-hidden="true"></i><span>Detalle</span></button><button class="user-action user-edit-action" type="button" title="Editar usuario" aria-label="Editar Diana Flores Ortega"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button><button class="user-action user-password-action" type="button" title="Restablecer contraseña" aria-label="Restablecer contraseña de Diana Flores Ortega"><i class="fa-solid fa-key" aria-hidden="true"></i><span>Contraseña</span></button><button class="user-action user-toggle-action" type="button" title="Desactivar usuario" aria-label="Desactivar Diana Flores Ortega"><i class="fa-solid fa-user-slash" aria-hidden="true"></i><span>Desactivar</span></button><button class="user-action user-delete-action" type="button" title="Eliminar usuario" aria-label="Eliminar Diana Flores Ortega"><i class="fa-regular fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="modal fade user-form-modal" id="user-form-modal" tabindex="-1" aria-labelledby="user-form-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="user-form" novalidate>
                        <div class="modal-header">
                            <div class="user-modal-heading"><span><i class="fa-solid fa-user-plus" aria-hidden="true"></i></span><div><small>Información de la cuenta</small><h2 id="user-form-modal-title" class="modal-title">Agregar nuevo usuario</h2></div></div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="user-modal-introduction"><span><i class="fa-solid fa-id-card" aria-hidden="true"></i></span><div><strong>Datos generales y laborales</strong><p>Completa los campos visibles. La información no se almacenará en esta maqueta.</p></div></div>
                            <div class="user-form-grid">
                                <div class="user-form-field"><label for="user-employee-input">Número de empleado <span>*</span></label><input id="user-employee-input" class="form-control" type="text" placeholder="Ej. EMP075" required><div class="invalid-feedback">Ingresa el número de empleado.</div></div>
                                <div class="user-form-field user-form-field-wide"><label for="user-name-input">Nombre completo <span>*</span></label><input id="user-name-input" class="form-control" type="text" placeholder="Nombre y apellidos" required><div class="invalid-feedback">Ingresa el nombre completo.</div></div>
                                <div class="user-form-field user-form-field-wide"><label for="user-email-input">Usuario o correo <span>*</span></label><input id="user-email-input" class="form-control" type="email" placeholder="usuario@empresa.com" required><div class="invalid-feedback">Ingresa un correo válido.</div></div>
                                <div class="user-form-field"><label for="user-role-input">Rol <span>*</span></label><select id="user-role-input" class="form-select" required><option value="">Selecciona un rol</option><option value="lider-operador">Líder/operador</option><option value="capacitador">Capacitador</option><option value="supervisor">Supervisor</option><option value="administrador">Administrador</option></select><div class="invalid-feedback">Selecciona un rol.</div></div>
                                <div class="user-form-field"><label for="user-position-input">Puesto <span>*</span></label><select id="user-position-input" class="form-select" required><option value="">Selecciona un puesto</option><option value="operador-produccion">Operador de producción</option><option value="operador-ensamble">Operador de ensamble</option><option value="lider-celula">Líder de célula</option><option value="tecnico-mantenimiento">Técnico de mantenimiento</option><option value="inspector-calidad">Inspector de calidad</option><option value="auxiliar-logistica">Auxiliar de logística</option><option value="supervisor-produccion">Supervisor de producción</option><option value="administrador-plataforma">Administrador de plataforma</option></select><div class="invalid-feedback">Selecciona un puesto.</div></div>
                                <div class="user-form-field"><label for="user-area-input">Área <span>*</span></label><select id="user-area-input" class="form-select" required><option value="">Selecciona un área</option><option value="produccion">Producción</option><option value="ensamble">Ensamble</option><option value="mantenimiento">Mantenimiento</option><option value="calidad">Calidad</option><option value="logistica">Logística</option><option value="capacitacion">Capacitación</option><option value="administracion">Administración</option></select><div class="invalid-feedback">Selecciona un área.</div></div>
                                <div class="user-form-field"><label for="user-supervisor-input">Supervisor <small>(opcional)</small></label><select id="user-supervisor-input" class="form-select"><option value="">Sin asignar</option><option>Roberto Cruz</option><option>Laura Gómez</option><option>Marta Silva</option><option>Andrés Navarro</option><option>Dirección general</option></select></div>
                                <div class="user-form-field"><label for="user-shift-input">Turno <small>(opcional)</small></label><select id="user-shift-input" class="form-select"><option value="">Sin asignar</option><option>Matutino</option><option>Vespertino</option><option>Nocturno</option><option>Mixto</option></select></div>
                                <div class="user-form-field"><label for="user-status-input">Estatus</label><select id="user-status-input" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                            </div>

                            <fieldset id="temporary-password-fields" class="temporary-password-fields">
                                <legend><i class="fa-solid fa-key" aria-hidden="true"></i> Contraseña temporal</legend>
                                <p>Solo representa el flujo visual de creación de una cuenta.</p>
                                <div class="user-form-grid password-grid">
                                    <div class="user-form-field"><label for="user-password-input">Contraseña temporal <span>*</span></label><input id="user-password-input" class="form-control" type="password" autocomplete="new-password" required><div class="invalid-feedback">Ingresa una contraseña temporal.</div></div>
                                    <div class="user-form-field"><label for="user-password-confirm-input">Confirmar contraseña <span>*</span></label><input id="user-password-confirm-input" class="form-control" type="password" autocomplete="new-password" required><div class="invalid-feedback">Las contraseñas deben coincidir.</div></div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="modal-footer"><button class="button user-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button class="button user-modal-save" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i><span id="save-user-button-label">Guardar usuario</span></button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade user-detail-modal" id="user-detail-modal" tabindex="-1" aria-labelledby="user-detail-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
                <div class="modal-header"><div class="user-modal-heading"><span><i class="fa-regular fa-address-card" aria-hidden="true"></i></span><div><small>Información registrada</small><h2 id="user-detail-title" class="modal-title">Detalle del usuario</h2></div></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body">
                    <div class="user-detail-profile"><span id="detail-user-avatar" class="user-avatar avatar-blue" aria-hidden="true">CM</span><div><h3 id="detail-user-name">Carlos Mendoza Ruiz</h3><p><span id="detail-user-employee">EMP001</span> · <span id="detail-user-email">carlos.mendoza@empresa.com</span></p></div><span id="detail-user-status" class="user-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></div>
                    <dl class="user-detail-grid"><div><dt>Rol</dt><dd id="detail-user-role">Líder/operador</dd></div><div><dt>Puesto</dt><dd id="detail-user-position">Líder de célula</dd></div><div><dt>Área</dt><dd id="detail-user-area">Producción</dd></div><div><dt>Supervisor</dt><dd id="detail-user-supervisor">Roberto Cruz</dd></div><div><dt>Turno</dt><dd id="detail-user-shift">Matutino</dd></div><div><dt>Último acceso</dt><dd id="detail-user-last-access">Hoy, 08:42</dd></div></dl>
                </div>
                <div class="modal-footer"><button class="button user-modal-save" type="button" data-bs-dismiss="modal">Cerrar detalle</button></div>
            </div></div>
        </div>

        <div class="modal fade user-password-modal" id="user-password-modal" tabindex="-1" aria-labelledby="user-password-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form id="password-reset-form" novalidate>
                <div class="modal-header"><div class="user-modal-heading"><span><i class="fa-solid fa-key" aria-hidden="true"></i></span><div><small>Acceso temporal</small><h2 id="user-password-modal-title" class="modal-title">Restablecer contraseña</h2></div></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body"><p class="password-user-copy">Nueva contraseña temporal para <strong id="password-user-name">Usuario seleccionado</strong>.</p><div class="frontend-warning"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><p>Esta acción es solo una simulación en esta maqueta frontend.</p></div><div class="password-reset-fields"><div class="user-form-field"><label for="reset-password-input">Nueva contraseña temporal</label><input id="reset-password-input" class="form-control" type="password" autocomplete="new-password" required></div><div class="user-form-field"><label for="reset-password-confirm-input">Confirmar contraseña temporal</label><input id="reset-password-confirm-input" class="form-control" type="password" autocomplete="new-password" required><div class="invalid-feedback">Las contraseñas deben coincidir.</div></div></div></div>
                <div class="modal-footer"><button class="button user-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button class="button user-password-confirm" type="submit"><i class="fa-solid fa-key" aria-hidden="true"></i> Restablecer contraseña</button></div>
            </form></div></div>
        </div>

        <div class="modal fade user-delete-modal" id="user-delete-modal" tabindex="-1" aria-labelledby="user-delete-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body"><span class="user-delete-icon"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></span><h2 id="user-delete-title">¿Eliminar este usuario?</h2><p>¿Estás seguro de eliminar a <strong id="delete-user-name">este usuario</strong>? Esta acción es solo visual en esta maqueta.</p></div><div class="modal-footer"><button class="button user-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button id="confirm-user-delete" class="button user-delete-confirm" type="button"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar usuario</button></div></div></div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="user-feedback-toast" class="toast user-feedback-toast" role="status" aria-live="polite" aria-atomic="true"><div class="toast-body"><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span><p id="user-feedback-message">Acción realizada de forma simulada.</p><button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button></div></div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
