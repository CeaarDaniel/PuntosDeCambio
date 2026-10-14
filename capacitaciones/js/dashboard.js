$(function () {
    const $body = $("body");
    const $moduleNotice = $("#module-notice");
    let noticeTimer;

    function showModuleNotice(moduleName) {
        window.clearTimeout(noticeTimer);
        $moduleNotice
            .addClass("is-visible")
            .find("span")
            .text(moduleName + " estará disponible en una próxima etapa.");

        noticeTimer = window.setTimeout(function () {
            $moduleNotice.removeClass("is-visible");
        }, 3200);
    }

    $("[data-future-module]").on("click", function (event) {
        event.preventDefault();
        showModuleNotice($(this).data("future-module"));

        if (window.innerWidth < 992) {
            $("#sidebar-overlay").trigger("click");
        }
    });

    $("#show-navigation").on("click", function () {
        if (window.innerWidth < 992) {
            $("#mobile-sidebar-toggle").trigger("click");
            return;
        }

        if ($body.hasClass("sidebar-collapsed")) {
            $("#sidebar-toggle").trigger("click");
        }
    });
});
