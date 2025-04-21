<?php
/*
Template Name: Consulta Vehiculo Template
*/

// --- Lógica PHP (Similar al bloque ACF, pero sin parámetros de bloque) ---

// Seguridad y Obtención de Datos
$id_vehiculo_url = isset($_GET['id_vehiculo']) ? sanitize_text_field($_GET['id_vehiculo']) : null;
$mensaje_error = '';
$mostrar_datos = false;
$datos_vehiculo = null; // Para almacenar los datos ACF si la clave es correcta

// Lógica de Procesamiento del Formulario (Cuando se envía la clave)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consulta_vehiculo_nonce']) && wp_verify_nonce($_POST['consulta_vehiculo_nonce'], 'consulta_vehiculo_action')) {

    $id_vehiculo_form = isset($_POST['id_vehiculo_hidden']) ? sanitize_text_field($_POST['id_vehiculo_hidden']) : null;
    $clave_introducida = isset($_POST['clave_acceso']) ? sanitize_text_field($_POST['clave_acceso']) : '';

    // Comprobación de seguridad básica: el ID del formulario debe coincidir con el ID de la URL
    if ($id_vehiculo_form && $id_vehiculo_form === $id_vehiculo_url) {

        // Buscar el post 'vehiculo_transformad' que coincida con el identificador_unico_qr
        // !!! ATENCIÓN: Revisa si el post_type es 'vehiculo_transformado' (con 'o') !!!
        // !!! ATENCIÓN: Revisa si la 'key' es realmente 'identificador_unico_para_qr' !!!
        $args = array(
            'post_type' => 'vehiculo_transformad', // <-- ¿Seguro que no es 'vehiculo_transformado'?
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key'     => 'identificador_unico_para_qr', // <-- Verifica este nombre de campo ACF
                    'value'   => $id_vehiculo_form,
                    'compare' => '=',
                ),
            ),
            'fields' => 'ids' // Solo necesitamos el ID
        );
        $vehiculos_encontrados = get_posts($args);

        if (!empty($vehiculos_encontrados)) {
            $vehiculo_post_id = $vehiculos_encontrados[0];

            // Obtener la clave guardada
            // !!! ATENCIÓN: Revisa si el nombre del campo clave es realmente 'clave_de_acceso_cliente' !!!
            $clave_guardada = get_field('clave_de_acceso_cliente', $vehiculo_post_id); // <-- Verifica este nombre de campo ACF

            // Comparar claves
            if ($clave_introducida === $clave_guardada) {
                // ¡Éxito! Cargar datos
                $mostrar_datos = true;
                // !!! ATENCIÓN: Revisa los nombres de estos campos ACF !!!
                $datos_vehiculo = [
                    'titulo' => get_the_title($vehiculo_post_id),
                    'modelo_vehiculo' => get_field('modelo_vehiculo', $vehiculo_post_id), // <-- Verifica 'modelo_vehiculo'
                    'bastidor' => get_field('bastidor', $vehiculo_post_id), // <-- Verifica 'bastidor'
                    'fecha_transformacion' => get_field('fecha_transformacion', $vehiculo_post_id),
                    'puente_luces' => get_field('equip_puente_luces', $vehiculo_post_id),
                    'reles' => get_field('equip_reles', $vehiculo_post_id),
                    'rotulacion' => get_field('equip_rotulacion', $vehiculo_post_id),
                    'nanoleds' => get_field('equip_nanoleds', $vehiculo_post_id),
                    'maletero' => get_field('equip_maletero', $vehiculo_post_id),
                    'kit_detenidos' => get_field('equip_kit_detenidos', $vehiculo_post_id),
                    'altavoz' => get_field('equip_altavoz', $vehiculo_post_id),
                    'amplificador' => get_field('equip_amplificador', $vehiculo_post_id),
                    'otros' => get_field('equip_otros', $vehiculo_post_id),
                    // 'emisora' => get_field('equip_emisora', $vehiculo_post_id), // <-- ¿Falta este?
                ];
            } else {
                $mensaje_error = 'La clave de acceso es incorrecta.';
            }
        } else {
            $mensaje_error = 'El identificador del vehículo no se ha encontrado.';
        }
    } else {
        $mensaje_error = 'Error de validación. Inténtelo de nuevo.';
        if (!$id_vehiculo_url) {
            $mensaje_error = 'Por favor, acceda a través del código QR proporcionado.';
        }
    }
} else if (!$id_vehiculo_url && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Mensaje si se accede directamente sin el ID en la URL y no es un POST fallido
    $mensaje_error = 'Identificador de vehículo no especificado. Por favor, acceda mediante el código QR.';
}

