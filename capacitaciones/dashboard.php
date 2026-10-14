<?php
$pageTitle = 'Dashboard | CapacitaLíder';
$sectionTitle = 'Dashboard';
$activePage = 'dashboard';
$basePath = '';
$pageStyle = 'css/dashboard.css';
$pageScript = 'js/dashboard.js';
$showModuleNotice = true;

include __DIR__ . '/includes/app-header.php';
?>
        <div class="dashboard-container">
            <section class="welcome-card" aria-labelledby="welcome-title">
                <div class="welcome-copy">
                    <span class="eyebrow"><i class="fa-solid fa-star" aria-hidden="true"></i> Tu espacio de aprendizaje</span>
                    <h2 id="welcome-title">Bienvenido a la app de capacitación</h2>
                    <p>
                        Encuentra en un solo lugar los cursos, evaluaciones y recursos que te ayudarán
                        a desarrollar tus habilidades de forma clara y sencilla.
                    </p>
                    <button id="show-navigation" class="button button-light" type="button">
                        <i class="fa-solid fa-compass" aria-hidden="true"></i>
                        <span>Explorar opciones</span>
                    </button>
                </div>

                <div class="welcome-visual" aria-hidden="true">
                    <div class="visual-orbit orbit-one"></div>
                    <div class="visual-orbit orbit-two"></div>
                    <div class="visual-icon">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <span class="visual-badge badge-book"><i class="fa-solid fa-book-open"></i></span>
                    <span class="visual-badge badge-check"><i class="fa-solid fa-check"></i></span>
                </div>
            </section>

            <section class="dashboard-summary" aria-labelledby="summary-title">
                <div class="content-heading">
                    <div>
                        <span class="section-kicker">Plataforma integral</span>
                        <h2 id="summary-title">Todo lo necesario para seguir creciendo</h2>
                    </div>
                    <p>Usa el menú lateral para acceder a cada módulo.</p>
                </div>

                <div class="summary-grid">
                    <article class="summary-card summary-card-cyan">
                        <span class="summary-icon"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i></span>
                        <div>
                            <h3>Aprende a tu ritmo</h3>
                            <p>Consulta cursos y contenidos preparados para fortalecer tus conocimientos.</p>
                        </div>
                    </article>

                    <article class="summary-card summary-card-purple">
                        <span class="summary-icon"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></span>
                        <div>
                            <h3>Evalúa tu avance</h3>
                            <p>Realiza evaluaciones con instrucciones claras y revisa tu progreso.</p>
                        </div>
                    </article>

                    <article class="summary-card summary-card-orange">
                        <span class="summary-icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
                        <div>
                            <h3>Fortalece al equipo</h3>
                            <p>Gestiona la capacitación y el desarrollo del personal desde un mismo lugar.</p>
                        </div>
                    </article>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/includes/app-footer.php'; ?>
