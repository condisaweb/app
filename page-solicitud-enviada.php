<?php
/**
 * Template Name: Plantilla Solicitud Enviada
 * Description: Plantilla para mostrar la confirmación de envío de la solicitud de rotulación, con estilo integrado.
 */

get_header(); // Incluye el encabezado de tu tema
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="entry-content">
                <?php
                // Recoger los parámetros de la URL
                $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                $message = isset($_GET['message']) ? sanitize_text_field(urldecode($_GET['message'])) : 'No se recibió un mensaje de confirmación.';
                $nombre = isset($_GET['nombre_contacto']) ? sanitize_text_field(urldecode($_GET['nombre_contacto'])) : 'N/A';
                $telefono = isset($_GET['telefono_contacto']) ? sanitize_text_field(urldecode($_GET['telefono_contacto'])) : 'N/A';
                $observaciones = isset($_GET['observaciones_contacto']) ? sanitize_text_field(urldecode($_GET['observaciones_contacto'])) : 'N/A';
                $modelo = isset($_GET['vehiculo_modelo']) ? sanitize_text_field(urldecode($_GET['vehiculo_modelo'])) : 'N/A';
                $matricula = isset($_GET['vehiculo_matricula']) ? sanitize_text_field(urldecode($_GET['vehiculo_matricula'])) : 'N/A';
                $policia = isset($_GET['vehiculo_policia']) ? sanitize_text_field(urldecode($_GET['vehiculo_policia'])) : 'N/A';

                // Decodificar grupos seleccionados e imágenes adjuntas
                $grupos_seleccionados = [];
                if (isset($_GET['grupos_seleccionados'])) {
                    $grupos_json = urldecode($_GET['grupos_seleccionados']);
                    $grupos_seleccionados = json_decode($grupos_json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $grupos_seleccionados = ['Error al decodificar grupos: ' . json_last_error_msg()];
                    }
                }

                $imagenes_adjuntas = [];
                if (isset($_GET['imagenes_adjuntas'])) {
                    $imagenes_json = urldecode($_GET['imagenes_adjuntas']);
                    $imagenes_adjuntas = json_decode($imagenes_json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $imagenes_adjuntas = ['Error al decodificar imágenes: ' . json_last_error_msg()];
                    }
                }
                ?>

                <div class="datos-vehiculo-wrapper confirmacion-page">
                    <div class="equipamiento-card confirmacion-card">
                        <div class="equipamiento-card-content text-center">
                            <?php if ($status === 'success') : ?>
                                <div class="confirmacion-icon-wrapper success">
                                    <span class="confirmacion-icon">&#10004;</span>
                                </div>
                                <h1 class="equipamiento-card-titulo">¡Solicitud Enviada Correctamente!</h1>
                                <p class="equipamiento-card-descripcion confirmacion-message">Hemos recibido tu solicitud de reposición de piezas.</p>
                                <p class="equipamiento-card-descripcion confirmacion-submessage">Nos pondremos en contacto contigo lo antes posible para confirmar los detalles.</p>
                            <?php else : ?>
                                <div class="confirmacion-icon-wrapper error">
                                    <span class="confirmacion-icon">&#10006;</span>
                                </div>
                                <h2 class="equipamiento-card-titulo">Ocurrió un Problema al Enviar</h2>
                                <p class="equipamiento-card-descripcion confirmacion-message"><?php echo esc_html($message); ?></p>
                                <p class="equipamiento-card-descripcion confirmacion-submessage">Por favor, inténtalo de nuevo o contacta con nosotros directamente.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="equipamiento-lista-tarjetas">
                        <div class="equipamiento-card">
                            <div class="equipamiento-card-content">
                                <h6 class="equipamiento-card-titulo">Datos de Contacto</h6>
                                <div class="equipamiento-card-descripcion"><strong>Nombre:</strong> <?php echo esc_html($nombre); ?></div>
                                <div class="equipamiento-card-descripcion"><strong>Teléfono:</strong> <a href="tel:<?php echo esc_attr($telefono); ?>"><?php echo esc_html($telefono); ?></a></div>
                                <div class="equipamiento-card-descripcion"><strong>Observaciones:</strong> <?php echo esc_html($observaciones); ?></div>
                            </div>
                        </div>

                        <div class="equipamiento-card">
                            <div class="equipamiento-card-content">
                                <h6 class="equipamiento-card-titulo">Información del Vehículo</h6>
                                <div class="equipamiento-card-descripcion"><strong>Modelo:</strong> <?php echo esc_html($modelo); ?></div>
                                <div class="equipamiento-card-descripcion"><strong>Matrícula:</strong> <?php echo esc_html($matricula); ?></div>
                                <div class="equipamiento-card-descripcion"><strong>Policía:</strong> <?php echo esc_html($policia); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($grupos_seleccionados) && is_array($grupos_seleccionados)) : ?>
                            <div class="equipamiento-card full-width-card">
                                <div class="equipamiento-card-content">
                                    <h6 class="equipamiento-card-titulo">Grupos de Rotulación Solicitados</h6>
                                    <ul class="grupos-seleccionados-lista">
                                        <?php foreach ($grupos_seleccionados as $grupo_nombre => $piezas) : ?>
                                            <li>
                                                <strong><?php echo esc_html($grupo_nombre); ?>:</strong>
                                                <?php if (is_array($piezas) && !empty($piezas)) : ?>
                                                    <ul class="piezas-seleccionadas">
                                                        <?php foreach ($piezas as $pieza) : ?>
                                                            <li><?php echo esc_html($pieza); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else : ?>
                                                    <span>Sin piezas especificadas.</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($imagenes_adjuntas) && is_array($imagenes_adjuntas)) : ?>
                            <div class="equipamiento-card full-width-card">
                                <div class="equipamiento-card-content">
                                    <h6 class="equipamiento-card-titulo">Imágenes Adjuntas</h6>
                                    <div class="galeria-adjunta">
                                        <?php foreach ($imagenes_adjuntas as $idx => $img_url) : ?>
                                            <a href="<?php echo esc_url($img_url); ?>" data-lightbox="imagenes-adjuntas" data-title="Imagen Adjunta <?php echo $idx + 1; ?>">
                                                <img src="<?php echo esc_url($img_url); ?>" alt="Imagen Adjunta" style="max-width: 100px; height: auto; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="#" class="btn-asistencia" id="btn-volver-consulta">
                        Volver a Consulta de Vehículo
                    </a>
                </div>

            </div>
        </article>
    </main>
</div>

<?php
get_footer(); // Incluye el pie de página de tu tema
?>