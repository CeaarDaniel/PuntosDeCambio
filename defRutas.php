<?php 
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include('./api/conexion.php'); 


if (!isset($_SESSION['loggedin'])) {
    if (!headers_sent())
    { 
        header('Location: ./login.php');
    }

   else
       {  
       echo '<script type="text/javascript">';
       echo 'window.location.href="login.php";';
       echo '</script>';
       echo '<noscript>';
       echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
       echo '</noscript>';
   }
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="es">
<head>
    <title>Sistema Uniformes</title>
    <meta charset="utf-8">
    <meta name="description" content="A Quasar Framework app">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="viewport" content="user-scalable=no,initial-scale=1.0,maximum-scale=1,minimum-scale=1,width=device-width">

    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <link rel="icon" type="image/ico" href="logo_b.png">
    <link rel="icon" type="image/ico" href="logo_b.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome (para íconos) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
   
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Estilos personalizados -->
    <link rel="icon" type="image/ico" href="logo_b.png">
    <link href="./style.css" rel="stylesheet">

    <!--Libreria Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!--Data table -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    
    <!-- DataTables Select -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

    <!--html2PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body style="background-color: #f7f7f7">
    <div id="q-app" >


        <?php include('navBar.php') ?>

            <!-- Contenido principal -->
            <div id="mainContent" class="padding-header animacion"></div>
    </div>


     <div class="modal fade" id="modalAccesoDenegado" tabindex="-1" aria-labelledby="modalAccesoDenegadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalAccesoDenegadoLabel">🚫 Acceso Denegado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    No tienes permisos para acceder a esta sección. Por favor, contacta al administrador del sistema si crees que esto es un error.
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Libreria Jquery! -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.print.min.js"></script>

    <script>
        /*
            FLUJO DE NAVEGACION
             1.SE CAMBIA DE RUTA CON LOS ENLACES EN LOS ELEMENTOS <a></a>, 
               CON LOS BOTONES ATRAS (popstate), ADELANTE, RECARGAR DEL NAVEGADOR 
               O INGRESANDO DIRECTAMENTE LA RUTA
             2.SE OBTIENE LA RUTA EN JS (obtenerSeccionActual)
             3. SE CARGA O CAMBIA EL CONTENIDO DE ACUERTO A LA RUTA OBTENIDA (navegar)
        */

        const modal = new bootstrap.Modal(document.getElementById('modalAccesoDenegado'));
        var rolUsuarioSession = <?php echo $_SESSION['rolUsuario']?>;
        const routes = {
            //ADMIN
            '1' : ['dashboard', 'almacen', 'altasArticulos', 'altasCategorias', 
                 'entradas', 'entradasPedido', 'entradasSalidas', 'entradasManuales',
                 'salidas', 'salidasPlanta', 'salidasVenta', 'salidasReposicion', 'salidasRenovacion',
                 'consultaSalidas', 'consultaVentas', 'consultasReportes', 'cambios', 'editarAlmacen', 
                 'pedidos', 'menuReportes', 'reporteVentas', 'reportesExistencias', 'reportesSalidas'
                ],
            //FINANZAS
            '2' : ['dashboard','consultaVentas', 'consultasReportes','almacen'],
            //ADMINISTRACION
            '3' : ['dashboard', 'almacen', 'salidasPlanta', 'salidasVenta', 'salidasReposicion', 'salidasRenovacion', 'cambios'],
            //COMPRAS
            '4': ['dashboard', 'almacen', 'altasArticulos', 'altasCategorias', 
                  'consultasReportes', 'pedidos', 'entradas', 'entradasPedido', 'entradasSalidas', 'entradasManuales'],
            //VIEWER
            '5' : ['dashboard', 'almacen'],
            //ALMACENISTA
            '6' : ['dashboard', 'almacen', 'entradas', 'entradasPedido', 'salidas', 'salidasPlanta', 
                   'salidasVenta', 'salidasReposicion', 'salidasRenovacion', 'consultaSalidas',
                   'consultaVentas', 'consultasReportes', 'pedidos', 'cambios',
                ]
        }


        //Mostrar el tooltip
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        //Fin mostrar el tooltipe

        //Funcion para la navegacion entre ventanas
        function navegar(pagina, id, pagsus) {

            console.log("Seccion actual:", pagina);
            var contenido = document.getElementById(pagsus); //OBTENER EL CONTENEDOR DE LA PAGINA ACTUAL
            var xhttp = new XMLHttpRequest();
            
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        window.scroll(0, 0);
                        contenido.innerHTML = this.responseText;
                              
                        // Eliminar el script anterior si existe
                        var oldScript = document.getElementById('jsDinamico');
                        if (oldScript) oldScript.remove();

                        // Verificar si el archivo JS existe antes de cargarlo
                        var scriptUrl = `./js/${pagina}.js`;
                        fetch(scriptUrl, { method: 'HEAD' })
                            .then(response => {
                                if (response.ok) {
                                    // Cargar el script envuelto en un IIFE
                                    return fetch(scriptUrl);
                                } else {
                                    throw new Error('El archivo JS no existe');
                                    //console.log(`Script no encontrado: ${scriptUrl}`);
                                }
                            })
                            .then(response => response.text())
                            .then(scriptText => {
                                var script = document.createElement('script');
                                script.id = 'jsDinamico';
                                // Envolver el script en una función autoejecutable
                                script.textContent = `(function() { ${scriptText} })();`;
                                document.body.appendChild(script);
                                //renderTable();
                            })
                            .catch(error => {
                                console.log(`Script no encontrado: ${scriptUrl}`);
                            });
                       
                    }
                };
                xhttp.open('POST', pagina + '.php', true); //SOLICITUD A LA PAGINA CON EL CONTENIDO NUEVO
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send('id=' + id);
        }

        //Funcion para obtener la rauta de la pagina actual
        function obtenerSeccionActual() {
            const hash = location.hash.split('/');
                if(hash[hash.length - 1] == '' || hash[hash.length - 1] == null || hash[hash.length - 1]==' ' || hash[hash.length - 1]=='')
                    hash[hash.length - 1] ='dashboard';

                //if(hash[hash.length - 1]=='pedidos') console.log('no tiene permisos para estar aqui')

                    //console.log(hash[hash.length - 1]);

            return hash[hash.length - 1]; 
        }

        // Función que carga contenido y cambia la URL
        function cargarRuta(pagina, id) {
            if(!routes[rolUsuarioSession].includes(pagina))
                 modal.show();

            else {
                if (!id) id = 0;

                const seccionActual = obtenerSeccionActual();
                    if (seccionActual === pagina) 
                        return;

                // Cambiar la URL (sin recargar)
                history.pushState({ seccionActual }, "", `/sistemaUniformesAdaptado/#/${pagina}`);

                var animacion = document.querySelector("#mainContent");
                animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina

                setTimeout(function () {
                    navegar(pagina, id,'mainContent')
                    animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                }.bind(this), 400);
            }
        }

        // Detectar el botón "atrás" o "adelante" del navegador
        window.addEventListener("popstate", (event) => {
            const nuevaSeccion = obtenerSeccionActual();

        if(!routes[rolUsuarioSession].includes(nuevaSeccion))
            modal.show();

            else {
                var animacion = document.querySelector("#mainContent");

                animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                setTimeout(function () {
                    navegar(nuevaSeccion,'0','mainContent')
                    animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                }.bind(this), 400);

                //OCULTAR LOS MODALES ABIERTOS
                document.querySelectorAll('.modal.show').forEach(modalEl => {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });

                // Eliminar cualquier backdrop que quedó
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style = ''; 
            }
        });

        // Cargar la página correcta al recargar la SPA
        window.addEventListener("DOMContentLoaded", () => {
            //const ruta = location.pathname.slice(1) || "inicio";
            const seccion = obtenerSeccionActual();

             if(!routes[rolUsuarioSession].includes(seccion)){
                modal.show();
             }

             else {
                //var animacion = document.querySelector("#mainContent");
                //animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina

                //setTimeout(function () {
                    navegar(seccion,'0','mainContent')
                    //animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                //}.bind(this), 400);
            }
        });

        function logOut(){
            let formDataArt = new FormData;
            formDataArt.append('opcion', 2);
                fetch("./api/login.php", {
                    method: "POST",
                    body: formDataArt,
                })
                    .then((response) => response.text())
                    .then((data) => {
                        window.location.href = "./login.php";
                    })
                    .catch((error) => {
                        console.log(error);
                    })
        }
    </script>
</body>
</html>