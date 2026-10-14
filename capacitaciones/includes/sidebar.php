    <aside id="app-sidebar" class="app-sidebar" aria-label="Navegación principal">
        <div class="sidebar-header">
            <a class="sidebar-brand" href="<?= $basePathEscaped ?>dashboard.php" aria-label="Ir al dashboard">
                <span class="brand-symbol" aria-hidden="true">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
                <span class="brand-copy">
                    <strong>CapacitaLíder</strong>
                    <small>Formación de personal</small>
                </span>
            </a>

            <button
                id="sidebar-toggle"
                class="sidebar-toggle"
                type="button"
                aria-label="Expandir menú"
                aria-controls="app-sidebar"
                aria-expanded="false"
            >
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-caption">Menú principal</p>
            <ul class="nav-list">
                <li>
                    <a class="nav-link<?= $activePage === 'dashboard' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>dashboard.php"<?= $activePage === 'dashboard' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Dashboard">
                        <span class="nav-icon"><i class="fa-solid fa-house" aria-hidden="true"></i></span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'cursos' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/cursos.php" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Cursos y contenido">
                        <span class="nav-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
                        <span class="nav-text">Cursos y contenido</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'evaluacionConocimientos' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/evaluacionConocimientos.php"<?= $activePage === 'evaluacionConocimientos' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Evaluación de conocimientos">
                        <span class="nav-icon"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i></span>
                        <span class="nav-text">Evaluación de conocimientos</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'evaluacionDesempeno' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/evaluacionDesempeno.php"<?= $activePage === 'evaluacionDesempeno' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Evaluación de desempeño">
                        <span class="nav-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
                        <span class="nav-text">Evaluación de desempeño</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'creacionCursos' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/creacionCursos.php"<?= $activePage === 'creacionCursos' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Creación de cursos">
                        <span class="nav-icon"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i></span>
                        <span class="nav-text">Creación de cursos</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'usuarios' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/gestionUsuarios.php"<?= $activePage === 'usuarios' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Gestión de usuarios">
                        <span class="nav-icon"><i class="fa-solid fa-users-gear" aria-hidden="true"></i></span>
                        <span class="nav-text">Gestión de usuarios</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'cursosAsignacion' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/asignacionCursos.php"<?= $activePage === 'cursosAsignacion' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Asignación de cursos">
                        <span class="nav-icon"><i class="fa-solid fa-bookmark" aria-hidden="true"></i></span>
                        <span class="nav-text">Asignación de cursos</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'catalogos' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/catalogos.php"<?= $activePage === 'catalogos' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Catálogos">
                        <span class="nav-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                        <span class="nav-text">Catálogos</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link<?= $activePage === 'reportes' ? ' active' : '' ?>" href="<?= $basePathEscaped ?>pages/reportes.php"<?= $activePage === 'reportes' ? ' aria-current="page"' : '' ?> data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="sidebar-tooltip" data-bs-title="Reportes">
                        <span class="nav-icon"><i class="fa-solid fa-chart-column" aria-hidden="true"></i></span>
                        <span class="nav-text">Reportes</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
