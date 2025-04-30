jQuery(document).ready(function($) {
    // Capturar clic en las regiones del SVG y añadir a la lista
    $('.mapsvg-region').on('click', function() {
        const piezaId = $(this).attr('id'); // ID de la región

        // Verificar si el contenedor #formulario-piezas existe
        if (!$('#formulario-piezas').length) {
            console.error('Contenedor #formulario-piezas no encontrado.');
            return;
        }

        // Añadir la pieza a la lista si no existe ya
        if (!$(`#formulario-piezas .pieza[data-id="${piezaId}"]`).length) {
            $('#formulario-piezas').append(`
                <li class="pieza" data-id="${piezaId}">
                    ${piezaId} <button class="eliminar">Eliminar</button>
                </li>
            `);
        } else {
            alert('La pieza ya está seleccionada.');
        }
    });

    // Eliminar piezas de la lista
    $(document).on('click', '.pieza .eliminar', function() {
        $(this).parent().remove();
    });

    // Sincronizar la lista con el formulario de Contact Form 7 antes de enviarlo
    document.addEventListener('wpcf7submit', function(event) {
        const piezasSeleccionadas = [];

        // Recopilar las piezas seleccionadas
        $('#formulario-piezas .pieza').each(function() {
            piezasSeleccionadas.push($(this).data('id'));
        });

        // Añadir las piezas seleccionadas al campo oculto del formulario
        $('input[name="piezas-seleccionadas"]').val(piezasSeleccionadas.join(', '));
    }, false);
});