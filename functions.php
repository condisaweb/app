<?php

/* *********************************************************************************************
 PLEASE DO NOT DELETE THIS FUNCTION BECAUSE THIS FUNCTION IS CALLING CHILD THEME CSS FILE
********************************************************************************************* */

function configurator_child_themestyles () {
    // Encolar los estilos de Pure.css desde la CDN
    wp_enqueue_style('purecss', 'https://cdnjs.cloudflare.com/ajax/libs/pure/2.0.6/pure-min.css', [], '2.0.6');
    wp_enqueue_style('child-theme-style', get_stylesheet_directory_uri() . '/child-theme-style.css', array('purecss'), '1.0');
    // Agregar estilos personalizados para el botón rojo del formulario
    wp_add_inline_style('child-theme-style', '
        .pure-button.remove-piece {
            background-color: #ff4d4d; /* Rojo */
            color: white; /* Texto en blanco */
            border: none; /* Sin bordes */
            border-radius: 4px; /* Bordes redondeados */
            font-size: 0.9em; /* Tamaño de letra */
            padding: 0.5em 1em; /* Espaciado interno */
            cursor: pointer; /* Cambiar el cursor al hover */
            transition: background-color 0.2s ease-in-out; /* Transición de color suave */
        }
        .pure-button.remove-piece:hover {
            background-color: #cc0000; /* Rojo más oscuro al pasar el mouse */
        }
    ');
}
add_action('wp_enqueue_scripts', 'configurator_child_themestyles');

/* ******************************************************************************************** */

add_action( 'genesis_entry_content', 'featured_post_image', 8 );
function featured_post_image() {
    if ( ! is_singular( 'post' ) )  return;
    the_post_thumbnail('post-image');
}

/* ******************************************************************************************** */
/* Código para manejar el envío del formulario y enviar un correo electrónico */
/* ******************************************************************************************** */

// Manejar el formulario al enviar
add_action('admin_post_nopriv_enviar_solicitud_piezas', 'procesar_solicitud_piezas');
add_action('admin_post_enviar_solicitud_piezas', 'procesar_solicitud_piezas');

function procesar_solicitud_piezas() {
    // Verificar que los datos vengan del formulario
    if (!isset($_POST['piezas']) || !is_array($_POST['piezas'])) {
        wp_die('Error: No se enviaron datos de piezas.');
    }

    // Obtener las piezas seleccionadas
    $piezas = array_map('sanitize_text_field', $_POST['piezas']);

    // Construir el contenido del correo
    $mensaje = "El usuario ha solicitado las siguientes piezas:\n\n";
    foreach ($piezas as $pieza) {
        $mensaje .= "- $pieza\n";
    }
    $mensaje .= "\nPor favor, póngase en contacto con el cliente para confirmar la solicitud.";

    // Configurar los datos del correo
    $to = get_option('admin_email'); // Correo del administrador del sitio
    $subject = 'Solicitud de Piezas de Reposición';
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    // Enviar el correo
    $enviado = wp_mail($to, $subject, $mensaje, $headers);

    if ($enviado) {
        // Redirigir a una página de confirmación
        wp_redirect(home_url('/confirmacion-solicitud/')); // Cambia '/confirmacion-solicitud/' por la URL de tu página de confirmación
        exit;
    } else {
        wp_die('Error: No se pudo enviar el correo. Por favor, inténtelo más tarde.');
    }
}

/* ******************************************************************************************** */
/* Código JavaScript para manejar la selección de piezas */
/* ******************************************************************************************** */

add_action('wp_footer', function() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectedPieces = new Set(); // Conjunto para mantener las piezas seleccionadas
            const listContainer = document.getElementById('lista-piezas-seleccionadas');

            // Asegurarse de que el contenedor existe
            if (!listContainer) {
                console.error('El contenedor con ID "lista-piezas-seleccionadas" no se encontró en el DOM.');
                return;
            }

            // Función para manejar clics en las áreas del mapa
            function attachClickHandlers() {
                const areas = document.querySelectorAll('.imp-object-rect'); // Selector para las áreas del mapa
                areas.forEach((area) => {
                    if (area.dataset.eventAttached === "true") return;

                    // Agregar evento de clic
                    area.addEventListener('click', function () {
                        const piezaId = this.getAttribute('data-title'); // ID de la pieza desde el atributo `data-title`

                        if (!piezaId) {
                            alert('Error: No se pudo obtener el ID de la pieza.');
                            return;
                        }

                        // Comprobar si ya está seleccionada
                        if (selectedPieces.has(piezaId)) {
                            selectedPieces.delete(piezaId);
                            const listItem = document.querySelector(`#pieza-${piezaId}`);
                            if (listItem) listItem.remove();
                        } else {
                            selectedPieces.add(piezaId);

                            // Crear un elemento de lista para la pieza seleccionada
                            const li = document.createElement('li');
                            li.id = `pieza-${piezaId}`;
                            li.className = 'pure-menu-item'; // Clase de Pure.css
                            li.innerHTML = `
                                <input type="hidden" name="piezas[]" value="${piezaId}"> 
                                <span class="pure-menu-link">${piezaId}</span>
                                <button type="button" class="pure-button remove-piece" data-id="${piezaId}">Eliminar</button>
                            `;
                            listContainer.appendChild(li);
                        }
                    });

                    area.dataset.eventAttached = "true";
                });
            }

            // Delegación de eventos para el botón "Eliminar"
            listContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-piece')) {
                    const piezaId = e.target.getAttribute('data-id');
                    if (piezaId) {
                        selectedPieces.delete(piezaId); // Eliminar del conjunto
                        const listItem = e.target.closest('li'); // Buscar el elemento <li> más cercano
                        if (listItem) listItem.remove(); // Eliminar de la lista
                    }
                }
            });

            // Observador de mutaciones para detectar cambios en el DOM
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        attachClickHandlers();
                    }
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });

            attachClickHandlers(); // Adjuntar manejadores iniciales
        });
    </script>
    <?php
});

/* ******************************************************************************************** */
/* Shortcode para mostrar el formulario de selección de piezas */
/* ******************************************************************************************** */

function shortcode_formulario_seleccion() {
    ob_start();
    ?>
    <form id="seleccion-piezas" method="post" action="<?php echo admin_url('admin-post.php?action=enviar_solicitud_piezas'); ?>" class="pure-form pure-form-stacked">
        <fieldset>
            <legend>Piezas de Reposición</legend>
            <ul id="lista-piezas-seleccionadas" class="pure-menu-list">
                <!-- Las piezas seleccionadas se agregarán aquí dinámicamente -->
            </ul>
            <button type="submit" class="pure-button pure-button-primary">Solicitar Reposición</button>
        </fieldset>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('formulario_seleccion', 'shortcode_formulario_seleccion');