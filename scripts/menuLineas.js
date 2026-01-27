
let lineForm = document.getElementById('lineForm');
let btnGuardarLinea = document.getElementById('btnGuardarLinea');

let lineCode = document.getElementById('lineCode')
let lineName = document.getElementById('lineName')
let supervisorSearch = document.getElementById('supervisorSearch')
let lineDescription = document.getElementById('lineDescription')

//Contenedor principal donde se cargan las lineas
let contenedorLineas = document.getElementById('contenedorLineas');

function openLayout(codigo,nombre){
    const url = "./pages/layoutFuncional.php?codigo="+ encodeURIComponent(codigo)+"&nombre="+ encodeURIComponent(nombre);
    const nombreVentana = "Layout";
    const anchoPantalla = screen.width;
    const altoPantalla = screen.height;
    const configuracion = "width="+anchoPantalla+",height="+altoPantalla+",resizable=yes,scrollbars=yes,status=yes";
    window.open(url, nombreVentana, configuracion);
}

//  Crear/agregar una nueva linea
function crearLinea(){
  if (lineForm.reportValidity()) {
        var formData = new FormData;
        let supervisor = (supervisorSearch.value.trim() === "") ? null : supervisorSearch.value;
        let descripcion = (lineDescription.value.trim() === "")  ? null : lineDescription.value;

        formData.append("opcion", "1");
        formData.append("codigoLinea", lineCode.value);
        formData.append("nombreLinea", lineName.value);
        formData.append("encargado", supervisor);
        formData.append("descripcion", descripcion);

        fetch("./api/operacionesLinea.php", {
                method: "POST",
                body: formData,
            })
            .then((response) => response.text())
            .then((data) => {
                
                data= JSON.parse(data)
            
                if(data.status=='ok'){
                    alert(data.mensaje);

                    //Limpiar formulario
                    document.getElementById('lineForm').reset();
                    console.log(data)

                    //Ocurtar modal
                    let modalAgregarLinea = bootstrap.Modal.getInstance(document.getElementById('modalAgregarLinea'));
                    modalAgregarLinea.hide();
                    
                    showLines();
                    //Funcion para actualizar el contenido de la pagina
                }

                else alert(data.mensaje)
            })
            .catch((error) => {
               console.log(error);
        });
  }
}

//Funcion para cargar las lineas
function showLines(){
   const formData = new FormData;
   formData.append('opcion', 4)

    fetch("./api/operacionesLinea.php", {
                method: "POST",
                body: formData,
            })
            .then((response) => response.text())
            .then((data) => {

                data= JSON.parse(data);

                    const contenedor = document.getElementById("contenedorLineas");

                    contenedor.innerHTML='';

                        data.forEach(item => {
                        contenedor.innerHTML += `
                            <div class="col-md-4 mb-4">
                                <div class="card-option contColLinea" data-codigo="${item.codigo_linea}" data-nombre="${item.nombre_linea}" >
                                    <i class="fa fa-industry card-icon"></i>
                                    <div class="card-label">${item.nombre_linea}</div>
                                </div>
                            </div>`;
                        });
            })
            .catch((error) => {
               console.log(error);
        });

}

showLines();

//Event listeners
btnGuardarLinea.addEventListener('click', crearLinea);

 contenedorLineas.addEventListener("click", function (e) {
    // Gestión de líneas
    if (e.target.closest(".contColLinea")) {
        e.preventDefault();

        let codigo = e.target.closest(".contColLinea").dataset.codigo;
        let nombre = e.target.closest(".contColLinea").dataset.nombre;

        openLayout(codigo, nombre);
        // aquí tu lógica
    }
});



