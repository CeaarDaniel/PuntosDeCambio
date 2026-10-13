/*
 * Inicialización visual del listado de anormalidades.
 * Los catálogos y el registro inicial de anormalidades se procesan mediante
 * la API del módulo.
 */
(function () {
    'use strict';

    const selectorTabla = '#tablaAnormalidades';
    const campoFiltroFechaInicio = document.getElementById('anormalidadFechaInicio');
    const campoFiltroFechaFin = document.getElementById('anormalidadFechaFin');
    const campoFiltroTurno = document.getElementById('anormalidadTurno');
    const campoFiltroFactorGerente = document.getElementById('anormalidadFactorGerente');
    const campoFiltroArea = document.getElementById('anormalidadArea');
    const campoFiltroLinea = document.getElementById('anormalidadLinea');
    const contadorRegistrosListado = document.getElementById('anormalidadesTotalRegistros');
    const modalRegistro = document.getElementById('modalNuevaAnormalidad');
    const formularioRegistroInicial = document.getElementById('formRegistroInicialAnormalidad');
    const botonGuardarRegistroInicial = document.getElementById('btnGuardarRegistroInicial');
    const campoArea = document.getElementById('registroArea');
    const campoLinea = document.getElementById('registroLinea');
    const campoModelo = document.getElementById('registroModelo');
    const campoProcesoDetectado = document.getElementById('registroProcesoDetectado');
    const campoProcesoGenerado = document.getElementById('registroProcesoGenerado');
    const campoNumeroSeguimiento = document.getElementById('registroNumeroSeguimiento');
    const campoFechaDeteccion = document.getElementById('registroFechaDeteccion');
    const campoTurno = document.getElementById('registroTurno');
    const campoTurnoId = document.getElementById('registroTurnoId');
    const campoNominaRegistra = document.getElementById('registroNominaRegistra');
    const campoNombreRegistra = document.getElementById('registroNombreRegistra');

    /*
     * Identidad de quien tiene la sesión abierta, publicada por index.php.
     * Sirve únicamente para que la persona vea a nombre de quién registra:
     * al guardar, el servidor la vuelve a tomar de $_SESSION e ignora lo que
     * llegue del formulario.
     */
    const USUARIO_SESION = window.USUARIO_SESION || { nomina: '', nombre: '' };

    /* Coloca al usuario de la sesión en un par de campos de identidad. */
    function colocarUsuarioSesion(campoNomina, campoNombre) {
        if (campoNomina) {
            campoNomina.value = USUARIO_SESION.nomina || '';
            campoNomina.readOnly = true;
        }

        if (campoNombre) {
            campoNombre.value = USUARIO_SESION.nombre || '';
            campoNombre.readOnly = true;
        }
    }
    const toastAnormalidades = document.getElementById('toastAnormalidades');
    const toastAnormalidadesIcono = document.getElementById('toastAnormalidadesIcon');
    const toastAnormalidadesTitulo = document.getElementById('toastAnormalidadesTitulo');
    const toastAnormalidadesMensaje = document.getElementById('toastAnormalidadesMensaje');
    const campoDetalle = document.getElementById('registroDetalleAnormalidad');
    const contadorDetalle = document.getElementById('detalleAnormalidadContador');
    const campoRevisionTipoAnormalidad = document.getElementById('revisionTipoAnormalidad');
    const contenedorOtroTipoAnormalidad = document.getElementById('revisionOtroTipoAnormalidadContenedor');
    const campoOtroTipoAnormalidad = document.getElementById('revisionOtroTipoAnormalidad');
    const contenedorRevisionCriticidad = document.getElementById('revisionCriticidadOpciones');
    const contenedorRevisionNivelRespuesta = document.getElementById('revisionNivelRespuestaOpciones');
    const contenedorRevision5m = document.getElementById('revisionClasificacion5mOpciones');
    const contenedorDisposicionCalidad = document.getElementById('cierreCalidadDisposicionOpciones');
    const contenedorResponsableCierre = document.getElementById('cierreResponsableOpciones');
    const botonRegistrarPrimeraRevision = document.getElementById('btnRegistrarPrimeraRevision');
    const contenidoBotonPrimeraRevision = botonRegistrarPrimeraRevision
        ? botonRegistrarPrimeraRevision.innerHTML
        : '';
    const campoAnalisisPERevision = document.getElementById('calidadRevisionEquipo');
    const botonRegistrarAnalisisPE = document.getElementById('btnRegistrarRevisionEquipo');
    const contenidoBotonAnalisisPE = botonRegistrarAnalisisPE
        ? botonRegistrarAnalisisPE.innerHTML
        : '';
    const campoAnalisisMFOperacion = document.getElementById('calidadAnalisisOperacion');
    const botonRegistrarAnalisisMF = document.getElementById('btnRegistrarAnalisisOperacion');
    const contenidoBotonAnalisisMF = botonRegistrarAnalisisMF
        ? botonRegistrarAnalisisMF.innerHTML
        : '';
    const campoConfirmacionQaPiezas = document.getElementById('calidadConfirmacionPiezas');
    const campoCantidadQaPiezas = document.getElementById('calidadCantidadPiezas');
    const campoConfirmacionQaProceso = document.getElementById('calidadConfirmacionProceso');
    const campoCantidadQaProceso = document.getElementById('calidadCantidadProceso');
    const botonRegistrarConfirmacionesQa = document.getElementById('btnRegistrarConfirmacionesQa');
    const contenidoBotonConfirmacionesQa = botonRegistrarConfirmacionesQa
        ? botonRegistrarConfirmacionesQa.innerHTML
        : '';
    const campoCausaAnalisis = document.getElementById('calidadCausa');
    const campoContramedidaAnalisis = document.getElementById('calidadContramedida');
    const botonRegistrarCausaContramedida = document.getElementById('btnRegistrarCausaContramedida');
    const contenidoBotonCausaContramedida = botonRegistrarCausaContramedida
        ? botonRegistrarCausaContramedida.innerHTML
        : '';
    const botonRegistrarDisposicionProducto = document.getElementById('btnRegistrarDisposicionProducto');
    const contenidoBotonDisposicionProducto = botonRegistrarDisposicionProducto
        ? botonRegistrarDisposicionProducto.innerHTML
        : '';
    const camposSerialMaterialAntes = [
        document.getElementById('serialAntes1'),
        document.getElementById('serialAntes2'),
        document.getElementById('serialAntes3')
    ];
    const botonRegistrarRevisionMaterialAntes = document.getElementById('btnRegistrarRevisionMaterialAntes');
    const contenidoBotonRevisionMaterialAntes = botonRegistrarRevisionMaterialAntes
        ? botonRegistrarRevisionMaterialAntes.innerHTML
        : '';
    const campoConfirmacionReinicio = document.getElementById('confirmacionReinicio');
    const campoFechaConfirmacionReinicio = document.getElementById('fechaConfirmacionReinicio');
    const camposSerialReinicio = [
        document.getElementById('serialReinicio1'),
        document.getElementById('serialReinicio2'),
        document.getElementById('serialReinicio3')
    ];
    const campoNominaAprobacionReinicio = document.getElementById('nominaInspectorCalidad');
    const campoNombreAprobacionReinicio = document.getElementById('nombreInspectorCalidad');
    const botonRegistrarReinicioOperacion = document.getElementById('btnRegistrarReinicioOperacion');
    const contenidoBotonReinicioOperacion = botonRegistrarReinicioOperacion
        ? botonRegistrarReinicioOperacion.innerHTML
        : '';
    const campoCierreReincidenteSi = document.getElementById('cierreReincidenteSi');
    const campoCierreReincidenteNo = document.getElementById('cierreReincidenteNo');
    const campoCierreNumeroSeguimiento = document.getElementById('cierreNumeroSeguimiento');
    const contenedorFactoresAtencion = document.getElementById('detalleFactoresAtencion');
    const campoNominaCierreQa = document.getElementById('cierreNominaStaffQa');
    const campoNombreCierreQa = document.getElementById('cierreNombreStaffQa');
    const campoFechaCierreQa = document.getElementById('cierreFechaStaffQa');
    const lienzoFirmaCierreQa = document.getElementById('cierreFirmaStaffQaCanvas');
    const campoFirmaCierreQa = document.getElementById('cierreFirmaStaffQa');
    const botonLimpiarFirmaCierreQa = document.querySelector(
        '[data-signature-clear="cierreFirmaStaffQaCanvas"]'
    );
    const botonRegistrarCierreQa = document.getElementById('btnRegistrarCierreReporteCalidad');
    const contenidoBotonCierreQa = botonRegistrarCierreQa
        ? botonRegistrarCierreQa.innerHTML
        : '';
    const campoNominaCierreGerente = document.getElementById('cierreNominaGerente');
    const campoNombreCierreGerente = document.getElementById('cierreNombreGerente');
    const campoFechaCierreGerente = document.getElementById('cierreFechaGerente');
    const lienzoFirmaCierreGerente = document.getElementById('cierreFirmaGerenteCanvas');
    const campoFirmaCierreGerente = document.getElementById('cierreFirmaGerente');
    const botonLimpiarFirmaCierreGerente = document.querySelector(
        '[data-signature-clear="cierreFirmaGerenteCanvas"]'
    );
    const botonRegistrarCierreGerente = document.getElementById('btnRegistrarAutorizacionGerente');
    const contenidoBotonCierreGerente = botonRegistrarCierreGerente
        ? botonRegistrarCierreGerente.innerHTML
        : '';
    const vistaAnormalidades = document.getElementById('anormalidadesView');
    const modalDetalle = document.getElementById('modalDetalleAnormalidad');
    const botonTabApertura = document.getElementById('tabAperturaButton');
    const botonTabAnalisis = document.getElementById('tabSeguimientoButton');
    const botonTabCierreCalidad = document.getElementById('tabCierreCalidadButton');
    const modalReincidencia = document.getElementById('modalNuevaReincidencia');
    const modalListadoReincidencias = document.getElementById('modalListadoReincidencias');
    const formularioReincidencia = document.getElementById('formRegistroReincidencia');
    const campoReincidenciaRegistro = document.getElementById('reincidenciaRegistroBase');
    const campoReincidenciaSeguimiento = document.getElementById('reincidenciaNumeroSeguimiento');
    const campoReincidenciaFechaHora = document.getElementById('reincidenciaFechaHora');
    const campoReincidenciaTurno = document.getElementById('reincidenciaTurno');
    const campoReincidenciaTipo = document.getElementById('reincidenciaTipo');
    const campoReincidenciaTipoClave = document.getElementById('reincidenciaTipoClave');
    const campoReincidenciaArea = document.getElementById('reincidenciaArea');
    const campoReincidenciaLinea = document.getElementById('reincidenciaLinea');
    const campoReincidenciaDetalle = document.getElementById('reincidenciaAnormalidad');
    const botonGuardarReincidencia = document.getElementById('btnGuardarReincidencia');
    const contenidoBotonGuardarReincidencia = botonGuardarReincidencia
        ? botonGuardarReincidencia.innerHTML
        : '';
    const formularioSeguimientoReincidencia = document.getElementById('formSeguimientoReincidencia');
    const campoFechaSeguimientoReincidencia = document.getElementById('seguimientoReincidenciaFechaHora');
    const campoCantidadSeguimientoReincidencia = document.getElementById('seguimientoReincidenciaCantidad');
    const contenedorDisposicionSeguimientoReincidencia = document.getElementById('seguimientoReincidenciaDisposicionOpciones');
    const botonRegistrarDatosSeguimientoReincidencia =
        document.getElementById('btnRegistrarDatosSeguimientoReincidencia');
    const contenidoBotonDatosSeguimientoReincidencia =
        botonRegistrarDatosSeguimientoReincidencia
            ? botonRegistrarDatosSeguimientoReincidencia.innerHTML
            : '';
    const campoCausaSeguimientoReincidencia = document.getElementById('seguimientoReincidenciaCausa');
    const campoContramedidaSeguimientoReincidencia =
        document.getElementById('seguimientoReincidenciaContramedida');
    const campoNominaAtiendeReincidencia = document.getElementById('seguimientoNominaResponsable');
    const campoNombreAtiendeReincidencia = document.getElementById('seguimientoNombreResponsable');
    const botonRegistrarCausaContramedidaReincidencia =
        document.getElementById('btnRegistrarCausaContramedidaReincidencia');
    const contenidoBotonCausaContramedidaReincidencia =
        botonRegistrarCausaContramedidaReincidencia
            ? botonRegistrarCausaContramedidaReincidencia.innerHTML
            : '';
    const campoNominaInspectorQaReincidencia = document.getElementById('seguimientoNominaInspector');
    const campoNombreInspectorQaReincidencia = document.getElementById('seguimientoNombreInspector');
    const botonRegistrarConfirmacionQaReincidencia =
        document.getElementById('btnRegistrarConfirmacionQaReincidencia');
    const contenidoBotonConfirmacionQaReincidencia =
        botonRegistrarConfirmacionQaReincidencia
            ? botonRegistrarConfirmacionQaReincidencia.innerHTML
            : '';
    const tablaReincidencias = document.getElementById('tablaReincidencias');
    const botonAgregarReincidencia = document.getElementById('btnAgregarReincidencia');
    const contenidoBotonAgregarReincidencia = botonAgregarReincidencia
        ? botonAgregarReincidencia.innerHTML
        : '';
    const detalleBaseReincidencias = document.getElementById('detalleBaseReincidencias');
    const botonDetalleBaseReincidencias = modalListadoReincidencias
        ? modalListadoReincidencias.querySelector('.anomaly-recurrence-base-toggle')
        : null;
    const lienzosFirma = document.querySelectorAll('[data-signature-canvas]');
    const rutaCatalogos = './api/operacionesAnormalidades.php';
    let tokenTransicionReincidencia = 0;
    let tokenReferenciaSeguimiento = 0;
    let tokenConsultaDetalle = 0;
    let tokenConsultaPrimeraRevision = 0;
    let tokenConsultaAnalisis = 0;
    let tokenConsultaCierreCalidad = 0;
    let tokenConsultaReincidenciaCierre = 0;
    let tokenConsultaListadoReincidencias = 0;
    let tokenConsultaListadoAnormalidades = 0;
    let tablaAnormalidades = null;
    let promesaCatalogosPrimeraRevision = null;
    const selectorBotonesEscrituraAnormalidad = [
        '#btnRegistrarPrimeraRevision',
        '#btnRegistrarRevisionEquipo',
        '#btnRegistrarAnalisisOperacion',
        '#btnRegistrarConfirmacionesQa',
        '#btnRegistrarCausaContramedida',
        '#btnRegistrarDisposicionProducto',
        '#btnRegistrarRevisionMaterialAntes',
        '#btnRegistrarReinicioOperacion',
        '#btnRegistrarCierreReporteCalidad',
        '#btnRegistrarAutorizacionGerente',
        '#btnGuardarReincidencia',
        '#btnAgregarReincidencia',
        '#btnRegistrarDatosSeguimientoReincidencia',
        '#btnRegistrarCausaContramedidaReincidencia',
        '#btnRegistrarConfirmacionQaReincidencia'
    ].join(',');

    /**
     * Indica si el registro abierto fue marcado como finalizado por el servidor.
     */
    function anormalidadEstaFinalizada(contenedor) {
        return Boolean(contenedor && contenedor.dataset.anormalidadFinalizada === 'true');
    }

    /**
     * Conserva el estado previo de cada control y bloquea solamente acciones de
     * captura. La consulta, las pestañas y la navegación continúan disponibles.
     */
    function aplicarBloqueoAnormalidadFinalizada(contenedor, finalizada) {
        if (!contenedor) {
            return;
        }

        contenedor.dataset.anormalidadFinalizada = finalizada ? 'true' : 'false';

        const controles = contenedor.querySelectorAll('input, select, textarea');
        const botones = contenedor.querySelectorAll(selectorBotonesEscrituraAnormalidad);
        const lienzos = contenedor.querySelectorAll('[data-signature-canvas]');

        controles.forEach(function (control) {
            if (finalizada) {
                if (control.dataset.finalLockDisabled === undefined) {
                    control.dataset.finalLockDisabled = control.disabled ? 'true' : 'false';
                    control.dataset.finalLockReadonly = control.readOnly ? 'true' : 'false';
                }

                if (control.matches('select, input[type="radio"], input[type="checkbox"]')) {
                    control.disabled = true;
                } else {
                    control.readOnly = true;
                }
                return;
            }

            if (control.dataset.finalLockDisabled !== undefined) {
                control.disabled = control.dataset.finalLockDisabled === 'true';
                control.readOnly = control.dataset.finalLockReadonly === 'true';
                delete control.dataset.finalLockDisabled;
                delete control.dataset.finalLockReadonly;
            }
        });

        botones.forEach(function (boton) {
            if (finalizada) {
                if (boton.dataset.finalLockDisabled === undefined) {
                    boton.dataset.finalLockDisabled = boton.disabled ? 'true' : 'false';
                    boton.dataset.finalLockTitle = boton.getAttribute('title') || '';
                }

                boton.disabled = true;
                boton.title = 'La anormalidad está finalizada';
                return;
            }

            if (boton.dataset.finalLockDisabled !== undefined) {
                boton.disabled = boton.dataset.finalLockDisabled === 'true';
                boton.title = boton.dataset.finalLockTitle || '';
                delete boton.dataset.finalLockDisabled;
                delete boton.dataset.finalLockTitle;
            }
        });

        lienzos.forEach(function (lienzo) {
            if (finalizada) {
                if (lienzo.dataset.finalLockPointerEvents === undefined) {
                    lienzo.dataset.finalLockPointerEvents = lienzo.style.pointerEvents || '';
                    lienzo.dataset.finalLockTabIndex = String(lienzo.tabIndex);
                    lienzo.dataset.finalLockAriaDisabled =
                        lienzo.getAttribute('aria-disabled') || '';
                }

                lienzo.style.pointerEvents = 'none';
                lienzo.setAttribute('aria-disabled', 'true');
                lienzo.tabIndex = -1;
                return;
            }

            if (lienzo.dataset.finalLockPointerEvents !== undefined) {
                lienzo.style.pointerEvents = lienzo.dataset.finalLockPointerEvents;
                lienzo.tabIndex = Number(lienzo.dataset.finalLockTabIndex);

                if (lienzo.dataset.finalLockAriaDisabled) {
                    lienzo.setAttribute(
                        'aria-disabled',
                        lienzo.dataset.finalLockAriaDisabled
                    );
                } else {
                    lienzo.removeAttribute('aria-disabled');
                }

                delete lienzo.dataset.finalLockPointerEvents;
                delete lienzo.dataset.finalLockTabIndex;
                delete lienzo.dataset.finalLockAriaDisabled;
            }
        });
    }

    /**
     * Sincroniza la interfaz cuando una lectura o una escritura informa que la
     * anormalidad alcanzó el estatus final (CLAVE 6).
     */
    function sincronizarAnormalidadFinalizada(estatus) {
        if (!estatus || String(estatus.clave_estatus || '').trim() !== '6') {
            return;
        }

        const idAnormalidad = String(estatus.id_anormalidad || '');

        actualizarEstatusFilaAnormalidad(idAnormalidad, estatus);

        if (modalDetalle && modalDetalle.dataset.idAnormalidad === idAnormalidad) {
            aplicarBloqueoAnormalidadFinalizada(modalDetalle, true);
        }

        if (modalListadoReincidencias &&
            modalListadoReincidencias.dataset.registroId === idAnormalidad) {
            aplicarBloqueoAnormalidadFinalizada(modalListadoReincidencias, true);
        }

        if (modalReincidencia && campoReincidenciaRegistro &&
            campoReincidenciaRegistro.value === idAnormalidad) {
            aplicarBloqueoAnormalidadFinalizada(modalReincidencia, true);
        }
    }

    /**
     * Reinicia un select y deja un mensaje que orienta al usuario.
     */
    function reiniciarSelect(campo, mensaje, deshabilitado) {
        if (!campo) {
            return;
        }

        campo.innerHTML = '';
        campo.appendChild(new Option(mensaje, '', true, true));
        campo.options[0].disabled = true;
        campo.disabled = deshabilitado;
    }

    /**
     * Consulta un catálogo mediante POST y valida la forma básica del JSON.
     */
    async function consultarOperacionAnormalidad(opcion, parametros) {
        const datos = new URLSearchParams();
        datos.set('opcion', opcion);

        Object.keys(parametros || {}).forEach(function (nombre) {
            datos.set(nombre, parametros[nombre]);
        });

        const respuesta = await fetch(rutaCatalogos, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: datos.toString()
        });
        const resultado = await respuesta.json();

        if (!respuesta.ok || resultado.status !== 'ok') {
            if (resultado.codigo === 'ANORMALIDAD_FINALIZADA') {
                sincronizarAnormalidadFinalizada(resultado.data);

                /*
                 * Algunos formularios restauran su botón en finally. Se vuelve
                 * a aplicar el bloqueo al terminar ese ciclo de la promesa.
                 */
                window.setTimeout(function () {
                    sincronizarAnormalidadFinalizada(resultado.data);
                }, 0);
            }

            const errorOperacion = new Error(
                resultado.mensaje || 'Respuesta inválida del servidor.'
            );
            errorOperacion.estadoHttp = respuesta.status;
            errorOperacion.respuesta = resultado;
            throw errorOperacion;
        }

        return resultado.data;
    }

    /**
     * Mantiene la validación específica para las operaciones que son catálogos.
     */
    async function consultarCatalogo(opcion, parametros) {
        const datos = await consultarOperacionAnormalidad(opcion, parametros);

        if (!Array.isArray(datos)) {
            throw new Error('El catálogo recibido no tiene el formato esperado.');
        }

        return datos;
    }

    /**
     * Muestra una notificación uniforme para las operaciones del módulo.
     * El detalle técnico solo se escribe en consola cuando la operación falla.
     */
    function mostrarToastAnormalidad(tipo, titulo, mensaje, detalleError) {
        if (detalleError !== undefined && detalleError !== null) {
            console.error('[Anormalidades] ' + titulo, {
                mensaje: mensaje,
                detalle: detalleError
            });
        }

        if (!toastAnormalidades || !toastAnormalidadesIcono ||
            !toastAnormalidadesTitulo || !toastAnormalidadesMensaje) {
            return;
        }

        const configuraciones = {
            success: { clase: 'anomaly-toast--success', icono: 'bi-check-lg' },
            error: { clase: 'anomaly-toast--error', icono: 'bi-x-octagon' },
            warning: { clase: 'anomaly-toast--warning', icono: 'bi-exclamation-triangle' },
            info: { clase: 'anomaly-toast--info', icono: 'bi-info-lg' }
        };
        const configuracion = configuraciones[tipo] || configuraciones.info;

        toastAnormalidades.classList.remove(
            'anomaly-toast--success',
            'anomaly-toast--error',
            'anomaly-toast--warning',
            'anomaly-toast--info'
        );
        toastAnormalidades.classList.add(configuracion.clase);
        toastAnormalidadesIcono.className = 'bi ' + configuracion.icono;
        toastAnormalidadesTitulo.textContent = titulo;
        toastAnormalidadesMensaje.textContent = mensaje;
        toastAnormalidades.setAttribute('aria-live', tipo === 'error' ? 'assertive' : 'polite');

        if (window.bootstrap && window.bootstrap.Toast) {
            window.bootstrap.Toast.getOrCreateInstance(toastAnormalidades, {
                delay: 4200,
                autohide: true
            }).show();
            return;
        }

        // Respaldo visual si Bootstrap todavía no está disponible.
        toastAnormalidades.classList.add('show');
        window.setTimeout(function () {
            toastAnormalidades.classList.remove('show');
        }, 4200);
    }

    /**
     * Devuelve una referencia legible del consecutivo asociado al modal.
     * Si el detalle aún no carga, evita exponer el ID interno del registro.
     */
    function obtenerReferenciaConsecutivoActual() {
        const consecutivo = modalDetalle
            ? String(modalDetalle.dataset.consecutivo || '').trim()
            : '';

        return consecutivo ? 'No. ' + consecutivo : 'seleccionada';
    }

    /**
     * Restablece únicamente el formulario de apertura después de que el
     * backend confirma el registro. En caso de error se conservan los datos.
     */
    function reiniciarFormularioNuevaAnormalidad() {
        if (!formularioRegistroInicial) {
            return;
        }

        formularioRegistroInicial.reset();
        formularioRegistroInicial.classList.remove('was-validated');
        formularioRegistroInicial.querySelectorAll('.is-invalid, .is-valid, [aria-invalid]').forEach(function (campo) {
            campo.classList.remove('is-invalid', 'is-valid');
            campo.removeAttribute('aria-invalid');
        });

        /* reset() vacía los campos, así que la identidad se repone después.
           Ya no se consulta el padrón: quien registra es quien tiene la
           sesión abierta. */
        colocarUsuarioSesion(campoNominaRegistra, campoNombreRegistra);

        if (contadorDetalle) {
            contadorDetalle.textContent = '0 / 500';
        }

        // Al quedar el área vacía, esta función deshabilita línea y estaciones.
        cargarLineasPorArea();
    }

    /**
     * Cierra el modal y limpia sus campos solamente tras un registro exitoso.
     */
    function cerrarModalNuevaAnormalidad() {
        if (modalRegistro && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalRegistro).hide();
        }

        reiniciarFormularioNuevaAnormalidad();
    }

    /**
     * Devuelve el texto visible de la opción seleccionada.
     */
    function obtenerTextoSelect(campo) {
        if (!campo || campo.selectedIndex < 0) {
            return '';
        }

        return campo.options[campo.selectedIndex].textContent.trim();
    }

    /**
     * Valida y envía el registro inicial a la API de anormalidades.
     * No. seguimiento se incluye como referencia, pero no es obligatorio
     * porque su valor definitivo se generará posteriormente en el backend.
     */
    async function registroNuevaAnormalidad() {
        if (!formularioRegistroInicial) {
            return null;
        }

        const camposObligatorios = [
            { nombre: 'fecha_deteccion', etiqueta: 'Fecha y hora de detección' },
            { nombre: 'turno', etiqueta: 'Turno', campoVisual: campoTurno },
            { nombre: 'id_area', etiqueta: 'Área' },
            { nombre: 'codigo_linea', etiqueta: 'Línea' },
            { nombre: 'detalle_anormalidad', etiqueta: 'Detalles de la anormalidad' },
            { nombre: 'numero_lote', etiqueta: 'No. lote' },
            { nombre: 'numero_serial', etiqueta: 'No. serial' },
            { nombre: 'cantidad', etiqueta: 'Cantidad' },
            { nombre: 'id_estacion_deteccion', etiqueta: 'Estación donde se detectó' },
            { nombre: 'id_estacion_generacion', etiqueta: 'Estación donde se generó' },
            { nombre: 'nomina_registra', etiqueta: 'Nómina de quien registra' },
            {
                nombre: 'nombre_registra',
                etiqueta: 'Nombre de quien registra',
                campoVisual: campoNombreRegistra
            }
        ];
        const camposFaltantes = [];
        let primerCampoInvalido = null;

        camposObligatorios.forEach(function (configuracion) {
            const campo = formularioRegistroInicial.elements.namedItem(configuracion.nombre);
            const campoVisual = configuracion.campoVisual || campo;
            const valor = campo ? String(campo.value || '').trim() : '';
            const incumpleRestricciones = campo && typeof campo.checkValidity === 'function'
                ? !campo.checkValidity()
                : false;
            const esInvalido = !campo || !valor || incumpleRestricciones;

            if (campoVisual) {
                campoVisual.classList.toggle('is-invalid', esInvalido);
                campoVisual.setAttribute('aria-invalid', esInvalido ? 'true' : 'false');
            }

            if (esInvalido) {
                camposFaltantes.push(configuracion.etiqueta);

                if (!primerCampoInvalido && campoVisual && !campoVisual.disabled) {
                    primerCampoInvalido = campoVisual;
                }
            }
        });

        if (camposFaltantes.length) {
            mostrarToastAnormalidad(
                'warning',
                'Datos incompletos',
                'Revisa: ' + camposFaltantes.join(', ') + '.',
                { camposFaltantes: camposFaltantes }
            );

            if (primerCampoInvalido) {
                primerCampoInvalido.focus();
            }

            return null;
        }

        const obtenerValor = function (nombre) {
            return String(formularioRegistroInicial.elements.namedItem(nombre).value || '').trim();
        };
        const valoresCapturados = {
            numero_seguimiento: obtenerValor('numero_seguimiento') || 'Se generará en el backend',
            fecha_deteccion: obtenerValor('fecha_deteccion'),
            turno: {
                id: obtenerValor('turno'),
                nombre: obtenerTextoSelect(campoTurno)
            },
            area: {
                id: obtenerValor('id_area'),
                nombre: obtenerTextoSelect(campoArea)
            },
            linea: {
                codigo: obtenerValor('codigo_linea'),
                nombre: obtenerTextoSelect(campoLinea)
            },
            // Opcional: vacío si la línea no tiene modelos o no se eligió uno.
            modelo: {
                id: campoModelo ? obtenerValor('id_modelo') : '',
                nombre: obtenerTextoSelect(campoModelo)
            },
            detalle_anormalidad: obtenerValor('detalle_anormalidad'),
            numero_lote: obtenerValor('numero_lote'),
            numero_serial: obtenerValor('numero_serial'),
            cantidad: obtenerValor('cantidad'),
            estacion_deteccion: {
                id: obtenerValor('id_estacion_deteccion'),
                nombre: obtenerTextoSelect(campoProcesoDetectado)
            },
            estacion_generacion: {
                id: obtenerValor('id_estacion_generacion'),
                nombre: obtenerTextoSelect(campoProcesoGenerado)
            },
            nomina_registra: obtenerValor('nomina_registra'),
            nombre_registra: obtenerValor('nombre_registra')
        };

        const datosRegistro = {
            idArea: valoresCapturados.area.id,
            codigoLinea: valoresCapturados.linea.codigo,
            idModelo: valoresCapturados.modelo.id,
            detalleAnormalidad: valoresCapturados.detalle_anormalidad,
            numeroLote: valoresCapturados.numero_lote,
            numeroSerial: valoresCapturados.numero_serial,
            cantidad: valoresCapturados.cantidad,
            idEstacionDeteccion: valoresCapturados.estacion_deteccion.id,
            idEstacionGeneracion: valoresCapturados.estacion_generacion.id,
            nominaRegistra: valoresCapturados.nomina_registra
        };
        const contenidoOriginalBoton = botonGuardarRegistroInicial
            ? botonGuardarRegistroInicial.innerHTML
            : '';

        if (botonGuardarRegistroInicial) {
            botonGuardarRegistroInicial.disabled = true;
            botonGuardarRegistroInicial.setAttribute('aria-busy', 'true');
            botonGuardarRegistroInicial.innerHTML =
                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';
        }

        try {
            const registroCreado = await consultarOperacionAnormalidad('1', datosRegistro);

            if (campoNumeroSeguimiento && registroCreado.no_seguimiento) {
                campoNumeroSeguimiento.value = registroCreado.no_seguimiento;
            }

            mostrarToastAnormalidad(
                'success',
                'Anormalidad registrada',
                'El registro ' + registroCreado.no_seguimiento + ' se guardó correctamente.'
            );

            cerrarModalNuevaAnormalidad();
            cargarListadoAnormalidades();

            return registroCreado;
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la anormalidad',
                error.message || 'Ocurrió un error al guardar la información.',
                {
                    estadoHttp: error.estadoHttp || null,
                    respuesta: error.respuesta || null,
                    error: error
                }
            );

            return null;
        } finally {
            if (botonGuardarRegistroInicial) {
                botonGuardarRegistroInicial.disabled = false;
                botonGuardarRegistroInicial.removeAttribute('aria-busy');
                botonGuardarRegistroInicial.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Agrega las opciones recibidas sin insertar HTML procedente de la API.
     */
    function llenarSelect(campo, registros, valor, texto, mensajeInicial, claveCatalogo) {
        reiniciarSelect(campo, mensajeInicial, false);

        registros.forEach(function (registro) {
            const opcion = new Option(registro[texto], registro[valor]);

            if (claveCatalogo && registro[claveCatalogo] !== undefined &&
                registro[claveCatalogo] !== null) {
                opcion.dataset.clave = String(registro[claveCatalogo]).trim();
            }

            campo.appendChild(opcion);
        });
    }

    /**
     * Llena un filtro conservando una primera opción vacía y seleccionable.
     * A diferencia de los formularios, "Todas" representa un criterio válido.
     */
    function llenarSelectFiltro(campo, registros, valor, texto, etiquetaTodos) {
        if (!campo) {
            return;
        }

        campo.innerHTML = '';
        campo.appendChild(new Option(etiquetaTodos, '', true, true));

        registros.forEach(function (registro) {
            campo.appendChild(new Option(registro[texto], registro[valor]));
        });

        campo.disabled = false;
    }

    /**
     * Coloca un estado sencillo dentro de los grupos dinámicos de la revisión.
     */
    function mostrarEstadoCatalogo(contenedor, mensaje) {
        if (!contenedor) {
            return;
        }

        contenedor.innerHTML = '';
        const texto = document.createElement('span');
        texto.className = 'anomaly-form-hint';
        texto.textContent = mensaje;
        contenedor.appendChild(texto);
    }

    /**
     * Genera radios a partir del catálogo, usando el IDe como valor de registro.
     */
    function llenarRadiosCatalogo(contenedor, registros, configuracion) {
        contenedor.innerHTML = '';

        if (!registros.length) {
            mostrarEstadoCatalogo(contenedor, 'No hay opciones activas disponibles.');
            return;
        }

        registros.forEach(function (registro) {
            const idControl = configuracion.prefijoId + String(registro.id);
            const entrada = document.createElement('input');
            const etiqueta = document.createElement('label');

            entrada.type = 'radio';
            entrada.className = 'btn-check';
            entrada.name = configuracion.nombreCampo;
            entrada.id = idControl;
            entrada.value = registro.id;
            entrada.autocomplete = 'off';
            entrada.required = true;

            etiqueta.className = 'btn ' + configuracion.obtenerClase(registro);
            etiqueta.htmlFor = idControl;

            if (typeof configuracion.llenarEtiqueta === 'function') {
                configuracion.llenarEtiqueta(registro, etiqueta);
            } else {
                etiqueta.textContent = registro.nombre;
            }

            if (registro.descripcion) {
                etiqueta.title = registro.descripcion;
            }

            contenedor.appendChild(entrada);
            contenedor.appendChild(etiqueta);
        });
    }

    /**
     * Genera las opciones múltiples del catálogo 5M sin insertar HTML recibido.
     */
    function llenarClasificaciones5m(registros) {
        contenedorRevision5m.innerHTML = '';

        if (!registros.length) {
            mostrarEstadoCatalogo(contenedorRevision5m, 'No hay clasificaciones 5M activas.');
            return;
        }

        registros.forEach(function (registro) {
            const idControl = 'revision5m' + String(registro.id);
            const etiqueta = document.createElement('label');
            const entrada = document.createElement('input');
            const contenido = document.createElement('span');
            const nombre = document.createElement('strong');
            const descripcion = document.createElement('small');

            etiqueta.className = 'anomaly-disposition-option';
            etiqueta.htmlFor = idControl;
            entrada.className = 'form-check-input';
            entrada.type = 'checkbox';
            entrada.id = idControl;
            entrada.name = 'revision_4m[]';
            entrada.value = registro.id;
            entrada.dataset.esOtro = ['otro', 'otros'].indexOf(
                String(registro.nombre || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim()
            ) !== -1 ? 'true' : 'false';
            nombre.textContent = registro.nombre;
            descripcion.textContent = registro.descripcion || 'Clasificación del catálogo 5M.';

            contenido.appendChild(nombre);
            contenido.appendChild(descripcion);
            etiqueta.appendChild(entrada);
            etiqueta.appendChild(contenido);
            contenedorRevision5m.appendChild(etiqueta);
        });
    }

    /**
     * Genera tarjetas de disposición reutilizables para los distintos flujos.
     */
    function llenarDisposicionesCatalogo(contenedor, registros, configuracion) {
        if (!contenedor) {
            return;
        }

        contenedor.innerHTML = '';

        if (!registros.length) {
            mostrarEstadoCatalogo(contenedor, 'No hay disposiciones activas disponibles.');
            return;
        }

        registros.forEach(function (registro) {
            const idControl = configuracion.prefijoId + String(registro.id);
            const etiqueta = document.createElement('label');
            const entrada = document.createElement('input');
            const contenido = document.createElement('span');
            const nombre = document.createElement('strong');
            const descripcion = document.createElement('small');

            etiqueta.className = 'anomaly-disposition-option';
            etiqueta.htmlFor = idControl;
            entrada.className = 'form-check-input';
            entrada.type = 'radio';
            entrada.id = idControl;
            entrada.name = configuracion.nombreCampo;
            entrada.value = registro.id;
            entrada.required = true;
            nombre.textContent = registro.nombre;
            descripcion.textContent = registro.descripcion || 'Disposición aplicable al producto o material afectado.';

            contenido.appendChild(nombre);
            contenido.appendChild(descripcion);
            etiqueta.appendChild(entrada);
            etiqueta.appendChild(contenido);
            contenedor.appendChild(etiqueta);
        });
    }

    function llenarDisposicionesCalidad(registros) {
        llenarDisposicionesCatalogo(contenedorDisposicionCalidad, registros, {
            prefijoId: 'cierreCalidadDisposicion',
            nombreCampo: 'disposicion_producto'
        });
    }

    function llenarDisposicionesSeguimientoReincidencia(registros) {
        llenarDisposicionesCatalogo(contenedorDisposicionSeguimientoReincidencia, registros, {
            prefijoId: 'seguimientoReincidenciaDisposicion',
            nombreCampo: 'seguimiento_reincidencia_disposicion'
        });
    }

    /**
     * Muestra los factores como responsables del cierre usando el generador
     * de radios compartido con criticidad y nivel de respuesta.
     */
    function llenarResponsablesCierre(registros) {
        if (!contenedorResponsableCierre) {
            return;
        }

        llenarRadiosCatalogo(contenedorResponsableCierre, registros, {
            prefijoId: 'cierreResponsableFactor',
            nombreCampo: 'cierre_responsable',
            obtenerClase: function () {
                return 'anomaly-responsible-button';
            },
            llenarEtiqueta: function (registro, etiqueta) {
                const clave = document.createElement('strong');
                const nombre = document.createElement('small');

                clave.textContent = registro.clave;
                nombre.textContent = registro.nombre;
                etiqueta.appendChild(clave);
                etiqueta.appendChild(nombre);
            }
        });
    }

    /**
     * Carga el catálogo y fija el tipo con CLAVE 1. El select permanece
     * bloqueado y tanto el select como el campo oculto conservan la CLAVE.
     */
    function llenarTipoReincidencia(registros) {
        if (!campoReincidenciaTipo || !campoReincidenciaTipoClave) {
            return false;
        }

        reiniciarSelect(campoReincidenciaTipo, 'Tipo no disponible', true);
        campoReincidenciaTipoClave.value = '';
        campoReincidenciaTipoClave.defaultValue = '';

        registros.forEach(function (registro) {
            const clave = String(registro.clave ?? '').trim();
            const opcion = new Option(registro.nombre, clave);
            opcion.dataset.clave = clave;

            if (registro.descripcion) {
                opcion.title = registro.descripcion;
            }

            campoReincidenciaTipo.appendChild(opcion);
        });

        const opcionReincidente = Array.from(campoReincidenciaTipo.options).find(function (opcion) {
            return opcion.dataset.clave === '1';
        });

        if (!opcionReincidente) {
            return false;
        }

        Array.from(campoReincidenciaTipo.options).forEach(function (opcion) {
            opcion.defaultSelected = false;
            opcion.selected = false;
        });
        opcionReincidente.selected = true;
        opcionReincidente.defaultSelected = true;
        campoReincidenciaTipo.value = opcionReincidente.value;
        campoReincidenciaTipoClave.value = opcionReincidente.value;
        campoReincidenciaTipoClave.defaultValue = opcionReincidente.value;
        return true;
    }

    /**
     * Carga de forma independiente los tipos activos de reincidencia.
     */
    async function cargarTiposReincidencia() {
        if (!campoReincidenciaTipo || !campoReincidenciaTipoClave) {
            return false;
        }

        reiniciarSelect(campoReincidenciaTipo, 'Cargando tipo...', true);
        campoReincidenciaTipoClave.value = '';

        if (botonGuardarReincidencia) {
            botonGuardarReincidencia.disabled = true;
        }

        try {
            const tipos = await consultarCatalogo('26');

            if (!llenarTipoReincidencia(tipos)) {
                throw new Error('No existe un tipo activo con CLAVE 1.');
            }

            if (botonGuardarReincidencia) {
                botonGuardarReincidencia.disabled = false;
            }
            return true;
        } catch (error) {
            reiniciarSelect(campoReincidenciaTipo, 'No fue posible cargar el tipo', true);
            campoReincidenciaTipoClave.value = '';
            mostrarToastAnormalidad(
                'error',
                'No se cargó el tipo de reincidencia',
                error.message || 'No fue posible consultar el catálogo correspondiente.',
                error
            );
            return false;
        }
    }

    /**
     * Obtiene en una sola petición los catálogos utilizados por el flujo.
     * Esta función se ejecuta al cargar la vista, no cada vez que se abre el modal.
     */
    async function cargarCatalogosPrimeraRevision() {
        if (!campoRevisionTipoAnormalidad || !contenedorRevisionCriticidad ||
            !contenedorRevisionNivelRespuesta || !contenedorRevision5m ||
            !contenedorDisposicionCalidad || !contenedorResponsableCierre ||
            !contenedorDisposicionSeguimientoReincidencia) {
            return;
        }

        reiniciarSelect(campoRevisionTipoAnormalidad, 'Cargando clasificaciones...', true);
        mostrarEstadoCatalogo(contenedorRevisionCriticidad, 'Cargando criticidad...');
        mostrarEstadoCatalogo(contenedorRevisionNivelRespuesta, 'Cargando niveles...');
        mostrarEstadoCatalogo(contenedorRevision5m, 'Cargando clasificación 5M...');
        mostrarEstadoCatalogo(contenedorDisposicionCalidad, 'Cargando disposiciones...');
        mostrarEstadoCatalogo(contenedorDisposicionSeguimientoReincidencia, 'Cargando disposiciones...');
        mostrarEstadoCatalogo(contenedorResponsableCierre, 'Cargando responsables...');
        reiniciarSelect(campoFiltroFactorGerente, 'Cargando responsables...', true);

        try {
            const catalogos = await consultarOperacionAnormalidad('9');

            if (!catalogos || !Array.isArray(catalogos.tipos_anormalidad) ||
                !Array.isArray(catalogos.criticidades) ||
                !Array.isArray(catalogos.niveles_respuesta) ||
                !Array.isArray(catalogos.clasificaciones_5m) ||
                !Array.isArray(catalogos.disposiciones) ||
                !Array.isArray(catalogos.factores)) {
                throw new Error('Los catálogos recibidos no tienen el formato esperado.');
            }

            if (catalogos.tipos_anormalidad.length) {
                llenarSelect(
                    campoRevisionTipoAnormalidad,
                    catalogos.tipos_anormalidad,
                    'id',
                    'nombre',
                    'Selecciona una clasificación',
                    'clave'
                );
                actualizarCampoOtroTipoAnormalidad(true);
            } else {
                reiniciarSelect(campoRevisionTipoAnormalidad, 'No hay clasificaciones activas', true);
            }

            llenarRadiosCatalogo(contenedorRevisionCriticidad, catalogos.criticidades, {
                prefijoId: 'revisionCriticidad',
                nombreCampo: 'revision_criticidad',
                obtenerClase: function (registro) {
                    const nombre = String(registro.nombre || '').toLowerCase();
                    return nombre.indexOf('no ') === 0
                        ? 'btn-outline-secondary'
                        : 'btn-outline-danger';
                }
            });
            llenarRadiosCatalogo(contenedorRevisionNivelRespuesta, catalogos.niveles_respuesta, {
                prefijoId: 'revisionNivelRespuesta',
                nombreCampo: 'revision_nivel_respuesta',
                obtenerClase: function () {
                    return 'btn-outline-primary';
                }
            });
            llenarClasificaciones5m(catalogos.clasificaciones_5m);
            llenarDisposicionesCalidad(catalogos.disposiciones);
            llenarDisposicionesSeguimientoReincidencia(catalogos.disposiciones);
            llenarResponsablesCierre(catalogos.factores);
            llenarSelectFiltro(
                campoFiltroFactorGerente,
                catalogos.factores,
                'id',
                'nombre',
                'Todos'
            );
            return true;
        } catch (error) {
            reiniciarSelect(campoRevisionTipoAnormalidad, 'No fue posible cargar el catálogo', true);
            mostrarEstadoCatalogo(contenedorRevisionCriticidad, 'No fue posible cargar las opciones.');
            mostrarEstadoCatalogo(contenedorRevisionNivelRespuesta, 'No fue posible cargar las opciones.');
            mostrarEstadoCatalogo(contenedorRevision5m, 'No fue posible cargar las opciones.');
            mostrarEstadoCatalogo(contenedorDisposicionCalidad, 'No fue posible cargar las disposiciones.');
            mostrarEstadoCatalogo(contenedorDisposicionSeguimientoReincidencia, 'No fue posible cargar las disposiciones.');
            mostrarEstadoCatalogo(contenedorResponsableCierre, 'No fue posible cargar los responsables.');
            llenarSelectFiltro(campoFiltroFactorGerente, [], 'id', 'nombre', 'Todos');
            mostrarToastAnormalidad(
                'error',
                'No se cargaron los catálogos',
                error.message || 'No fue posible consultar los catálogos.',
                error
            );
            return false;
        }
    }

    /**
     * Mantiene "Otros" como una opción exclusiva dentro de la clasificación 5M.
     */
    function actualizarExclusionClasificacion5m(entradaCambiada) {
        if (!contenedorRevision5m || contenedorRevision5m.dataset.soloLectura === 'true') {
            return;
        }

        const entradas = Array.from(
            contenedorRevision5m.querySelectorAll('input[name="revision_4m[]"]')
        );
        const entradaOtro = entradas.find(function (entrada) {
            return entrada.dataset.esOtro === 'true';
        });
        const entradasRegulares = entradas.filter(function (entrada) {
            return entrada.dataset.esOtro !== 'true';
        });

        if (!entradaOtro) {
            return;
        }

        if (entradaCambiada && entradaCambiada.dataset.esOtro === 'true' && entradaCambiada.checked) {
            entradasRegulares.forEach(function (entrada) {
                entrada.checked = false;
            });
        } else if (entradaCambiada && entradaCambiada.checked) {
            entradaOtro.checked = false;
        }

        const hayClasificacionRegular = entradasRegulares.some(function (entrada) {
            return entrada.checked;
        });

        entradaOtro.disabled = hayClasificacionRegular;
        entradasRegulares.forEach(function (entrada) {
            entrada.disabled = entradaOtro.checked;
        });
    }

    /**
     * El IDe del catálogo puede cambiar entre bases. Por eso la visibilidad se
     * decide con la CLAVE 20 almacenada como data-clave en la opción.
     */
    function actualizarCampoOtroTipoAnormalidad(limpiarAlOcultar) {
        if (!campoRevisionTipoAnormalidad || !contenedorOtroTipoAnormalidad ||
            !campoOtroTipoAnormalidad) {
            return false;
        }

        const opcionSeleccionada = campoRevisionTipoAnormalidad.selectedOptions[0];
        const esTipoOtro = Boolean(
            opcionSeleccionada && String(opcionSeleccionada.dataset.clave || '').trim() === '20'
        );

        contenedorOtroTipoAnormalidad.hidden = !esTipoOtro;
        campoOtroTipoAnormalidad.required = esTipoOtro;

        if (!esTipoOtro && limpiarAlOcultar) {
            campoOtroTipoAnormalidad.value = '';
        }

        return esTipoOtro;
    }

    function configurarEdicionPrimeraRevision(soloLectura) {
        if (campoRevisionTipoAnormalidad) {
            campoRevisionTipoAnormalidad.disabled = soloLectura;
        }

        [contenedorRevisionCriticidad, contenedorRevisionNivelRespuesta, contenedorRevision5m]
            .forEach(function (contenedor) {
                if (!contenedor) {
                    return;
                }

                contenedor.querySelectorAll('input').forEach(function (entrada) {
                    entrada.disabled = soloLectura;
                });
            });

        if (contenedorRevision5m) {
            contenedorRevision5m.dataset.soloLectura = soloLectura ? 'true' : 'false';
        }

        if (campoOtroTipoAnormalidad) {
            campoOtroTipoAnormalidad.readOnly = soloLectura;
        }

        actualizarCampoOtroTipoAnormalidad(false);

        if (botonRegistrarPrimeraRevision) {
            botonRegistrarPrimeraRevision.disabled = soloLectura;
            botonRegistrarPrimeraRevision.removeAttribute('aria-busy');
            botonRegistrarPrimeraRevision.innerHTML = soloLectura
                ? '<i class="bi bi-lock" aria-hidden="true"></i> Primera revisión registrada'
                : contenidoBotonPrimeraRevision;
        }

        if (!soloLectura) {
            actualizarExclusionClasificacion5m();
        }
    }

    /**
     * Evita conservar selecciones al cambiar de una anormalidad a otra.
     */
    function limpiarPrimeraRevision() {
        if (campoRevisionTipoAnormalidad) {
            campoRevisionTipoAnormalidad.value = '';
        }

        if (campoOtroTipoAnormalidad) {
            campoOtroTipoAnormalidad.value = '';
        }

        actualizarCampoOtroTipoAnormalidad(true);

        [contenedorRevisionCriticidad, contenedorRevisionNivelRespuesta, contenedorRevision5m]
            .forEach(function (contenedor) {
                if (!contenedor) {
                    return;
                }

                contenedor.querySelectorAll('input').forEach(function (entrada) {
                    entrada.checked = false;
                });
            });

        configurarEdicionPrimeraRevision(false);
    }

    function mostrarCargaPrimeraRevision() {
        if (campoRevisionTipoAnormalidad) {
            campoRevisionTipoAnormalidad.disabled = true;
        }

        if (campoOtroTipoAnormalidad) {
            campoOtroTipoAnormalidad.readOnly = true;
        }

        [contenedorRevisionCriticidad, contenedorRevisionNivelRespuesta, contenedorRevision5m]
            .forEach(function (contenedor) {
                if (contenedor) {
                    contenedor.querySelectorAll('input').forEach(function (entrada) {
                        entrada.disabled = true;
                    });
                }
            });

        if (botonRegistrarPrimeraRevision) {
            botonRegistrarPrimeraRevision.disabled = true;
            botonRegistrarPrimeraRevision.setAttribute('aria-busy', 'true');
            botonRegistrarPrimeraRevision.innerHTML =
                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Consultando...';
        }
    }

    function seleccionarRadioCatalogo(contenedor, nombreCampo, valor) {
        if (!contenedor) {
            return;
        }

        const entrada = Array.from(
            contenedor.querySelectorAll('input[name="' + nombreCampo + '"]')
        ).find(function (opcion) {
            return opcion.value === String(valor);
        });

        if (entrada) {
            entrada.checked = true;
        }
    }

    /**
     * Muestra los IDs almacenados y bloquea los controles de una revisión existente.
     */
    function mostrarPrimeraRevisionRegistrada(revision) {
        limpiarPrimeraRevision();

        if (campoRevisionTipoAnormalidad && revision.id_tipo_anormalidad !== null) {
            campoRevisionTipoAnormalidad.value = String(revision.id_tipo_anormalidad);
        }

        if (campoOtroTipoAnormalidad) {
            campoOtroTipoAnormalidad.value = revision.otros_tipo_anormalidad || '';
        }

        actualizarCampoOtroTipoAnormalidad(false);

        seleccionarRadioCatalogo(
            contenedorRevisionCriticidad,
            'revision_criticidad',
            revision.id_criticidad
        );
        seleccionarRadioCatalogo(
            contenedorRevisionNivelRespuesta,
            'revision_nivel_respuesta',
            revision.id_nivel_respuesta
        );

        const idsCategoria5m = (revision.ids_categoria_5m || []).map(String);

        if (contenedorRevision5m) {
            contenedorRevision5m.querySelectorAll('input[name="revision_4m[]"]').forEach(function (entrada) {
                entrada.checked = idsCategoria5m.indexOf(entrada.value) !== -1;
            });
        }

        configurarEdicionPrimeraRevision(true);
    }

    /**
     * Consulta la revisión al abrir Info. Los catálogos se esperan solamente
     * si todavía se están cargando; no se vuelven a solicitar.
     */
    async function cargarPrimeraRevision(idAnormalidad) {
        const tokenActual = ++tokenConsultaPrimeraRevision;
        mostrarCargaPrimeraRevision();

        try {
            if (promesaCatalogosPrimeraRevision) {
                const catalogosDisponibles = await promesaCatalogosPrimeraRevision;

                if (catalogosDisponibles === false) {
                    throw new Error('Los catálogos de la primera revisión no están disponibles.');
                }
            }

            mostrarCargaPrimeraRevision();

            const revision = await consultarOperacionAnormalidad('11', {
                idAnormalidad: idAnormalidad
            });

            if (tokenActual !== tokenConsultaPrimeraRevision) {
                return;
            }

            if (revision.registrada) {
                mostrarPrimeraRevisionRegistrada(revision);
            } else {
                limpiarPrimeraRevision();
            }
        } catch (error) {
            if (tokenActual !== tokenConsultaPrimeraRevision) {
                return;
            }

            configurarEdicionPrimeraRevision(true);
            if (botonRegistrarPrimeraRevision) {
                botonRegistrarPrimeraRevision.innerHTML =
                    '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Revisión no disponible';
            }
            mostrarToastAnormalidad(
                'error',
                'No se consultó la primera revisión',
                error.message || 'No fue posible consultar la información registrada.',
                error
            );
        } finally {
            if (tokenActual === tokenConsultaPrimeraRevision &&
                anormalidadEstaFinalizada(modalDetalle)) {
                aplicarBloqueoAnormalidadFinalizada(modalDetalle, true);
            }
        }
    }

    /**
     * Registra la revisión y actualiza el tipo de la anormalidad en una sola
     * operación transaccional del backend.
     */
    async function registrarPrimeraRevision() {
        if (!modalDetalle || !botonRegistrarPrimeraRevision) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const criticidadSeleccionada = contenedorRevisionCriticidad
            ? contenedorRevisionCriticidad.querySelector('input[name="revision_criticidad"]:checked')
            : null;
        const nivelSeleccionado = contenedorRevisionNivelRespuesta
            ? contenedorRevisionNivelRespuesta.querySelector('input[name="revision_nivel_respuesta"]:checked')
            : null;
        const categoriasSeleccionadas = contenedorRevision5m
            ? Array.from(contenedorRevision5m.querySelectorAll('input[name="revision_4m[]"]:checked'))
            : [];
        const esTipoOtro = actualizarCampoOtroTipoAnormalidad(false);
        const detalleTipoOtro = campoOtroTipoAnormalidad
            ? campoOtroTipoAnormalidad.value.trim()
            : '';
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad)) {
            errores.push('No se identificó la anormalidad que se está revisando.');
        }

        if (!campoRevisionTipoAnormalidad || !campoRevisionTipoAnormalidad.value) {
            errores.push('Selecciona el tipo de anormalidad.');
        }

        if (esTipoOtro && !detalleTipoOtro) {
            errores.push('Describe el tipo de anormalidad seleccionado como Otro.');
        } else if (esTipoOtro && detalleTipoOtro.length > 500) {
            errores.push('Los comentarios del tipo Otro no pueden exceder 500 caracteres.');
        }

        if (!criticidadSeleccionada) {
            errores.push('Selecciona la criticidad.');
        }

        if (!nivelSeleccionado) {
            errores.push('Selecciona el nivel de respuesta.');
        }

        if (!categoriasSeleccionadas.length) {
            errores.push('Selecciona al menos una clasificación 5M.');
        }

        if (errores.length) {
            mostrarToastAnormalidad(
                'warning',
                'Revisión incompleta',
                errores[0]
            );
            return;
        }

        const contenidoOriginalBoton = botonRegistrarPrimeraRevision.innerHTML;
        let registroExitoso = false;
        botonRegistrarPrimeraRevision.disabled = true;
        botonRegistrarPrimeraRevision.setAttribute('aria-busy', 'true');
        botonRegistrarPrimeraRevision.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const revision = await consultarOperacionAnormalidad('10', {
                idAnormalidad: idAnormalidad,
                idTipoAnormalidad: campoRevisionTipoAnormalidad.value,
                idCriticidad: criticidadSeleccionada.value,
                idNivelRespuesta: nivelSeleccionado.value,
                otrosTipoAnormalidad: esTipoOtro ? detalleTipoOtro : '',
                idsCategoria5m: JSON.stringify(categoriasSeleccionadas.map(function (entrada) {
                    return entrada.value;
                }))
            });

            registroExitoso = true;
            actualizarEstatusFilaAnormalidad(idAnormalidad, revision);

            /*
             * Las 5M se vuelven a marcar con la selección enviada y no con la
             * respuesta: el servidor guarda exactamente ese conjunto (si un id
             * no es válido rechaza todo el registro). Así los checks no se
             * desmarcan al bloquear el formulario tras registrar.
             */
            mostrarPrimeraRevisionRegistrada({
                id_tipo_anormalidad: revision.id_tipo_anormalidad,
                otros_tipo_anormalidad: revision.otros_tipo_anormalidad,
                id_criticidad: revision.id_criticidad,
                id_nivel_respuesta: revision.id_nivel_respuesta,
                ids_categoria_5m: categoriasSeleccionadas.map(function (entrada) {
                    return entrada.value;
                })
            });

            mostrarToastAnormalidad(
                'success',
                'Primera revisión registrada',
                'La revisión de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la revisión',
                error.message || 'No fue posible guardar la primera revisión.',
                error
            );
        } finally {
            botonRegistrarPrimeraRevision.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarPrimeraRevision.disabled = false;
                botonRegistrarPrimeraRevision.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Restablece únicamente los controles del análisis PE cuando se consulta
     * otra anormalidad. Así se evita conservar información del registro previo.
     */
    function limpiarAnalisisPE() {
        if (campoAnalisisPERevision) {
            campoAnalisisPERevision.value = '';
            campoAnalisisPERevision.readOnly = false;
        }

        document.querySelectorAll('input[name="resultado_revision_equipo"]').forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        if (botonRegistrarAnalisisPE) {
            botonRegistrarAnalisisPE.disabled = false;
            botonRegistrarAnalisisPE.removeAttribute('aria-busy');
            botonRegistrarAnalisisPE.innerHTML = contenidoBotonAnalisisPE;
        }
    }

    /**
     * Registra el análisis de PE y la responsabilidad del factor asociado.
     * El backend ejecuta ambas inserciones dentro de una sola transacción.
     */
    async function registroAnalisisPE() {
        if (!modalDetalle || !campoAnalisisPERevision || !botonRegistrarAnalisisPE) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const revisionRealizada = campoAnalisisPERevision.value.trim();
        const resultadoSeleccionado = document.querySelector(
            'input[name="resultado_revision_equipo"]:checked'
        );
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad)) {
            errores.push('No se identificó la anormalidad que se está revisando.');
        }

        if (!revisionRealizada) {
            errores.push('Describe la revisión realizada por PE.');
        } else if (revisionRealizada.length > 500) {
            errores.push('La revisión realizada no puede exceder 500 caracteres.');
        }

        if (!resultadoSeleccionado || ['OK', 'NG'].indexOf(resultadoSeleccionado.value) === -1) {
            errores.push('Selecciona el resultado OK o NG de la revisión PE.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Análisis PE incompleto', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarAnalisisPE.innerHTML;
        let registroExitoso = false;

        botonRegistrarAnalisisPE.disabled = true;
        botonRegistrarAnalisisPE.setAttribute('aria-busy', 'true');
        botonRegistrarAnalisisPE.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const analisis = await consultarOperacionAnormalidad('12', {
                idAnormalidad: idAnormalidad,
                revisionRealizada: revisionRealizada,
                resultado: resultadoSeleccionado.value
            });

            registroExitoso = true;
            campoAnalisisPERevision.readOnly = true;
            document.querySelectorAll('input[name="resultado_revision_equipo"]').forEach(function (entrada) {
                entrada.disabled = true;
            });
            botonRegistrarAnalisisPE.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Revisión PE registrada';
            actualizarDisponibilidadCausaContramedida(true);

            mostrarToastAnormalidad(
                'success',
                'Análisis PE registrado',
                'La revisión PE de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró el análisis PE',
                error.message || 'No fue posible guardar la revisión realizada por PE.',
                error
            );
        } finally {
            botonRegistrarAnalisisPE.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarAnalisisPE.disabled = false;
                botonRegistrarAnalisisPE.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Limpia los controles de Manufactura antes de consultar otra anormalidad.
     */
    function limpiarAnalisisMF() {
        if (campoAnalisisMFOperacion) {
            campoAnalisisMFOperacion.value = '';
            campoAnalisisMFOperacion.readOnly = false;
        }

        document.querySelectorAll('input[name="resultado_analisis_operacion"]').forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        if (botonRegistrarAnalisisMF) {
            botonRegistrarAnalisisMF.disabled = false;
            botonRegistrarAnalisisMF.removeAttribute('aria-busy');
            botonRegistrarAnalisisMF.innerHTML = contenidoBotonAnalisisMF;
        }
    }

    /**
     * Registra el análisis de operación realizado por Manufactura y su factor.
     * Las dos inserciones se confirman o revierten juntas desde el backend.
     */
    async function registroAnalisisMF() {
        if (!modalDetalle || !campoAnalisisMFOperacion || !botonRegistrarAnalisisMF) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const analisisRealizado = campoAnalisisMFOperacion.value.trim();
        const resultadoSeleccionado = document.querySelector(
            'input[name="resultado_analisis_operacion"]:checked'
        );
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad)) {
            errores.push('No se identificó la anormalidad que se está analizando.');
        }

        if (!analisisRealizado) {
            errores.push('Describe el análisis de operación realizado por Manufactura.');
        } else if (analisisRealizado.length > 500) {
            errores.push('El análisis realizado no puede exceder 500 caracteres.');
        }

        if (!resultadoSeleccionado || ['OK', 'NG'].indexOf(resultadoSeleccionado.value) === -1) {
            errores.push('Selecciona el resultado OK o NG del análisis de operación.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Análisis de operación incompleto', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarAnalisisMF.innerHTML;
        let registroExitoso = false;

        botonRegistrarAnalisisMF.disabled = true;
        botonRegistrarAnalisisMF.setAttribute('aria-busy', 'true');
        botonRegistrarAnalisisMF.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const analisis = await consultarOperacionAnormalidad('13', {
                idAnormalidad: idAnormalidad,
                analisisRealizado: analisisRealizado,
                resultado: resultadoSeleccionado.value
            });

            registroExitoso = true;
            campoAnalisisMFOperacion.readOnly = true;
            document.querySelectorAll('input[name="resultado_analisis_operacion"]').forEach(function (entrada) {
                entrada.disabled = true;
            });
            botonRegistrarAnalisisMF.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Análisis de operación registrado';
            actualizarDisponibilidadCausaContramedida(true);

            mostrarToastAnormalidad(
                'success',
                'Análisis de operación registrado',
                'El análisis de Manufactura de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró el análisis de operación',
                error.message || 'No fue posible guardar el análisis realizado por Manufactura.',
                error
            );
        } finally {
            botonRegistrarAnalisisMF.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarAnalisisMF.disabled = false;
                botonRegistrarAnalisisMF.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Restablece las dos confirmaciones QA al consultar otra anormalidad.
     */
    function limpiarConfirmacionesQa() {
        [campoConfirmacionQaPiezas, campoCantidadQaPiezas,
            campoConfirmacionQaProceso, campoCantidadQaProceso]
            .forEach(function (campo) {
                if (!campo) {
                    return;
                }

                campo.value = '';
                campo.readOnly = false;
            });

        document.querySelectorAll(
            'input[name="resultado_confirmacion_piezas"], ' +
            'input[name="resultado_confirmacion_proceso"]'
        ).forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        if (botonRegistrarConfirmacionesQa) {
            botonRegistrarConfirmacionesQa.disabled = false;
            botonRegistrarConfirmacionesQa.removeAttribute('aria-busy');
            botonRegistrarConfirmacionesQa.innerHTML = contenidoBotonConfirmacionesQa;
        }
    }

    /**
     * Registra conjuntamente las confirmaciones de piezas y de componentes
     * en proceso. El backend asigna los momentos 1 y 2 desde su catálogo.
     */
    async function registroConfirmacionesQA() {
        if (!modalDetalle || !campoConfirmacionQaPiezas || !campoCantidadQaPiezas ||
            !campoConfirmacionQaProceso || !campoCantidadQaProceso ||
            !botonRegistrarConfirmacionesQa) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const confirmacionPiezas = campoConfirmacionQaPiezas.value.trim();
        const cantidadPiezasTexto = campoCantidadQaPiezas.value.trim();
        const resultadoPiezas = document.querySelector(
            'input[name="resultado_confirmacion_piezas"]:checked'
        );
        const confirmacionProceso = campoConfirmacionQaProceso.value.trim();
        const cantidadProcesoTexto = campoCantidadQaProceso.value.trim();
        const resultadoProceso = document.querySelector(
            'input[name="resultado_confirmacion_proceso"]:checked'
        );
        const cantidadPiezas = Number(cantidadPiezasTexto);
        const cantidadProceso = Number(cantidadProcesoTexto);
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad)) {
            errores.push('No se identificó la anormalidad que se está confirmando.');
        }

        if (!confirmacionPiezas) {
            errores.push('Describe la confirmación de las piezas o componentes anormales.');
        } else if (confirmacionPiezas.length > 500) {
            errores.push('La confirmación de piezas no puede exceder 500 caracteres.');
        }

        if (!/^\d+$/.test(cantidadPiezasTexto) || !Number.isSafeInteger(cantidadPiezas) ||
            cantidadPiezas < 0 || cantidadPiezas > 2147483647) {
            errores.push('Captura una cantidad válida para las piezas confirmadas.');
        }

        if (!resultadoPiezas || ['OK', 'NG'].indexOf(resultadoPiezas.value) === -1) {
            errores.push('Selecciona el resultado OK o NG de la confirmación de piezas.');
        }

        if (!confirmacionProceso) {
            errores.push('Describe la confirmación de los componentes en proceso.');
        } else if (confirmacionProceso.length > 500) {
            errores.push('La confirmación en proceso no puede exceder 500 caracteres.');
        }

        if (!/^\d+$/.test(cantidadProcesoTexto) || !Number.isSafeInteger(cantidadProceso) ||
            cantidadProceso < 0 || cantidadProceso > 2147483647) {
            errores.push('Captura una cantidad válida para los componentes en proceso.');
        }

        if (!resultadoProceso || ['OK', 'NG'].indexOf(resultadoProceso.value) === -1) {
            errores.push('Selecciona el resultado OK o NG de la confirmación en proceso.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Confirmaciones QA incompletas', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarConfirmacionesQa.innerHTML;
        let registroExitoso = false;

        botonRegistrarConfirmacionesQa.disabled = true;
        botonRegistrarConfirmacionesQa.setAttribute('aria-busy', 'true');
        botonRegistrarConfirmacionesQa.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            await consultarOperacionAnormalidad('14', {
                idAnormalidad: idAnormalidad,
                confirmacionPiezas: confirmacionPiezas,
                cantidadPiezas: cantidadPiezas,
                resultadoPiezas: resultadoPiezas.value,
                confirmacionProceso: confirmacionProceso,
                cantidadProceso: cantidadProceso,
                resultadoProceso: resultadoProceso.value
            });

            registroExitoso = true;
            [campoConfirmacionQaPiezas, campoCantidadQaPiezas,
                campoConfirmacionQaProceso, campoCantidadQaProceso]
                .forEach(function (campo) {
                    campo.readOnly = true;
                });
            document.querySelectorAll(
                'input[name="resultado_confirmacion_piezas"], ' +
                'input[name="resultado_confirmacion_proceso"]'
            ).forEach(function (entrada) {
                entrada.disabled = true;
            });
            botonRegistrarConfirmacionesQa.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Confirmaciones QA registradas';
            actualizarDisponibilidadCausaContramedida(true);

            mostrarToastAnormalidad(
                'success',
                'Confirmaciones QA registradas',
                'Las confirmaciones QA de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardaron correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registraron las confirmaciones QA',
                error.message || 'No fue posible guardar las confirmaciones realizadas por Calidad.',
                error
            );
        } finally {
            botonRegistrarConfirmacionesQa.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarConfirmacionesQa.disabled = false;
                botonRegistrarConfirmacionesQa.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Habilita la captura de causa y contramedida únicamente cuando ya existe
     * por lo menos un análisis PE, MF o QA para la anormalidad abierta.
     */
    function actualizarDisponibilidadCausaContramedida(habilitada) {
        [campoCausaAnalisis, campoContramedidaAnalisis].forEach(function (campo) {
            if (campo) {
                campo.readOnly = !habilitada;
            }
        });

        if (botonRegistrarCausaContramedida) {
            botonRegistrarCausaContramedida.disabled = !habilitada;
            botonRegistrarCausaContramedida.removeAttribute('aria-busy');
            botonRegistrarCausaContramedida.innerHTML = habilitada
                ? contenidoBotonCausaContramedida
                : '<i class="bi bi-lock" aria-hidden="true"></i> Requiere un análisis previo';
        }
    }

    /**
     * Limpia y bloquea la causa y contramedida al abrir otra anormalidad,
     * mientras se consultan sus análisis registrados.
     */
    function limpiarCausaContramedida() {
        [campoCausaAnalisis, campoContramedidaAnalisis].forEach(function (campo) {
            if (!campo) {
                return;
            }

            campo.value = '';
        });

        actualizarDisponibilidadCausaContramedida(false);
    }

    /**
     * Registra la causa y la contramedida. El backend comprueba que exista
     * previamente un análisis PE, MF o las dos confirmaciones QA.
     */
    async function registroCausaContramedida() {
        if (!modalDetalle || !campoCausaAnalisis || !campoContramedidaAnalisis ||
            !botonRegistrarCausaContramedida) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const causa = campoCausaAnalisis.value.trim();
        const contramedida = campoContramedidaAnalisis.value.trim();
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad)) {
            errores.push('No se identificó la anormalidad correspondiente.');
        }

        if (!causa) {
            errores.push('Describe la causa identificada.');
        } else if (causa.length > 800) {
            errores.push('La causa no puede exceder 800 caracteres.');
        }

        if (!contramedida) {
            errores.push('Describe la contramedida aplicada.');
        } else if (contramedida.length > 800) {
            errores.push('La contramedida no puede exceder 800 caracteres.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Registro incompleto', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarCausaContramedida.innerHTML;
        let registroExitoso = false;

        botonRegistrarCausaContramedida.disabled = true;
        botonRegistrarCausaContramedida.setAttribute('aria-busy', 'true');
        botonRegistrarCausaContramedida.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const causaContramedidaRegistrada = await consultarOperacionAnormalidad('15', {
                idAnormalidad: idAnormalidad,
                causa: causa,
                contramedida: contramedida
            });

            registroExitoso = true;
            actualizarEstatusFilaAnormalidad(idAnormalidad, causaContramedidaRegistrada);
            campoCausaAnalisis.readOnly = true;
            campoContramedidaAnalisis.readOnly = true;
            botonRegistrarCausaContramedida.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Causa y contramedida registradas';

            mostrarToastAnormalidad(
                'success',
                'Causa y contramedida registradas',
                'La causa y contramedida de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardaron correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la causa y contramedida',
                error.message || 'No fue posible guardar la causa y la contramedida.',
                error
            );
        } finally {
            botonRegistrarCausaContramedida.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarCausaContramedida.disabled = false;
                botonRegistrarCausaContramedida.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Restablece la disposición al abrir el detalle de otra anormalidad.
     * Los radios se generan desde el catálogo, por eso se localizan por nombre.
     */
    function limpiarDisposicionCalidad() {
        document.querySelectorAll('input[name="disposicion_producto"]').forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        if (botonRegistrarDisposicionProducto) {
            botonRegistrarDisposicionProducto.disabled = false;
            botonRegistrarDisposicionProducto.removeAttribute('aria-busy');
            botonRegistrarDisposicionProducto.innerHTML = contenidoBotonDisposicionProducto;
        }
    }

    /**
     * Crea el cierre de Calidad con la disposición seleccionada. Los datos del
     * reinicio se completarán posteriormente sobre el mismo registro.
     */
    async function registroDisposicionProducto() {
        if (!modalDetalle || !contenedorDisposicionCalidad ||
            !botonRegistrarDisposicionProducto) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const disposicionSeleccionada = contenedorDisposicionCalidad.querySelector(
            'input[name="disposicion_producto"]:checked'
        );
        const idDisposicion = disposicionSeleccionada ? disposicionSeleccionada.value : '';
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad) || Number(idAnormalidad) < 1) {
            errores.push('No se identificó la anormalidad correspondiente.');
        }

        if (!/^\d+$/.test(idDisposicion) || Number(idDisposicion) < 1) {
            errores.push('Selecciona una disposición válida para el producto o material.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Disposición incompleta', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarDisposicionProducto.innerHTML;
        let registroExitoso = false;

        botonRegistrarDisposicionProducto.disabled = true;
        botonRegistrarDisposicionProducto.setAttribute('aria-busy', 'true');
        botonRegistrarDisposicionProducto.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            await consultarOperacionAnormalidad('17', {
                idAnormalidad: idAnormalidad,
                idDisposicion: idDisposicion
            });

            registroExitoso = true;
            document.querySelectorAll('input[name="disposicion_producto"]').forEach(function (entrada) {
                entrada.disabled = true;
            });
            botonRegistrarDisposicionProducto.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Disposición registrada';

            mostrarToastAnormalidad(
                'success',
                'Disposición registrada',
                'La disposición de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la disposición',
                error.message || 'No fue posible guardar la disposición seleccionada.',
                error
            );
        } finally {
            botonRegistrarDisposicionProducto.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarDisposicionProducto.disabled = false;
                botonRegistrarDisposicionProducto.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Restablece los controles de la revisión previa al consultar otra
     * anormalidad y evita conservar datos pertenecientes al registro anterior.
     */
    function limpiarRevisionMaterialAntes() {
        camposSerialMaterialAntes.forEach(function (campo) {
            if (!campo) {
                return;
            }

            campo.value = '';
            campo.readOnly = false;
        });

        document.querySelectorAll('input[name^="resultado_serial_antes_"]').forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        if (botonRegistrarRevisionMaterialAntes) {
            botonRegistrarRevisionMaterialAntes.disabled = false;
            botonRegistrarRevisionMaterialAntes.removeAttribute('aria-busy');
            botonRegistrarRevisionMaterialAntes.innerHTML = contenidoBotonRevisionMaterialAntes;
        }
    }

    /**
     * Valida y registra conjuntamente las tres muestras revisadas antes de la
     * anormalidad. El momento y el cierre se resuelven de forma segura en la API.
     */
    async function registroRevisionMaterialAntes() {
        if (!modalDetalle || !botonRegistrarRevisionMaterialAntes ||
            camposSerialMaterialAntes.some(function (campo) { return !campo; })) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const muestras = [];
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad) || Number(idAnormalidad) < 1) {
            errores.push('No se identificó la anormalidad correspondiente.');
        }

        camposSerialMaterialAntes.forEach(function (campo, indice) {
            const numeroSerial = indice + 1;
            const codigoSerial = campo.value.trim();
            const resultadoSeleccionado = document.querySelector(
                'input[name="resultado_serial_antes_' + numeroSerial + '"]:checked'
            );

            if (!codigoSerial) {
                errores.push('Captura el código del serial ' + numeroSerial + '.');
            } else if (codigoSerial.length > 80) {
                errores.push('El serial ' + numeroSerial + ' no puede exceder 80 caracteres.');
            }

            if (!resultadoSeleccionado || ['OK', 'NG'].indexOf(resultadoSeleccionado.value) === -1) {
                errores.push('Selecciona el resultado OK o NG del serial ' + numeroSerial + '.');
            }

            muestras.push({
                codigo: codigoSerial,
                resultado: resultadoSeleccionado ? resultadoSeleccionado.value : ''
            });
        });

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Revisión de material incompleta', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarRevisionMaterialAntes.innerHTML;
        let registroExitoso = false;

        botonRegistrarRevisionMaterialAntes.disabled = true;
        botonRegistrarRevisionMaterialAntes.setAttribute('aria-busy', 'true');
        botonRegistrarRevisionMaterialAntes.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            await consultarOperacionAnormalidad('18', {
                idAnormalidad: idAnormalidad,
                serial1: muestras[0].codigo,
                resultado1: muestras[0].resultado,
                serial2: muestras[1].codigo,
                resultado2: muestras[1].resultado,
                serial3: muestras[2].codigo,
                resultado3: muestras[2].resultado
            });

            registroExitoso = true;
            camposSerialMaterialAntes.forEach(function (campo) {
                campo.readOnly = true;
            });
            document.querySelectorAll('input[name^="resultado_serial_antes_"]').forEach(function (entrada) {
                entrada.disabled = true;
            });
            botonRegistrarRevisionMaterialAntes.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Revisión de material registrada';

            mostrarToastAnormalidad(
                'success',
                'Revisión de material registrada',
                'Las muestras de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardaron correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la revisión de material',
                error.message || 'No fue posible guardar las muestras revisadas.',
                error
            );
        } finally {
            botonRegistrarRevisionMaterialAntes.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarRevisionMaterialAntes.disabled = false;
                botonRegistrarRevisionMaterialAntes.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Restablece la captura de reinicio al consultar otra anormalidad y vuelve
     * a colocar la identidad del aprobador autenticado.
     */
    function limpiarReinicioOperacion() {
        [campoConfirmacionReinicio]
            .concat(camposSerialReinicio)
            .forEach(function (campo) {
                if (!campo) {
                    return;
                }

                campo.value = '';
                campo.readOnly = false;
            });

        if (campoFechaConfirmacionReinicio) {
            campoFechaConfirmacionReinicio.value = obtenerFechaHoraLocal().slice(0, 10);
            campoFechaConfirmacionReinicio.readOnly = true;
        }

        document.querySelectorAll('input[name^="resultado_serial_reinicio_"]').forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        colocarUsuarioSesion(campoNominaAprobacionReinicio, campoNombreAprobacionReinicio);

        if (botonRegistrarReinicioOperacion) {
            botonRegistrarReinicioOperacion.disabled = false;
            botonRegistrarReinicioOperacion.removeAttribute('aria-busy');
            botonRegistrarReinicioOperacion.innerHTML = contenidoBotonReinicioOperacion;
        }
    }

    /**
     * Verifica una fecha YYYY-MM-DD sin permitir normalizaciones automáticas
     * como convertir una fecha inexistente al siguiente mes.
     */
    function fechaIsoEsValida(valor) {
        const coincidencia = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor);

        if (!coincidencia) {
            return false;
        }

        const anio = Number(coincidencia[1]);
        const mes = Number(coincidencia[2]);
        const dia = Number(coincidencia[3]);
        const fecha = new Date(anio, mes - 1, dia);

        return fecha.getFullYear() === anio &&
            fecha.getMonth() === mes - 1 &&
            fecha.getDate() === dia;
    }

    /**
     * Registra conjuntamente la confirmación, la aprobación del usuario en
     * sesión y las tres muestras posteriores al reinicio. El backend ejecuta
     * todo en una transacción.
     */
    async function registroReinicioOperacion() {
        if (!modalDetalle || !campoConfirmacionReinicio ||
            !campoFechaConfirmacionReinicio || !botonRegistrarReinicioOperacion ||
            camposSerialReinicio.some(function (campo) { return !campo; })) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const confirmacion = campoConfirmacionReinicio.value.trim();
        const fechaConfirmacion = campoFechaConfirmacionReinicio.value.trim();
        const muestras = [];
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad) || Number(idAnormalidad) < 1) {
            errores.push('No se identificó la anormalidad correspondiente.');
        }

        if (!confirmacion) {
            errores.push('Describe la confirmación del producto, operación o máquina.');
        } else if (confirmacion.length > 700) {
            errores.push('La confirmación del reinicio no puede exceder 700 caracteres.');
        }

        if (!fechaIsoEsValida(fechaConfirmacion)) {
            errores.push('Selecciona una fecha de confirmación válida.');
        }

        camposSerialReinicio.forEach(function (campo, indice) {
            const numeroSerial = indice + 1;
            const codigoSerial = campo.value.trim();
            const resultadoSeleccionado = document.querySelector(
                'input[name="resultado_serial_reinicio_' + numeroSerial + '"]:checked'
            );

            if (!codigoSerial) {
                errores.push('Captura el código del serial ' + numeroSerial + ' del reinicio.');
            } else if (codigoSerial.length > 80) {
                errores.push('El serial ' + numeroSerial + ' no puede exceder 80 caracteres.');
            }

            if (!resultadoSeleccionado || ['OK', 'NG'].indexOf(resultadoSeleccionado.value) === -1) {
                errores.push('Selecciona el resultado OK o NG del serial ' + numeroSerial + '.');
            }

            muestras.push({
                codigo: codigoSerial,
                resultado: resultadoSeleccionado ? resultadoSeleccionado.value : ''
            });
        });

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Reinicio de operación incompleto', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarReinicioOperacion.innerHTML;
        let registroExitoso = false;

        botonRegistrarReinicioOperacion.disabled = true;
        botonRegistrarReinicioOperacion.setAttribute('aria-busy', 'true');
        botonRegistrarReinicioOperacion.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const reinicioRegistrado = await consultarOperacionAnormalidad('19', {
                idAnormalidad: idAnormalidad,
                confirmacion: confirmacion,
                fechaConfirmacion: fechaConfirmacion,
                serial1: muestras[0].codigo,
                resultado1: muestras[0].resultado,
                serial2: muestras[1].codigo,
                resultado2: muestras[1].resultado,
                serial3: muestras[2].codigo,
                resultado3: muestras[2].resultado
            });

            registroExitoso = true;
            actualizarEstatusFilaAnormalidad(idAnormalidad, reinicioRegistrado);
            [campoConfirmacionReinicio, campoFechaConfirmacionReinicio]
                .concat(camposSerialReinicio)
                .forEach(function (campo) {
                    campo.readOnly = true;
                });
            document.querySelectorAll('input[name^="resultado_serial_reinicio_"]').forEach(function (entrada) {
                entrada.disabled = true;
            });
            botonRegistrarReinicioOperacion.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Reinicio de operación registrado';

            mostrarToastAnormalidad(
                'success',
                'Reinicio de operación registrado',
                'El reinicio de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró el reinicio',
                error.message || 'No fue posible guardar el reinicio de operación.',
                error
            );
        } finally {
            botonRegistrarReinicioOperacion.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarReinicioOperacion.disabled = false;
                botonRegistrarReinicioOperacion.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Selecciona y bloquea un resultado almacenado sin disparar cambios.
     */
    function mostrarResultadoAnalisisRegistrado(nombreCampo, resultado) {
        document.querySelectorAll('input[name="' + nombreCampo + '"]').forEach(function (entrada) {
            entrada.checked = entrada.value === String(resultado);
            entrada.disabled = true;
        });
    }

    /**
     * Cambia un botón de registro a su estado final de solo lectura.
     */
    function bloquearBotonAnalisisRegistrado(boton, texto) {
        if (!boton) {
            return;
        }

        boton.disabled = true;
        boton.removeAttribute('aria-busy');
        boton.innerHTML = '<i class="bi bi-lock" aria-hidden="true"></i> ' + texto;
    }

    /* =================================================================
       Estado de captura de las pestañas del seguimiento

       Cada etapa agrupa varios registros. Se marca en verde cuando todos
       sus requisitos están cumplidos y en naranja mientras falte alguno.
       Un requisito puede ser un grupo de registros alternativos, donde
       basta con que exista uno (ver la etapa de Análisis).

       En lugar de repetir la detección, se lee la misma señal que ya deja
       el código al bloquear una sección: el botón queda deshabilitado y su
       contenido pasa a mostrar el candado. Así el indicador no se puede
       desincronizar de lo que ve el usuario.

       El flujo de reincidencia no cuenta: una anormalidad puede cerrarse
       sin que exista ninguna.
       ================================================================= */
    const PESTANAS_FLUJO = [
        {
            pestana: 'tabAperturaButton',
            registros: ['btnRegistrarPrimeraRevision']
        },
        {
            pestana: 'tabSeguimientoButton',
            registros: [
                /* Los tres análisis departamentales son alternativos: según la
                   anormalidad puede atenderla un solo departamento o varios,
                   así que basta con que exista uno. */
                [
                    'btnRegistrarRevisionEquipo',
                    'btnRegistrarAnalisisOperacion',
                    'btnRegistrarConfirmacionesQa'
                ],
                'btnRegistrarCausaContramedida'
            ]
        },
        {
            pestana: 'tabCierreCalidadButton',
            registros: [
                'btnRegistrarDisposicionProducto',
                'btnRegistrarRevisionMaterialAntes',
                'btnRegistrarReinicioOperacion'
            ]
        },
        {
            pestana: 'tabCierreButton',
            registros: [
                'btnRegistrarCierreReporteCalidad',
                'btnRegistrarAutorizacionGerente'
            ]
        }
    ];

    let temporizadorEstadoPestanas = null;

    /**
     * Una sección está registrada cuando su botón quedó bloqueado avisando
     * que ya se guardó.
     *
     * No basta con el candado: el botón de la autorización también lo muestra
     * cuando dice "Firma QA requerida", que significa justo lo contrario. Por
     * eso se exige además que el texto hable de algo registrado, que es la
     * redacción que usan todas las secciones al bloquearse.
     *
     * Un botón ausente se considera pendiente, para no dar por completa una
     * etapa que no se pudo comprobar.
     */
    function seccionRegistrada(idBoton) {
        const boton = document.getElementById(idBoton);

        if (!boton || !boton.querySelector('.bi-lock')) {
            return false;
        }

        return /registrad[oa]s?/i.test(boton.textContent || '');
    }

    /**
     * Un requisito de la etapa puede ser un solo registro obligatorio, o un
     * grupo de registros alternativos (un arreglo), donde basta con uno.
     */
    function requisitoCumplido(requisito) {
        return Array.isArray(requisito)
            ? requisito.some(seccionRegistrada)
            : seccionRegistrada(requisito);
    }

    function actualizarEstadoPestanas() {
        PESTANAS_FLUJO.forEach(function (etapa) {
            const pestana = document.getElementById(etapa.pestana);

            if (!pestana) {
                return;
            }

            const completa = etapa.registros.every(requisitoCumplido);

            pestana.classList.toggle('is-completa', completa);
            pestana.classList.toggle('is-pendiente', !completa);
            pestana.title = completa
                ? 'Todos los registros de esta etapa están capturados'
                : 'Esta etapa tiene registros pendientes';

            const indicador = pestana.querySelector('.anomaly-flow-state');

            if (indicador) {
                indicador.className = 'bi anomaly-flow-state ' +
                    (completa ? 'bi-check-circle-fill' : 'bi-clock-history');
            }
        });
    }

    /* Agrupa las recalculaciones: al cargar el detalle el modal cambia muchas
       veces seguidas y basta con revisar el resultado final. */
    function programarEstadoPestanas() {
        window.clearTimeout(temporizadorEstadoPestanas);
        temporizadorEstadoPestanas = window.setTimeout(actualizarEstadoPestanas, 120);
    }

    /**
     * Aplica solamente las secciones que ya tienen información registrada.
     * Las secciones sin datos conservan su estado actual.
     */
    function mostrarRegistrosAnalisis(registros) {
        if (!registros) {
            return;
        }

        if (registros.pe && campoAnalisisPERevision) {
            campoAnalisisPERevision.value = registros.pe.revision_realizada || '';
            campoAnalisisPERevision.readOnly = true;
            mostrarResultadoAnalisisRegistrado('resultado_revision_equipo', registros.pe.resultado);
            bloquearBotonAnalisisRegistrado(botonRegistrarAnalisisPE, 'Revisión PE registrada');
        }

        if (registros.mf && campoAnalisisMFOperacion) {
            campoAnalisisMFOperacion.value = registros.mf.analisis_realizado || '';
            campoAnalisisMFOperacion.readOnly = true;
            mostrarResultadoAnalisisRegistrado('resultado_analisis_operacion', registros.mf.resultado);
            bloquearBotonAnalisisRegistrado(botonRegistrarAnalisisMF, 'Análisis de operación registrado');
        }

        const confirmacionPiezas = registros.qa ? registros.qa.piezas : null;
        const confirmacionProceso = registros.qa ? registros.qa.proceso : null;

        if (confirmacionPiezas && campoConfirmacionQaPiezas && campoCantidadQaPiezas) {
            campoConfirmacionQaPiezas.value = confirmacionPiezas.confirmacion || '';
            campoCantidadQaPiezas.value = confirmacionPiezas.cantidad;
            campoConfirmacionQaPiezas.readOnly = true;
            campoCantidadQaPiezas.readOnly = true;
            mostrarResultadoAnalisisRegistrado(
                'resultado_confirmacion_piezas',
                confirmacionPiezas.resultado
            );
        }

        if (confirmacionProceso && campoConfirmacionQaProceso && campoCantidadQaProceso) {
            campoConfirmacionQaProceso.value = confirmacionProceso.confirmacion || '';
            campoCantidadQaProceso.value = confirmacionProceso.cantidad;
            campoConfirmacionQaProceso.readOnly = true;
            campoCantidadQaProceso.readOnly = true;
            mostrarResultadoAnalisisRegistrado(
                'resultado_confirmacion_proceso',
                confirmacionProceso.resultado
            );
        }

        if (confirmacionPiezas || confirmacionProceso) {
            bloquearBotonAnalisisRegistrado(
                botonRegistrarConfirmacionesQa,
                confirmacionPiezas && confirmacionProceso
                    ? 'Confirmaciones QA registradas'
                    : 'Registro QA parcial existente'
            );
        }

        const tieneAnalisisPrevio = Boolean(
            registros.pe || registros.mf || (confirmacionPiezas && confirmacionProceso)
        );

        if (registros.causa_contramedida && campoCausaAnalisis && campoContramedidaAnalisis) {
            campoCausaAnalisis.value = registros.causa_contramedida.causa || '';
            campoContramedidaAnalisis.value = registros.causa_contramedida.contramedida || '';
            campoCausaAnalisis.readOnly = true;
            campoContramedidaAnalisis.readOnly = true;
            bloquearBotonAnalisisRegistrado(
                botonRegistrarCausaContramedida,
                'Causa y contramedida registradas'
            );
        } else {
            actualizarDisponibilidadCausaContramedida(tieneAnalisisPrevio);
        }
    }

    /**
     * Consulta los registros al mostrarse la pestaña Análisis. Una respuesta
     * de un registro anterior se descarta mediante el token de consulta.
     */
    async function cargarRegistrosAnalisis() {
        if (!modalDetalle) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';

        if (!/^\d+$/.test(idAnormalidad)) {
            return;
        }

        const tokenActual = ++tokenConsultaAnalisis;

        try {
            const registros = await consultarOperacionAnormalidad('16', {
                idAnormalidad: idAnormalidad
            });

            if (tokenActual !== tokenConsultaAnalisis ||
                modalDetalle.dataset.idAnormalidad !== idAnormalidad) {
                return;
            }

            mostrarRegistrosAnalisis(registros);
        } catch (error) {
            if (tokenActual !== tokenConsultaAnalisis) {
                return;
            }

            mostrarToastAnormalidad(
                'error',
                'No se consultó el análisis',
                error.message || 'No fue posible consultar los registros de esta sección.',
                error
            );
        }
    }

    /**
     * Marca un radio almacenado y bloquea todas las opciones de su grupo.
     */
    function mostrarRadioCalidadRegistrado(nombreCampo, valor) {
        document.querySelectorAll('input[name="' + nombreCampo + '"]').forEach(function (entrada) {
            entrada.checked = entrada.value === String(valor);
            entrada.disabled = true;
        });
    }

    /**
     * Presenta las muestras de una sección de Calidad. La API registra siempre
     * las tres juntas; también se bloquea un registro histórico parcial para no
     * sobrescribirlo accidentalmente.
     */
    function mostrarMuestrasCalidadRegistradas(muestras, campos, prefijoResultado, boton, textoBoton) {
        if (!Array.isArray(muestras) || !muestras.length) {
            return false;
        }

        const muestrasPorNumero = {};

        muestras.forEach(function (muestra) {
            muestrasPorNumero[String(muestra.no_serial)] = muestra;
        });

        campos.forEach(function (campo, indice) {
            const numeroSerial = indice + 1;
            const muestra = muestrasPorNumero[String(numeroSerial)];

            if (campo) {
                campo.value = muestra ? (muestra.codigo_serial || '') : '';
                campo.readOnly = true;
            }

            document.querySelectorAll(
                'input[name="' + prefijoResultado + numeroSerial + '"]'
            ).forEach(function (entrada) {
                entrada.checked = Boolean(muestra) && entrada.value === String(muestra.resultado);
                entrada.disabled = true;
            });
        });

        bloquearBotonAnalisisRegistrado(
            boton,
            muestras.length === 3 ? textoBoton : 'Registro parcial existente'
        );

        return true;
    }

    /**
     * Aplica de manera independiente la disposición, la revisión previa y el
     * reinicio. Una sección sin registro conserva sus controles habilitados.
     */
    function mostrarRegistrosCierreCalidad(registros) {
        if (!registros) {
            return;
        }

        const cierre = registros.cierre;
        const muestrasAntes = registros.muestras ? registros.muestras.antes : [];
        const muestrasReinicio = registros.muestras ? registros.muestras.reinicio : [];

        if (cierre && cierre.id_disposicion !== null && cierre.id_disposicion !== undefined) {
            mostrarRadioCalidadRegistrado('disposicion_producto', cierre.id_disposicion);
            bloquearBotonAnalisisRegistrado(
                botonRegistrarDisposicionProducto,
                'Disposición registrada'
            );
        }

        mostrarMuestrasCalidadRegistradas(
            muestrasAntes,
            camposSerialMaterialAntes,
            'resultado_serial_antes_',
            botonRegistrarRevisionMaterialAntes,
            'Revisión de material registrada'
        );

        const tieneDatosReinicio = Boolean(cierre) && (
            cierre.confirmacion_reinicio !== null ||
            cierre.fecha_confirmacion_reinicio !== null ||
            cierre.nomina_aprobReinicio !== null ||
            cierre.nombre_aprobReinicio !== null
        );
        const tieneMuestrasReinicio = Array.isArray(muestrasReinicio) && muestrasReinicio.length > 0;

        if (!tieneDatosReinicio && !tieneMuestrasReinicio) {
            return;
        }

        if (campoConfirmacionReinicio) {
            campoConfirmacionReinicio.value = cierre && cierre.confirmacion_reinicio
                ? cierre.confirmacion_reinicio
                : '';
            campoConfirmacionReinicio.readOnly = true;
        }

        if (campoFechaConfirmacionReinicio) {
            campoFechaConfirmacionReinicio.value = cierre && cierre.fecha_confirmacion_reinicio
                ? cierre.fecha_confirmacion_reinicio
                : '';
            campoFechaConfirmacionReinicio.readOnly = true;
        }

        if (campoNominaAprobacionReinicio) {
            campoNominaAprobacionReinicio.value = cierre && cierre.nomina_aprobReinicio
                ? cierre.nomina_aprobReinicio
                : '';
        }

        if (campoNombreAprobacionReinicio) {
            campoNombreAprobacionReinicio.value = cierre && cierre.nombre_aprobReinicio
                ? cierre.nombre_aprobReinicio
                : '';
        }

        mostrarMuestrasCalidadRegistradas(
            muestrasReinicio,
            camposSerialReinicio,
            'resultado_serial_reinicio_',
            botonRegistrarReinicioOperacion,
            'Reinicio de operación registrado'
        );

        /* También bloquea el botón cuando existen datos del cierre sin muestras. */
        if (tieneDatosReinicio && !tieneMuestrasReinicio) {
            camposSerialReinicio.forEach(function (campo) {
                if (campo) {
                    campo.readOnly = true;
                }
            });
            document.querySelectorAll('input[name^="resultado_serial_reinicio_"]').forEach(function (entrada) {
                entrada.disabled = true;
            });
            bloquearBotonAnalisisRegistrado(
                botonRegistrarReinicioOperacion,
                'Registro parcial existente'
            );
        }
    }

    /**
     * Consulta los datos únicamente cuando se muestra Verificación de Calidad.
     * El token evita aplicar una respuesta perteneciente a otra anormalidad.
     */
    async function cargarRegistrosCierreCalidad() {
        if (!modalDetalle) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';

        if (!/^\d+$/.test(idAnormalidad)) {
            return;
        }

        const tokenActual = ++tokenConsultaCierreCalidad;

        try {
            if (promesaCatalogosPrimeraRevision) {
                await promesaCatalogosPrimeraRevision;
            }

            const registros = await consultarOperacionAnormalidad('20', {
                idAnormalidad: idAnormalidad
            });

            if (tokenActual !== tokenConsultaCierreCalidad ||
                modalDetalle.dataset.idAnormalidad !== idAnormalidad) {
                return;
            }

            mostrarRegistrosCierreCalidad(registros);
        } catch (error) {
            if (tokenActual !== tokenConsultaCierreCalidad) {
                return;
            }

            mostrarToastAnormalidad(
                'error',
                'No se consultó la verificación de Calidad',
                error.message || 'No fue posible consultar los registros de esta sección.',
                error
            );
        }
    }

    /**
     * Conserva la sección de reincidencia como información de solo lectura y
     * establece No como valor seguro mientras se consulta el registro real.
     */
    function limpiarDatosReincidenciaCierre() {
        if (campoCierreReincidenteSi) {
            campoCierreReincidenteSi.checked = false;
            campoCierreReincidenteSi.disabled = true;
        }

        if (campoCierreReincidenteNo) {
            campoCierreReincidenteNo.checked = true;
            campoCierreReincidenteNo.disabled = true;
        }

        if (campoCierreNumeroSeguimiento) {
            campoCierreNumeroSeguimiento.value = '';
            campoCierreNumeroSeguimiento.readOnly = true;
        }
    }

    /**
     * Convierte la fecha ISO del servidor en una referencia legible.
     */
    function obtenerTextoFechaCierre(valor) {
        if (!valor) {
            return 'Sin información';
        }

        const fecha = formatearFechaListado(valor);
        return fecha.fecha + ' · ' + fecha.hora;
    }

    /**
     * Dibuja en el canvas la imagen JPEG guardada para una firma registrada.
     */
    function mostrarFirmaCierreRegistrada(lienzo, nombreArchivo, carpeta, tokenConsulta, idAnormalidad) {
        if (!lienzo || !nombreArchivo) {
            return;
        }

        const imagenFirma = new Image();

        imagenFirma.addEventListener('load', function () {
            if (tokenConsulta !== tokenConsultaReincidenciaCierre || !modalDetalle ||
                modalDetalle.dataset.idAnormalidad !== idAnormalidad) {
                return;
            }

            const contexto = prepararLienzoFirma(lienzo);

            if (!contexto) {
                return;
            }

            contexto.save();
            contexto.setTransform(1, 0, 0, 1, 0, 0);
            contexto.clearRect(0, 0, lienzo.width, lienzo.height);
            contexto.drawImage(imagenFirma, 0, 0, lienzo.width, lienzo.height);
            contexto.restore();

            const contenedor = lienzo.closest('.anomaly-signature-canvas-wrap');

            if (contenedor) {
                contenedor.classList.add('has-signature');
            }
        });

        imagenFirma.addEventListener('error', function () {
            if (tokenConsulta !== tokenConsultaReincidenciaCierre) {
                return;
            }

            mostrarToastAnormalidad(
                'error',
                'Firma no disponible',
                'No fue posible cargar la imagen registrada de la firma.',
                { carpeta: carpeta, archivo: nombreArchivo }
            );
        });

        imagenFirma.src = './img/anormalidades/' + carpeta + '/' + encodeURIComponent(nombreArchivo);
    }

    /**
     * Muestra y bloquea exclusivamente el cierre registrado por Staff QA.
     */
    function mostrarCierreQaRegistrado(registro, tokenConsulta, idAnormalidad) {
        if (!registro) {
            return;
        }

        if (campoNominaCierreQa) {
            campoNominaCierreQa.value = registro.nomina_cierra || 'Sin información';
        }

        if (campoNombreCierreQa) {
            campoNombreCierreQa.value = registro.nombre_cierra || 'Sin información';
        }

        if (campoFechaCierreQa) {
            campoFechaCierreQa.value = obtenerTextoFechaCierre(registro.fecha_cierre);
        }

        let factorEncontrado = false;

        document.querySelectorAll('input[name="cierre_responsable"]').forEach(function (entrada) {
            const coincide = entrada.value === String(registro.id_factor);
            entrada.checked = coincide;
            entrada.disabled = true;
            factorEncontrado = factorEncontrado || coincide;
        });

        if (!factorEncontrado && contenedorResponsableCierre) {
            const anterior = contenedorResponsableCierre.querySelector('[data-factor-registrado]');

            if (anterior) {
                anterior.remove();
            }

            const opcion = document.createElement('div');
            const clave = document.createElement('strong');
            const estado = document.createElement('small');

            opcion.className = 'btn anomaly-responsible-button active disabled';
            opcion.dataset.factorRegistrado = 'true';
            clave.textContent = registro.factor || ('Factor ' + registro.id_factor);
            estado.textContent = 'Responsable registrado';
            opcion.appendChild(clave);
            opcion.appendChild(estado);
            contenedorResponsableCierre.appendChild(opcion);
        }

        if (lienzoFirmaCierreQa) {
            lienzoFirmaCierreQa.dataset.signatureLocked = 'true';
            lienzoFirmaCierreQa.style.pointerEvents = 'none';
            lienzoFirmaCierreQa.setAttribute('aria-disabled', 'true');
            lienzoFirmaCierreQa.tabIndex = -1;
        }

        if (botonLimpiarFirmaCierreQa) {
            botonLimpiarFirmaCierreQa.disabled = true;
        }

        bloquearBotonAnalisisRegistrado(botonRegistrarCierreQa, 'Cierre de QA registrado');
        mostrarFirmaCierreRegistrada(
            lienzoFirmaCierreQa,
            registro.firma,
            'firmasQA',
            tokenConsulta,
            idAnormalidad
        );
    }

    /**
     * Muestra y bloquea exclusivamente la autorización registrada del gerente.
     */
    function mostrarAutorizacionGerenteRegistrada(registro, tokenConsulta, idAnormalidad) {
        if (!registro) {
            return;
        }

        if (campoNominaCierreGerente) {
            campoNominaCierreGerente.value = registro.nomina_cierra || 'Sin información';
        }

        if (campoNombreCierreGerente) {
            campoNombreCierreGerente.value = registro.nombre_cierra || 'Sin información';
        }

        if (campoFechaCierreGerente) {
            campoFechaCierreGerente.value = obtenerTextoFechaCierre(registro.fecha_cierre);
        }

        actualizarDisponibilidadAutorizacionGerente(true, true);
        mostrarFirmaCierreRegistrada(
            lienzoFirmaCierreGerente,
            registro.firma,
            'firmasGerente',
            tokenConsulta,
            idAnormalidad
        );
    }

    /**
     * Consulta la relación de reincidencia al mostrar la pestaña de Cierre.
     * El usuario únicamente puede consultar estos valores, nunca modificarlos.
     */
    async function cargarDatosReincidenciaCierre() {
        if (!modalDetalle) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';

        if (!/^\d+$/.test(idAnormalidad)) {
            return;
        }

        const tokenActual = ++tokenConsultaReincidenciaCierre;

        try {
            if (promesaCatalogosPrimeraRevision) {
                await promesaCatalogosPrimeraRevision;
            }

            const datosCierre = await consultarOperacionAnormalidad('21', {
                idAnormalidad: idAnormalidad
            });

            if (tokenActual !== tokenConsultaReincidenciaCierre ||
                modalDetalle.dataset.idAnormalidad !== idAnormalidad) {
                return;
            }

            const esReincidente = Number(datosCierre.es_reincidente) === 1;

            if (campoCierreReincidenteSi) {
                campoCierreReincidenteSi.checked = esReincidente;
                campoCierreReincidenteSi.disabled = true;
            }

            if (campoCierreReincidenteNo) {
                campoCierreReincidenteNo.checked = !esReincidente;
                campoCierreReincidenteNo.disabled = true;
            }

            if (campoCierreNumeroSeguimiento) {
                campoCierreNumeroSeguimiento.value = datosCierre.no_seguimiento || '';
                campoCierreNumeroSeguimiento.readOnly = true;
            }

            actualizarDisponibilidadAutorizacionGerente(
                Number(datosCierre.cierre_qa_firmado) === 1,
                Number(datosCierre.autorizacion_gerente_registrada) === 1
            );
            mostrarCierreQaRegistrado(datosCierre.cierre_qa, tokenActual, idAnormalidad);
            mostrarAutorizacionGerenteRegistrada(
                datosCierre.autorizacion_gerente,
                tokenActual,
                idAnormalidad
            );
        } catch (error) {
            if (tokenActual !== tokenConsultaReincidenciaCierre) {
                return;
            }

            mostrarToastAnormalidad(
                'error',
                'No se consultó la reincidencia',
                error.message || 'No fue posible consultar la información de reincidencia.',
                error
            );
        }
    }

    /**
     * Prepara el cierre de QA con la identidad del usuario autenticado.
     */
    function limpiarCierreReporteCalidad() {
        colocarUsuarioSesion(campoNominaCierreQa, campoNombreCierreQa);

        if (campoFechaCierreQa) {
            campoFechaCierreQa.value = '';
            campoFechaCierreQa.readOnly = true;
        }

        if (contenedorResponsableCierre) {
            const factorRegistrado = contenedorResponsableCierre.querySelector(
                '[data-factor-registrado]'
            );

            if (factorRegistrado) {
                factorRegistrado.remove();
            }
        }

        document.querySelectorAll('input[name="cierre_responsable"]').forEach(function (entrada) {
            entrada.checked = false;
            entrada.disabled = false;
        });

        if (lienzoFirmaCierreQa) {
            lienzoFirmaCierreQa.dataset.signatureLocked = 'false';
            lienzoFirmaCierreQa.style.pointerEvents = '';
            lienzoFirmaCierreQa.removeAttribute('aria-disabled');
            lienzoFirmaCierreQa.tabIndex = 0;
            limpiarFirma(lienzoFirmaCierreQa);
        } else if (campoFirmaCierreQa) {
            campoFirmaCierreQa.value = '';
        }

        if (botonLimpiarFirmaCierreQa) {
            botonLimpiarFirmaCierreQa.disabled = false;
        }

        if (botonRegistrarCierreQa) {
            botonRegistrarCierreQa.disabled = false;
            botonRegistrarCierreQa.removeAttribute('aria-busy');
            botonRegistrarCierreQa.innerHTML = contenidoBotonCierreQa;
        }
    }

    /**
     * Registra el cierre documentado por QA. La identidad se obtiene nuevamente
     * en el backend y el navegador únicamente envía el factor y la firma JPEG.
     */
    async function registroCierreReporteCalidad() {
        if (!modalDetalle || !campoFirmaCierreQa || !botonRegistrarCierreQa) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const responsableSeleccionado = document.querySelector(
            'input[name="cierre_responsable"]:checked'
        );
        const idFactor = responsableSeleccionado ? responsableSeleccionado.value : '';
        const firma = campoFirmaCierreQa.value.trim();
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad) || Number(idAnormalidad) < 1) {
            errores.push('No se identificó la anormalidad correspondiente.');
        }

        if (!/^\d+$/.test(idFactor) || Number(idFactor) < 1) {
            errores.push('Selecciona el factor responsable del cierre.');
        }

        if (!firma || firma.indexOf('data:image/jpeg;base64,') !== 0) {
            errores.push('Captura la firma de Staff QA antes de registrar el cierre.');
        }

        if (firma.length > 2800000) {
            errores.push('La firma capturada excede el tamaño permitido.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Cierre de QA incompleto', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarCierreQa.innerHTML;
        let registroExitoso = false;

        botonRegistrarCierreQa.disabled = true;
        botonRegistrarCierreQa.setAttribute('aria-busy', 'true');
        botonRegistrarCierreQa.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const cierreQaRegistrado = await consultarOperacionAnormalidad('22', {
                idAnormalidad: idAnormalidad,
                idFactor: idFactor,
                firma: firma
            });

            registroExitoso = true;
            actualizarEstatusFilaAnormalidad(idAnormalidad, cierreQaRegistrado);
            document.querySelectorAll('input[name="cierre_responsable"]').forEach(function (entrada) {
                entrada.disabled = true;
            });

            if (lienzoFirmaCierreQa) {
                lienzoFirmaCierreQa.dataset.signatureLocked = 'true';
                lienzoFirmaCierreQa.style.pointerEvents = 'none';
                lienzoFirmaCierreQa.setAttribute('aria-disabled', 'true');
                lienzoFirmaCierreQa.tabIndex = -1;
            }

            if (botonLimpiarFirmaCierreQa) {
                botonLimpiarFirmaCierreQa.disabled = true;
            }

            botonRegistrarCierreQa.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Cierre de QA registrado';

            actualizarDisponibilidadAutorizacionGerente(true, false);
            cargarDatosReincidenciaCierre();

            mostrarToastAnormalidad(
                'success',
                'Cierre de QA registrado',
                'El cierre de la anormalidad ' + obtenerReferenciaConsecutivoActual() + ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró el cierre de QA',
                error.message || 'No fue posible guardar el cierre documentado por Calidad.',
                error
            );
        } finally {
            botonRegistrarCierreQa.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarCierreQa.disabled = false;
                botonRegistrarCierreQa.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Habilita la firma del gerente únicamente después del cierre firmado por
     * QA y la bloquea definitivamente si ya existe la autorización final.
     */
    function actualizarDisponibilidadAutorizacionGerente(cierreQaFirmado, autorizacionRegistrada) {
        const disponible = cierreQaFirmado && !autorizacionRegistrada;

        if (lienzoFirmaCierreGerente) {
            lienzoFirmaCierreGerente.dataset.signatureLocked = disponible ? 'false' : 'true';
            lienzoFirmaCierreGerente.style.pointerEvents = disponible ? '' : 'none';
            lienzoFirmaCierreGerente.setAttribute('aria-disabled', disponible ? 'false' : 'true');
            lienzoFirmaCierreGerente.tabIndex = disponible ? 0 : -1;
        }

        if (botonLimpiarFirmaCierreGerente) {
            botonLimpiarFirmaCierreGerente.disabled = !disponible;
        }

        if (botonRegistrarCierreGerente) {
            botonRegistrarCierreGerente.disabled = !disponible;
            botonRegistrarCierreGerente.removeAttribute('aria-busy');

            if (autorizacionRegistrada) {
                botonRegistrarCierreGerente.innerHTML =
                    '<i class="bi bi-lock" aria-hidden="true"></i> Autorización registrada';
            } else if (!cierreQaFirmado) {
                botonRegistrarCierreGerente.innerHTML =
                    '<i class="bi bi-lock" aria-hidden="true"></i> Firma QA requerida';
            } else {
                botonRegistrarCierreGerente.innerHTML = contenidoBotonCierreGerente;
            }
        }
    }

    /**
     * Prepara la autorización final con la identidad del usuario autenticado.
     */
    function limpiarAutorizacionGerente() {
        colocarUsuarioSesion(campoNominaCierreGerente, campoNombreCierreGerente);

        if (campoFechaCierreGerente) {
            campoFechaCierreGerente.value = '';
            campoFechaCierreGerente.readOnly = true;
        }

        if (lienzoFirmaCierreGerente) {
            limpiarFirma(lienzoFirmaCierreGerente);
        } else if (campoFirmaCierreGerente) {
            campoFirmaCierreGerente.value = '';
        }

        actualizarDisponibilidadAutorizacionGerente(false, false);
    }

    /**
     * Envía la firma del gerente. El backend valida que Staff QA haya firmado
     * previamente el cierre antes de aceptar esta autorización.
     */
    async function registroAutorizacionGerente() {
        if (!modalDetalle || !campoFirmaCierreGerente || !botonRegistrarCierreGerente) {
            return;
        }

        const idAnormalidad = modalDetalle.dataset.idAnormalidad || '';
        const firma = campoFirmaCierreGerente.value.trim();
        const errores = [];

        if (!/^\d+$/.test(idAnormalidad) || Number(idAnormalidad) < 1) {
            errores.push('No se identificó la anormalidad correspondiente.');
        }

        if (!firma || firma.indexOf('data:image/jpeg;base64,') !== 0) {
            errores.push('Captura la firma del gerente antes de registrar la autorización.');
        }

        if (firma.length > 2800000) {
            errores.push('La firma capturada excede el tamaño permitido.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Autorización incompleta', errores[0]);
            return;
        }

        const contenidoOriginalBoton = botonRegistrarCierreGerente.innerHTML;
        let registroExitoso = false;

        botonRegistrarCierreGerente.disabled = true;
        botonRegistrarCierreGerente.setAttribute('aria-busy', 'true');
        botonRegistrarCierreGerente.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const autorizacionRegistrada = await consultarOperacionAnormalidad('23', {
                idAnormalidad: idAnormalidad,
                firma: firma
            });

            registroExitoso = true;
            sincronizarAnormalidadFinalizada(autorizacionRegistrada);

            if (lienzoFirmaCierreGerente) {
                lienzoFirmaCierreGerente.dataset.signatureLocked = 'true';
                lienzoFirmaCierreGerente.style.pointerEvents = 'none';
                lienzoFirmaCierreGerente.setAttribute('aria-disabled', 'true');
                lienzoFirmaCierreGerente.tabIndex = -1;
            }

            if (botonLimpiarFirmaCierreGerente) {
                botonLimpiarFirmaCierreGerente.disabled = true;
            }

            botonRegistrarCierreGerente.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Autorización registrada';

            cargarDatosReincidenciaCierre();

            mostrarToastAnormalidad(
                'success',
                'Autorización registrada',
                'El gerente autorizó el cierre de la anormalidad ' + obtenerReferenciaConsecutivoActual() + '.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la autorización',
                error.message || 'No fue posible guardar la autorización del gerente.',
                error
            );
        } finally {
            botonRegistrarCierreGerente.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarCierreGerente.disabled = false;
                botonRegistrarCierreGerente.innerHTML = contenidoOriginalBoton;
            }
        }
    }

    /**
     * Limpia la referencia cuando todavía no existe una línea seleccionada.
     */
    function reiniciarReferenciaSeguimiento(mensaje) {
        tokenReferenciaSeguimiento += 1;

        if (!campoNumeroSeguimiento) {
            return;
        }

        campoNumeroSeguimiento.value = '';
        campoNumeroSeguimiento.placeholder = mensaje || 'Selecciona una línea';
    }

    /**
     * Obtiene el siguiente consecutivo anual de la línea únicamente como
     * referencia. El backend deberá volver a calcularlo durante el INSERT.
     */
    async function cargarReferenciaSeguimiento() {
        if (!campoNumeroSeguimiento || !campoLinea) {
            return;
        }

        const codigoLinea = campoLinea.value;
        const tokenActual = ++tokenReferenciaSeguimiento;

        campoNumeroSeguimiento.value = '';
        campoNumeroSeguimiento.placeholder = codigoLinea
            ? 'Calculando referencia...'
            : 'Selecciona una línea';

        if (!codigoLinea) {
            return;
        }

        try {
            const referencia = await consultarOperacionAnormalidad('6', {
                codigoLinea: codigoLinea
            });

            if (tokenActual !== tokenReferenciaSeguimiento || campoLinea.value !== codigoLinea) {
                return;
            }

            if (!referencia || !referencia.numero_referencia) {
                throw new Error('La API no devolvió el número de referencia.');
            }

            campoNumeroSeguimiento.value = referencia.numero_referencia;
            campoNumeroSeguimiento.placeholder = 'Se validará al guardar';
        } catch (error) {
            if (tokenActual !== tokenReferenciaSeguimiento || campoLinea.value !== codigoLinea) {
                return;
            }

            campoNumeroSeguimiento.value = '';
            campoNumeroSeguimiento.placeholder = 'Referencia no disponible';
            mostrarToastAnormalidad(
                'error',
                'No se generó la referencia',
                'No fue posible calcular el número de seguimiento de la línea seleccionada.',
                { codigoLinea: codigoLinea, error: error }
            );
        }
    }

    /**
     * Carga el catálogo inicial de áreas.
     */
    async function cargarAreas() {
        reiniciarSelect(campoArea, 'Cargando áreas...', true);
        reiniciarSelect(campoLinea, 'Primero selecciona el área', true);
        reiniciarSelect(campoModelo, 'Primero selecciona la línea', true);
        reiniciarSelect(campoProcesoDetectado, 'Primero selecciona la línea', true);
        reiniciarSelect(campoProcesoGenerado, 'Primero selecciona la línea', true);
        reiniciarSelect(campoFiltroArea, 'Cargando áreas...', true);
        llenarSelectFiltro(campoFiltroLinea, [], 'codigo_linea', 'nombre_linea', 'Todas');
        reiniciarReferenciaSeguimiento('Selecciona una línea');

        try {
            const areas = await consultarCatalogo('2');

            if (!areas.length) {
                reiniciarSelect(campoArea, 'No hay áreas disponibles', true);
                llenarSelectFiltro(campoFiltroArea, [], 'id_area', 'nombre_area', 'Todas');
                return;
            }

            llenarSelect(campoArea, areas, 'id_area', 'nombre_area', 'Selecciona un área');
            llenarSelectFiltro(campoFiltroArea, areas, 'id_area', 'nombre_area', 'Todas');
        } catch (error) {
            reiniciarSelect(campoArea, 'No fue posible cargar las áreas', true);
            llenarSelectFiltro(campoFiltroArea, [], 'id_area', 'nombre_area', 'Todas');
            llenarSelectFiltro(campoFiltroLinea, [], 'codigo_linea', 'nombre_linea', 'Todas');
            mostrarToastAnormalidad(
                'error',
                'No se cargaron las áreas',
                'No fue posible obtener el catálogo de áreas.',
                error
            );
        }
    }

    /**
     * Carga únicamente las líneas pertenecientes al área seleccionada.
     */
    async function cargarLineasPorArea() {
        const idArea = campoArea ? campoArea.value : '';

        reiniciarSelect(campoLinea, idArea ? 'Cargando líneas...' : 'Primero selecciona el área', true);
        reiniciarSelect(campoModelo, 'Primero selecciona la línea', true);
        reiniciarSelect(campoProcesoDetectado, 'Primero selecciona la línea', true);
        reiniciarSelect(campoProcesoGenerado, 'Primero selecciona la línea', true);
        reiniciarReferenciaSeguimiento('Selecciona una línea');

        if (!idArea) {
            return;
        }

        try {
            const lineas = await consultarCatalogo('3', { idArea: idArea });

            // Descarta respuestas atrasadas si el usuario cambió de área.
            if (campoArea.value !== idArea) {
                return;
            }

            if (!lineas.length) {
                reiniciarSelect(campoLinea, 'No hay líneas para esta área', true);
                return;
            }

            llenarSelect(campoLinea, lineas, 'codigo_linea', 'nombre_linea', 'Selecciona una línea');
        } catch (error) {
            if (campoArea.value === idArea) {
                reiniciarSelect(campoLinea, 'No fue posible cargar las líneas', true);
                mostrarToastAnormalidad(
                    'error',
                    'No se cargaron las líneas',
                    'No fue posible obtener las líneas del área seleccionada.',
                    { idArea: idArea, error: error }
                );
            }
        }
    }

    /**
     * Carga las líneas del filtro según el área seleccionada. Si se elige
     * "Todas", la línea vuelve a su opción general sin consultar al servidor.
     */
    async function cargarLineasFiltroPorArea() {
        if (!campoFiltroArea || !campoFiltroLinea) {
            return;
        }

        const idArea = campoFiltroArea.value;

        llenarSelectFiltro(campoFiltroLinea, [], 'codigo_linea', 'nombre_linea', 'Todas');

        if (!idArea) {
            return;
        }

        campoFiltroLinea.disabled = true;

        try {
            const lineas = await consultarCatalogo('3', { idArea: idArea });

            if (campoFiltroArea.value !== idArea) {
                return;
            }

            llenarSelectFiltro(
                campoFiltroLinea,
                lineas,
                'codigo_linea',
                'nombre_linea',
                'Todas'
            );
        } catch (error) {
            if (campoFiltroArea.value !== idArea) {
                return;
            }

            llenarSelectFiltro(campoFiltroLinea, [], 'codigo_linea', 'nombre_linea', 'Todas');
            mostrarToastAnormalidad(
                'error',
                'No se cargaron las líneas',
                'No fue posible obtener las líneas del área seleccionada.',
                { idArea: idArea, error: error }
            );
        }
    }

    /**
     * Carga las estaciones de la línea en los dos campos de proceso.
     */
    async function cargarEstacionesPorLinea() {
        const codigoLinea = campoLinea ? campoLinea.value : '';
        const mensaje = codigoLinea ? 'Cargando estaciones...' : 'Primero selecciona la línea';

        reiniciarSelect(campoProcesoDetectado, mensaje, true);
        reiniciarSelect(campoProcesoGenerado, mensaje, true);

        if (!codigoLinea) {
            return;
        }

        try {
            const estaciones = await consultarCatalogo('4', { codigoLinea: codigoLinea });

            // Descarta respuestas atrasadas si el usuario cambió de línea.
            if (campoLinea.value !== codigoLinea) {
                return;
            }

            if (!estaciones.length) {
                reiniciarSelect(campoProcesoDetectado, 'No hay estaciones para esta línea', true);
                reiniciarSelect(campoProcesoGenerado, 'No hay estaciones para esta línea', true);
                return;
            }

            llenarSelect(
                campoProcesoDetectado,
                estaciones,
                'id_estacion',
                'nombre_estacion',
                'Selecciona la estación donde se detectó'
            );
            llenarSelect(
                campoProcesoGenerado,
                estaciones,
                'id_estacion',
                'nombre_estacion',
                'Selecciona la estación donde se generó'
            );
        } catch (error) {
            if (campoLinea.value === codigoLinea) {
                reiniciarSelect(campoProcesoDetectado, 'No fue posible cargar las estaciones', true);
                reiniciarSelect(campoProcesoGenerado, 'No fue posible cargar las estaciones', true);
                mostrarToastAnormalidad(
                    'error',
                    'No se cargaron las estaciones',
                    'No fue posible obtener las estaciones de la línea seleccionada.',
                    { codigoLinea: codigoLinea, error: error }
                );
            }
        }
    }

    /**
     * Carga los modelos activos asignados a la línea (SRD_MODELOS_LINEA).
     * Una línea sin modelos deja el campo deshabilitado con el aviso.
     */
    async function cargarModelosPorLinea() {
        if (!campoModelo) {
            return;
        }

        const codigoLinea = campoLinea ? campoLinea.value : '';

        reiniciarSelect(
            campoModelo,
            codigoLinea ? 'Cargando modelos...' : 'Primero selecciona la línea',
            true
        );

        if (!codigoLinea) {
            return;
        }

        try {
            const modelos = await consultarCatalogo('31', { codigoLinea: codigoLinea });

            // Descarta respuestas atrasadas si el usuario cambió de línea.
            if (campoLinea.value !== codigoLinea) {
                return;
            }

            if (!modelos.length) {
                reiniciarSelect(campoModelo, 'No hay modelos para esta línea', true);
                return;
            }

            llenarSelect(campoModelo, modelos, 'idModelo', 'modelo', 'Sin modelo');

            // El modelo es opcional: la primera opción permite volver a dejarlo vacío.
            campoModelo.options[0].disabled = false;
        } catch (error) {
            if (campoLinea.value === codigoLinea) {
                reiniciarSelect(campoModelo, 'No fue posible cargar los modelos', true);
                mostrarToastAnormalidad(
                    'error',
                    'No se cargaron los modelos',
                    'No fue posible obtener los modelos de la línea seleccionada.',
                    { codigoLinea: codigoLinea, error: error }
                );
            }
        }
    }

    /**
     * Selecciona el turno activo que corresponde a la hora indicada.
     * Turno 1: de 08:00 inclusive a 20:00 no inclusive.
     * Turno 2: de 20:00 inclusive a 08:00 no inclusive.
     */
    function seleccionarTurnoActual(fecha) {
        if (!campoTurno || !campoTurnoId) {
            return;
        }

        const claveTurno = obtenerClaveTurnoActual(fecha);
        const opcionTurno = Array.from(campoTurno.options).find(function (opcion) {
            return opcion.dataset.clave === claveTurno;
        });

        if (!opcionTurno) {
            campoTurno.value = '';
            campoTurnoId.value = '';
            return;
        }

        campoTurno.value = opcionTurno.value;
        campoTurnoId.value = opcionTurno.value;
    }

    /**
     * Obtiene la clave del turno según el horario definido para anormalidades.
     * Turno 1: 08:00 a 19:59. Turno 2: 20:00 a 07:59.
     */
    function obtenerClaveTurnoActual(fecha) {
        const minutosActuales = (fecha.getHours() * 60) + fecha.getMinutes();
        return minutosActuales >= 480 && minutosActuales < 1200 ? '1' : '2';
    }

    /**
     * Selecciona y mantiene bloqueado el turno automático de la reincidencia.
     */
    function seleccionarTurnoReincidenciaActual(fecha) {
        if (!campoReincidenciaTurno) {
            return;
        }

        const claveTurno = obtenerClaveTurnoActual(fecha);
        const opcionTurno = Array.from(campoReincidenciaTurno.options).find(function (opcion) {
            return opcion.dataset.clave === claveTurno;
        });

        campoReincidenciaTurno.value = opcionTurno ? opcionTurno.value : '';
        campoReincidenciaTurno.disabled = true;
    }

    /**
     * Carga los turnos activos. La CLAVE define el horario y el IDe es el valor
     * que se registra como llave foránea.
     */
    async function cargarTurnos() {
        if ((!campoTurno || !campoTurnoId) && !campoReincidenciaTurno && !campoFiltroTurno) {
            return;
        }

        if (campoTurno && campoTurnoId) {
            reiniciarSelect(campoTurno, 'Cargando turno...', true);
            campoTurnoId.value = '';
        }

        if (campoReincidenciaTurno) {
            reiniciarSelect(campoReincidenciaTurno, 'Cargando turnos...', true);
        }

        reiniciarSelect(campoFiltroTurno, 'Cargando turnos...', true);

        try {
            const turnos = await consultarCatalogo('5');

            if (!turnos.length) {
                if (campoTurno) {
                    reiniciarSelect(campoTurno, 'No hay turnos activos', true);
                }

                if (campoReincidenciaTurno) {
                    reiniciarSelect(campoReincidenciaTurno, 'No hay turnos activos', true);
                }

                llenarSelectFiltro(campoFiltroTurno, [], 'id_turno', 'nombre_turno', 'Todos los turnos');
                return;
            }

            llenarSelectFiltro(
                campoFiltroTurno,
                turnos,
                'id_turno',
                'nombre_turno',
                'Todos los turnos'
            );

            if (campoTurno) {
                reiniciarSelect(campoTurno, 'Turno no disponible', true);
            }

            if (campoReincidenciaTurno) {
                reiniciarSelect(campoReincidenciaTurno, 'Turno no disponible', true);
            }

            turnos.forEach(function (turno) {
                if (campoTurno) {
                    const opcionTurnoInicial = new Option(turno.nombre_turno, turno.id_turno);
                    opcionTurnoInicial.dataset.clave = String(turno.clave).trim();
                    campoTurno.appendChild(opcionTurnoInicial);
                }

                if (campoReincidenciaTurno) {
                    const opcionTurnoReincidencia = new Option(turno.nombre_turno, turno.id_turno);
                    opcionTurnoReincidencia.dataset.clave = String(turno.clave).trim();
                    campoReincidenciaTurno.appendChild(opcionTurnoReincidencia);
                }
            });

            if (campoTurno && campoTurnoId) {
                seleccionarTurnoActual(new Date());
            }

            if (campoReincidenciaTurno) {
                seleccionarTurnoReincidenciaActual(new Date());
            }
        } catch (error) {
            if (campoTurno) {
                reiniciarSelect(campoTurno, 'No fue posible cargar el turno', true);
            }

            if (campoTurnoId) {
                campoTurnoId.value = '';
            }

            if (campoReincidenciaTurno) {
                reiniciarSelect(campoReincidenciaTurno, 'No fue posible cargar los turnos', true);
            }

            llenarSelectFiltro(campoFiltroTurno, [], 'id_turno', 'nombre_turno', 'Todos los turnos');
            mostrarToastAnormalidad(
                'error',
                'No se cargó el turno',
                'No fue posible obtener el catálogo de turnos activos.',
                error
            );
        }
    }

    /**
     * Devuelve la fecha local en el formato requerido por datetime-local.
     */
    function obtenerFechaHoraLocal(fechaReferencia) {
        const fecha = fechaReferencia || new Date();
        const completar = function (valor) {
            return String(valor).padStart(2, '0');
        };

        return fecha.getFullYear() + '-' +
            completar(fecha.getMonth() + 1) + '-' +
            completar(fecha.getDate()) + 'T' +
            completar(fecha.getHours()) + ':' +
            completar(fecha.getMinutes());
    }

    /**
     * Sincroniza los campos automáticos usando una sola lectura del reloj.
     */
    function actualizarFechaYTurno() {
        const fechaActual = new Date();

        if (campoFechaDeteccion) {
            campoFechaDeteccion.value = obtenerFechaHoraLocal(fechaActual);
        }

        seleccionarTurnoActual(fechaActual);
    }

    /**
     * Llena la fecha y el turno de referencia de la reincidencia sin permitir
     * su edición.
     */
    function actualizarFechaYTurnoReincidencia() {
        const fechaActual = new Date();

        if (campoReincidenciaFechaHora) {
            campoReincidenciaFechaHora.value = obtenerFechaHoraLocal(fechaActual);
            campoReincidenciaFechaHora.readOnly = true;
        }

        seleccionarTurnoReincidenciaActual(fechaActual);
    }

    /**
     * Coloca texto dentro de un campo de consulta del modal.
     * Se usa textContent para mantener la maqueta simple y segura.
     */
    function mostrarDatoDetalle(idCampo, valor) {
        const campo = document.getElementById(idCampo);

        if (campo) {
            campo.textContent = valor || 'Sin información';
        }
    }

    /**
     * Recupera el texto de una celda de la fila seleccionada.
     */
    function obtenerTextoFila(fila, selector) {
        const elemento = fila ? fila.querySelector(selector) : null;
        return elemento ? elemento.textContent.trim() : '';
    }

    /**
     * Prepara el formulario de reincidencia con los datos de la fila elegida.
     * Cada apertura reinicia el detalle y conserva como consulta los datos
     * generales de la anormalidad seleccionada.
     */
    function cargarDatosReincidencia(fila) {
        if (!fila) {
            return;
        }

        const idRegistro = obtenerTextoFila(fila, '.anomaly-id');
        const area = obtenerTextoFila(fila, '.area-badge');
        const linea = obtenerTextoFila(fila, '.line-name');
        const datosFila = tablaAnormalidades ? tablaAnormalidades.row(fila).data() : null;
        const numeroSeguimiento = datosFila && datosFila.no_seguimiento
            ? datosFila.no_seguimiento
            : obtenerTextoFila(fila, 'td:nth-child(3) .cell-note');

        if (formularioReincidencia) {
            formularioReincidencia.reset();
        }

        aplicarBloqueoAnormalidadFinalizada(modalReincidencia, false);

        if (campoReincidenciaRegistro) {
            campoReincidenciaRegistro.value = idRegistro;
        }

        if (campoReincidenciaSeguimiento) {
            campoReincidenciaSeguimiento.value = numeroSeguimiento;
        }

        actualizarFechaYTurnoReincidencia();

        if (campoReincidenciaArea) {
            campoReincidenciaArea.value = area;
        }

        if (campoReincidenciaLinea) {
            campoReincidenciaLinea.value = linea;
        }

        mostrarDatoDetalle('reincidenciaRegistroId', 'ID ' + idRegistro);

        aplicarBloqueoAnormalidadFinalizada(
            modalReincidencia,
            String(datosFila && datosFila.clave_estatus || '').trim() === '6'
        );
    }

    /**
     * Actualiza localmente la fila para impedir otra captura del encabezado.
     */
    function marcarReincidenciaRegistrada(idAnormalidad) {
        if (!tablaAnormalidades) {
            return;
        }

        tablaAnormalidades.rows().every(function () {
            const registro = this.data();

            if (registro && String(registro.id_anormalidad) === String(idAnormalidad)) {
                registro.tiene_reincidencia = 1;
                this.data(registro);
            }
        });
        tablaAnormalidades.draw(false);
    }

    /**
     * Sustituye únicamente los datos de estatus de la fila indicada. DataTables
     * conserva la página actual y no realiza una nueva petición al servidor.
     */
    function actualizarEstatusFilaAnormalidad(idAnormalidad, estatus) {
        if (!tablaAnormalidades || !estatus) {
            return;
        }

        let filaActualizada = false;

        tablaAnormalidades.rows().every(function () {
            const registro = this.data();

            if (!filaActualizada && registro &&
                String(registro.id_anormalidad) === String(idAnormalidad)) {
                registro.id_estatus_actual = estatus.id_estatus_actual;
                registro.clave_estatus = estatus.clave_estatus;
                registro.estatus_actual = estatus.estatus_actual;
                this.data(registro);
                filaActualizada = true;
            }
        });

        if (filaActualizada) {
            tablaAnormalidades.draw(false);
        }
    }

    /**
     * Valida y registra el encabezado único de reincidencia de la anormalidad.
     * El backend vuelve a validar y usa su propio reloj para fecha y turno.
     */
    async function registroNuevaReincidencia() {
        if (!formularioReincidencia || !botonGuardarReincidencia) {
            return;
        }

        const datosRegistro = {
            idAnormalidad: campoReincidenciaRegistro ? campoReincidenciaRegistro.value.trim() : '',
            numeroSeguimiento: campoReincidenciaSeguimiento
                ? campoReincidenciaSeguimiento.value.trim()
                : '',
            claveTipoReincidencia: campoReincidenciaTipoClave
                ? campoReincidenciaTipoClave.value.trim()
                : '',
            detalleAnormalidad: campoReincidenciaDetalle
                ? campoReincidenciaDetalle.value.trim()
                : ''
        };
        const errores = [];

        if (!/^\d+$/.test(datosRegistro.idAnormalidad) || Number(datosRegistro.idAnormalidad) < 1) {
            errores.push('No se identificó la anormalidad seleccionada.');
        }

        if (!datosRegistro.numeroSeguimiento || datosRegistro.numeroSeguimiento.length > 50) {
            errores.push('El número de seguimiento de la anormalidad no es válido.');
        }

        if (!datosRegistro.claveTipoReincidencia ||
            datosRegistro.claveTipoReincidencia.length > 50) {
            errores.push('El tipo de reincidencia no está disponible.');
        }

        if (!datosRegistro.detalleAnormalidad || datosRegistro.detalleAnormalidad.length > 600) {
            errores.push('Captura el detalle de la anormalidad con un máximo de 600 caracteres.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Registro incompleto', errores[0]);

            if (!datosRegistro.detalleAnormalidad && campoReincidenciaDetalle) {
                campoReincidenciaDetalle.focus();
            }
            return;
        }

        botonGuardarReincidencia.disabled = true;
        botonGuardarReincidencia.setAttribute('aria-busy', 'true');
        botonGuardarReincidencia.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            const registroCreado = await consultarOperacionAnormalidad('24', datosRegistro);

            marcarReincidenciaRegistrada(datosRegistro.idAnormalidad);

            if (window.bootstrap && modalReincidencia) {
                window.bootstrap.Modal.getOrCreateInstance(modalReincidencia).hide();
            }

            mostrarToastAnormalidad(
                'success',
                'Reincidencia registrada',
                'La reincidencia de la anormalidad ' +
                    (registroCreado.no_seguimiento || datosRegistro.numeroSeguimiento) +
                    ' se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la reincidencia',
                error.message || 'No fue posible registrar la reincidencia.',
                error
            );
        } finally {
            botonGuardarReincidencia.disabled = false;
            botonGuardarReincidencia.removeAttribute('aria-busy');
            botonGuardarReincidencia.innerHTML = contenidoBotonGuardarReincidencia;
        }
    }

    /**
     * Retira las clases temporales usadas por la animación de ambas vistas.
     */
    function limpiarTransicionReincidencias() {
        if (!modalListadoReincidencias) {
            return;
        }

        modalListadoReincidencias.querySelectorAll('.anomaly-recurrence-view').forEach(function (vista) {
            vista.classList.remove(
                'is-exiting-forward',
                'is-entering-forward',
                'is-exiting-back',
                'is-entering-back'
            );
        });
    }

    /**
     * Cambia entre las dos vistas con un desplazamiento corto y desvanecido.
     * El token evita que una animación pendiente modifique un modal cerrado.
     */
    function cambiarVistaReincidencias(vistaSalida, vistaEntrada, direccion) {
        if (!vistaSalida || !vistaEntrada || !modalListadoReincidencias) {
            return;
        }

        const reducirMovimiento = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const claseSalida = direccion === 'forward'
            ? 'is-exiting-forward'
            : 'is-exiting-back';
        const claseEntrada = direccion === 'forward'
            ? 'is-entering-forward'
            : 'is-entering-back';
        const tokenActual = ++tokenTransicionReincidencia;

        limpiarTransicionReincidencias();

        if (reducirMovimiento) {
            vistaSalida.hidden = true;
            vistaEntrada.hidden = false;
            delete modalListadoReincidencias.dataset.viewTransitioning;
            return;
        }

        modalListadoReincidencias.dataset.viewTransitioning = 'true';
        vistaSalida.classList.add(claseSalida);

        window.setTimeout(function () {
            if (tokenActual !== tokenTransicionReincidencia) {
                return;
            }

            vistaSalida.hidden = true;
            vistaSalida.classList.remove(claseSalida);
            vistaEntrada.classList.add(claseEntrada);
            vistaEntrada.hidden = false;

            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    if (tokenActual !== tokenTransicionReincidencia) {
                        return;
                    }

                    vistaEntrada.classList.remove(claseEntrada);

                    window.setTimeout(function () {
                        if (tokenActual === tokenTransicionReincidencia) {
                            delete modalListadoReincidencias.dataset.viewTransitioning;
                        }
                    }, 210);
                });
            });
        }, 200);
    }

    /**
     * Restaura el contenido principal del modal para volver al historial.
     */
    function mostrarVistaListadoReincidencias(conTransicion) {
        const vistaListado = document.getElementById('vistaListadoReincidencias');
        const vistaDetalle = document.getElementById('vistaDetalleReincidencia');

        mostrarDatoDetalle('listadoReincidenciasEyebrow', 'Historial relacionado');
        mostrarDatoDetalle('modalListadoReincidenciasLabel', 'Reincidencias registradas');

        if (conTransicion && vistaDetalle && !vistaDetalle.hidden) {
            cambiarVistaReincidencias(vistaDetalle, vistaListado, 'back');
            return;
        }

        ++tokenTransicionReincidencia;
        limpiarTransicionReincidencias();
        delete modalListadoReincidencias.dataset.viewTransitioning;

        if (vistaListado) {
            vistaListado.hidden = false;
        }

        if (vistaDetalle) {
            vistaDetalle.hidden = true;
        }
    }

    /**
     * Devuelve el detalle general a su estado inicial para que cada apertura
     * del historial comience con la sección contraída.
     */
    function contraerDetalleBaseReincidencias() {
        if (!detalleBaseReincidencias || !botonDetalleBaseReincidencias) {
            return;
        }

        detalleBaseReincidencias.classList.remove('show', 'collapsing');
        detalleBaseReincidencias.classList.add('collapse');
        detalleBaseReincidencias.style.removeProperty('height');
        botonDetalleBaseReincidencias.classList.add('collapsed');
        botonDetalleBaseReincidencias.setAttribute('aria-expanded', 'false');
    }

    /**
     * Activa los tooltips de los botones de seguimiento, incluyendo los que
     * se agregan después de actualizar el historial desde el servidor.
     */
    function inicializarTooltipsSeguimiento(contenedor) {
        if (!contenedor || !window.bootstrap || !window.bootstrap.Tooltip) {
            return;
        }

        contenedor.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (boton) {
            window.bootstrap.Tooltip.getOrCreateInstance(boton, {
                container: modalListadoReincidencias
            });
        });
    }

    /**
     * Confirma y crea una nueva anormalidad dentro del encabezado de
     * reincidencia abierto. El usuario no captura datos en este primer paso.
     */
    async function registrarAnormalidadReincidente() {
        if (!modalListadoReincidencias || !botonAgregarReincidencia ||
            modalListadoReincidencias.dataset.viewTransitioning === 'true' ||
            botonAgregarReincidencia.disabled) {
            return;
        }

        const idAnormalidad = modalListadoReincidencias.dataset.registroId || '';
        const idReincidencia = modalListadoReincidencias.dataset.idReincidencia || '';
        const numeroSeguimiento = modalListadoReincidencias.dataset.numeroSeguimiento || '';

        if (!/^\d+$/.test(idAnormalidad) || !/^\d+$/.test(idReincidencia)) {
            mostrarToastAnormalidad(
                'warning',
                'Reincidencia no registrada',
                'Registra primero la reincidencia general de esta anormalidad.'
            );
            return;
        }

        const referencia = numeroSeguimiento || 'seleccionada';
        const confirmado = window.confirm(
            '¿Desea hacer un nuevo registro de anormalidad reincidente para la anormalidad ' +
            referencia + '?'
        );

        if (!confirmado) {
            return;
        }

        botonAgregarReincidencia.disabled = true;
        botonAgregarReincidencia.setAttribute('aria-busy', 'true');
        botonAgregarReincidencia.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        try {
            await consultarOperacionAnormalidad('27', {
                idAnormalidad: idAnormalidad,
                idReincidencia: idReincidencia
            });

            try {
                await actualizarHistorialReincidencias(idAnormalidad);
            } catch (errorActualizacion) {
                mostrarToastAnormalidad(
                    'warning',
                    'Reincidencia registrada',
                    'El registro se guardó, pero no fue posible actualizar el historial. ' +
                        'Vuelve a abrir la ventana para consultarlo.',
                    errorActualizacion
                );
                return;
            }

            mostrarToastAnormalidad(
                'success',
                'Reincidencia agregada',
                'El nuevo registro de la anormalidad ' + referencia +
                    ' se agregó al historial.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se agregó la reincidencia',
                error.message || 'No fue posible crear el registro solicitado.',
                error
            );
        } finally {
            botonAgregarReincidencia.disabled = false;
            botonAgregarReincidencia.removeAttribute('aria-busy');
            botonAgregarReincidencia.innerHTML = contenidoBotonAgregarReincidencia;
        }
    }

    /**
     * Sustituye el cuerpo de la tabla por un estado informativo accesible.
     */
    function mostrarEstadoTablaReincidencias(titulo, descripcion, icono) {
        const cuerpoTabla = tablaReincidencias ? tablaReincidencias.querySelector('tbody') : null;

        if (!cuerpoTabla) {
            return;
        }

        cuerpoTabla.innerHTML =
            '<tr id="listadoReincidenciasVacio"><td colspan="7" class="anomaly-recurrence-empty">' +
            '<i class="bi ' + escaparHtml(icono || 'bi-inbox') + '" aria-hidden="true"></i>' +
            '<strong>' + escaparHtml(titulo) + '</strong>' +
            '<span>' + escaparHtml(descripcion) + '</span></td></tr>';
    }

    /**
     * Genera las filas con los detalles pertenecientes al encabezado consultado.
     */
    function mostrarSeguimientosReincidencia(seguimientos, reincidencia) {
        const cuerpoTabla = tablaReincidencias ? tablaReincidencias.querySelector('tbody') : null;

        if (!cuerpoTabla) {
            return;
        }

        cuerpoTabla.innerHTML = '';

        if (!seguimientos.length) {
            mostrarEstadoTablaReincidencias(
                'Sin seguimientos registrados',
                reincidencia
                    ? 'Esta reincidencia todavía no cuenta con anormalidades relacionadas.'
                    : 'La anormalidad todavía no tiene un registro general de reincidencia.',
                'bi-inbox'
            );
            return;
        }

        seguimientos.forEach(function (seguimiento) {
            const consecutivo = Number(seguimiento.consecutivo) || 0;
            const fecha = formatearFechaListado(seguimiento.fecha_hora);
            const datosGeneralesRegistrados = seguimiento.fecha_hora !== null &&
                seguimiento.cantidad !== null && seguimiento.cantidad !== undefined &&
                seguimiento.id_dispocicion !== null &&
                seguimiento.id_dispocicion !== undefined &&
                Boolean(seguimiento.nomina_registraDET) &&
                Boolean(seguimiento.nombre_reguitraDET);
            const disposicion = seguimiento.disposicion_descripcion ||
                seguimiento.disposicion || 'Pendiente de registro';
            const cantidad = seguimiento.cantidad === null ||
                seguimiento.cantidad === undefined
                ? 'Pendiente'
                : seguimiento.cantidad;
            const causaContramedidaRegistrada = Boolean(seguimiento.causa) &&
                Boolean(seguimiento.contramedida) &&
                Boolean(seguimiento.nomina_atiende) &&
                Boolean(seguimiento.nombre_atiende);
            const confirmacionesQa = Array.isArray(seguimiento.confirmaciones_qa)
                ? seguimiento.confirmaciones_qa
                : [];
            const clavesConfirmacionQa = new Set(confirmacionesQa.map(function (confirmacion) {
                return String(confirmacion.momento_clave || '') + '_' +
                    String(confirmacion.no_revision || '');
            }));
            const revisionesQaEsperadas = ['1_1', '1_2', '1_3', '2_1', '2_2', '2_3'];
            const confirmacionesQaCompletas = confirmacionesQa.length === 6 &&
                revisionesQaEsperadas.every(function (clave) {
                    return clavesConfirmacionQa.has(clave);
                });
            const inspectorQaCompleto = Boolean(seguimiento.nomina_inspectorQA) &&
                Boolean(seguimiento.nombre_inspectorQA);
            const estadoConfirmacionQa = confirmacionesQaCompletas && inspectorQaCompleto
                ? 'complete'
                : (confirmacionesQa.length > 0 || seguimiento.nomina_inspectorQA ||
                   seguimiento.nombre_inspectorQA ? 'partial' : 'pending');
            const causa = seguimiento.causa || 'Pendiente de registro';
            const contramedida = seguimiento.contramedida || 'Pendiente de registro';
            const filaSeguimiento = document.createElement('tr');

            filaSeguimiento.dataset.recurrenceItem = String(consecutivo);
            filaSeguimiento.dataset.idAnormalidadReincidente =
                String(seguimiento.id_anormalidad_reincidente || '');
            filaSeguimiento.dataset.followDateTime = String(seguimiento.fecha_hora || '');
            filaSeguimiento.dataset.followDispositionId =
                String(seguimiento.id_dispocicion || '');
            filaSeguimiento.dataset.followNominaRegistra =
                String(seguimiento.nomina_registraDET || '');
            filaSeguimiento.dataset.followNombreRegistra =
                String(seguimiento.nombre_reguitraDET || '');
            filaSeguimiento.dataset.followRegistered =
                datosGeneralesRegistrados ? 'true' : 'false';
            filaSeguimiento.dataset.causeRegistered =
                causaContramedidaRegistrada ? 'true' : 'false';
            filaSeguimiento.dataset.followCauseRaw = String(seguimiento.causa || '');
            filaSeguimiento.dataset.followCountermeasureRaw =
                String(seguimiento.contramedida || '');
            filaSeguimiento.dataset.followNominaAtiende =
                String(seguimiento.nomina_atiende || '');
            filaSeguimiento.dataset.followNombreAtiende =
                String(seguimiento.nombre_atiende || '');
            filaSeguimiento.dataset.qaStatus = estadoConfirmacionQa;
            filaSeguimiento.dataset.qaConfirmations = JSON.stringify(confirmacionesQa);
            filaSeguimiento.dataset.qaInspectorPayroll =
                String(seguimiento.nomina_inspectorQA || '');
            filaSeguimiento.dataset.qaInspectorName =
                String(seguimiento.nombre_inspectorQA || '');
            filaSeguimiento.dataset.recurrenceDate = fecha.fecha;
            filaSeguimiento.dataset.recurrenceTime = fecha.hora;
            filaSeguimiento.dataset.recurrenceShift = reincidencia ? reincidencia.turno || '' : '';
            filaSeguimiento.dataset.recurrenceType = reincidencia
                ? reincidencia.tipo_reincidencia_nombre || reincidencia.tipo_reincidencia || ''
                : '';
            filaSeguimiento.dataset.recurrenceStatus = seguimiento.causa
                ? 'Análisis registrado'
                : 'Pendiente de análisis';
            filaSeguimiento.dataset.recurrenceDescription = reincidencia
                ? reincidencia.detalle_anormalidad || ''
                : '';
            filaSeguimiento.dataset.followQuantity = String(seguimiento.cantidad ?? '');
            filaSeguimiento.dataset.followDisposition = disposicion;
            filaSeguimiento.dataset.followCause = causa;
            filaSeguimiento.dataset.followCountermeasure = contramedida;

            filaSeguimiento.innerHTML =
                '<td class="text-center"><button type="button" ' +
                'class="btn btn-outline-custom anomaly-recurrence-follow-button" ' +
                'data-bs-toggle="tooltip" data-bs-placement="top" title="Dar seguimiento" ' +
                'aria-label="Dar seguimiento al registro ' + escaparHtml(consecutivo) + '">' +
                '<i class="bi bi-arrow-right-circle" aria-hidden="true"></i></button></td>' +
                '<td><span class="consecutive-badge">' +
                escaparHtml(String(consecutivo).padStart(2, '0')) + '</span></td>' +
                '<td><strong class="detection-time">' + escaparHtml(fecha.hora) + '</strong>' +
                '<small class="cell-note">' + escaparHtml(fecha.fecha) + '</small></td>' +
                '<td><strong>' + escaparHtml(cantidad) + '</strong></td>' +
                '<td>' + escaparHtml(disposicion) + '</td>' +
                '<td>' + escaparHtml(causa) + '</td>' +
                '<td>' + escaparHtml(contramedida) + '</td>';

            cuerpoTabla.appendChild(filaSeguimiento);
        });

        inicializarTooltipsSeguimiento(cuerpoTabla);
    }

    /**
     * Vuelve a consultar las filas reales del historial sin cambiar la vista
     * interna que el usuario tenga abierta en el modal.
     */
    async function actualizarHistorialReincidencias(idAnormalidad) {
        const datosActualizados = await consultarOperacionAnormalidad('25', {
            idAnormalidad: idAnormalidad
        });

        if (!datosActualizados || !datosActualizados.anormalidad ||
            !datosActualizados.reincidencia ||
            !Array.isArray(datosActualizados.seguimientos)) {
            throw new Error('El historial actualizado no tiene el formato esperado.');
        }

        modalListadoReincidencias.dataset.recurrenceTotal =
            String(datosActualizados.seguimientos.length);
        modalListadoReincidencias.dataset.numeroSeguimiento =
            String(datosActualizados.anormalidad.no_seguimiento || '');
        modalListadoReincidencias.dataset.idReincidencia =
            String(datosActualizados.reincidencia.id_reincidencia);
        if (String(datosActualizados.anormalidad.clave_estatus || '').trim() === '6') {
            sincronizarAnormalidadFinalizada(datosActualizados.anormalidad);
        } else {
            aplicarBloqueoAnormalidadFinalizada(modalListadoReincidencias, false);
        }
        mostrarSeguimientosReincidencia(
            datosActualizados.seguimientos,
            datosActualizados.reincidencia
        );

        return datosActualizados;
    }

    /**
     * Consulta el encabezado y los seguimientos reales de la anormalidad.
     */
    async function cargarListadoReincidencias(fila) {
        if (!fila || !modalListadoReincidencias) {
            return;
        }

        const idRegistro = obtenerTextoFila(fila, '.anomaly-id');
        const datosFila = tablaAnormalidades ? tablaAnormalidades.row(fila).data() : null;

        if (!/^\d+$/.test(idRegistro)) {
            mostrarToastAnormalidad(
                'warning',
                'Registro no disponible',
                'No fue posible identificar la anormalidad seleccionada.'
            );
            return;
        }

        const tokenActual = ++tokenConsultaListadoReincidencias;

        modalListadoReincidencias.dataset.registroId = idRegistro;
        aplicarBloqueoAnormalidadFinalizada(modalListadoReincidencias, false);
        aplicarBloqueoAnormalidadFinalizada(
            modalListadoReincidencias,
            String(datosFila && datosFila.clave_estatus || '').trim() === '6'
        );
        modalListadoReincidencias.dataset.recurrenceTotal = '0';
        delete modalListadoReincidencias.dataset.idReincidencia;
        delete modalListadoReincidencias.dataset.numeroSeguimiento;
        mostrarVistaListadoReincidencias();
        contraerDetalleBaseReincidencias();
        mostrarDatoDetalle('listadoReincidenciasRegistroId', 'ID ' + idRegistro);
        mostrarDatoDetalle('seguimientoAnormalidadId', 'ID ' + idRegistro);
        mostrarDatoDetalle('detalleBaseIdReincidencia', 'Cargando...');
        mostrarDatoDetalle('detalleBaseNumeroSeguimiento', 'Cargando...');
        mostrarDatoDetalle('seguimientoFechaDeteccion', 'Cargando...');
        mostrarDatoDetalle('seguimientoTurnoBase', 'Cargando...');
        mostrarDatoDetalle('seguimientoAreaBase', 'Cargando...');
        mostrarDatoDetalle('seguimientoLineaBase', 'Cargando...');
        mostrarDatoDetalle('detalleBaseRegistradoPor', 'Cargando...');
        mostrarDatoDetalle('detalleBaseTipoReincidencia', 'Cargando...');
        mostrarDatoDetalle('detalleBaseDescripcionReincidencia', 'Cargando...');
        mostrarEstadoTablaReincidencias(
            'Consultando seguimientos',
            'Espera mientras se obtiene la información registrada.',
            'bi-arrow-repeat'
        );

        try {
            const datos = await consultarOperacionAnormalidad('25', {
                idAnormalidad: idRegistro
            });

            if (tokenActual !== tokenConsultaListadoReincidencias ||
                modalListadoReincidencias.dataset.registroId !== idRegistro) {
                return;
            }

            if (!datos || !datos.anormalidad || !Array.isArray(datos.seguimientos)) {
                throw new Error('La información de reincidencias no tiene el formato esperado.');
            }

            const anormalidad = datos.anormalidad;
            const reincidencia = datos.reincidencia;
            const seguimientos = datos.seguimientos;
            const fechaRegistro = reincidencia
                ? formatearFechaListado(reincidencia.fecha_hora)
                : null;

            modalListadoReincidencias.dataset.recurrenceTotal = String(seguimientos.length);
            modalListadoReincidencias.dataset.numeroSeguimiento =
                String(anormalidad.no_seguimiento || '');
            if (String(anormalidad.clave_estatus || '').trim() === '6') {
                sincronizarAnormalidadFinalizada(anormalidad);
            } else {
                aplicarBloqueoAnormalidadFinalizada(modalListadoReincidencias, false);
            }

            if (reincidencia) {
                modalListadoReincidencias.dataset.idReincidencia =
                    String(reincidencia.id_reincidencia);
            }

            mostrarDatoDetalle('seguimientoAnormalidadId', 'ID ' + anormalidad.id_anormalidad);
            mostrarDatoDetalle(
                'detalleBaseIdReincidencia',
                reincidencia ? 'ID ' + reincidencia.id_reincidencia : 'Sin registro'
            );
            mostrarDatoDetalle('detalleBaseNumeroSeguimiento', anormalidad.no_seguimiento);
            mostrarDatoDetalle(
                'seguimientoFechaDeteccion',
                fechaRegistro ? fechaRegistro.fecha + ' · ' + fechaRegistro.hora : 'Sin registro'
            );
            mostrarDatoDetalle(
                'seguimientoTurnoBase',
                reincidencia ? 'Turno ' + reincidencia.turno : 'Sin registro'
            );
            mostrarDatoDetalle('seguimientoAreaBase', anormalidad.nombre_area);
            mostrarDatoDetalle(
                'seguimientoLineaBase',
                anormalidad.nombre_linea || anormalidad.codigo_linea
            );
            mostrarDatoDetalle(
                'detalleBaseRegistradoPor',
                reincidencia ? reincidencia.registrado_por : 'Sin registro'
            );
            mostrarDatoDetalle(
                'detalleBaseTipoReincidencia',
                reincidencia
                    ? reincidencia.tipo_reincidencia_nombre || reincidencia.tipo_reincidencia
                    : 'Sin registro'
            );
            mostrarDatoDetalle(
                'detalleBaseDescripcionReincidencia',
                reincidencia ? reincidencia.detalle_anormalidad : 'Sin registro'
            );
            mostrarSeguimientosReincidencia(seguimientos, reincidencia);
        } catch (error) {
            if (tokenActual !== tokenConsultaListadoReincidencias) {
                return;
            }

            mostrarEstadoTablaReincidencias(
                'No se cargaron los seguimientos',
                'Intenta abrir nuevamente el historial de reincidencias.',
                'bi-exclamation-triangle'
            );
            mostrarToastAnormalidad(
                'error',
                'No se consultaron las reincidencias',
                error.message || 'No fue posible obtener la información registrada.',
                error
            );
        }
    }

    /**
     * Completa una sola vez los datos generales de la anormalidad reincidente
     * seleccionada. La identidad del usuario se resolverá en el backend.
     */
    async function registrarDatosSeguimientoReincidencia() {
        if (!modalListadoReincidencias || !botonRegistrarDatosSeguimientoReincidencia ||
            botonRegistrarDatosSeguimientoReincidencia.disabled) {
            return;
        }

        const disposicionSeleccionada = contenedorDisposicionSeguimientoReincidencia
            ? contenedorDisposicionSeguimientoReincidencia.querySelector(
                'input[name="seguimiento_reincidencia_disposicion"]:checked'
            )
            : null;
        const datosRegistro = {
            idAnormalidad: modalListadoReincidencias.dataset.registroId || '',
            idReincidencia: modalListadoReincidencias.dataset.idReincidencia || '',
            idAnormalidadReincidente:
                modalListadoReincidencias.dataset.idAnormalidadReincidente || '',
            fechaHora: campoFechaSeguimientoReincidencia
                ? campoFechaSeguimientoReincidencia.value.trim()
                : '',
            cantidad: campoCantidadSeguimientoReincidencia
                ? campoCantidadSeguimientoReincidencia.value.trim()
                : '',
            idDisposicion: disposicionSeleccionada
                ? disposicionSeleccionada.value
                : ''
        };
        const errores = [];

        if (!/^\d+$/.test(datosRegistro.idAnormalidad) ||
            !/^\d+$/.test(datosRegistro.idReincidencia) ||
            !/^\d+$/.test(datosRegistro.idAnormalidadReincidente)) {
            errores.push('No se identificó correctamente la reincidencia seleccionada.');
        }

        if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(datosRegistro.fechaHora) ||
            Number.isNaN(new Date(datosRegistro.fechaHora).getTime())) {
            errores.push('Captura una fecha y hora válidas.');
        }

        if (!/^\d+$/.test(datosRegistro.cantidad)) {
            errores.push('La cantidad debe ser un número entero igual o mayor que cero.');
        }

        if (!/^\d+$/.test(datosRegistro.idDisposicion)) {
            errores.push('Selecciona una disposición válida.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Registro incompleto', errores[0]);
            return;
        }

        botonRegistrarDatosSeguimientoReincidencia.disabled = true;
        botonRegistrarDatosSeguimientoReincidencia.setAttribute('aria-busy', 'true');
        botonRegistrarDatosSeguimientoReincidencia.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        let registroExitoso = false;

        try {
            const registro = await consultarOperacionAnormalidad('28', datosRegistro);
            registroExitoso = true;

            if (campoFechaSeguimientoReincidencia) {
                campoFechaSeguimientoReincidencia.value =
                    String(registro.fecha_hora || datosRegistro.fechaHora).slice(0, 16);
                campoFechaSeguimientoReincidencia.readOnly = true;
            }

            if (campoCantidadSeguimientoReincidencia) {
                campoCantidadSeguimientoReincidencia.readOnly = true;
            }

            if (contenedorDisposicionSeguimientoReincidencia) {
                contenedorDisposicionSeguimientoReincidencia
                    .querySelectorAll('input[name="seguimiento_reincidencia_disposicion"]')
                    .forEach(function (campo) {
                        campo.disabled = true;
                    });
            }

            botonRegistrarDatosSeguimientoReincidencia.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Datos del seguimiento registrados';

            try {
                await actualizarHistorialReincidencias(datosRegistro.idAnormalidad);
            } catch (errorActualizacion) {
                mostrarToastAnormalidad(
                    'warning',
                    'Seguimiento registrado',
                    'Los datos se guardaron, pero no fue posible actualizar el historial. ' +
                        'Vuelve a abrir la ventana para consultarlos.',
                    errorActualizacion
                );
                return;
            }

            mostrarToastAnormalidad(
                'success',
                'Seguimiento registrado',
                'Los datos generales de la reincidencia se guardaron correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró el seguimiento',
                error.message || 'No fue posible guardar los datos del seguimiento.',
                error
            );
        } finally {
            botonRegistrarDatosSeguimientoReincidencia.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarDatosSeguimientoReincidencia.disabled = false;
                botonRegistrarDatosSeguimientoReincidencia.innerHTML =
                    contenidoBotonDatosSeguimientoReincidencia;
            }
        }
    }

    /**
     * Registra una sola causa y contramedida para la anormalidad reincidente
     * abierta. Los datos del responsable se asignan únicamente en el backend.
     */
    async function registrarCausaContramedidaReincidencia() {
        if (!modalListadoReincidencias || !botonRegistrarCausaContramedidaReincidencia ||
            botonRegistrarCausaContramedidaReincidencia.disabled) {
            return;
        }

        const datosRegistro = {
            idAnormalidad: modalListadoReincidencias.dataset.registroId || '',
            idReincidencia: modalListadoReincidencias.dataset.idReincidencia || '',
            idAnormalidadReincidente:
                modalListadoReincidencias.dataset.idAnormalidadReincidente || '',
            causa: campoCausaSeguimientoReincidencia
                ? campoCausaSeguimientoReincidencia.value.trim()
                : '',
            contramedida: campoContramedidaSeguimientoReincidencia
                ? campoContramedidaSeguimientoReincidencia.value.trim()
                : ''
        };
        const errores = [];

        if (!/^\d+$/.test(datosRegistro.idAnormalidad) ||
            !/^\d+$/.test(datosRegistro.idReincidencia) ||
            !/^\d+$/.test(datosRegistro.idAnormalidadReincidente)) {
            errores.push('No se identificó correctamente la reincidencia seleccionada.');
        }

        if (!datosRegistro.causa || datosRegistro.causa.length > 700) {
            errores.push('Captura la causa con un máximo de 700 caracteres.');
        }

        if (!datosRegistro.contramedida || datosRegistro.contramedida.length > 700) {
            errores.push('Captura la contramedida con un máximo de 700 caracteres.');
        }

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Registro incompleto', errores[0]);
            return;
        }

        botonRegistrarCausaContramedidaReincidencia.disabled = true;
        botonRegistrarCausaContramedidaReincidencia.setAttribute('aria-busy', 'true');
        botonRegistrarCausaContramedidaReincidencia.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        let registroExitoso = false;

        try {
            const registro = await consultarOperacionAnormalidad('29', datosRegistro);
            registroExitoso = true;

            if (campoCausaSeguimientoReincidencia) {
                campoCausaSeguimientoReincidencia.readOnly = true;
            }

            if (campoContramedidaSeguimientoReincidencia) {
                campoContramedidaSeguimientoReincidencia.readOnly = true;
            }

            if (campoNominaAtiendeReincidencia) {
                campoNominaAtiendeReincidencia.value = registro.nomina_atiende || USUARIO_SESION.nomina;
            }

            if (campoNombreAtiendeReincidencia) {
                campoNombreAtiendeReincidencia.value =
                    registro.nombre_atiende || USUARIO_SESION.nombre;
            }

            botonRegistrarCausaContramedidaReincidencia.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Causa y contramedida registradas';

            try {
                await actualizarHistorialReincidencias(datosRegistro.idAnormalidad);
            } catch (errorActualizacion) {
                mostrarToastAnormalidad(
                    'warning',
                    'Causa y contramedida registradas',
                    'La información se guardó, pero no fue posible actualizar el historial. ' +
                        'Vuelve a abrir la ventana para consultarla.',
                    errorActualizacion
                );
                return;
            }

            mostrarToastAnormalidad(
                'success',
                'Causa y contramedida registradas',
                'La información de la reincidencia se guardó correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registró la causa y contramedida',
                error.message || 'No fue posible guardar la información solicitada.',
                error
            );
        } finally {
            botonRegistrarCausaContramedidaReincidencia.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarCausaContramedidaReincidencia.disabled = false;
                botonRegistrarCausaContramedidaReincidencia.innerHTML =
                    contenidoBotonCausaContramedidaReincidencia;
            }
        }
    }

    /**
     * Registra de forma conjunta las tres revisiones anteriores y posteriores
     * de QA. El inspector se obtiene exclusivamente en el backend.
     */
    async function registrarConfirmacionQaReincidencia() {
        if (!modalListadoReincidencias || !formularioSeguimientoReincidencia ||
            !botonRegistrarConfirmacionQaReincidencia ||
            botonRegistrarConfirmacionQaReincidencia.disabled) {
            return;
        }

        const obtenerResultado = function (nombreCampo) {
            const seleccionado = formularioSeguimientoReincidencia.querySelector(
                'input[name="' + nombreCampo + '"]:checked'
            );

            return seleccionado ? seleccionado.value : '';
        };
        const datosRegistro = {
            idAnormalidad: modalListadoReincidencias.dataset.registroId || '',
            idReincidencia: modalListadoReincidencias.dataset.idReincidencia || '',
            idAnormalidadReincidente:
                modalListadoReincidencias.dataset.idAnormalidadReincidente || '',
            resultadoAntes1: obtenerResultado('seguimiento_qa_antes_1'),
            resultadoAntes2: obtenerResultado('seguimiento_qa_antes_2'),
            resultadoAntes3: obtenerResultado('seguimiento_qa_antes_3'),
            resultadoDespues1: obtenerResultado('seguimiento_qa_despues_1'),
            resultadoDespues2: obtenerResultado('seguimiento_qa_despues_2'),
            resultadoDespues3: obtenerResultado('seguimiento_qa_despues_3')
        };
        const errores = [];

        if (!/^\d+$/.test(datosRegistro.idAnormalidad) ||
            !/^\d+$/.test(datosRegistro.idReincidencia) ||
            !/^\d+$/.test(datosRegistro.idAnormalidadReincidente)) {
            errores.push('No se identificó correctamente la reincidencia seleccionada.');
        }

        [
            datosRegistro.resultadoAntes1,
            datosRegistro.resultadoAntes2,
            datosRegistro.resultadoAntes3,
            datosRegistro.resultadoDespues1,
            datosRegistro.resultadoDespues2,
            datosRegistro.resultadoDespues3
        ].forEach(function (resultado) {
            if (resultado !== 'OK' && resultado !== 'NG') {
                errores.push('Selecciona OK o NG en las seis revisiones de QA.');
            }
        });

        if (errores.length) {
            mostrarToastAnormalidad('warning', 'Registro incompleto', errores[0]);
            return;
        }

        botonRegistrarConfirmacionQaReincidencia.disabled = true;
        botonRegistrarConfirmacionQaReincidencia.setAttribute('aria-busy', 'true');
        botonRegistrarConfirmacionQaReincidencia.innerHTML =
            '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registrando...';

        let registroExitoso = false;

        try {
            const registro = await consultarOperacionAnormalidad('30', datosRegistro);
            registroExitoso = true;

            formularioSeguimientoReincidencia
                .querySelectorAll('input[name^="seguimiento_qa_"]')
                .forEach(function (campo) {
                    campo.disabled = true;
                });

            if (campoNominaInspectorQaReincidencia) {
                campoNominaInspectorQaReincidencia.value =
                    registro.nomina_inspectorQA || USUARIO_SESION.nomina;
            }

            if (campoNombreInspectorQaReincidencia) {
                campoNombreInspectorQaReincidencia.value =
                    registro.nombre_inspectorQA || USUARIO_SESION.nombre;
            }

            botonRegistrarConfirmacionQaReincidencia.innerHTML =
                '<i class="bi bi-lock" aria-hidden="true"></i> Confirmaciones de QA registradas';

            try {
                await actualizarHistorialReincidencias(datosRegistro.idAnormalidad);
            } catch (errorActualizacion) {
                mostrarToastAnormalidad(
                    'warning',
                    'Confirmaciones QA registradas',
                    'Las revisiones se guardaron, pero no fue posible actualizar el historial. ' +
                        'Vuelve a abrir la ventana para consultarlas.',
                    errorActualizacion
                );
                return;
            }

            mostrarToastAnormalidad(
                'success',
                'Confirmaciones QA registradas',
                'Las seis revisiones y el inspector se guardaron correctamente.'
            );
        } catch (error) {
            mostrarToastAnormalidad(
                'error',
                'No se registraron las confirmaciones QA',
                error.message || 'No fue posible guardar las revisiones de QA.',
                error
            );
        } finally {
            botonRegistrarConfirmacionQaReincidencia.removeAttribute('aria-busy');

            if (!registroExitoso) {
                botonRegistrarConfirmacionQaReincidencia.disabled = false;
                botonRegistrarConfirmacionQaReincidencia.innerHTML =
                    contenidoBotonConfirmacionQaReincidencia;
            }
        }
    }

    /**
     * Consulta nuevamente el historial antes de abrir el seguimiento para
     * reflejar registros realizados por otros usuarios o pestañas.
     */
    async function cargarDetalleReincidencia(filaReincidencia) {
        if (!filaReincidencia || !modalListadoReincidencias ||
            modalListadoReincidencias.dataset.loadingRecurrenceDetail === 'true') {
            return;
        }

        const idAnormalidad = modalListadoReincidencias.dataset.registroId || '';
        const idAnormalidadReincidente =
            filaReincidencia.dataset.idAnormalidadReincidente || '';
        let filaActualizada = filaReincidencia;

        modalListadoReincidencias.dataset.loadingRecurrenceDetail = 'true';

        try {
            await actualizarHistorialReincidencias(idAnormalidad);

            if (tablaReincidencias) {
                const filasActualizadas = tablaReincidencias.querySelectorAll(
                    '[data-id-anormalidad-reincidente]'
                );

                filasActualizadas.forEach(function (fila) {
                    if (fila.dataset.idAnormalidadReincidente === idAnormalidadReincidente) {
                        filaActualizada = fila;
                    }
                });
            }
        } catch (error) {
            mostrarToastAnormalidad(
                'warning',
                'No se actualizó el seguimiento',
                'Se mostrará la última información disponible en la ventana.',
                error
            );
        } finally {
            delete modalListadoReincidencias.dataset.loadingRecurrenceDetail;
        }

        mostrarDetalleReincidencia(filaActualizada);
    }

    /**
     * Cambia el contenido del modal al formulario y aplica el estado
     * independiente de cada sección consultada.
     */
    function mostrarDetalleReincidencia(filaReincidencia) {
        if (!filaReincidencia || !modalListadoReincidencias) {
            return;
        }

        if (modalListadoReincidencias.dataset.viewTransitioning === 'true') {
            return;
        }

        const numeroSeguimiento = filaReincidencia.dataset.recurrenceItem || '--';
        const idAnormalidadReincidente =
            filaReincidencia.dataset.idAnormalidadReincidente || '';
        const datosSeguimientoRegistrados =
            filaReincidencia.dataset.followRegistered === 'true';

        // Se limpia el formulario antes de mostrar la fila seleccionada.
        if (formularioSeguimientoReincidencia) {
            formularioSeguimientoReincidencia.reset();
        }

        if (campoFechaSeguimientoReincidencia) {
            campoFechaSeguimientoReincidencia.value = datosSeguimientoRegistrados
                ? String(filaReincidencia.dataset.followDateTime || '').slice(0, 16)
                : obtenerFechaHoraLocal();
            campoFechaSeguimientoReincidencia.readOnly = true;
        }

        if (campoCantidadSeguimientoReincidencia) {
            campoCantidadSeguimientoReincidencia.value = datosSeguimientoRegistrados
                ? filaReincidencia.dataset.followQuantity || ''
                : '';
            campoCantidadSeguimientoReincidencia.readOnly = datosSeguimientoRegistrados;
        }

        if (contenedorDisposicionSeguimientoReincidencia) {
            contenedorDisposicionSeguimientoReincidencia
                .querySelectorAll('input[name="seguimiento_reincidencia_disposicion"]')
                .forEach(function (campo) {
                    campo.checked = datosSeguimientoRegistrados &&
                        campo.value === filaReincidencia.dataset.followDispositionId;
                    campo.disabled = datosSeguimientoRegistrados;
                });
        }

        if (botonRegistrarDatosSeguimientoReincidencia) {
            botonRegistrarDatosSeguimientoReincidencia.disabled =
                datosSeguimientoRegistrados;
            botonRegistrarDatosSeguimientoReincidencia.removeAttribute('aria-busy');
            botonRegistrarDatosSeguimientoReincidencia.innerHTML =
                datosSeguimientoRegistrados
                    ? '<i class="bi bi-lock" aria-hidden="true"></i> Datos del seguimiento registrados'
                    : contenidoBotonDatosSeguimientoReincidencia;
        }

        const causaContramedidaRegistrada =
            filaReincidencia.dataset.causeRegistered === 'true';

        if (campoCausaSeguimientoReincidencia) {
            campoCausaSeguimientoReincidencia.value = causaContramedidaRegistrada
                ? filaReincidencia.dataset.followCauseRaw || ''
                : '';
            campoCausaSeguimientoReincidencia.readOnly = causaContramedidaRegistrada;
        }

        if (campoContramedidaSeguimientoReincidencia) {
            campoContramedidaSeguimientoReincidencia.value = causaContramedidaRegistrada
                ? filaReincidencia.dataset.followCountermeasureRaw || ''
                : '';
            campoContramedidaSeguimientoReincidencia.readOnly =
                causaContramedidaRegistrada;
        }

        if (campoNominaAtiendeReincidencia) {
            campoNominaAtiendeReincidencia.value = causaContramedidaRegistrada
                ? filaReincidencia.dataset.followNominaAtiende || ''
                : USUARIO_SESION.nomina;
            campoNominaAtiendeReincidencia.readOnly = true;
        }

        if (campoNombreAtiendeReincidencia) {
            campoNombreAtiendeReincidencia.value = causaContramedidaRegistrada
                ? filaReincidencia.dataset.followNombreAtiende || ''
                : USUARIO_SESION.nombre;
            campoNombreAtiendeReincidencia.readOnly = true;
        }

        if (botonRegistrarCausaContramedidaReincidencia) {
            botonRegistrarCausaContramedidaReincidencia.disabled =
                causaContramedidaRegistrada;
            botonRegistrarCausaContramedidaReincidencia.removeAttribute('aria-busy');
            botonRegistrarCausaContramedidaReincidencia.innerHTML =
                causaContramedidaRegistrada
                    ? '<i class="bi bi-lock" aria-hidden="true"></i> Causa y contramedida registradas'
                    : contenidoBotonCausaContramedidaReincidencia;
        }

        let confirmacionesQa = [];

        try {
            confirmacionesQa = JSON.parse(filaReincidencia.dataset.qaConfirmations || '[]');
        } catch (errorConfirmaciones) {
            console.error('[Anormalidades] Confirmaciones QA inválidas en el historial', {
                detalle: errorConfirmaciones
            });
        }

        const estadoConfirmacionQa = filaReincidencia.dataset.qaStatus || 'pending';
        const confirmacionesPorClave = {};

        if (Array.isArray(confirmacionesQa)) {
            confirmacionesQa.forEach(function (confirmacion) {
                const clave = String(confirmacion.momento_clave || '') + '_' +
                    String(confirmacion.no_revision || '');
                confirmacionesPorClave[clave] = confirmacion.resultado || '';
            });
        }

        if (formularioSeguimientoReincidencia) {
            formularioSeguimientoReincidencia
                .querySelectorAll('input[name^="seguimiento_qa_"]')
                .forEach(function (campo) {
                    const coincidencia = campo.name.match(
                        /^seguimiento_qa_(antes|despues)_([1-3])$/
                    );
                    const claveMomento = coincidencia && coincidencia[1] === 'antes'
                        ? '1'
                        : '2';
                    const claveRevision = coincidencia ? coincidencia[2] : '';

                    campo.checked = coincidencia
                        ? confirmacionesPorClave[claveMomento + '_' + claveRevision] === campo.value
                        : false;
                    campo.disabled = estadoConfirmacionQa !== 'pending';
                });
        }

        if (campoNominaInspectorQaReincidencia) {
            campoNominaInspectorQaReincidencia.value =
                filaReincidencia.dataset.qaInspectorPayroll || USUARIO_SESION.nomina;
            campoNominaInspectorQaReincidencia.readOnly = true;
        }

        if (campoNombreInspectorQaReincidencia) {
            campoNombreInspectorQaReincidencia.value =
                filaReincidencia.dataset.qaInspectorName || USUARIO_SESION.nombre;
            campoNombreInspectorQaReincidencia.readOnly = true;
        }

        if (botonRegistrarConfirmacionQaReincidencia) {
            botonRegistrarConfirmacionQaReincidencia.disabled =
                estadoConfirmacionQa !== 'pending';
            botonRegistrarConfirmacionQaReincidencia.removeAttribute('aria-busy');
            botonRegistrarConfirmacionQaReincidencia.innerHTML =
                estadoConfirmacionQa === 'complete'
                    ? '<i class="bi bi-lock" aria-hidden="true"></i> Confirmaciones de QA registradas'
                    : (estadoConfirmacionQa === 'partial'
                        ? '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i> Registro QA parcial'
                        : contenidoBotonConfirmacionQaReincidencia);
        }

        const vistaListado = document.getElementById('vistaListadoReincidencias');
        const vistaDetalle = document.getElementById('vistaDetalleReincidencia');

        modalListadoReincidencias.dataset.idAnormalidadReincidente =
            idAnormalidadReincidente;

        /* Cada registro comienza visualmente en la primera etapa del formulario. */
        const botonPrimeraSeccion = document.getElementById(
            'tabDatosSeguimientoReincidenciaButton'
        );

        if (botonPrimeraSeccion && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(botonPrimeraSeccion).show();
        }

        aplicarBloqueoAnormalidadFinalizada(
            modalListadoReincidencias,
            anormalidadEstaFinalizada(modalListadoReincidencias)
        );

        mostrarDatoDetalle('listadoReincidenciasEyebrow', 'Registro No. ' + numeroSeguimiento);
        mostrarDatoDetalle('modalListadoReincidenciasLabel', 'Seguimiento de reincidencia');

        cambiarVistaReincidencias(vistaListado, vistaDetalle, 'forward');
    }

    /**
     * Presenta un mensaje de referencia cuando los factores están cargando,
     * no existen o no pudieron consultarse.
     */
    function mostrarEstadoFactoresAtencion(mensaje) {
        if (!contenedorFactoresAtencion) {
            return;
        }

        const elemento = document.createElement('div');
        const texto = document.createElement('span');

        contenedorFactoresAtencion.innerHTML = '';
        elemento.className = 'anomaly-serial-entry';
        texto.className = 'anomaly-form-hint';
        texto.textContent = mensaje;
        elemento.appendChild(texto);
        contenedorFactoresAtencion.appendChild(elemento);
    }

    /**
     * Construye los factores registrados como campos sin nombre y de solo
     * lectura, para que nunca formen parte de una captura posterior.
     */
    function mostrarFactoresAtencion(factores) {
        if (!contenedorFactoresAtencion) {
            return;
        }

        if (!Array.isArray(factores) || !factores.length) {
            mostrarEstadoFactoresAtencion('No hay áreas o departamentos responsables registrados.');
            return;
        }

        contenedorFactoresAtencion.innerHTML = '';

        factores.forEach(function (factor, indice) {
            const fila = document.createElement('div');
            const renglon = document.createElement('div');
            const datos = [
                {
                    etiqueta: 'Área / departamento',
                    valor: factor.nombre_factor + (factor.clave_factor ? ' · ' + factor.clave_factor : ''),
                    clase: 'col-lg-4'
                },
                {
                    etiqueta: 'No. nómina',
                    valor: factor.nomina_responsable || 'Sin información',
                    clase: 'col-lg-3 col-md-4'
                },
                {
                    etiqueta: 'Nombre de quien atiende',
                    valor: factor.nombre_responsable || 'Sin información',
                    clase: 'col-lg-5 col-md-8'
                }
            ];

            fila.className = 'anomaly-serial-entry';
            renglon.className = 'row g-3 align-items-end';

            datos.forEach(function (dato, posicion) {
                const columna = document.createElement('div');
                const etiqueta = document.createElement('label');
                const campo = document.createElement('input');
                const idCampo = 'detalleFactorAtencion' + indice + 'Campo' + posicion;

                columna.className = dato.clase;
                etiqueta.className = 'form-label';
                etiqueta.htmlFor = idCampo;
                etiqueta.textContent = dato.etiqueta;
                campo.type = 'text';
                campo.className = 'form-control';
                campo.id = idCampo;
                campo.value = dato.valor || 'Sin información';
                campo.readOnly = true;
                campo.tabIndex = -1;

                columna.appendChild(etiqueta);
                columna.appendChild(campo);
                renglon.appendChild(columna);
            });

            fila.appendChild(renglon);
            contenedorFactoresAtencion.appendChild(fila);
        });
    }

    /**
     * Consulta por ID y llena la sección de Detección con los valores reales
     * almacenados en SA_anormalidad y los nombres de sus catálogos relacionados.
     */
    async function cargarDetalleApertura(idRegistro) {
        const idAnormalidad = String(idRegistro || '').trim();
        const tokenActual = ++tokenConsultaDetalle;
        const camposDetalle = [
            'detalleNumeroSeguimiento',
            'detalleFechaDeteccion',
            'detalleTurno',
            'detalleArea',
            'detalleLinea',
            'detalleModelo',
            'detalleDescripcion',
            'detalleLote',
            'detalleSerial',
            'detalleCantidad',
            'detalleProcesoDetectado',
            'detalleProcesoGenerado',
            'detalleNombreDetecta',
            'detalleNominaDetecta'
        ];

        if (!/^\d+$/.test(idAnormalidad)) {
            mostrarToastAnormalidad(
                'error',
                'No se pudo consultar',
                'El registro seleccionado no contiene un identificador válido.',
                { idAnormalidad: idRegistro }
            );
            return;
        }

        mostrarDatoDetalle('detalleRegistroId', 'ID ' + idAnormalidad);
        mostrarDatoDetalle('detalleReincidencias', '--');
        mostrarEstadoFactoresAtencion('Cargando áreas responsables...');
        camposDetalle.forEach(function (idCampo) {
            mostrarDatoDetalle(idCampo, 'Cargando...');
        });

        try {
            const detalle = await consultarOperacionAnormalidad('8', {
                idAnormalidad: idAnormalidad
            });

            // Evita que una respuesta anterior sobrescriba el último registro elegido.
            if (tokenActual !== tokenConsultaDetalle) {
                return;
            }

            const fecha = formatearFechaListado(detalle.fecha_hora_deteccion);
            const cantidad = Number(detalle.cantidad);
            const textoCantidad = Number.isFinite(cantidad)
                ? cantidad + (cantidad === 1 ? ' pieza' : ' piezas')
                : detalle.cantidad;
            const estacionDeteccion = detalle.estacion_deteccion || 'Sin información';
            const estacionGeneracion = detalle.estacion_generacion || 'Sin información';
            const segmentosSeguimiento = String(detalle.no_seguimiento || '').split('/');
            const consecutivo = segmentosSeguimiento.length
                ? segmentosSeguimiento[segmentosSeguimiento.length - 1].trim()
                : '';

            modalDetalle.dataset.consecutivo = consecutivo;
            if (String(detalle.clave_estatus || '').trim() === '6') {
                sincronizarAnormalidadFinalizada(detalle);
            } else {
                aplicarBloqueoAnormalidadFinalizada(modalDetalle, false);
            }

            mostrarDatoDetalle('detalleRegistroId', 'ID ' + detalle.id_anormalidad);
            mostrarDatoDetalle('detalleNumeroSeguimiento', detalle.no_seguimiento);
            mostrarDatoDetalle('detalleFechaDeteccion', fecha.fecha + ' · ' + fecha.hora);
            mostrarDatoDetalle('detalleTurno', 'Turno ' + detalle.turno);
            mostrarDatoDetalle('detalleArea', detalle.nombre_area);
            mostrarDatoDetalle('detalleLinea', detalle.nombre_linea || detalle.codigo_linea);
            // El modelo es opcional: registros previos o líneas sin modelos lo tienen vacío.
            mostrarDatoDetalle('detalleModelo', detalle.modelo || 'Sin modelo registrado');
            mostrarDatoDetalle('detalleDescripcion', detalle.detalle_anormalidad);
            mostrarDatoDetalle('detalleLote', detalle.no_lote);
            mostrarDatoDetalle('detalleSerial', detalle.no_serial);
            mostrarDatoDetalle('detalleCantidad', textoCantidad);
            mostrarDatoDetalle('detalleProcesoDetectado', estacionDeteccion);
            mostrarDatoDetalle('detalleProcesoGenerado', estacionGeneracion);
            mostrarDatoDetalle('detalleNombreDetecta', detalle.nombre_registra);
            mostrarDatoDetalle('detalleNominaDetecta', 'Nómina ' + detalle.nomina_registra);
            mostrarDatoDetalle('detalleReincidencias', detalle.total_reincidencias || 0);
            mostrarFactoresAtencion(detalle.factores_atencion);
        } catch (error) {
            if (tokenActual !== tokenConsultaDetalle) {
                return;
            }

            camposDetalle.forEach(function (idCampo) {
                mostrarDatoDetalle(idCampo, 'No disponible');
            });
            mostrarEstadoFactoresAtencion('No fue posible consultar las áreas responsables.');
            mostrarToastAnormalidad(
                'error',
                'No se cargó el detalle',
                error.message || 'No fue posible consultar la anormalidad seleccionada.',
                error
            );
        }
    }

    /**
     * Ajusta el canvas a su tamaño visible para conservar nitidez en pantallas
     * normales y de alta densidad. Se ejecuta al mostrar la pestaña de cierre,
     * ya que un canvas oculto no permite calcular correctamente sus medidas.
     */
    function prepararLienzoFirma(lienzo) {
        const medidas = lienzo.getBoundingClientRect();

        if (!medidas.width || !medidas.height) {
            return null;
        }

        const escala = Math.max(window.devicePixelRatio || 1, 1);
        const anchoReal = Math.round(medidas.width * escala);
        const altoReal = Math.round(medidas.height * escala);

        if (lienzo.width !== anchoReal || lienzo.height !== altoReal) {
            lienzo.width = anchoReal;
            lienzo.height = altoReal;
        }

        const contexto = lienzo.getContext('2d');
        contexto.setTransform(escala, 0, 0, escala, 0, 0);
        contexto.lineWidth = 2.2;
        contexto.lineCap = 'round';
        contexto.lineJoin = 'round';
        contexto.strokeStyle = '#27428f';
        lienzo.dataset.signatureReady = 'true';

        return contexto;
    }

    /**
     * Convierte la posición del puntero a coordenadas internas del canvas.
     */
    function obtenerPuntoFirma(lienzo, event) {
        const medidas = lienzo.getBoundingClientRect();

        return {
            x: event.clientX - medidas.left,
            y: event.clientY - medidas.top
        };
    }

    /**
     * Exporta la firma en el formato solicitado por cada canvas. Para JPEG se
     * utiliza un lienzo temporal blanco, evitando fondos negros o transparentes.
     */
    function obtenerImagenFirma(lienzo) {
        const formato = lienzo.dataset.signatureFormat || 'image/png';

        if (formato !== 'image/jpeg') {
            return lienzo.toDataURL('image/png');
        }

        const lienzoTemporal = document.createElement('canvas');
        const contextoTemporal = lienzoTemporal.getContext('2d');

        lienzoTemporal.width = lienzo.width;
        lienzoTemporal.height = lienzo.height;
        contextoTemporal.fillStyle = '#ffffff';
        contextoTemporal.fillRect(0, 0, lienzoTemporal.width, lienzoTemporal.height);
        contextoTemporal.drawImage(lienzo, 0, 0);

        return lienzoTemporal.toDataURL('image/jpeg', 0.9);
    }

    /**
     * Habilita el trazo con mouse, pantalla táctil o lápiz digital.
     * La imagen resultante queda también en un campo oculto para facilitar
     * una futura integración con backend, sin enviar datos actualmente.
     */
    function configurarLienzoFirma(lienzo) {
        let dibujando = false;
        let contexto = null;
        const contenedor = lienzo.closest('.anomaly-signature-canvas-wrap');
        const salida = document.getElementById(lienzo.dataset.signatureOutput);

        function asegurarContexto() {
            if (!contexto || lienzo.dataset.signatureReady !== 'true') {
                contexto = prepararLienzoFirma(lienzo);
            }

            return contexto;
        }

        lienzo.addEventListener('pointerdown', function (event) {
            if (lienzo.dataset.signatureLocked === 'true') {
                return;
            }

            const punto = obtenerPuntoFirma(lienzo, event);

            event.preventDefault();

            if (!asegurarContexto()) {
                return;
            }

            dibujando = true;
            lienzo.setPointerCapture(event.pointerId);
            contexto.beginPath();
            contexto.moveTo(punto.x, punto.y);

            if (contenedor) {
                contenedor.classList.add('has-signature');
            }
        });

        lienzo.addEventListener('pointermove', function (event) {
            if (!dibujando || !contexto) {
                return;
            }

            event.preventDefault();
            const punto = obtenerPuntoFirma(lienzo, event);
            contexto.lineTo(punto.x, punto.y);
            contexto.stroke();
        });

        function finalizarTrazo(event) {
            if (!dibujando || !contexto) {
                return;
            }

            dibujando = false;
            contexto.closePath();

            if (lienzo.hasPointerCapture(event.pointerId)) {
                lienzo.releasePointerCapture(event.pointerId);
            }

            if (salida) {
                salida.value = obtenerImagenFirma(lienzo);
            }
        }

        lienzo.addEventListener('pointerup', finalizarTrazo);
        lienzo.addEventListener('pointercancel', finalizarTrazo);
    }

    /**
     * Borra visualmente una firma y limpia su representación temporal.
     */
    function limpiarFirma(lienzo) {
        let contexto = prepararLienzoFirma(lienzo);
        const contenedor = lienzo.closest('.anomaly-signature-canvas-wrap');
        const salida = document.getElementById(lienzo.dataset.signatureOutput);

        /* También limpia un canvas oculto que conserva dimensiones anteriores. */
        if (!contexto && lienzo.width && lienzo.height) {
            contexto = lienzo.getContext('2d');
        }

        if (contexto) {
            contexto.save();
            contexto.setTransform(1, 0, 0, 1, 0, 0);
            contexto.clearRect(0, 0, lienzo.width, lienzo.height);
            contexto.restore();
        }

        lienzo.dataset.signatureReady = 'false';

        if (contenedor) {
            contenedor.classList.remove('has-signature');
        }

        if (salida) {
            salida.value = '';
        }
    }

    if (campoArea && campoLinea && campoProcesoDetectado && campoProcesoGenerado) {
        cargarAreas();
        campoArea.addEventListener('change', cargarLineasPorArea);
        campoLinea.addEventListener('change', cargarEstacionesPorLinea);
        campoLinea.addEventListener('change', cargarModelosPorLinea);
        campoLinea.addEventListener('change', cargarReferenciaSeguimiento);
    }

    if (campoFiltroArea && campoFiltroLinea) {
        campoFiltroArea.addEventListener('change', cargarLineasFiltroPorArea);
    }

    if ((campoTurno && campoTurnoId) || campoReincidenciaTurno || campoFiltroTurno) {
        cargarTurnos();
    }

    if (formularioRegistroInicial && botonGuardarRegistroInicial) {
        botonGuardarRegistroInicial.addEventListener('click', registroNuevaAnormalidad);
    }

    if (botonRegistrarPrimeraRevision) {
        botonRegistrarPrimeraRevision.addEventListener('click', registrarPrimeraRevision);
    }

    if (botonRegistrarAnalisisPE) {
        botonRegistrarAnalisisPE.addEventListener('click', registroAnalisisPE);
    }

    if (botonRegistrarAnalisisMF) {
        botonRegistrarAnalisisMF.addEventListener('click', registroAnalisisMF);
    }

    if (botonRegistrarConfirmacionesQa) {
        botonRegistrarConfirmacionesQa.addEventListener('click', registroConfirmacionesQA);
    }

    if (botonRegistrarCausaContramedida) {
        botonRegistrarCausaContramedida.addEventListener('click', registroCausaContramedida);
    }

    if (botonRegistrarDatosSeguimientoReincidencia) {
        botonRegistrarDatosSeguimientoReincidencia.addEventListener(
            'click',
            registrarDatosSeguimientoReincidencia
        );
    }

    if (botonRegistrarCausaContramedidaReincidencia) {
        botonRegistrarCausaContramedidaReincidencia.addEventListener(
            'click',
            registrarCausaContramedidaReincidencia
        );
    }

    if (botonRegistrarConfirmacionQaReincidencia) {
        botonRegistrarConfirmacionQaReincidencia.addEventListener(
            'click',
            registrarConfirmacionQaReincidencia
        );
    }

    if (botonRegistrarDisposicionProducto) {
        botonRegistrarDisposicionProducto.addEventListener('click', registroDisposicionProducto);
    }

    if (botonRegistrarRevisionMaterialAntes) {
        botonRegistrarRevisionMaterialAntes.addEventListener('click', registroRevisionMaterialAntes);
    }

    if (botonRegistrarReinicioOperacion) {
        botonRegistrarReinicioOperacion.addEventListener('click', registroReinicioOperacion);
    }

    if (botonRegistrarCierreQa) {
        botonRegistrarCierreQa.addEventListener('click', registroCierreReporteCalidad);
    }

    if (botonRegistrarCierreGerente) {
        botonRegistrarCierreGerente.addEventListener('click', registroAutorizacionGerente);
    }

    if (botonTabApertura) {
        botonTabApertura.addEventListener('shown.bs.tab', function () {
            if (modalDetalle && /^\d+$/.test(modalDetalle.dataset.idAnormalidad || '')) {
                cargarDetalleApertura(modalDetalle.dataset.idAnormalidad);
            }
        });
    }

    if (botonTabAnalisis) {
        botonTabAnalisis.addEventListener('shown.bs.tab', async function () {
            await cargarRegistrosAnalisis();
            aplicarBloqueoAnormalidadFinalizada(
                modalDetalle,
                anormalidadEstaFinalizada(modalDetalle)
            );
        });
    }

    if (botonTabCierreCalidad) {
        botonTabCierreCalidad.addEventListener('shown.bs.tab', async function () {
            await cargarRegistrosCierreCalidad();
            aplicarBloqueoAnormalidadFinalizada(
                modalDetalle,
                anormalidadEstaFinalizada(modalDetalle)
            );
        });
    }

    if (campoRevisionTipoAnormalidad) {
        campoRevisionTipoAnormalidad.addEventListener('change', function () {
            actualizarCampoOtroTipoAnormalidad(true);
        });
    }

    if (contenedorRevision5m) {
        contenedorRevision5m.addEventListener('change', function (event) {
            const entrada = event.target.closest('input[name="revision_4m[]"]');

            if (entrada) {
                actualizarExclusionClasificacion5m(entrada);
            }
        });
    }

    // Actualiza el contador del comentario sin enviar ni guardar información.
    if (campoDetalle && contadorDetalle) {
        campoDetalle.addEventListener('input', function () {
            contadorDetalle.textContent = campoDetalle.value.length + ' / 500';
        });
    }

    // La fecha, hora y turno se recalculan automáticamente al abrir el formulario.
    if (modalRegistro && campoFechaDeteccion) {
        modalRegistro.addEventListener('show.bs.modal', function () {
            actualizarFechaYTurno();
            colocarUsuarioSesion(campoNominaRegistra, campoNombreRegistra);
        });
    }

    lienzosFirma.forEach(configurarLienzoFirma);

    // Los botones de limpieza usan delegación para mantener simple el marcado.
    if (vistaAnormalidades) {
        vistaAnormalidades.addEventListener('click', function (event) {
            const botonLimpiar = event.target.closest('[data-signature-clear]');

            if (!botonLimpiar) {
                return;
            }

            const lienzo = document.getElementById(botonLimpiar.dataset.signatureClear);

            if (lienzo) {
                limpiarFirma(lienzo);
            }
        });
    }

    // Inicializa las medidas cuando la pestaña ya es visible.
    const botonTabCierre = document.getElementById('tabCierreButton');

    if (botonTabCierre) {
        botonTabCierre.addEventListener('shown.bs.tab', async function () {
            await cargarDatosReincidenciaCierre();
            aplicarBloqueoAnormalidadFinalizada(
                modalDetalle,
                anormalidadEstaFinalizada(modalDetalle)
            );

            window.requestAnimationFrame(function () {
                lienzosFirma.forEach(function (lienzo) {
                    // No se redimensiona una firma ya capturada para no borrarla.
                    if (lienzo.dataset.signatureReady !== 'true') {
                        prepararLienzoFirma(lienzo);
                    }
                });
            });
        });
    }

    /**
     * Abre la tabla de reincidencias de la anormalidad seleccionada.
     */
    if (vistaAnormalidades && modalListadoReincidencias) {
        vistaAnormalidades.addEventListener('click', function (event) {
            const botonListado = event.target.closest('button.anomaly-action.action-repeat');

            if (!botonListado || !window.bootstrap) {
                return;
            }

            cargarListadoReincidencias(botonListado.closest('tr'));
            window.bootstrap.Modal.getOrCreateInstance(modalListadoReincidencias).show();
        });
    }

    // Navegación interna del modal: listado -> detalle y regreso al listado.
    if (modalListadoReincidencias) {
        inicializarTooltipsSeguimiento(tablaReincidencias);

        modalListadoReincidencias.addEventListener('click', function (event) {
            const botonSeguimiento = event.target.closest('.anomaly-recurrence-follow-button');
            const botonVolver = event.target.closest('#btnVolverListadoReincidencias');
            const botonAgregar = event.target.closest('#btnAgregarReincidencia');

            if (botonAgregar) {
                registrarAnormalidadReincidente();
                return;
            }

            if (botonSeguimiento) {
                if (window.bootstrap && window.bootstrap.Tooltip) {
                    const tooltip = window.bootstrap.Tooltip.getInstance(botonSeguimiento);

                    if (tooltip) {
                        tooltip.hide();
                    }
                }

                cargarDetalleReincidencia(botonSeguimiento.closest('[data-recurrence-item]'));
                return;
            }

            if (botonVolver) {
                if (modalListadoReincidencias.dataset.viewTransitioning !== 'true') {
                    mostrarVistaListadoReincidencias(true);
                }
            }
        });

        modalListadoReincidencias.addEventListener('hidden.bs.modal', function () {
            ++tokenConsultaListadoReincidencias;
            mostrarVistaListadoReincidencias(false);
        });
    }

    /**
     * Abre el registro de reincidencia desde la fila seleccionada y prepara
     * sus valores automáticos antes de permitir el envío.
     */
    if (vistaAnormalidades && modalReincidencia) {
        vistaAnormalidades.addEventListener('click', function (event) {
            const botonReincidencia = event.target.closest('button.anomaly-action.action-recurrence-add');

            if (!botonReincidencia || !window.bootstrap) {
                return;
            }

            cargarDatosReincidencia(botonReincidencia.closest('tr'));
            window.bootstrap.Modal.getOrCreateInstance(modalReincidencia).show();
        });
    }

    if (botonGuardarReincidencia) {
        botonGuardarReincidencia.addEventListener('click', registroNuevaReincidencia);
    }

    /**
     * Abre el detalle desde cualquier botón Info de la tabla.
     * La navegación vuelve siempre a Apertura para mostrar primero el inicio.
     */
    if (vistaAnormalidades && modalDetalle) {
        vistaAnormalidades.addEventListener('click', function (event) {
            const botonInfo = event.target.closest('button.anomaly-action.action-info');

            if (!botonInfo || !window.bootstrap) {
                return;
            }

            const fila = botonInfo.closest('tr');
            const datosFila = tablaAnormalidades ? tablaAnormalidades.row(fila).data() : null;
            const idRegistro = botonInfo.dataset.idAnormalidad ||
                obtenerTextoFila(fila, '.anomaly-id');
            const cargarAperturaDirectamente = !botonTabApertura ||
                !window.bootstrap.Tab || botonTabApertura.classList.contains('active');

            modalDetalle.dataset.idAnormalidad = idRegistro;
            modalDetalle.dataset.consecutivo = obtenerTextoFila(fila, '.consecutive-badge');
            aplicarBloqueoAnormalidadFinalizada(modalDetalle, false);
            tokenConsultaAnalisis += 1;
            tokenConsultaCierreCalidad += 1;
            tokenConsultaReincidenciaCierre += 1;
            limpiarPrimeraRevision();
            limpiarAnalisisPE();
            limpiarAnalisisMF();
            limpiarConfirmacionesQa();
            limpiarCausaContramedida();
            limpiarDisposicionCalidad();
            limpiarRevisionMaterialAntes();
            limpiarReinicioOperacion();
            limpiarDatosReincidenciaCierre();
            limpiarCierreReporteCalidad();
            limpiarAutorizacionGerente();
            aplicarBloqueoAnormalidadFinalizada(
                modalDetalle,
                String(datosFila && datosFila.clave_estatus || '').trim() === '6'
            );

            if (cargarAperturaDirectamente) {
                cargarDetalleApertura(idRegistro);
            }

            cargarPrimeraRevision(idRegistro);

            if (botonTabApertura && window.bootstrap.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(botonTabApertura).show();
            }

            window.bootstrap.Modal.getOrCreateInstance(modalDetalle).show();
        });
    }

    /**
     * Escapa cualquier texto procedente de la base antes de insertarlo como HTML.
     */
    function escaparHtml(valor) {
        const contenedor = document.createElement('div');
        contenedor.textContent = valor === null || valor === undefined ? '' : String(valor);
        return contenedor.innerHTML;
    }

    /**
     * Separa la fecha ISO devuelta por SQL Server sin alterar la zona horaria.
     */
    function formatearFechaListado(valor) {
        const fechaHora = String(valor || '');
        const coincidencia = fechaHora.match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        if (!coincidencia) {
            return { fecha: fechaHora || 'Sin fecha', hora: '--:--' };
        }

        return {
            fecha: coincidencia[3] + ' ' + meses[Number(coincidencia[2]) - 1] + ' ' + coincidencia[1],
            hora: coincidencia[4] + ':' + coincidencia[5]
        };
    }

    function obtenerClaseArea(nombreArea) {
        const area = String(nombreArea || '').toUpperCase();

        if (area.indexOf('SENSOR') !== -1) {
            return ' area-sensor';
        }

        if (area.indexOf('CUARTO') !== -1) {
            return ' area-cleanroom';
        }

        return '';
    }

    function obtenerClaseFactor(factores) {
        const factor = String(factores || '').toLowerCase();

        if (factor.indexOf('mantenimiento') !== -1 || factor.indexOf('ingenier') !== -1) {
            return ' factor-maintenance';
        }

        if (factor.indexOf('calidad') !== -1) {
            return ' factor-quality';
        }

        if (factor.indexOf('manufactura') !== -1) {
            return ' factor-manufacturing';
        }

        return '';
    }

    /**
     * Asigna un distintivo a cada una de las seis etapas registradas en el
     * catálogo, sin reemplazar el nombre que devuelve la base de datos.
     */
    function obtenerPresentacionEstatus(clave, nombre) {
        const claveEstatus = String(clave || '').trim();
        const nombreEstatus = String(nombre || '').toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

        const presentaciones = {
            '1': { clase: ' status-detection', icono: 'bi-exclamation-diamond-fill' },
            '2': { clase: ' status-analysis', icono: 'bi-search' },
            '3': { clase: ' status-verification', icono: 'bi-patch-check-fill' },
            '4': { clase: ' status-quality-close', icono: 'bi-shield-check' },
            '5': { clase: ' status-manager-close', icono: 'bi-person-check-fill' },
            '6': { clase: ' status-finalized', icono: 'bi-check-circle-fill' }
        };

        if (presentaciones[claveEstatus]) {
            return presentaciones[claveEstatus];
        }

        /* Respaldo por nombre para registros cuyo catálogo no tenga clave. */
        if (nombreEstatus.indexOf('final') !== -1) {
            return presentaciones['6'];
        }

        if (nombreEstatus.indexOf('gerente') !== -1) {
            return presentaciones['5'];
        }

        if (nombreEstatus.indexOf('cierre') !== -1) {
            return presentaciones['4'];
        }

        if (nombreEstatus.indexOf('verificacion') !== -1) {
            return presentaciones['3'];
        }

        if (nombreEstatus.indexOf('analisis') !== -1 ||
            nombreEstatus.indexOf('contramedida') !== -1) {
            return presentaciones['2'];
        }

        if (nombreEstatus.indexOf('deteccion') !== -1) {
            return presentaciones['1'];
        }

        return { clase: ' status-default', icono: 'bi-circle-fill' };
    }

    /**
     * Mantiene el resumen del encabezado sincronizado con los registros que
     * DataTables muestra después de aplicar su búsqueda local.
     */
    function actualizarContadorRegistrosListado(total) {
        if (!contadorRegistrosListado) {
            return;
        }

        const cantidad = Number.isFinite(Number(total)) ? Number(total) : 0;
        contadorRegistrosListado.textContent = cantidad +
            (cantidad === 1 ? ' registro' : ' registros');
    }

    /**
     * Crea o actualiza DataTables usando un arreglo local. Después de esta
     * carga inicial, búsqueda, orden y paginación no realizan peticiones.
     */
    function mostrarListadoAnormalidades(registros) {
        if (!window.jQuery || !window.jQuery.fn.DataTable || !window.jQuery(selectorTabla).length) {
            throw new Error('DataTables no está disponible para mostrar el listado.');
        }

        if (window.jQuery.fn.DataTable.isDataTable(selectorTabla)) {
            tablaAnormalidades = window.jQuery(selectorTabla).DataTable();
            tablaAnormalidades.clear().rows.add(registros).draw();
            tablaAnormalidades.columns.adjust();
            return;
        }

        tablaAnormalidades = window.jQuery(selectorTabla).DataTable({
            data: registros,
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return '';
                        }

                        const id = escaparHtml(fila.id_anormalidad);
                        const reincidenciaRegistrada = Number(fila.tiene_reincidencia) === 1;
                        const anormalidadFinalizada = String(fila.clave_estatus || '').trim() === '6';
                        const botonReincidencia = anormalidadFinalizada
                            ? '<button type="button" class="anomaly-action action-recurrence-add" disabled ' +
                                'title="Anormalidad finalizada" aria-label="La anormalidad del registro ' +
                                id + ' está finalizada"><i class="bi bi-lock" aria-hidden="true"></i></button>'
                            : reincidenciaRegistrada
                            ? '<button type="button" class="anomaly-action action-recurrence-add" disabled ' +
                                'title="Reincidencia registrada" aria-label="La reincidencia del registro ' +
                                id + ' ya fue registrada"><i class="bi bi-check-circle" aria-hidden="true"></i></button>'
                            : '<button type="button" class="anomaly-action action-recurrence-add" ' +
                                'title="Registrar nueva reincidencia" aria-label="Registrar reincidencia para el registro ' +
                                id + '"><i class="bi bi-plus-circle" aria-hidden="true"></i></button>';
                        return '<div class="anomaly-actions">' +
                            '<button type="button" class="anomaly-action action-info" data-id-anormalidad="' + id + '" title="Seguimiento de anormalidad" aria-label="Ver detalle del registro ' + id + '"><i class="bi bi-info-lg" aria-hidden="true"></i></button>' +
                            '<button type="button" class="anomaly-action action-repeat" title="Ver reincidencias" aria-label="Ver reincidencias del registro ' + id + '"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></button>' +
                            botonReincidencia +
                            '</div>';
                    }
                },
                {
                    data: 'id_anormalidad',
                    render: function (dato, tipo) {
                        return tipo === 'display'
                            ? '<span class="anomaly-id">' + escaparHtml(dato) + '</span>'
                            : dato;
                    }
                },
                {
                    data: 'consecutivo',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'sort' || tipo === 'type') {
                            return Number.parseInt(dato, 10) || 0;
                        }

                        if (tipo === 'filter') {
                            return String(dato || '') + ' ' + String(fila.no_seguimiento || '');
                        }

                        return '<span class="consecutive-badge">' + escaparHtml(dato) + '</span>' +
                            '<small class="cell-note">' + escaparHtml(fila.no_seguimiento) + '</small>';
                    }
                },
                {
                    data: 'estatus_actual',
                    render: function (dato, tipo, fila) {
                        const nombreEstatus = dato || 'Sin estatus';

                        if (tipo === 'filter') {
                            return nombreEstatus + ' ' + String(fila.clave_estatus || '') +
                                (Number(fila.tiene_reincidencia) === 1 ? ' Con reincidencia' : '');
                        }

                        if (tipo !== 'display') {
                            return nombreEstatus;
                        }

                        const presentacion = obtenerPresentacionEstatus(
                            fila.clave_estatus,
                            nombreEstatus
                        );
                        const indicadorReincidencia = Number(fila.tiene_reincidencia) === 1
                            ? '<small class="anomaly-recurrence-indicator" ' +
                                'title="Esta anormalidad tiene reincidencias registradas">' +
                                '<i class="bi bi-arrow-repeat" aria-hidden="true"></i>' +
                                'Con reincidencia</small>'
                            : '';

                        return '<span class="status-badge' + presentacion.clase + '">' +
                            '<i class="bi ' + presentacion.icono + '" aria-hidden="true"></i>' +
                            escaparHtml(nombreEstatus) + '</span>' + indicadorReincidencia;
                    }
                },
                {
                    data: 'nombre_area',
                    render: function (dato, tipo) {
                        return tipo === 'display'
                            ? '<span class="area-badge' + obtenerClaseArea(dato) + '">' + escaparHtml(dato) + '</span>'
                            : dato;
                    }
                },
                {
                    data: 'nombre_linea',
                    render: function (dato, tipo, fila) {
                        const nombreLinea = dato || fila.codigo_linea || '';
                        return tipo === 'display'
                            ? '<strong class="line-name">' + escaparHtml(nombreLinea) + '</strong>'
                            : nombreLinea;
                    }
                },
                {
                    data: 'turno',
                    render: function (dato, tipo) {
                        return tipo === 'display'
                            ? '<span class="shift-badge">' + escaparHtml(dato) + '</span>'
                            : dato;
                    }
                },
                {
                    data: 'fecha_hora_deteccion',
                    render: function (dato, tipo) {
                        const fecha = formatearFechaListado(dato);

                        if (tipo === 'sort' || tipo === 'type') {
                            return dato || '';
                        }

                        if (tipo === 'filter') {
                            return fecha.fecha + ' ' + fecha.hora + ' ' + String(dato || '');
                        }

                        return '<strong class="detection-time">' + escaparHtml(fecha.hora) + '</strong>' +
                            '<small class="cell-note">' + escaparHtml(fecha.fecha) + '</small>';
                    }
                },
                {
                    data: 'estacion_deteccion',
                    render: function (dato, tipo) {
                        const estacion = dato || 'Sin estación';

                        if (tipo === 'filter') {
                            return estacion;
                        }

                        if (tipo !== 'display') {
                            return estacion;
                        }

                        return '<strong class="station-name">' + escaparHtml(estacion) + '</strong>';
                    }
                },
                {
                    data: 'nombre_personal',
                    render: function (dato, tipo, fila) {
                        const nombre = dato || 'Sin nombre';

                        if (tipo === 'filter') {
                            return nombre + ' ' + String(fila.nomina_personal || '');
                        }

                        if (tipo !== 'display') {
                            return nombre;
                        }

                        return '<strong class="person-name">' + escaparHtml(nombre) + '</strong>' +
                            '<small class="cell-note">Nómina ' + escaparHtml(fila.nomina_personal) + '</small>';
                    }
                },
                {
                    data: 'factores',
                    render: function (dato, tipo) {
                        const factores = dato || 'Sin asignar';
                        return tipo === 'display'
                            ? '<span class="factor-badge' + obtenerClaseFactor(factores) + '">' + escaparHtml(factores) + '</span>'
                            : factores;
                    }
                }
            ],
            processing: false,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            deferRender: true,
            pageLength: 8,
            lengthChange: true,
            lengthMenu: [
                [8, 12, 25],
                [8, 12, 25]
            ],
            autoWidth: false,
            scrollX: true,
            scrollY: '54vh',
            scrollCollapse: true,
            order: [[1, 'desc']],
            columnDefs: [
                {
                    targets: '_all',
                    className: 'text-center'
                },
                {
                    targets: 0,
                    width: '132px'
                }
            ],
            language: {
                search: 'Buscar:',
                searchPlaceholder: 'ID, estatus, línea, personal...',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrados de _MAX_ registros)',
                zeroRecords: 'No se encontraron anormalidades',
                emptyTable: 'No hay anormalidades registradas',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            },
            drawCallback: function () {
                actualizarContadorRegistrosListado(this.api().page.info().recordsDisplay);
            },
            initComplete: function () {
                this.api().columns.adjust();
            }
        });
    }

    /**
     * Obtiene el arreglo filtrado y lo entrega a la opción `data` de
     * DataTables. La tabla no utiliza Ajax ni procesamiento del lado servidor.
     */
    async function cargarListadoAnormalidades() {
        const tokenActual = ++tokenConsultaListadoAnormalidades;
        const filtros = {
            fechaInicio: campoFiltroFechaInicio ? campoFiltroFechaInicio.value : '',
            fechaFin: campoFiltroFechaFin ? campoFiltroFechaFin.value : '',
            idTurno: campoFiltroTurno ? campoFiltroTurno.value : '',
            idFactorGerente: campoFiltroFactorGerente ? campoFiltroFactorGerente.value : '',
            idArea: campoFiltroArea ? campoFiltroArea.value : '',
            codigoLinea: campoFiltroLinea ? campoFiltroLinea.value : ''
        };

        try {
            const registros = await consultarOperacionAnormalidad('7', filtros);

            /* Una respuesta anterior no debe reemplazar el filtro más reciente. */
            if (tokenActual !== tokenConsultaListadoAnormalidades) {
                return;
            }

            if (!Array.isArray(registros)) {
                throw new Error('El listado recibido no tiene el formato esperado.');
            }

            mostrarListadoAnormalidades(registros);
        } catch (error) {
            if (tokenActual !== tokenConsultaListadoAnormalidades) {
                return;
            }

            mostrarToastAnormalidad(
                'error',
                'No se cargó el listado',
                error.message || 'No fue posible consultar las anormalidades registradas.',
                error
            );
        }
    }

    /* Cada cambio vuelve a consultar el conjunto filtrado; DataTables conserva
       su búsqueda, orden y paginación completamente en el navegador. */
    [
        campoFiltroFechaInicio,
        campoFiltroFechaFin,
        campoFiltroTurno,
        campoFiltroFactorGerente,
        campoFiltroArea,
        campoFiltroLinea
    ].forEach(function (campoFiltro) {
        if (campoFiltro) {
            campoFiltro.addEventListener('change', cargarListadoAnormalidades);
        }
    });

    /* El estado de las pestañas se recalcula solo: cualquier cambio dentro del
       modal —la carga del detalle o el bloqueo de una sección recién guardada—
       redibuja los indicadores sin tener que avisar desde cada registro. */
    if (modalDetalle) {
        new MutationObserver(programarEstadoPestanas).observe(modalDetalle, {
            childList: true,
            subtree: true
        });

        /* Cada etapa consulta sus datos hasta que se muestra su pestaña, así
           que al abrir el modal solo se conoce el estado de la primera. Se
           piden las otras tres de una vez para que los indicadores digan la
           verdad desde el inicio y no solo después de visitarlas. */
        modalDetalle.addEventListener('shown.bs.modal', function () {
            actualizarEstadoPestanas();

            if (!/^\d+$/.test(modalDetalle.dataset.idAnormalidad || '')) {
                return;
            }

            cargarRegistrosAnalisis();
            cargarRegistrosCierreCalidad();
            cargarDatosReincidenciaCierre();
        });
    }

    promesaCatalogosPrimeraRevision = cargarCatalogosPrimeraRevision();
    cargarTiposReincidencia();
    cargarListadoAnormalidades();
})();
