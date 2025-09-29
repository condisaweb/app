<?php
/* Template Name: Reposición de Piezas */
get_header();

// Obtener el ID del vehículo desde la URL
$id_vehiculo_url = isset($_GET['id_vehiculo']) ? sanitize_text_field($_GET['id_vehiculo']) : '';
$modelo_vehiculo = isset($_GET['modelo']) ? sanitize_text_field($_GET['modelo']) : '';
$matricula = isset($_GET['matricula']) ? sanitize_text_field($_GET['matricula']) : '';
$policia = isset($_GET['policia']) ? sanitize_text_field($_GET['policia']) : '';

if (empty($id_vehiculo_url)) {
    echo '<p>Error: No se ha proporcionado un ID de vehículo válido. Por favor, acceda mediante el código QR.</p>';
    get_footer();
    exit;
}

// Buscar el post del vehículo con el identificador único
$args = array(
    'post_type' => 'vehiculo_transformad',
    'posts_per_page' => 1,
    'meta_query' => array(
        array(
            'key'     => 'identificador_unico_para_qr',
            'value'   => $id_vehiculo_url,
            'compare' => '=',
        ),
    ),
    'fields' => 'ids'
);
$vehiculos_encontrados = get_posts($args);

if (empty($vehiculos_encontrados)) {
    echo '<p>Error: El identificador del vehículo no se ha encontrado.</p>';
    get_footer();
    exit;
}

$vehiculo_post_id = $vehiculos_encontrados[0];

// Obtener el shortcode desde ACF
$shortcode_rotulacion = get_field('shortcode_rotulacion', $vehiculo_post_id);

if (!$shortcode_rotulacion) {
    echo '<p>Error: No se encontró el shortcode de rotulación para este vehículo.</p>';
    get_footer();
    exit;
}

// Ejecutar el shortcode
?>

<div id="local-message" style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); padding:15px; border-radius:5px; z-index:9999; text-align:center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"></div>

<div id="page-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal"></span>
        <div id="modal-part-1" class="modal-part active">
            <div class="header-content">
                <h1>Solicitar piezas</h1>
                <p>En esta sección, aprenderás a seleccionar piezas en el plano de planta del vehículo.</p>
                <ol>
                    <li>
                        <strong>Seleccionar piezas en el plano:</strong>
                        Haz clic en las piezas del plano de planta que necesites reponer de tu vehículo. Las piezas seleccionadas se agregarán automáticamente al formulario.
                    </li>
                    <li>
                        <strong>Cambiar entre plano y formulario:</strong>
                        Usa los botones disponibles para alternar entre el plano y el formulario. Esto te permitirá verificar las piezas seleccionadas antes de continuar.
                    </li>
                </ol>
                <button id="next-to-part-2" class="modal-button">Siguiente</button>
            </div>
        </div>

        <div id="modal-part-2" class="modal-part">
            <div class="header-content">
                <h1>Completar la solicitud</h1>
                <p>Aquí, aprenderás a gestionar las piezas seleccionadas y completar el formulario.</p>
                <ol>
                    <li>
                        <strong>Eliminar piezas seleccionadas:</strong>
                        Si seleccionaste una pieza por error, simplemente haz clic en el botón <strong>Ver Selección</strong>. Ahí encontrarás las piezas añadidas y podrás eliminar la que no necesites.
                    </li>
                    <li>
                        <strong>Completar el formulario de contacto:</strong>
                        Una vez que tengas todas las piezas seleccionadas, rellena el formulario con tus datos de contacto. Esto nos permitirá procesar tu solicitud de manera efectiva.
                    </li>
                </ol>
                <button id="back-to-part-1" class="modal-button">Anterior</button>
                <button id="close-modal" class="modal-button">Cerrar</button>
            </div>
        </div>
    </div>
</div>

    <div class="reposicion-piezas-wrapper">
            <div class="imagen-interactiva-wrapper">
                <div id="plano-interactivo-container" class="plano-interactivo">
                    <div class="plano-rotulacion">
                        <?php echo do_shortcode($shortcode_rotulacion); ?>
                    </div>
                </div>

                <div class="barra-resumen-selecciones">
                    <h6><span id="contador-piezas">0</span> piezas seleccionadas</h6>
                    <button class="card btn-page-back" id="toggle-visibility-button">Ver Selección</button>
                    <a href="#" class="card" id="btn-volver-consulta">
                        Volver a Consulta de Vehículo
                    </a>
                </div>

            </div>
        <div id="formulario-rotulacion-container" class="formulario-rotulacion hidden">
            <form id="region-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
                <div class="informacion-vehiculo">
                    <h3>Información del Vehículo</h3>
                    <p><strong>Modelo:</strong> <span id="vehiculo-modelo"></span></p>
                    <p><strong>Matrícula:</strong> <span id="vehiculo-matricula"></span></p>
                    <p><strong>Policía:</strong> <span id="vehiculo-policia"></span></p>
                </div>

                <input type="hidden" name="action" value="mi_formulario_rotulacion">

                <?php wp_nonce_field( 'mi_formulario_rotulacion_nonce', 'mi_formulario_seguridad' ); ?>

                <h3>Piezas seleccionadas</h3>
                <table id="selected-groups-table" border="1">
                    <thead>
                        <tr>
                            <th>Grupo/Pieza</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>

                <hr />
                <h3>Información de Contacto</h3>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ingresa tu nombre" required>

                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Ingresa tu número sin prefijo" required pattern="[0-9]{9,}" title="Ingresa solo los dígitos del número, sin prefijo">

                <label for="observaciones">Observaciones:</label>
                <textarea id="observaciones" name="observaciones" rows="4" placeholder="Añade tus observaciones"></textarea>

                <hr />
                <h3>Adjuntar fotos del vehículo</h3>
                <label for="vehicle-images">Fotos del vehículo:</label>
                <input type="file" id="vehicle-images" name="vehicle_images[]" multiple accept="image/*">
                <small>Puedes adjuntar varias imágenes. Tamaño máximo recomendado: 5MB por imagen.</small>
                <div id="preview-container"></div>

                <button type="submit">Enviar</button>
            </form>
            
            <div id="formulario-rotulacion-container" class="formulario-rotulacion hidden">
                <form id="region-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
                    <button class="card" type="submit">Enviar</button>
                </form>
            </div>
            <div id="loading-spinner" style="display: none;">
                <span>Enviando solicitud...</span>
            </div>
        </div>
    </div>    

<?php get_footer(); ?>