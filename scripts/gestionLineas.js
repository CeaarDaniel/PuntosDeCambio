let optionLayout = document.getElementById('optionLayout');
optionLayout.addEventListener('click', openLayout);

    //Funcion Para Mostrar el layout
     function openLayout(){
        const url = "./pages/layout.php";
        const nombreVentana = "Layout";
        const anchoPantalla = screen.width;
        const altoPantalla = screen.height;
        const configuracion = "width="+anchoPantalla+",height="+altoPantalla+",resizable=yes,scrollbars=yes,status=yes";
        window.open(url, nombreVentana, configuracion);
    }