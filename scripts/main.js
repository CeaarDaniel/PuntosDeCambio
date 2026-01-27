
const pageTitle = document.getElementById('page-title') //Contenedor del titulo principal
const navLinks = document.querySelectorAll('a'); //Opciones de la barra de navegavcion

        //Funcion para la navegacion entre ventanas
        function navegar(pagina, id, pagsus) {

            //console.log("Seccion actual:", pagina);
            var contenido = document.getElementById(pagsus); //OBTENER EL CONTENEDOR DE LA PAGINA ACTUAL


            var xhttp = new XMLHttpRequest();
            
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        window.scroll(0, 0);
                        contenido.innerHTML = this.responseText;
                         updatePageTitle(pagina);
                              
                        // Eliminar el script anterior si existe
                        var oldScript = document.getElementById('jsDinamico');
                        if (oldScript) oldScript.remove();

                        // Verificar si el archivo JS existe antes de cargarlo
                        var scriptUrl = `./scripts/${pagina}.js`;
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
                xhttp.open('POST', './pages/'+pagina + '.php', true); //SOLICITUD A LA PAGINA CON EL CONTENIDO NUEVO
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send('id=' + id);
        }

        //Funcion para obtener la ruta de la pagina actual
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
            //if(!routes[rolUsuarioSession].includes(pagina))
                 //modal.show();

            //else {
                if (!id) id = 0;

                const seccionActual = obtenerSeccionActual();
                    if (seccionActual === pagina) 
                        return;

                // Cambiar la URL (sin recargar)
                history.pushState({ seccionActual }, "", `/PuntosDeCambio/#/${pagina}`);


                var animacion = document.querySelector("#content-area");
                animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina

                setTimeout(function () {
                    navegar(pagina, id,'content-area')
                    animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                }.bind(this), 400);
            //}
        }

        // Detectar el botón "atrás" o "adelante" del navegador
        window.addEventListener("popstate", (event) => {
            const nuevaSeccion = obtenerSeccionActual();

        //if(!routes[rolUsuarioSession].includes(nuevaSeccion))
            //modal.show();

            //else {
                var animacion = document.querySelector("#content-area");

                animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                setTimeout(function () {
                    navegar(nuevaSeccion,'0','content-area')
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
            //}
        });

        // Cargar la página correcta al recargar la SPA
        window.addEventListener("DOMContentLoaded", () => {
            //const ruta = location.pathname.slice(1) || "inicio";
            const seccion = obtenerSeccionActual();

             //if(!routes[rolUsuarioSession].includes(seccion)){
             //   modal.show();
             //}

             //else {
                var animacion = document.querySelector("#content-area");
                animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina

                setTimeout(function () {
                    navegar(seccion,'0','content-area')
                    animacion.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
                }.bind(this), 400);
            //}
        });

        //Actualizar el titulo de la pagina principal
        function updatePageTitle(sectionId) {
            const titles = {
                    'dashboard': 'Dashboard',
                    'menuLineas': 'Gestión de Líneas',
                    'puntosCambio': 'Puntos de Cambio',
                    'menuCertificaciones': 'Certificaciones',
                    'change-points': 'Puntos de Cambio',
                    'menuReportes': 'Reportes y Estadísticas',
                    'estadisticas' : 'Estadísticas'
            };

            pageTitle.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
            setTimeout(function () {
                    pageTitle.textContent = titles[sectionId] || 'Puntos de Cambio y Certificaciones';   
                pageTitle.classList.toggle("ocultar-mostrar"); //cambia la opacidad en 1  al cambiar de pagina
            }.bind(this), 400);
        }

        function backPage(){
            window.history.back(); 
        }



    //Señalar en el navBarr la opcion dentro de la que se encuntra 

    /*
      navLinks.forEach(link => {
        link.addEventListener('click', function() {
          
          // Remove active class from all links
          navLinks.forEach(item => {
            item.classList.remove('active');
          });
          
          // Add active class to clicked link
          this.classList.add('active');
        });
      });
    */