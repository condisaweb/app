<?php
/*
Template Name: Consulta Vehiculo Template
*/


// Seguridad y Obtención de Datos
$id_vehiculo_url = isset($_GET['id_vehiculo']) ? sanitize_text_field($_GET['id_vehiculo']) : null;
$mensaje_error = '';
$mostrar_datos = false;
$datos_vehiculo = null; // Para almacenar los datos ACF si la clave es correcta

// Comprobar si el usuario está autenticado
$usuario_autenticado = is_user_logged_in();

// Lógica de Procesamiento del Formulario (Cuando se envía la clave)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consulta_vehiculo_nonce']) && wp_verify_nonce($_POST['consulta_vehiculo_nonce'], 'consulta_vehiculo_action')) {
    $id_vehiculo_form = isset($_POST['id_vehiculo_hidden']) ? sanitize_text_field($_POST['id_vehiculo_hidden']) : null;
    $clave_introducida = isset($_POST['clave_acceso']) ? sanitize_text_field($_POST['clave_acceso']) : '';

    // Comprobación de seguridad básica: el ID del formulario debe coincidir con el ID de la URL
    if ($id_vehiculo_form && $id_vehiculo_form === $id_vehiculo_url) {

        // Buscar el post 'vehiculo_transformad' que coincida con el identificador_unico_qr
        $args = array(
            'post_type' => 'vehiculo_transformad', 
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key'     => 'identificador_unico_para_qr',
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
            $clave_guardada = get_field('clave_de_acceso_cliente', $vehiculo_post_id);

            // Comparar claves
            if ($clave_introducida === $clave_guardada) {
                // ¡Éxito! Cargar datos
                $mostrar_datos = true;
            // Cargar datos del vehículo
                $datos_vehiculo = [
                    'titulo' => get_the_title($vehiculo_post_id),
                    'galeria' => get_field('galeria_fotos', $vehiculo_post_id),
                    'modelo_vehiculo' => get_field('modelo_vehiculo', $vehiculo_post_id),
                    'policia' => get_field('policia', $vehiculo_post_id),
                    'matricula' => get_field('matricula', $vehiculo_post_id),
                    'bastidor' => get_field('bastidor', $vehiculo_post_id),
                    'fecha_transformacion' => get_field('fecha_transformacion', $vehiculo_post_id),
                    'garantia_acf_valor' => get_field('garantia', $vehiculo_post_id),
                    // Equipamiento
                    'puente_luces' => get_field('equip_puente_luces', $vehiculo_post_id),
                    'fusibles' => get_field('equip_fusibles', $vehiculo_post_id),
                    'rotulacion' => get_field('equip_rotulacion', $vehiculo_post_id),
                    'nanoleds' => get_field('equip_nanoleds', $vehiculo_post_id),
                    'maletero' => get_field('equip_maletero', $vehiculo_post_id),
                    'kit_detenidos' => get_field('equip_kit_detenidos', $vehiculo_post_id),
                    'altavoz' => get_field('equip_altavoz', $vehiculo_post_id),
                    'amplificador' => get_field('equip_amplificador', $vehiculo_post_id),
                    'botonera' => get_field('equip_botonera', $vehiculo_post_id),
                    'imagen_interactiva' => get_field('imagen_interactiva', $vehiculo_post_id),
                    'shortcode_rotulacion' => get_field('shortcode_rotulacion', $vehiculo_post_id),
                    'propiedad' => get_field('propiedad', $vehiculo_post_id), // Obtén el grupo "Propiedad"
                ];

                // Si el grupo "Propiedad" tiene datos, accede a los subcampos
                if ($datos_vehiculo['propiedad']) {
                    $nombre_propiedad = $datos_vehiculo['propiedad']['nombre_propiedad'] ?? '';
                    $telefono_propiedad = $datos_vehiculo['propiedad']['telefono_propiedad'] ?? '';
                    $email_propiedad = $datos_vehiculo['propiedad']['email_propiedad'] ?? '';
                }

                // --- LÓGICA DE CÁLCULO DE LA FECHA DE FIN DE GARANTÍA ---
                $garantia_fin = ''; // Inicializamos por si el cálculo falla

                $valor_garantia_acf = $datos_vehiculo['garantia_acf_valor']; 
                $duracion_garantia_para_calculo = 2; // Valor por defecto

                if (!empty($valor_garantia_acf) && is_numeric($valor_garantia_acf)) {
                    $duracion_garantia_para_calculo = (int)$valor_garantia_acf;
                }

                $fecha_transformacion_raw = $datos_vehiculo['fecha_transformacion']; 

                if (!empty($fecha_transformacion_raw)) {
                    $fecha_ini = DateTime::createFromFormat('d/m/Y', $fecha_transformacion_raw);
                    if ($fecha_ini) {
                        $fecha_ini->modify('+' . $duracion_garantia_para_calculo . ' years');
                        $garantia_fin = $fecha_ini->format('d/m/Y');
                    }
                }
                // --- FIN LÓGICA DE CÁLCULO ---

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
    $mensaje_error = 'Identificador de vehículo no especificado. Por favor, acceda mediante el código QR.';
}

// --- Incluir Cabecera del Tema ---

get_header();

    // Pasar los datos del vehículo a JavaScript
    if ($mostrar_datos) {
        $etilometro_info = false;
        $desfibrilador_info = false;

        if (isset($datos_vehiculo['maletero']) && is_array($datos_vehiculo['maletero'])) {
            foreach ($datos_vehiculo['maletero'] as $item) {
                // Convertir a minúsculas solo una vez
                $item_name_lower = strtolower($item['nombre'] ?? '');
                $item_description_lower = strtolower($item['descripcion'] ?? '');

                // MODIFICACIÓN: AÑADIR "alcoholímetro" como sinónimo de "etilómetro"
                if (strpos($item_name_lower, 'etilómetro') !== false || strpos($item_description_lower, 'etilómetro') !== false ||
                    strpos($item_name_lower, 'alcoholímetro') !== false || strpos($item_description_lower, 'alcoholímetro') !== false) {
                    $etilometro_info = [
                        'nombre' => $item['nombre'] ?? '',
                        'descripcion' => $item['descripcion'] ?? '',
                        'imagen_url' => isset($item['imagen']['url']) ? esc_url($item['imagen']['url']) : ''
                    ];
                }

                if (strpos($item_name_lower, 'desfibrilador') !== false || strpos($item_description_lower, 'desfibrilador') !== false || strpos($item_name_lower, 'dea') !== false || strpos($item_description_lower, 'dea') !== false) { // Puedes añadir "DEA" como alias
                    $desfibrilador_info = [
                        'nombre' => $item['nombre'] ?? '',
                        'descripcion' => $item['descripcion'] ?? '',
                        'imagen_url' => isset($item['imagen']['url']) ? esc_url($item['imagen']['url']) : ''
                    ];
                }
            }
        }
        // --- FIN DE NUEVA LÓGICA PARA ETILÓMETRO Y DESFIBRILADOR ---

        // Recopilar las URLs de las imágenes de fusibles
        $fusibles_image_urls = [];
        if (isset($datos_vehiculo['fusibles']['imagen']) && is_array($datos_vehiculo['fusibles']['imagen'])) {
            foreach ($datos_vehiculo['fusibles']['imagen'] as $imagen_data) {
                if (isset($imagen_data['url'])) {
                    $fusibles_image_urls[] = esc_url($imagen_data['url']);
                }
            }
        }

        // Recopilar las URLs de las imágenes del Kit de Detenidos (AHORA COMO GALERÍA)
        $kit_detenidos_image_urls = [];
        // Ahora usando el nombre de campo correcto: 'kit_detenidos'
        if (isset($datos_vehiculo['kit_detenidos']['imagen']) && is_array($datos_vehiculo['kit_detenidos']['imagen'])) {
            foreach ($datos_vehiculo['kit_detenidos']['imagen'] as $imagen_data) {
                if (isset($imagen_data['url'])) {
                    $kit_detenidos_image_urls[] = esc_url($imagen_data['url']);
                }
            }
        }

        wp_enqueue_script(
            'consulta-vehiculo-script',
            get_stylesheet_directory_uri() . '/js/consulta-vehiculo.js',
            [],
            null,
            true
        );
        wp_localize_script('consulta-vehiculo-script', 'datosVehiculo', [
            'id_vehiculo' => esc_html($id_vehiculo_url),
            'modelo' => esc_html($datos_vehiculo['modelo_vehiculo']),
            'garantia_fin' => esc_html($garantia_fin),
            'matricula' => esc_html($datos_vehiculo['matricula']),
            'policia' => esc_html($datos_vehiculo['policia']),
            'amplificador_image_url' => esc_url($datos_vehiculo['amplificador']['imagen']['url']),
            'fusibles_image_urls' => $fusibles_image_urls,
            'kit_detenidos_image_urls' => $kit_detenidos_image_urls,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('consulta-vehiculo-nonce'),
            'etilometro_data' => $etilometro_info,
            'desfibrilador_data' => $desfibrilador_info,
            'propiedad_info' => [
                'nombre' => $datos_vehiculo['propiedad']['nombre_propiedad'] ?? '',
                'telefono' => $datos_vehiculo['propiedad']['telefono_propiedad'] ?? '',
                'email' => $datos_vehiculo['propiedad']['email_propiedad'] ?? '',
            ],
        ]);
    }
?>
<!-- CONTENIDO DEL TEMPLATE -->
<main id="main" class="site-main"> 
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="entry-content"> 
            <?php if ($mostrar_datos) : ?>

                <div class="datos-vehiculo-wrapper">
                    <div class="equipamiento-card-content">
                        <h2 class="equipamiento-card-titulo">¿Necesitas asistencia para reparación?</h2>
                        <button id="btn-asistencia" class="btn-asistencia">Asistencia para Reparación</button>
                    </div>
                </div>
                <div id="modal-asistencia" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div id="modal-body"></div>
                        <button id="btn-back" class="btn-back" style="display: none;">Ir hacia atrás</button>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            if (!$mostrar_datos) {
                ?>
                <div class="consulta-vehiculo-wrapper">
                    <h2>Consultar Información del Vehículo</h2>
                    <?php if ($mensaje_error) : ?>
                        <p><?php echo esc_html($mensaje_error); ?></p>
                    <?php endif; ?>
                    <?php if ($id_vehiculo_url && $mensaje_error !== 'Identificador de vehículo no especificado. Por favor, acceda mediante el código QR.' && $mensaje_error !== 'El identificador del vehículo no se ha encontrado.') : ?>
                        <p>Por favor, introduzca la clave de acceso asociada al vehículo con identificador: <strong><?php echo esc_html($id_vehiculo_url); ?></strong></p>
                        <form method="POST" action="<?php echo esc_url(add_query_arg(null, null)); ?>">
                            <?php wp_nonce_field('consulta_vehiculo_action', 'consulta_vehiculo_nonce'); ?>
                            <input type="hidden" name="id_vehiculo_hidden" value="<?php echo esc_attr($id_vehiculo_url); ?>">
                            <p>
                                <label for="clave_acceso">Clave de Acceso:</label><br>
                                <input type="password" id="clave_acceso" name="clave_acceso" required>
                            </p>
                            <p>
                                <input type="submit" value="Consultar Vehículo">
                            </p>
                        </form>
                    <?php endif; ?>
                </div>
                <?php
            } else {
                if (!function_exists('mostrar_grupo_equipamiento')) {
                    function mostrar_grupo_equipamiento($etiqueta, $grupo)
                    {
                        // Si el grupo está vacío, no mostrar nada
                        if (empty($grupo)) {
                            return;
                        }

                        // Determinar el tipo de grupo
                        $es_repetidor = isset($grupo[0]) && is_array($grupo[0]) && (isset($grupo[0]['nombre']) || isset($grupo[0]['descripcion']) || isset($grupo[0]['imagen']));
                        
                        // NUEVA CONDICIÓN: Si el grupo es un array y contiene una clave 'imagen' que a su vez es un array de imágenes (una galería)
                        // Esto cubre el caso de equip_fusibles (un campo de Grupo con subcampo 'imagen' que es una galería)
                        $es_grupo_con_galeria = is_array($grupo) && isset($grupo['imagen']) && is_array($grupo['imagen']) && !empty($grupo['imagen']) && isset($grupo['imagen'][0]['ID']) && isset($grupo['imagen'][0]['url']);

                        // Si no hay datos relevantes (ni repetidor, ni galería directa en grupo, ni imagen/descripción individual)
                        if (!$es_repetidor && !$es_grupo_con_galeria && empty($grupo['descripcion']) && empty($grupo['imagen']) && empty($grupo['nombre'])) {
                            return;
                        }

                        $lightbox_group = 'grupo-' . sanitize_title($etiqueta);

                        echo '<div class="equipamiento-card">';
                        echo '<div class="equipamiento-card-content">';
                        echo '<h6 class="equipamiento-card-titulo">' . esc_html($etiqueta) . '</h6>';

                        if ($es_repetidor) {
                            // Lógica existente para campos repetidores
                            foreach ($grupo as $elemento) {
                                $nombre = $elemento['nombre'] ?? '';
                                $descripcion = $elemento['descripcion'] ?? '';
                                $imagen = $elemento['imagen'] ?? ''; // Si es un repetidor con subcampo imagen simple
                                echo '<div class="equipamiento-item">';
                                if ($imagen && is_array($imagen) && isset($imagen['url'])) {
                                    $full_url = esc_url($imagen['url']);
                                    $thumb_url = esc_url($imagen['sizes']['medium'] ?? $imagen['sizes']['thumbnail'] ?? $imagen['url']);
                                    $alt_text = esc_attr($imagen['alt'] ?? $nombre);
                                    echo '<div class="equipamiento-card-img">';
                                    echo '<a href="' . $full_url . '" data-lightbox="' . $lightbox_group . '" data-title="' . esc_attr($nombre) . '">';
                                    echo '<img src="' . $thumb_url . '" alt="' . $alt_text . '">';
                                    echo '</a></div>';
                                }
                                if ($nombre) {
                                    echo '<div class="equipamiento-item-titulo"><p><strong>' . esc_html($nombre) . '</strong></p></div>';
                                }
                                if ($descripcion) {
                                    echo '<div class="equipamiento-item-descripcion">' . esc_html($descripcion) . '</div>';
                                }
                                echo '</div>';
                            }
                        } elseif ($es_grupo_con_galeria) {
                            // --- LÓGICA CORREGIDA PARA CAMPOS DE GRUPO CON SUB-CAMPO GALERÍA ---
                            if (!empty($grupo['descripcion'])) {
                                echo '<div class="equipamiento-item-descripcion" style="margin-bottom: 15px;">' . esc_html($grupo['descripcion']) . '</div>';
                            }
                            echo '<div class="galeria-equipamiento-item" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">';
                            foreach ($grupo['imagen'] as $imagen) { // Iterar sobre el subcampo 'imagen' que es la galería
                                if (is_array($imagen) && isset($imagen['url'])) {
                                    $full_url = esc_url($imagen['url']);
                                    $thumb_url = esc_url($imagen['sizes']['medium'] ?? $imagen['sizes']['thumbnail'] ?? $imagen['url']);
                                    $alt_text = esc_attr($imagen['alt'] ?? $etiqueta . ' imagen');
                                    echo '<div class="equipamiento-card-img-galeria">';
                                    echo '<a href="' . $full_url . '" data-lightbox="' . $lightbox_group . '" data-title="' . $alt_text . '">';
                                    echo '<img src="' . $thumb_url . '" alt="' . $alt_text . '" style="border-radius: 6px; box-shadow: 0 1px 6px #0002;">';
                                    echo '</a></div>';
                                }
                            }
                            echo '</div>';
                            // ------------------------------------------------------------------
                        } else {
                            // Lógica existente para campos individuales de imagen/texto que NO son repetidores
                            // Esto seguirá funcionando para campos como 'amplificador' si siguen siendo de tipo 'Imagen' simple,
                            // o si solo tienen descripción/nombre.
                            if (!empty($grupo['imagen']) && is_array($grupo['imagen']) && isset($grupo['imagen']['url'])) {
                                $full_url = esc_url($grupo['imagen']['url']);
                                $thumb_url = esc_url($grupo['imagen']['sizes']['medium'] ?? $grupo['imagen']['sizes']['thumbnail'] ?? $grupo['imagen']['url']);
                                $alt_text = esc_attr($grupo['imagen']['alt'] ?? $etiqueta);
                                echo '<div class="equipamiento-card-img">';
                                echo '<a href="' . $full_url . '" data-lightbox="' . $lightbox_group . '" data-title="' . esc_attr($etiqueta) . '">';
                                echo '<img src="' . $thumb_url . '" alt="' . $alt_text . '">';
                                echo '</a></div>';
                            }
                            if (!empty($grupo['nombre'])) {
                                echo '<div class="equipamiento-item-titulo">' . esc_html($grupo['nombre']) . '</div>';
                            }
                            if (!empty($grupo['descripcion'])) {
                                echo '<div class="equipamiento-item-descripcion">' . esc_html($grupo['descripcion']) . '</div>';
                            }
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
            ?>

            <div class="datos-vehiculo-wrapper">
                <h2>Información del Vehículo</h2>
                <div class="equipamiento-lista-tarjetas">
                    <div class="equipamiento-card">
                        <div class="equipamiento-card-content">
                            <h6 class="equipamiento-card-titulo"><?php echo esc_html($datos_vehiculo['policia']); ?></h6>
                            <div class="equipamiento-card-descripcion"><strong>Modelo Vehículo:</strong> <?php echo esc_html($datos_vehiculo['modelo_vehiculo']); ?></div>
                            <div class="equipamiento-card-descripcion"><strong>Matricula:</strong> <?php echo esc_html($datos_vehiculo['matricula']); ?></div>
                            <div class="equipamiento-card-descripcion"><strong>Nº Bastidor:</strong> <?php echo esc_html($datos_vehiculo['bastidor']); ?></div>
                            <div class="equipamiento-card-descripcion"><strong>Fecha Transformación:</strong> <?php echo esc_html($datos_vehiculo['fecha_transformacion']); ?></div>
                            <div class="equipamiento-card-descripcion"><strong>Garantía Federal Vama:</strong> <?php echo esc_html($garantia_fin); ?></div>
                            
                               <?php if (!empty($datos_vehiculo['galeria']) && is_array($datos_vehiculo['galeria'])): ?>
                                <div class="galeria-vehiculo" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
                                    <?php foreach ($datos_vehiculo['galeria'] as $idx => $img): ?>
                                        <a
                                                href="<?php echo esc_url($img['url']); ?>"
                                                data-lightbox="galeria-vehiculo-<?php echo esc_attr($datos_vehiculo['matricula']); ?>"
                                                data-title="<?php echo esc_attr($img['alt'] ?: 'Imagen del vehículo'); ?>"
                                                style="display: inline-block;"
                                        >
                                            <img
                                                    src="<?php echo esc_url($img['sizes']['medium']); ?>"
                                                    alt="<?php echo esc_attr($img['alt']); ?>"
                                                    style="border-radius: 6px; box-shadow: 0 1px 6px #0002;"
                                            />
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="equipamiento-lista-tarjetas">
                    <div class="equipamiento-card">
                        <div class="equipamiento-card-content">
                            <h6 class="equipamiento-card-titulo">Propietario</h6>
                            <div class="equipamiento-card-descripcion"><strong>Nombre:</strong> <?php echo esc_html($nombre_propiedad); ?></div>
                            <div class="equipamiento-card-descripcion"><strong>Teléfono:</strong> 
                                <a href="tel:<?php echo esc_attr($telefono_propiedad); ?>">
                                        <?php echo esc_html($telefono_propiedad); ?>
                                </a>
                            </div>
                            <div class="equipamiento-card-descripcion"><strong>Correo Electrónico:</strong> 
                                <a href="mailto:<?php echo esc_attr($email_propiedad); ?>">
                                        <?php echo esc_html($email_propiedad); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <h2>Equipamiento Instalado</h2>
                <?php
                    mostrar_grupo_equipamiento('Puente de Luces', $datos_vehiculo['puente_luces']);
                    mostrar_grupo_equipamiento('Botonera', $datos_vehiculo['botonera']);
                    mostrar_grupo_equipamiento('Altavoz', $datos_vehiculo['altavoz']);
                    mostrar_grupo_equipamiento('Amplificador', $datos_vehiculo['amplificador']);
                    mostrar_grupo_equipamiento('Caja de fusibles principal', $datos_vehiculo['fusibles']);
                    mostrar_grupo_equipamiento('Nanoleds', $datos_vehiculo['nanoleds']);
                    mostrar_grupo_equipamiento('Kit de Detenidos', $datos_vehiculo['kit_detenidos']);
                    mostrar_grupo_equipamiento('Equipamiento Maletero', $datos_vehiculo['maletero']);  
                    mostrar_grupo_equipamiento('Rotulación', $datos_vehiculo['rotulacion']);

                ?>
                <hr>
                <div class="equipamiento-card">
                    <div class="equipamiento-card-content">
                        <h2 class="equipamiento-card-titulo">Reposición de piezas de rotulación</h2>
                        <p>¿El vehículo ha sufrido daños de carrocería y necesita reponer la rotulación? Acceda al siguiente enlace y siga las instrucciones para solicitar la reposición de las piezas necesarias.</p>
                        <a href="<?php echo site_url('/reposicion-de-piezas?id_vehiculo=' . urlencode($id_vehiculo_url) . '&modelo=' . urlencode($datos_vehiculo['modelo_vehiculo']) . '&matricula=' . urlencode($datos_vehiculo['matricula']) . '&policia=' . urlencode($datos_vehiculo['policia'])); ?>" class="btn-asistencia" role="button">
                                Reposición de Piezas
                        </a>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        </div>
    </article>
</main>
<?php
get_footer();
?>