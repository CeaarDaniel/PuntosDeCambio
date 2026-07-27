let lineForm = document.getElementById('lineForm');
let btnGuardarLinea = document.getElementById('btnGuardarLinea');

let lineCode = document.getElementById('lineCode');
let lineName = document.getElementById('lineName');
let imageLine = document.getElementById('imageLine');
let selectAreas = document.getElementById('idArea');

let supervisorSearch = document.getElementById('supervisorSearch')
let nombreSupervisorSearch = document.getElementById('nombreSupervisorSearch');
let flagEmpleado = true; //Variable para validar que el empleado existe

let lineDescription = document.getElementById('lineDescription')

//Contenedor principal donde se cargan las lineas
let contenedorLineas = document.getElementById('contenedorLineas');

const regexCodigoLinea = /^[A-Za-z0-9-]+$/;

function openLayout(codigo,nombre){
    const url = "./pages/gestionLinea.php?codigo="+ encodeURIComponent(codigo)+"&nombre="+ encodeURIComponent(nombre);
    const nombreVentana = "Layout";
    const anchoPantalla = screen.width;
    const altoPantalla = screen.height;
    const configuracion = "width="+anchoPantalla+",height="+altoPantalla+",resizable=yes,scrollbars=yes,status=yes";
    window.open(url, nombreVentana, configuracion);
}

//Listar el tipo de area en el formulario crearLinea
function mostrarAreas(){
    let formDataAreas = new FormData();
    formDataAreas.append('opcion', 2);

    fetch("./api/operacionesMenuLineas.php", {
        method: "POST",
        body: formDataAreas,
    })
    .then((response) => response.text())
    .then((data) => {
        data = JSON.parse(data);
        selectAreas.innerHTML = '';

        if (data.status !== 'ok') {
                selectAreas.innerHTML = '<option value="">Selecciona una opción...</option>';
                console.log(data);
            return;
        }
        
        data.response.forEach((area) => {
            const opcion = document.createElement('option');
            opcion.value = area.idArea;
            opcion.textContent = area.nombreArea;
            selectAreas.appendChild(opcion);
        });
    }).catch((error) => {
        selectAreas.innerHTML = '<option value="">Selecciona una opción...</option>';
        console.error(error);
    });
}

//Crear/agregar una nueva linea
function crearLinea(){
    //Validar el formulario
    if (!lineForm.reportValidity()) {
        return;
    }

    //Validar el codigoLinea
    if (!regexCodigoLinea.test(lineCode.value.trim())) {
        alert('El código de línea solo puede contener letras, números y guion medio (-). No se permiten espacios ni caracteres especiales.');
        return;
    }

    //Validar la nomina del empleado
    if(!flagEmpleado){
        alert('No se encontro informacion del empleado ingresado');
        return
    }

        var formData = new FormData;
        let supervisor = (supervisorSearch.value.trim() === "") ? null : supervisorSearch.value;
        let descripcion = (lineDescription.value.trim() === "")  ? null : lineDescription.value;

        formData.append("opcion", "1");
        formData.append("codigoLinea", lineCode.value.trim());
        formData.append("nombreLinea", lineName.value);
        formData.append("idArea", idArea.value);

        (supervisor) ? formData.append("encargado", supervisor) : '';
        (descripcion) ? formData.append("descripcion", descripcion) : '';

        formData.append("imageLine", imageLine.files[0]);

            fetch("./api/operacionesMenuLineas.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.text())
                .then((data) => {
                    console.log(data);
                    data= JSON.parse(data)
                
                    if(data.status=='ok'){
                        alert(data.mensaje);

                        //Limpiar formulario
                        document.getElementById('lineForm').reset();
                        console.log(data)

                        //Ocurtar modal
                        let modalAgregarLinea = bootstrap.Modal.getInstance(document.getElementById('modalAgregarLinea'));
                        modalAgregarLinea.hide();
                        
                        //Funcion para actualizar el contenido de la pagina
                        showLines();
                        
                        nombreSupervisorSearch.classList.remove("text-danger")
                        nombreSupervisorSearch.textContent= "Nombre del empleado..."; 
                        lineForm.reset();
                        flagEmpleado = true;
                    }

                    else alert(data.mensaje)
                })
                .catch((error) => {
                console.log(error);
            });
}

//Funcion para cargar las lineas
function showLines(){
   const formData = new FormData;
   formData.append('opcion', 4)

    fetch("./api/operacionesMenuLineas.php", {
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
                            <div class="col-12 col-md-4 mb-4">
                                <div class="card-option contColLinea" data-codigo="${item.codigo_linea}" data-nombre="${item.nombre_linea}" >
                                            ${(item.imagen) ? `<img src="img/lineas/${item.imagen}" class="card-img-top" alt="${item.nombre_linea}" style="max-width:250px; max-height:250px">` 
                                                            : '<img src="img/lineas/def1.jpg" class="card-img-top" alt="No fue posible cargar la imagen" style="max-width:250px; max-height:250px">'}
                                    
                                            <br>                                    
                                    <div class="card-label">
                                        <h5 class="card-title text-center">${item.nombre_linea}</h5>
                                    </div>
                                </div>
                            </div>`;
                        });

 
            })
            .catch((error) => {
               console.log(error);
        });

}

showLines();
mostrarAreas();

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

//OBTENER NOMBRE DEL EMPLEADO
supervisorSearch.addEventListener('change', function (){
    if(supervisorSearch && supervisorSearch.value !='') {
        let formDataConsultarNombre = new FormData();
        formDataConsultarNombre.append('nomina', supervisorSearch.value)
        formDataConsultarNombre.append('opcion', 7)

        //nominaModalRegistrar.disabled = true
        //nombreModalRegistrar.value= ''; 
        nombreSupervisorSearch.classList.remove("text-danger")
        nombreSupervisorSearch.textContent= "Consultando datos del empleado...";  

            fetch("./api/operacionesMenuLineas.php", {
                    method: "POST",
                    body: formDataConsultarNombre,
                })
                .then((response) => response.text())
                .then((data) => {
                    console.log(data)
                    data= JSON.parse(data)
                    if(data.estatus=='ok'){
                        nombreSupervisorSearch.textContent= data.nombre;
                        flagEmpleado = true;
                     }
                    
                    else{
                        nombreSupervisorSearch.classList.add("text-danger")
                        nombreSupervisorSearch.textContent= "No se encontró información del empleado";  
                        console.log(data.error); 
                        flagEmpleado = false;
                    }

                    //nominaModalRegistrar.disabled = false;
                })
                .catch((error) => {
                    nombreSupervisorSearch.classList.add("text-danger")
                    nombreSupervisorSearch.textContent= "Error de conexion..."; 
                    flagEmpleado = false;
                    //nominaModalRegistrar.disabled = false;
                    console.log(error);
            });
    }

    else{
        nombreSupervisorSearch.classList.remove("text-danger")
        nombreSupervisorSearch.textContent= "Nombre del empleado..."; 
        flagEmpleado = true;
    }
})