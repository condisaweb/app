jQuery(document).ready(function ($) {
    // --- Referencias a elementos del DOM ---
    const planoContainer = $('#plano-interactivo-container');
    const formularioContainer = $('#formulario-rotulacion-container');
    const toggleButton = $('#toggle-visibility-button');
    const contadorPiezas = $('#contador-piezas');
    const selectedGroupsTableBody = $('#selected-groups-table tbody');
    const localMessageContainer = $('#local-message');
    const loadingSpinner = $('#loading-spinner');

    let selectedGroups = {}; // Formato: { groupName: [piece1, piece2, ...] }

    // --- Funcionalidad del Modal de Bienvenida (Intro a la página) ---
    const pageModal = $('#page-modal');
    const nextToPart2Btn = $('#next-to-part-2');
    const backToPart1Btn = $('#back-to-part-1');
    const closeModalBtn = $('#close-modal');
    const modalPart1 = $('#modal-part-1');
    const modalPart2 = $('#modal-part-2');

    // Mostrar el modal al cargar la página
    if (pageModal.length) { // Asegura que el modal existe antes de intentar mostrarlo
        pageModal.css('display', 'flex');

        nextToPart2Btn.on('click', function() {
            modalPart1.removeClass('active');
            modalPart2.addClass('active');
        });

        backToPart1Btn.on('click', function() {
            modalPart2.removeClass('active');
            modalPart1.addClass('active');
        });

        closeModalBtn.on('click', function() {
            pageModal.css('display', 'none');
        });

        // Cierre del modal haciendo clic fuera de su contenido
        $(window).on('click', function(event) {
            if ($(event.target).is(pageModal)) {
                pageModal.css('display', 'none');
            }
        });
    }

    // --- NUEVO: Lógica para el botón "Volver a Consulta de Vehículo" ---
    const btnVolverConsulta = $('#btn-volver-consulta');

    // Asegúrate de que 'datosVehiculo' esté disponible globalmente (normalmente se localiza con wp_localize_script)
    // y que contenga 'id_vehiculo'.
    if (btnVolverConsulta.length && typeof datosVehiculo !== 'undefined' && datosVehiculo.id_vehiculo) {
        // *** IMPORTANTE: Reemplaza '/consulta-de-vehiculo/' con la URL BASE REAL de tu página de consulta de vehículo.
        // Por ejemplo: 'https://tu-dominio.com/consulta-de-vehiculo/'
        const urlBaseConsulta = '/consulta-de-vehiculo/'; 
        const vehiculoId = datosVehiculo.id_vehiculo;
        
        btnVolverConsulta.attr('href', `${urlBaseConsulta}?id_vehiculo=${vehiculoId}`);
    } else {
        console.warn('El botón de "Volver a Consulta" no se pudo configurar dinámicamente. Posiblemente falta el elemento HTML con ID "btn-volver-consulta", o la variable "datosVehiculo.id_vehiculo" no está definida. El botón podría no funcionar o no dirigir a la URL correcta.');
        // Opcional: Si el botón no puede tener una URL dinámica, puedes ocultarlo
        // if (btnVolverConsulta.length) {
        //     btnVolverConsulta.hide();
        // }
    }
    // --- FIN NUEVO: Lógica para el botón "Volver a Consulta de Vehículo" ---

    
    // --- Inicialización y Visibilidad de Contenedores ---
    // Asegurarse de que el plano esté visible y el formulario oculto al cargar
    planoContainer.removeClass('hidden').show();
    formularioContainer.addClass('hidden').hide();

    // Rellenar información del vehículo en el formulario al cargar, si 'datosVehiculo' está disponible
    if (typeof datosVehiculo !== 'undefined' && datosVehiculo.modelo) {
        $('#vehiculo-modelo').text(datosVehiculo.modelo || 'No disponible');
        $('#vehiculo-matricula').text(datosVehiculo.matricula || 'No disponible');
        $('#vehiculo-policia').text(datosVehiculo.policia || 'No disponible');
    }

    // --- Lógica del Botón "Ver Selección" (toggleButton) ---
    toggleButton.on('click', function () {
        localMessageContainer.empty().hide(); // Ocultar mensajes al cambiar de vista

        if (planoContainer.is(':visible')) {
            // Cambiar de Plano a Formulario
            planoContainer.addClass('hidden').hide();
            formularioContainer.removeClass('hidden').show();
            toggleButton.text('Volver al Plano'); // Cambiar el texto del botón
            populateSelectedPiecesTable(); // Llama para rellenar la tabla
        } else {
            // Cambiar de Formulario a Plano
            formularioContainer.addClass('hidden').hide();
            planoContainer.removeClass('hidden').show();
            toggleButton.text('Ver Selecciones'); // Cambiar el texto del botón
        }
    });

    // --- Lógica de selección/deselección de MapSVG desde el mapa ---
    // Delegación de eventos para elementos .mapsvg-region que pueden cargarse dinámicamente
    $(document).on('click touchstart', '.mapsvg-region', function (e) {
        e.preventDefault();

        const $thisRegion = $(this);
        const parentGroupElement = $thisRegion.closest('g[data-name]');
        const parentGroupName = parentGroupElement.data('name');

        if (!parentGroupName) {
            // En entorno de producción, puedes quitar este aviso o registrarlo de otra manera.
            // console.warn('mapsvg-region clicada sin un grupo padre con data-name. Asegúrate de que tu SVG tenga <g data-name="..."> que contenga las regiones.');
            return;
        }

        const groupRegionsInDom = parentGroupElement.find('.mapsvg-region');
        const allPiecesInGroupAreSelected = groupRegionsInDom.toArray().every(el => $(el).hasClass('selected-region'));

        if (allPiecesInGroupAreSelected) {
            groupRegionsInDom.removeClass('selected-region');
            removeSelectedPieceGroup(parentGroupName);
        } else {
            groupRegionsInDom.addClass('selected-region');

            const piecesInGroup = groupRegionsInDom.map(function() {
                return $(this).data('name') || $(this).attr('id');
            }).get();
            addSelectedPieceGroup(parentGroupName, piecesInGroup);
        }
    });

    // --- Funciones para manejar selecciones de MapSVG ---
    window.addSelectedPieceGroup = function(groupName, pieces) {
        if (!selectedGroups[groupName]) {
            selectedGroups[groupName] = pieces;
            updateCounter();
            if (!formularioContainer.hasClass('hidden')) {
                populateSelectedPiecesTable();
            }
        } else {
            displayLocalMessage(`El grupo "${groupName}" ya está seleccionado.`, 'info');
        }
    };

    window.removeSelectedPieceGroup = function(groupName) {
        if (selectedGroups[groupName]) {
            delete selectedGroups[groupName];
            updateCounter();
            if (!formularioContainer.hasClass('hidden')) {
                populateSelectedPiecesTable();
            }
        }
    };

    // --- Funciones de Utilidad ---

    // Función para mostrar mensajes LOCALES en la página
    function displayLocalMessage(message, type = 'info') {
        if (!localMessageContainer.length) {
            // Si el contenedor no existe, no podemos mostrar el mensaje localmente.
            // En un entorno real, podrías considerar una alerta fallback o simplemente retornar.
            return;
        }

        localMessageContainer.empty();
        let alertClass = '';
        if (type === 'success') {
            alertClass = 'page-message success';
        } else if (type === 'error') {
            alertClass = 'page-message error';
        } else if (type === 'warning') {
            alertClass = 'page-message warning';
        } else {
            alertClass = 'page-message info';
        }

        const messageHtml = `<div class="${alertClass}">
                                ${message}
                                <button type="button" class="btn-close" aria-label="Close" style="float: right; border: none; background: none; font-size: 1.2em; cursor: pointer; color: inherit;">&times;</button>
                            </div>`;
        localMessageContainer.html(messageHtml);
        localMessageContainer.fadeIn();

        $('html, body').animate({
            scrollTop: localMessageContainer.offset().top - 50
        }, 500);

        localMessageContainer.find('.btn-close').on('click', function() {
            $(this).closest('.page-message').fadeOut('slow', function() {
                $(this).remove();
            });
        });

        setTimeout(() => {
            localMessageContainer.fadeOut('slow', function() {
                $(this).empty();
            });
        }, 5000);
    }

    // Función para actualizar el contador de piezas seleccionadas
    function updateCounter() {
        const totalPieces = Object.values(selectedGroups).reduce((acc, pieces) => acc + pieces.length, 0);
        contadorPiezas.text(totalPieces);
    }

    // Función para rellenar la tabla de piezas seleccionadas en el formulario
    function populateSelectedPiecesTable() {
        selectedGroupsTableBody.empty();

        if (Object.keys(selectedGroups).length === 0) {
            selectedGroupsTableBody.append('<tr><td colspan="2">No hay piezas seleccionadas.</td></tr>');
            return;
        }

        for (const groupName in selectedGroups) {
            const pieces = selectedGroups[groupName];

            const groupRow = `
                <tr class="group-row" data-group="${groupName}">
                    <td><strong>${groupName}</strong></td>
                    <td>
                        <button type="button" class="remove-group-btn" data-group="${groupName}">Eliminar Grupo</button>
                    </td>
                </tr>
            `;
            selectedGroupsTableBody.append(groupRow);

            pieces.forEach(pieceName => {
                const pieceRow = `
                    <tr class="piece-row" data-group="${groupName}" data-piece="${pieceName}">
                        <td>-- ${pieceName}</td>
                        <td>
                            <button type="button" class="remove-piece-btn" data-group="${groupName}" data-piece="${pieceName}">Eliminar</button>
                        </td>
                    </tr>
                `;
                selectedGroupsTableBody.append(pieceRow);
            });
        }

        // Manejadores de eventos para botones de eliminar (delegados)
        selectedGroupsTableBody.off('click', '.remove-piece-btn').on('click', '.remove-piece-btn', function () {
            const groupName = $(this).data('group');
            const pieceName = $(this).data('piece');

            if (selectedGroups[groupName]) {
                const initialLength = selectedGroups[groupName].length;
                selectedGroups[groupName] = selectedGroups[groupName].filter(p => p !== pieceName);

                // Revertir selección visual si la pieza no fue realmente eliminada (debería eliminarse)
                if (selectedGroups[groupName].length === initialLength) {
                    // console.warn(`Pieza "${pieceName}" no encontrada en el grupo "${groupName}".`);
                }

                $(`g[data-name="${groupName}"] .mapsvg-region[data-name="${pieceName}"], g[data-name="${groupName}"] .mapsvg-region[id="${pieceName}"]`).removeClass('selected-region');

                if (selectedGroups[groupName].length === 0) {
                    delete selectedGroups[groupName];
                    $(`g[data-name="${groupName}"] .mapsvg-region`).removeClass('selected-region');
                }
            }

            updateCounter();
            populateSelectedPiecesTable();
            displayLocalMessage(`Pieza "${pieceName}" eliminada.`, 'info');
        });

        selectedGroupsTableBody.off('click', '.remove-group-btn').on('click', '.remove-group-btn', function () {
            const groupName = $(this).data('group');

            if (selectedGroups[groupName]) {
                delete selectedGroups[groupName];
                $(`g[data-name="${groupName}"] .mapsvg-region`).removeClass('selected-region');
            }

            updateCounter();
            populateSelectedPiecesTable();
            displayLocalMessage(`Grupo "${groupName}" eliminado.`, 'info');
        });
    }

    // Función para resetear completamente el estado visual y de datos
    function resetAllSelections() {
        selectedGroups = {};
        updateCounter();

        $('.mapsvg-region').removeClass('selected-region');

        $('#selected-groups-table tbody').empty();

        $('#preview-container').empty();
        $('#vehicle-images').val('');
        $('#region-form')[0].reset();
    }

    // --- Manejo de previsualización de imágenes ---
    // Se utiliza un selector jQuery para el evento change, ya que es más coherente con el resto del script
    $('#vehicle-images').on('change', function (event) {
        const fileList = event.target.files;
        const previewContainer = document.getElementById('preview-container'); // Aquí se puede seguir usando vanilla JS ya que solo es un elemento

        if (previewContainer) { // Asegurarse de que el contenedor exista
            previewContainer.innerHTML = '';

            Array.from(fileList).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.width = '100px';
                    img.style.height = 'auto';
                    img.style.marginRight = '10px';
                    img.style.marginBottom = '10px';
                    img.style.borderRadius = '5px';
                    img.style.border = '1px solid #ddd';
                    previewContainer.appendChild(img);
                }
            });
        }
    });


    // --- Envío del Formulario (AJAX) ---
    $('#region-form').on('submit', function (e) {
        e.preventDefault();

        // Función para preparar los grupos: asegura que solo se envíen piezas válidas.
        function prepareGroupsForSubmission(groups) {
            const cleanedGroups = {};
            for (const groupName in groups) {
                if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
                    // Filter para asegurar que solo se incluyen valores no nulos/undefined en las piezas
                    const pieces = groups[groupName].filter(p => p);
                    if (pieces.length > 0) {
                        cleanedGroups[groupName] = pieces;
                    }
                }
            }
            return cleanedGroups;
        }

        // Obtener los grupos seleccionados en un formato limpio
        const gruposParaEnviar = prepareGroupsForSubmission(selectedGroups);

        if (Object.keys(gruposParaEnviar).length === 0) {
            displayLocalMessage('Por favor, selecciona al menos un grupo de rotulación.', 'warning');
            return;
        }

        const nombre = $('#nombre').val();
        const telefono = $('#telefono').val();
        const observaciones = $('#observaciones').val();
        const vehicleImages = $('#vehicle-images')[0].files;

        if (!nombre || !telefono) {
            displayLocalMessage('Por favor, completa los campos requeridos: Nombre y Teléfono.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'save_regions');

        // Validar si datosVehiculo y su nonce están disponibles
        if (typeof datosVehiculo === 'undefined' || !datosVehiculo.nonce) {
            displayLocalMessage('Error de seguridad: Nonce no encontrado. Por favor, recarga la página.', 'error');
            return;
        }
        formData.append('nonce', datosVehiculo.nonce);


        const formDataData = {
            nombre: nombre,
            telefono: telefono,
            observaciones: observaciones,
            grupos: JSON.stringify(gruposParaEnviar),
            vehiculo: {
                modelo: datosVehiculo.modelo || '',
                matricula: datosVehiculo.matricula || '',
                policia: datosVehiculo.policia || ''
            },
            id_vehiculo_qr: datosVehiculo.id_vehiculo || 'N/A' // Asegura que se envía id_vehiculo_qr
        };
        formData.append('data', JSON.stringify(formDataData));

        for (let i = 0; i < vehicleImages.length; i++) {
            formData.append('vehicle_images[]', vehicleImages[i]);
        }

        const submitButton = $(this).find('button[type="submit"]');

        // --- Mostrar el spinner y deshabilitar el botón ---
        submitButton.prop('disabled', true).text('Enviando...');
        loadingSpinner.css('display', 'flex'); // Muestra el spinner
        localMessageContainer.empty().hide();

        $.ajax({
            url: datosVehiculo.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {
                // --- Ocultar el spinner y habilitar el botón en éxito ---
                loadingSpinner.css('display', 'none'); // Oculta el spinner
                submitButton.prop('disabled', false).text('Enviar');
                localMessageContainer.fadeOut();

                if (response.success && response.data && response.data.redirect_url) {
                    resetAllSelections();
                    window.location.href = response.data.redirect_url;
                } else {
                    const errorMessage = response.data && response.data.message ? response.data.message : 'Ocurrió un error inesperado al enviar la solicitud. Por favor, inténtalo de nuevo.';
                    displayLocalMessage(errorMessage, 'error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // --- Ocultar el spinner y habilitar el botón en error ---
                loadingSpinner.css('display', 'none'); // Oculta el spinner
                submitButton.prop('disabled', false).text('Enviar');
                localMessageContainer.fadeOut();

                let errorMessage = 'Ha ocurrido un error de conexión. Por favor, verifica tu conexión a internet y inténtalo de nuevo.';

                // console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText); // Solo para depuración en desarrollo

                if (jqXHR.status === 400) {
                    errorMessage = 'El servidor rechazó la solicitud (Bad Request). ';
                    if (jqXHR.responseText) {
                        try {
                            const errorJson = JSON.parse(jqXHR.responseText);
                            errorMessage += 'Detalles: ' + (errorJson.data && errorJson.data.message ? errorJson.data.message : jqXHR.responseText);
                        } catch (e) {
                            errorMessage += 'Detalles: ' + jqXHR.responseText;
                        }
                    }
                } else if (jqXHR.status === 500) {
                    errorMessage = 'Error interno del servidor (500). Por favor, intenta de nuevo más tarde.';
                }

                displayLocalMessage(errorMessage, 'error');
            }
        });
    });

    // --- Inicialización al cargar la página ---
    updateCounter();
});