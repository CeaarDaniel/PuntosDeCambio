<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Puntos de cambio y certificaciones</title>
    <!--BOOSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
  
    <!--Bostrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <!--Custom css -->
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="row p-0 m-0"> 
        <div class="col-12 my-1 py-1 col-md-1 d-flex flex-md-column flex-row" style="overflow-y: auto;">
              <button type="button" class="icon-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Crear estaciones" aria-label="Crear estaciones" style="font-size: 40px; color:#015105; font-weight: 500;">
                <i class="bi bi-building"></i>
              </button>
              <button type="button" class="icon-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Asignar operador" aria-label="Asignar operador" style="font-size: 40px; color:#0299c6; font-weight: 500;">
                <i class="bi bi-person-plus"></i>
              </button>
              <button type="button" class="icon-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Guardar acomodo de layout" aria-label="Acomodo de layout" style="font-size: 40px; color:#804901; font-weight: 500;">
                <i class="bi bi-layout-text-sidebar"></i>
              </button>
              <button type="button" class="icon-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Punto de cambio" aria-label="Punto de cambio" style="font-size: 40px; color:#008b8d; font-weight: 500;">
                <i class="bi bi-arrow-repeat"></i>
              </button>
              <button type="button" class="icon-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Registro de asistencia" aria-label="Registro de asistencia" style="font-size: 40px; color:#0299c6; font-weight: 500;">
                <i class="bi bi-check2-square"></i>
              </button>
              <button type="button" class="icon-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Reporte" aria-label="Reporte de asistencias" style="font-size: 40px; color:#8760fa; font-weight: 500;">
                <i class="bi bi-file-earmark-bar-graph"></i>
              </button>
        </div>
        <div class="col-12 col-md-11">
                <div class="body-drag">
                    <div class="scroll-container" id="scrollContainer">
                      <div class="container" style="top: 0px; left: 0px;">

                            <div class="row p-0 m-0">
                                  <div class="col-12 p-0 m-0">
                                    Estacion 1
                                  </div> 

                                <div class="col-4 p-0 m-0">
                                  <button class="btn btn-primary m-0" onclick="alert('boton 1')">1</button>
                                </div>
                                <div class="col-4 p-0 m-0">
                                  <button class="btn btn-primary m-0" onclick="alert('boton 2')">2</button>
                                </div>
                                <div class="col-4 p-0 m-0">
                                  <button class="btn btn-primary m-0" onclick="alert('boton 3')">3</button>
                                </div>
                            </div>
                              
                            </div>
                            <div class="container" style="top: 105px; left: 10px; background-color: #2196F3;">
                              <div style="width: 100%; height: 100%;">
                                  Estacion 2 Lorem ipsum dolor sit amet consectetur, adipisicing elit. Error quam aut molestias. 
                                  Autem consequuntur voluptate beatae ipsam ad ex, maxime modi non, inventore, maiores doloribus 
                                  atque eos voluptatibus cupiditate temporibus!
                              </div>
                            </div>  
                            <div class="container" style="top: 210px; left: 150px; background-color: #ff8800;">Estacion 3</div>
                            <div class="container" style="top: 315px; left: 210px; background-color: #9022ff;">Estacion 4</div>
                            <div class="container" style="top: 420px; left: 300px; background-color: #da0000;">Estacion 5</div>
                            <div class="container" style="top: 525px; left: 120px; background-color: #e383f7;">Estacion 6</div>
                            <div class="container" style="top: 315px; left: 492px; background-color: #00b9da;">Estacion 7</div>
                            <div class="container" style="top: 0px; left: 202px; background-color: #008b8d;">Estacion 8</div>
                            <div class="container" style="top: 50px; left: 303px; background-color: #02007d;">Estacion 9</div>
                            <div class="container" style="top: 120px; left: 404px; background-color: #da00bd;">Estacion 10</div>
                            <div class="container" style="top: 90px; left: 505px; background-color: #00da0b;">Estacion 11</div>
                            <div class="container" style="top: 130px; left: 606px; background-color: #00da83;">Estacion 12</div>
                            <div class="container" style="top: 210px; left: 707px; background-color: #00da83;">Estacion 13</div>
                            <div class="container" style="top: 40px; left: 808px; background-color: #06b9ff;">Estacion 14</div>
                            <div class="container" style="top: 70px; left: 909px; background-color: #d6ffb3;">Estacion 15</div>
                            <div class="container" style="top: 490px; left: 920px; background-color: #c0ffe6;">Estacion 16</div>
                    </div>
                </div>
        </div>
    </div>
  <script>

    //Elemento donde estan contenidas las etiquetas de las estaciones
    const scrollContainer = document.getElementById('scrollContainer');

    //Etiquetas de las estaciones dentro del scroll del layout 
    const estaciones = scrollContainer.querySelectorAll('.container');

    estaciones.forEach(estacion => {
      estacion.addEventListener('mousedown', onMouseDown);


      function onMouseDown(e) {
        e.preventDefault();

        // Obbtener el tamaño y la posición del elemento respecto al viewport del contenedor padre
        const parentRect = scrollContainer.getBoundingClientRect();

        // Posición inicial del mouse y del contenedor (estacion)
        const containerRect = estacion.getBoundingClientRect();

        // Diferencia entre el mouse y la esquina superior izquierda del contenedor (relativo al viewport)
        let shiftX = e.clientX - containerRect.left; // => containerRect.x; 
        let shiftY = e.clientY - containerRect.top; // => containerRect.y;


        //clientX, clientY son equivalentes a left y top, son propiedades de eventos (e) de mouse 
        //Devuelve las coordenadas del ratón dentro del viewport.

        // Función para mover el contenedor dentro del scrollContainer
        function moveAt(pageX, pageY) { //PASAR LAS CODENADAS DEL MOUSE
          // Calcular la nueva posición relativa al contenedor padre
          let newLeft = pageX - (parentRect.left+ window.scrollX) - shiftX + scrollContainer.scrollLeft;
          let newTop = pageY - (parentRect.top+ window.scrollY) - shiftY + scrollContainer.scrollTop;

          if (newLeft < 0) newLeft = 0;
          if (newTop < 0) newTop = 0;
          //if (newLeft > rightEdge) newLeft = rightEdge;
          //if (newTop > bottomEdge) newTop = bottomEdge;

          estacion.style.left = newLeft + 'px';
          estacion.style.top = newTop + 'px';
        }    

        function onMouseMove(e) {
          moveAt(e.pageX, e.pageY);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', function onMouseUp() {
          document.removeEventListener('mousemove', onMouseMove);
          document.removeEventListener('mouseup', onMouseUp);
        });
      }

      estacion.ondragstart = function() {
        return false;
      };
    });

     // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  </script>