// --- Incluir Cabecera del Tema ---
get_header();
?>

<main id="main" class="site-main"> <?php // O la estructura principal de tu tema ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="entry-content"> <?php // Contenedor de contenido de tu tema ?>

            <?php
            // --- Sección de Visualización (HTML) ---

            // Si NO se deben mostrar los datos, mostrar formulario o mensaje inicial.
            if (!$mostrar_datos) {
                ?>
                <div class="consulta-vehiculo-wrapper"> <?php // <-- Eliminado style="padding: 20px;" ?>
                    <h2>Consultar Información del Vehículo</h2>

                    <?php if ($mensaje_error) : ?>
                        <p><?php echo esc_html($mensaje_error); ?></p> <?php // <-- Eliminado style="..." ?>
                    <?php endif; ?>

                    <?php if ($id_vehiculo_url && $mensaje_error !== 'Identificador de vehículo no especificado. Por favor, acceda mediante el código QR.' && $mensaje_error !== 'El identificador del vehículo no se ha encontrado.') : // Mostrar formulario si hay ID y no es error fatal ?>
                        <p>Por favor, introduzca la clave de acceso asociada al vehículo con identificador: <strong><?php echo esc_html($id_vehiculo_url); ?></strong></p>

                        <form method="POST" action="<?php echo esc_url(add_query_arg(null, null)); // Enviar a la misma URL ?>">
                            <?php wp_nonce_field('consulta_vehiculo_action', 'consulta_vehiculo_nonce'); // Nonce de seguridad ?>
                            <input type="hidden" name="id_vehiculo_hidden" value="<?php echo esc_attr($id_vehiculo_url); ?>">

                            <p>
                                <label for="clave_acceso">Clave de Acceso:</label><br>
                                <input type="password" id="clave_acceso" name="clave_acceso" required> <?php // <-- Eliminado style="..." ?>
                            </p>

                            <p>
                                <input type="submit" value="Consultar Vehículo"> <?php // <-- Eliminado style="..." ?>
                            </p>
                        </form>
                    <?php endif; ?>
                </div>
            <?php
            } else {
                // Si la clave es correcta ($mostrar_datos es true), mostrar la información.

                // Función auxiliar para mostrar un grupo de equipamiento
                if (!function_exists('mostrar_grupo_equipamiento')) {
                    function mostrar_grupo_equipamiento($etiqueta, $grupo)
                    {
                        if (empty($grupo) || (empty($grupo['descripcion']) && empty($grupo['imagen']))) {
                            return;
                        }

                        // ** CAMBIO: Añadida clase 'equipamiento-grupo' y eliminado style="..." **
                        echo '<div class="equipamiento-grupo">';
                        echo '<h4>' . esc_html($etiqueta) . '</h4>';

                        if (!empty($grupo['descripcion'])) {
                            echo '<p><strong>Descripción:</strong><br>' . nl2br(esc_html($grupo['descripcion'])) . '</p>';
                        }

                        if (!empty($grupo['imagen'])) {
                            echo '<p><strong>Imágenes:</strong></p>';
                            // ** CAMBIO: Eliminado style="..." del div de la galería **
                            echo '<div class="galeria-equipamiento equipamiento-lightbox-gallery">';

                            if (is_array($grupo['imagen'])) {
                                $lightbox_group_id = 'equip-' . sanitize_title($etiqueta);

                                if (isset($grupo['imagen'][0]['url'])) { // Galería
                                    foreach ($grupo['imagen'] as $imagen_item) {
                                        if (is_array($imagen_item) && isset($imagen_item['url'])) {
                                            $full_url = esc_url($imagen_item['url']);
                                            $thumb_url = esc_url($imagen_item['sizes']['medium'] ?? $imagen_item['sizes']['thumbnail'] ?? $full_url);
                                            $alt_text = esc_attr($imagen_item['alt']);

                                            echo '<a href="' . $full_url . '" data-lightbox="' . $lightbox_group_id . '" data-title="' . esc_attr($etiqueta) . '">';
                                            // ** CAMBIO: Eliminado style="..." de la imagen **
                                            echo '<img src="' . $thumb_url . '" alt="' . $alt_text . '" class="equipamiento-thumb">';
                                            echo '</a>';
                                        }
                                    }
                                } elseif (isset($grupo['imagen']['url'])) { // Imagen Simple
                                    $full_url = esc_url($grupo['imagen']['url']);
                                    $thumb_url = esc_url($grupo['imagen']['sizes']['medium'] ?? $grupo['imagen']['sizes']['thumbnail'] ?? $full_url);
                                    $alt_text = esc_attr($grupo['imagen']['alt']);

                                    echo '<a href="' . $full_url . '" data-lightbox="' . $lightbox_group_id . '" data-title="' . esc_attr($etiqueta) . '">';
                                    // ** CAMBIO: Eliminado style="..." de la imagen **
                                    echo '<img src="' . $thumb_url . '" alt="' . $alt_text . '" class="equipamiento-thumb">';
                                    echo '</a>';
                                }
                            }
                            echo '</div>'; // Cierre .galeria-equipamiento
                        }
                        echo '</div>'; // Cierre .equipamiento-grupo
                    }
                }

            ?>
                <div class="datos-vehiculo-wrapper">
                    <?php // <-- Eliminado style="padding: 20px;" ?>
                    <h2>Información del Vehículo: <?php echo esc_html($datos_vehiculo['titulo'] ?? $id_vehiculo_url); ?></h2>

                    <p><strong>Modelo Vehículo:</strong> <?php echo esc_html($datos_vehiculo['modelo_vehiculo']); ?></p>
                    <p><strong>Nº Bastidor:</strong> <?php echo esc_html($datos_vehiculo['bastidor']); ?></p>
                    <p><strong>Fecha Transformación:</strong> <?php echo esc_html($datos_vehiculo['fecha_transformacion']); ?></p>
                    <?php // Añadir aquí cualquier otro campo simple que tengas... ?>

                    <hr>
                    <h3>Equipamiento Instalado</h3>

                    <?php
                    // Mostrar cada grupo de equipamiento usando la función definida arriba
                    mostrar_grupo_equipamiento('Puente de Luces', $datos_vehiculo['puente_luces']);
                    mostrar_grupo_equipamiento('Relés', $datos_vehiculo['reles']);
                    mostrar_grupo_equipamiento('Rotulación', $datos_vehiculo['rotulacion']);
                    mostrar_grupo_equipamiento('Nanoleds', $datos_vehiculo['nanoleds']);
                    mostrar_grupo_equipamiento('Equipamiento Maletero', $datos_vehiculo['maletero']);
                    mostrar_grupo_equipamiento('Kit de Detenidos', $datos_vehiculo['kit_detenidos']);
                    mostrar_grupo_equipamiento('Altavoz', $datos_vehiculo['altavoz']);
                    mostrar_grupo_equipamiento('Amplificador', $datos_vehiculo['amplificador']);
                    // mostrar_grupo_equipamiento('Emisora', $datos_vehiculo['emisora']); // <-- Llamada comentada si el campo no se obtiene arriba
                    mostrar_grupo_equipamiento('Otros', $datos_vehiculo['otros']);
                    ?>
<?php

$args = [
    'post_type'  => 'piezas_rotulacion',
    'meta_query' => [
        [
            'key'   => 'vehiculo_asociado',
            'value' => $id_vehiculo_url,
            'compare' => '='
        ]
    ]
];

error_log('Args de WP_Query: ' . print_r($args, true));

$query = new WP_Query($args);

if ($query->have_posts()) :
    echo '<h3>Piezas Disponibles para el Vehículo</h3>';
    echo '<ul>';
    while ($query->have_posts()) : $query->the_post();
        echo '<li>' . get_the_title() . '</li>';
    endwhile;
    echo '</ul>';
    wp_reset_postdata();
else :
    echo '<p>No hay piezas asociadas a este vehículo.</p>';
endif;

$piezas_query = new WP_Query($args);

if ($piezas_query->have_posts()) {
    while ($piezas_query->have_posts()) {
        $piezas_query->the_post();
        $vehiculo_asociado = get_post_meta(get_the_ID(), 'vehiculo_asociado', true);

        // Mostrar el valor asociado
        error_log('Vehículo asociado en esta pieza: ' . $vehiculo_asociado);
    }
} else {
    error_log('No se encontraron piezas asociadas.');
}
?>
                    <hr>
                    <h3>Piezas Disponibles para el Vehículo</h3>

                    <?php
                    // Obtener el croquis y las piezas asociadas
                    $args = [
                        'post_type'  => 'piezas_rotulacion',
                        'meta_query' => [
                            [
                                'key'     => 'vehiculo_asociado', // Relación con el vehículo
                                'value'   => $id_vehiculo_url,
                                'compare' => '='
                            ]
                        ]
                    ];

                    $query = new WP_Query($args);

                    if ($query->have_posts()) :
                        // Obtener el croquis del vehículo (asumimos que es del primer post encontrado)
                        $query->the_post();
                        $croquis_piezas = get_field('croquis_piezas'); // Croquis general
                        rewind_posts();

                        if ($croquis_piezas) :
                    ?>
                            <div class="croquis-container">
                                <h3>Selecciona las piezas en el croquis</h3>
                                <img src="<?php echo esc_url($croquis_piezas['url']); ?>" alt="Croquis del Vehículo" class="croquis-imagen">

                                <div class="piezas-overlay">
                                    <?php
                                    while ($query->have_posts()) : $query->the_post();
                                        $codigo_pieza = get_field('codigo_pieza');
                                        $codigo_pieza = get_field('codigo_pieza');
                                    ?>
                                        <div class="pieza" style="left: ?>%;">
                                            <input type="checkbox" id="pieza_<?php echo esc_attr($codigo_pieza); ?>" name="piezas[]" value="<?php echo esc_attr($codigo_pieza); ?>">
                                            <label for="pieza_<?php echo esc_attr($codigo_pieza); ?>">
                                                <?php echo esc_html($codigo_pieza); ?>
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php
                        endif;

                        wp_reset_postdata();
                    else :
                        echo '<p>No hay piezas asociadas a este vehículo.</p>';
                    endif;
                    ?>

                    <form id="form-seleccion-piezas" action="https://condisatransformaciones.com/procesar-pedido" method="POST">
                        <input type="hidden" name="id_vehiculo" value="<?php echo esc_attr($id_vehiculo_url); ?>">
                        <button type="submit">Solicitar Piezas Seleccionadas</button>
                    </form>

                    <?php
                        $piezas_asociadas = get_field('piezas_asociadas', $vehiculo_post_id); // ID del vehículo transformado

                        if ($piezas_asociadas) :
                            echo '<h3>Piezas Disponibles para el Vehículo</h3>';
                            echo '<ul>';
                            foreach ($piezas_asociadas as $pieza) :
                                echo '<li>' . get_the_title($pieza) . ' - $' . get_field('precio_pieza', $pieza) . '</li>';
                            endforeach;
                            echo '</ul>';
                        else :
                            echo '<p>No hay piezas asociadas a este vehículo.</p>';
                        endif;
                        ?>
                </div>
            <?php
            } // Fin del else ($mostrar_datos)
            ?>

        </div>
    </article>
</main>
<?php

// --- Incluir Pie de Página del Tema ---
get_footer();
?>