    <header class="app-topbar">
        <div class="topbar-section">
            <button
                id="mobile-sidebar-toggle"
                class="icon-button mobile-menu-button"
                type="button"
                aria-label="Abrir menú"
                aria-controls="app-sidebar"
                aria-expanded="false"
            >
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <div class="section-heading">
                <span>Sección actual</span>
                <h1><?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
        </div>

        <div class="user-menu">
            <button
                id="user-menu-button"
                class="user-menu-button"
                type="button"
                aria-label="Abrir menú de usuario"
                aria-controls="user-dropdown"
                aria-expanded="false"
            >
                <span class="user-avatar" aria-hidden="true">CM</span>
                <span class="user-summary">
                    <strong>Carlos Mendoza</strong>
                    <small>Operador</small>
                </span>
                <i class="fa-solid fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
            </button>

            <div id="user-dropdown" class="user-dropdown" role="menu" aria-hidden="true">
                <a href="<?= $basePathEscaped ?>login.php" class="dropdown-item" role="menuitem">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                    <span>Salir</span>
                </a>
            </div>
        </div>
    </header>
