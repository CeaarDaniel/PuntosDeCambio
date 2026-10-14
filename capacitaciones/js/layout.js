$(function () {
    const $body = $("body");
    const $sidebarToggle = $("#sidebar-toggle");
    const $mobileSidebarToggle = $("#mobile-sidebar-toggle");
    const $sidebarOverlay = $("#sidebar-overlay");
    const $userMenu = $(".user-menu");
    const $userMenuButton = $("#user-menu-button");
    const $userDropdown = $("#user-dropdown");
    const mobileBreakpoint = 992;
    const navigationTooltips = Array.from(document.querySelectorAll(".sidebar-nav [data-bs-toggle='tooltip']"))
        .map(function (element) {
            return bootstrap.Tooltip.getOrCreateInstance(element, {
                container: "body",
                trigger: "hover focus"
            });
        });

    function isMobileLayout() {
        return window.innerWidth < mobileBreakpoint;
    }

    function updateNavigationTooltips() {
        const shouldShowTooltips = !isMobileLayout() && $body.hasClass("sidebar-collapsed");

        navigationTooltips.forEach(function (tooltip) {
            if (shouldShowTooltips) {
                tooltip.enable();
            } else {
                tooltip.hide();
                tooltip.disable();
            }
        });
    }

    function updateDesktopSidebarState() {
        const isCollapsed = $body.hasClass("sidebar-collapsed");

        $sidebarToggle
            .attr("aria-expanded", !isCollapsed)
            .attr("aria-label", isCollapsed ? "Expandir menú" : "Comprimir menú");

        updateNavigationTooltips();
    }

    function openMobileSidebar() {
        $body.addClass("sidebar-mobile-open");
        $mobileSidebarToggle.attr("aria-expanded", "true").attr("aria-label", "Cerrar menú");
        $sidebarToggle.attr("aria-label", "Cerrar menú");
        $sidebarToggle.find("i").attr("class", "fa-solid fa-xmark");
        updateNavigationTooltips();
    }

    function closeMobileSidebar() {
        $body.removeClass("sidebar-mobile-open");
        $mobileSidebarToggle.attr("aria-expanded", "false").attr("aria-label", "Abrir menú");
        $sidebarToggle.find("i").attr("class", "fa-solid fa-chevron-right");
        updateDesktopSidebarState();
    }

    function closeUserMenu() {
        $userMenuButton.attr("aria-expanded", "false");
        $userDropdown.removeClass("is-open").attr("aria-hidden", "true");
    }

    $sidebarToggle.on("click", function () {
        if (isMobileLayout()) {
            closeMobileSidebar();
            return;
        }

        $body.toggleClass("sidebar-collapsed");
        updateDesktopSidebarState();
    });

    $mobileSidebarToggle.on("click", function () {
        if ($body.hasClass("sidebar-mobile-open")) {
            closeMobileSidebar();
        } else {
            openMobileSidebar();
        }
    });

    $sidebarOverlay.on("click", closeMobileSidebar);

    $userMenuButton.on("click", function () {
        const willOpen = !$userDropdown.hasClass("is-open");

        $userMenuButton.attr("aria-expanded", willOpen);
        $userDropdown.toggleClass("is-open", willOpen).attr("aria-hidden", !willOpen);
    });

    $(document).on("click", function (event) {
        if (!$(event.target).closest($userMenu).length) {
            closeUserMenu();
        }
    });

    $(document).on("keydown", function (event) {
        if (event.key !== "Escape") {
            return;
        }

        closeUserMenu();
        closeMobileSidebar();
        $userMenuButton.trigger("focus");
    });

    $(window).on("resize", function () {
        if (!isMobileLayout()) {
            closeMobileSidebar();
        }

        updateNavigationTooltips();
    });

    updateDesktopSidebarState();
});
