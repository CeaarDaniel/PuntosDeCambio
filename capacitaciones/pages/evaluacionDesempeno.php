<?php
$pageTitle = 'Evaluación de desempeño | CapacitaLíder';
$sectionTitle = 'Evaluación de desempeño';
$activePage = 'evaluacionDesempeno';
$basePath = '../';
$pageVendorStyles = ['DataTables/datatables.min.css'];
$pageVendorScripts = ['DataTables/datatables.min.js'];
$pageStyle = 'css/evaluacionDesempeno.css';
$pageScript = 'js/evaluacionDesempeno.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="performance-selection-page">
            <section class="performance-hero" aria-labelledby="performance-title">
                <div class="performance-hero-copy">
                    <span class="performance-eyebrow">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                        Seguimiento del personal
                    </span>
                    <h2 id="performance-title">Evaluación de desempeño</h2>
                    <p class="performance-subtitle">Selecciona un colaborador para iniciar su evaluación</p>
                    <p class="performance-description">
                        Consulta el personal disponible y elige a la persona cuyo desempeño deseas evaluar.
                        En la siguiente etapa podrás revisar su información y avance de capacitación.
                    </p>
                </div>

                <div class="performance-hero-visual" aria-hidden="true">
                    <span class="performance-visual-frame"></span>
                    <div class="performance-main-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div class="performance-chart-bars">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="performance-accent-icon"><i class="fa-solid fa-arrow-trend-up"></i></span>
                </div>
            </section>

            <section class="performance-filters" aria-labelledby="performance-filters-title">
                <div class="performance-filter-heading">
                    <span class="performance-filter-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="performance-filters-title">Buscar colaborador</h2>
                        <p>Usa los filtros para encontrar rápidamente a la persona que deseas evaluar.</p>
                    </div>
                </div>

                <form class="performance-filter-form" action="#" method="get">
                    <div class="performance-filter-field employee-search-field">
                        <label for="employee-search">Nombre o número de empleado</label>
                        <div class="performance-filter-control">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input
                                id="employee-search"
                                class="form-control"
                                type="search"
                                name="employeeSearch"
                                placeholder="Ej. Ana Martínez o EMP014"
                            >
                        </div>
                    </div>

                    <div class="performance-filter-field">
                        <label for="supervisor-filter">Supervisor</label>
                        <div class="performance-filter-control">
                            <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                            <select id="supervisor-filter" class="form-select" name="supervisor">
                                <option value="">Todos los supervisores</option>
                                <option value="roberto-cruz">Roberto Cruz</option>
                                <option value="laura-gomez">Laura Gómez</option>
                                <option value="marta-silva">Marta Silva</option>
                                <option value="andres-navarro">Andrés Navarro</option>
                            </select>
                        </div>
                    </div>

                    <div class="performance-filter-field">
                        <label for="area-filter">Área</label>
                        <div class="performance-filter-control">
                            <i class="fa-solid fa-building" aria-hidden="true"></i>
                            <select id="area-filter" class="form-select" name="area">
                                <option value="">Todas las áreas</option>
                                <option value="produccion">Producción</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="calidad">Calidad</option>
                                <option value="logistica">Logística</option>
                            </select>
                        </div>
                    </div>

                    <div class="performance-filter-actions">
                        <button class="button performance-search-button" type="button">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            Buscar
                        </button>
                        <button class="button performance-clear-button" type="reset">
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            Limpiar filtros
                        </button>
                    </div>
                </form>
            </section>

            <section class="employee-selection" aria-labelledby="employee-list-title">
                <div class="employee-section-heading">
                    <div>
                        <span class="employee-section-label">Personal disponible</span>
                        <h2 id="employee-list-title">Selecciona un colaborador</h2>
                        <p>Revisa la información básica antes de iniciar su evaluación de desempeño.</p>
                    </div>
                    <span class="employee-count"><i class="fa-solid fa-users" aria-hidden="true"></i> 6 colaboradores</span>
                </div>

                <div class="employee-table-panel">
                    <table id="employees-performance-table" class="table align-middle performance-employee-table">
                        <caption class="visually-hidden">Personal disponible para evaluación de desempeño</caption>
                        <thead>
                            <tr>
                                <th scope="col">Colaborador</th>
                                <th scope="col">No. empleado</th>
                                <th scope="col">Puesto</th>
                                <th scope="col">Área</th>
                                <th scope="col">Supervisor</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-order="Ana Martínez López">
                                    <div class="table-employee-profile">
                                        <span class="employee-avatar table-employee-avatar avatar-cyan" aria-hidden="true">AM</span>
                                        <div>
                                            <strong>Ana Martínez López</strong>
                                            <span class="table-employee-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Disponible</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-employee-number">EMP014</span></td>
                                <td>Operadora de producción</td>
                                <td><span class="table-area-badge area-production">Producción</span></td>
                                <td>Roberto Cruz</td>
                                <td>
                                    <a class="button table-employee-action" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Ana Martínez López">
                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                        Evaluar desempeño
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td data-order="Luis Hernández Vega">
                                    <div class="table-employee-profile">
                                        <span class="employee-avatar table-employee-avatar avatar-purple" aria-hidden="true">LH</span>
                                        <div>
                                            <strong>Luis Hernández Vega</strong>
                                            <span class="table-employee-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Disponible</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-employee-number">EMP027</span></td>
                                <td>Técnico de mantenimiento</td>
                                <td><span class="table-area-badge area-maintenance">Mantenimiento</span></td>
                                <td>Laura Gómez</td>
                                <td>
                                    <a class="button table-employee-action" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Luis Hernández Vega">
                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                        Evaluar desempeño
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td data-order="Fernanda Ruiz Soto">
                                    <div class="table-employee-profile">
                                        <span class="employee-avatar table-employee-avatar avatar-orange" aria-hidden="true">FR</span>
                                        <div>
                                            <strong>Fernanda Ruiz Soto</strong>
                                            <span class="table-employee-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Disponible</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-employee-number">EMP039</span></td>
                                <td>Inspectora de calidad</td>
                                <td><span class="table-area-badge area-quality">Calidad</span></td>
                                <td>Marta Silva</td>
                                <td>
                                    <a class="button table-employee-action" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Fernanda Ruiz Soto">
                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                        Evaluar desempeño
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td data-order="Jorge Castillo Mora">
                                    <div class="table-employee-profile">
                                        <span class="employee-avatar table-employee-avatar avatar-pink" aria-hidden="true">JC</span>
                                        <div>
                                            <strong>Jorge Castillo Mora</strong>
                                            <span class="table-employee-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Disponible</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-employee-number">EMP052</span></td>
                                <td>Operador de ensamble</td>
                                <td><span class="table-area-badge area-production">Producción</span></td>
                                <td>Roberto Cruz</td>
                                <td>
                                    <a class="button table-employee-action" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Jorge Castillo Mora">
                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                        Evaluar desempeño
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td data-order="Daniela Flores Reyes">
                                    <div class="table-employee-profile">
                                        <span class="employee-avatar table-employee-avatar avatar-success" aria-hidden="true">DF</span>
                                        <div>
                                            <strong>Daniela Flores Reyes</strong>
                                            <span class="table-employee-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Disponible</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-employee-number">EMP061</span></td>
                                <td>Auxiliar de logística</td>
                                <td><span class="table-area-badge area-logistics">Logística</span></td>
                                <td>Andrés Navarro</td>
                                <td>
                                    <a class="button table-employee-action" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Daniela Flores Reyes">
                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                        Evaluar desempeño
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td data-order="Miguel Torres Salas">
                                    <div class="table-employee-profile">
                                        <span class="employee-avatar table-employee-avatar avatar-gold" aria-hidden="true">MT</span>
                                        <div>
                                            <strong>Miguel Torres Salas</strong>
                                            <span class="table-employee-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Disponible</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="table-employee-number">EMP074</span></td>
                                <td>Líder de célula</td>
                                <td><span class="table-area-badge area-production">Producción</span></td>
                                <td>Roberto Cruz</td>
                                <td>
                                    <a class="button table-employee-action" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Miguel Torres Salas">
                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                        Evaluar desempeño
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="employee-grid">
                    <article class="employee-card employee-card-production">
                        <div class="employee-card-header">
                            <span class="employee-avatar avatar-cyan" role="img" aria-label="Avatar de Ana Martínez">AM</span>
                            <div class="employee-identity">
                                <span class="employee-number">EMP014</span>
                                <h3>Ana Martínez López</h3>
                                <span class="employee-position">Operadora de producción</span>
                            </div>
                            <span class="employee-available" title="Disponible para evaluación">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Disponible
                            </span>
                        </div>
                        <dl class="employee-details">
                            <div>
                                <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                                <dd>Operadora de producción</dd>
                            </div>
                            <div>
                                <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                                <dd>Producción</dd>
                            </div>
                            <div class="employee-supervisor-detail">
                                <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor directo</dt>
                                <dd>Roberto Cruz</dd>
                            </div>
                        </dl>
                        <a class="button employee-action-button" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Ana Martínez López">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            Evaluar desempeño
                        </a>
                    </article>

                    <article class="employee-card employee-card-maintenance">
                        <div class="employee-card-header">
                            <span class="employee-avatar avatar-purple" role="img" aria-label="Avatar de Luis Hernández">LH</span>
                            <div class="employee-identity">
                                <span class="employee-number">EMP027</span>
                                <h3>Luis Hernández Vega</h3>
                                <span class="employee-position">Técnico de mantenimiento</span>
                            </div>
                            <span class="employee-available" title="Disponible para evaluación">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Disponible
                            </span>
                        </div>
                        <dl class="employee-details">
                            <div>
                                <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                                <dd>Técnico de mantenimiento</dd>
                            </div>
                            <div>
                                <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                                <dd>Mantenimiento</dd>
                            </div>
                            <div class="employee-supervisor-detail">
                                <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor directo</dt>
                                <dd>Laura Gómez</dd>
                            </div>
                        </dl>
                        <a class="button employee-action-button" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Luis Hernández Vega">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            Evaluar desempeño
                        </a>
                    </article>

                    <article class="employee-card employee-card-quality">
                        <div class="employee-card-header">
                            <span class="employee-avatar avatar-orange" role="img" aria-label="Avatar de Fernanda Ruiz">FR</span>
                            <div class="employee-identity">
                                <span class="employee-number">EMP039</span>
                                <h3>Fernanda Ruiz Soto</h3>
                                <span class="employee-position">Inspectora de calidad</span>
                            </div>
                            <span class="employee-available" title="Disponible para evaluación">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Disponible
                            </span>
                        </div>
                        <dl class="employee-details">
                            <div>
                                <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                                <dd>Inspectora de calidad</dd>
                            </div>
                            <div>
                                <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                                <dd>Calidad</dd>
                            </div>
                            <div class="employee-supervisor-detail">
                                <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor directo</dt>
                                <dd>Marta Silva</dd>
                            </div>
                        </dl>
                        <a class="button employee-action-button" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Fernanda Ruiz Soto">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            Evaluar desempeño
                        </a>
                    </article>

                    <article class="employee-card employee-card-production">
                        <div class="employee-card-header">
                            <span class="employee-avatar avatar-pink" role="img" aria-label="Avatar de Jorge Castillo">JC</span>
                            <div class="employee-identity">
                                <span class="employee-number">EMP052</span>
                                <h3>Jorge Castillo Mora</h3>
                                <span class="employee-position">Operador de ensamble</span>
                            </div>
                            <span class="employee-available" title="Disponible para evaluación">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Disponible
                            </span>
                        </div>
                        <dl class="employee-details">
                            <div>
                                <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                                <dd>Operador de ensamble</dd>
                            </div>
                            <div>
                                <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                                <dd>Producción</dd>
                            </div>
                            <div class="employee-supervisor-detail">
                                <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor directo</dt>
                                <dd>Roberto Cruz</dd>
                            </div>
                        </dl>
                        <a class="button employee-action-button" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Jorge Castillo Mora">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            Evaluar desempeño
                        </a>
                    </article>

                    <article class="employee-card employee-card-logistics">
                        <div class="employee-card-header">
                            <span class="employee-avatar avatar-success" role="img" aria-label="Avatar de Daniela Flores">DF</span>
                            <div class="employee-identity">
                                <span class="employee-number">EMP061</span>
                                <h3>Daniela Flores Reyes</h3>
                                <span class="employee-position">Auxiliar de logística</span>
                            </div>
                            <span class="employee-available" title="Disponible para evaluación">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Disponible
                            </span>
                        </div>
                        <dl class="employee-details">
                            <div>
                                <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                                <dd>Auxiliar de logística</dd>
                            </div>
                            <div>
                                <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                                <dd>Logística</dd>
                            </div>
                            <div class="employee-supervisor-detail">
                                <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor directo</dt>
                                <dd>Andrés Navarro</dd>
                            </div>
                        </dl>
                        <a class="button employee-action-button" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Daniela Flores Reyes">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            Evaluar desempeño
                        </a>
                    </article>

                    <article class="employee-card employee-card-production">
                        <div class="employee-card-header">
                            <span class="employee-avatar avatar-gold" role="img" aria-label="Avatar de Miguel Torres">MT</span>
                            <div class="employee-identity">
                                <span class="employee-number">EMP074</span>
                                <h3>Miguel Torres Salas</h3>
                                <span class="employee-position">Líder de célula</span>
                            </div>
                            <span class="employee-available" title="Disponible para evaluación">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Disponible
                            </span>
                        </div>
                        <dl class="employee-details">
                            <div>
                                <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                                <dd>Líder de célula</dd>
                            </div>
                            <div>
                                <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                                <dd>Producción</dd>
                            </div>
                            <div class="employee-supervisor-detail">
                                <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor directo</dt>
                                <dd>Roberto Cruz</dd>
                            </div>
                        </dl>
                        <a class="button employee-action-button" href="detalleDesempeno.php" aria-label="Evaluar desempeño de Miguel Torres Salas">
                            <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                            Evaluar desempeño
                        </a>
                    </article>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