</body>
</html>

<!--
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Contenedores Movibles</title>
    <style>
      body {
        margin: 0;
        height: 100vh;
        background: #f0f0f0;
        overflow: hidden;
        user-select: none; /* Evita seleccionar texto al arrastrar */
      }
      .container {
        width: 150px;
        height: 150px;
        background-color: #4CAF50;
        color: white;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        cursor: grab;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: box-shadow 0.2s ease;
        display: flex;
        position: absolute;

      }
      .container:active {
        cursor: grabbing;
        box-shadow: 0 8px 12px rgba(0,0,0,0.2);
      }
    </style>
  </head>
  <body>

      <div style="width: 100%;  align-items: center; justify-content: center;  position: absolute;">
          <div style="overflow-y: auto; overflow-x: auto; width:400px; height: 450px; position: absolute; background: white;">
              <div class="container" style="top: 50px; left: 50px;">Contenedor 1</div>
              <div class="container" style="top: 50px; left: 250px; background-color: #2196F3;">Contenedor 2</div>
              <div class="container" style="top: 250px; left: 50px; background-color: #FF5722;">Contenedor 3</div>
          </div>
      </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script>
      const containers = document.querySelectorAll('.container');

      containers.forEach(container => {
        container.addEventListener('mousedown', onMouseDown);

        function onMouseDown(e) {
          e.preventDefault();

          let shiftX = e.clientX - container.getBoundingClientRect().left;
          let shiftY = e.clientY - container.getBoundingClientRect().top;

          function moveAt(pageX, pageY) {
            container.style.left = pageX - shiftX + 'px';
            container.style.top = pageY - shiftY + 'px';
          }

          function onMouseMove(e) {
            moveAt(e.pageX, e.pageY);
          }

          document.addEventListener('mousemove', onMouseMove);

          document.addEventListener('mouseup', function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
          });
        }

        // Para evitar que el contenedor se pueda seleccionar y arrastrar por defecto
        container.ondragstart = function() {
          return false;
        };
      });
    </script>
  </body>
  </html>
-->