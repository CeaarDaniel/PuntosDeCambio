const certificationsMenu = document.getElementById('certifications');

if (certificationsMenu) {
    certificationsMenu.addEventListener('click', function (event) {
        const option = event.target.closest('[data-certification-route]');

        if (!option) {
            return;
        }

        event.preventDefault();
        cargarRuta(option.dataset.certificationRoute);
    });
}
