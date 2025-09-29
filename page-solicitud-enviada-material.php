<?php
/**
 * Template Name: Plantilla Confirmacion Solicitud Enviada de material
 * Description: Plantilla para mostrar la confirmación de envío de la solicitud de material, con estilo integrado.
 */

get_header(); // Incluye el encabezado de tu tema
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="entry-content">
                <?php
                // En la plantilla de tu página confirmacion-solicitud.php o a través de un shortcode

                // Decodificar y luego sanitizar. rawurldecode() es ideal para parámetros de URL de JS.
                $status = isset($_GET['status']) ? sanitize_text_field(rawurldecode($_GET['status'])) : '';
                $message = isset($_GET['message']) ? sanitize_text_field(rawurldecode($_GET['message'])) : 'Solicitud procesada.';

                // Parámetros de pedido de material (no se están usando en el output, pero aplica la misma lógica si los mostraras)
                $item = isset($_GET['item']) ? sanitize_text_field(rawurldecode($_GET['item'])) : ''; 
                $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 0;

                // Parámetros de rotulación (mantener si la misma página se usa para rotulación)
                $nombre_rotulacion = isset($_GET['nombre']) ? sanitize_text_field(rawurldecode($_GET['nombre'])) : '';
                $telefono_rotulacion = isset($_GET['telefono']) ? sanitize_text_field(rawurldecode($_GET['telefono'])) : '';
                $observaciones_rotulacion = isset($_GET['observaciones']) ? sanitize_textarea_field(rawurldecode($_GET['observaciones'])) : ''; 
                
                // Parámetros del vehículo
                $vehiculo_modelo = isset($_GET['vehiculo_modelo']) ? sanitize_text_field(rawurldecode($_GET['vehiculo_modelo'])) : 'N/A'; // Usar 'vehiculo_modelo' de la URL como envías desde JS
                $vehiculo_matricula = isset($_GET['vehiculo_matricula']) ? sanitize_text_field(rawurldecode($_GET['vehiculo_matricula'])) : 'N/A'; // Usar 'vehiculo_matricula' de la URL
                $vehiculo_policia = isset($_GET['vehiculo_policia']) ? sanitize_text_field(rawurldecode($_GET['vehiculo_policia'])) : 'N/A'; // Usar 'vehiculo_policia' de la URL
                $id_vehiculo_qr = isset($_GET['vehiculo_id']) ? sanitize_text_field(rawurldecode($_GET['vehiculo_id'])) : 'N/A'; // Usar 'vehiculo_id' de la URL (si es que lo envías así)
                // Nota: Tu JS envía 'vehiculo_modelo', 'vehiculo_matricula', 'vehiculo_policia'. Tu PHP los esperaba como 'modelo', 'matricula', 'policia'. He corregido esto para que coincidan.
                // Revisa si 'id_vehiculo_qr' se envía como 'vehiculo_id' en JS. Si no, debería ser 'N/A' o ajusta el nombre.

                // --- NUEVOS PARÁMETROS: INFORMACIÓN DE CONTACTO DEL SOLICITANTE DE REPUESTO ---
                $nombre_contacto = isset($_GET['nombre_contacto']) ? sanitize_text_field(rawurldecode($_GET['nombre_contacto'])) : 'N/A';
                $telefono_contacto = isset($_GET['telefono_contacto']) ? sanitize_text_field(rawurldecode($_GET['telefono_contacto'])) : 'N/A';
                $observaciones_contacto = isset($_GET['observaciones_contacto']) ? sanitize_textarea_field(rawurldecode($_GET['observaciones_contacto'])) : 'Sin observaciones adicionales.';
                
                // Estos parámetros de producto_tipo, producto_nombre, etc., no se enviaron desde el JS anterior,
                // por lo que permanecerán vacíos a menos que vengan de otro lugar.
                $producto_tipo = isset($_GET['producto_tipo']) ? sanitize_text_field(rawurldecode($_GET['producto_tipo'])) : '';
                $producto_nombre = isset($_GET['producto_nombre']) ? sanitize_text_field(rawurldecode($_GET['producto_nombre'])) : '';
                $producto_descripcion = isset($_GET['producto_descripcion']) ? sanitize_textarea_field(rawurldecode($_GET['producto_descripcion'])) : '';
                $producto_imagen_url = isset($_GET['producto_imagen_url']) ? esc_url_raw(rawurldecode($_GET['producto_imagen_url'])) : '';
                ?>

                <div class="datos-vehiculo-wrapper confirmacion-page"> 
                    <div class="equipamiento-card confirmacion-card text-center">
                        <div class="equipamiento-card-content">
                            
                            <?php if ($status === 'success') : ?>
                                <div class="confirmacion-icon-wrapper success">
                                    <span class="confirmacion-icon">&#10004;</span>
                                </div>
                                <h1>¡Solicitud de Material Recibida!</h1>
                                <p class="lead text-success"><?php echo esc_html($message); ?></p>
                            <?php else : ?>
                                <div class="confirmacion-icon-wrapper error">
                                    <span class="confirmacion-icon">&#10006;</span>
                                </div>
                                <h2 class="equipamiento-card-titulo">Ocurrió un Problema al Enviar</h2>
                                <p class="lead text-danger"><?php echo esc_html($message); ?></p>
                            <?php endif; ?>
                            <p>Gracias por tu solicitud. Nos pondremos en contacto contigo pronto.</p>
                        </div>
                    </div>

                    <div class="equipamiento-lista-tarjetas">
                        <div class="equipamiento-card">
                            <div class="equipamiento-card-content">
                                <h6 class="equipamiento-card-titulo">Detalles del Vehículo</h6>
                                <p><strong>Modelo del Vehículo:</strong> <?php echo esc_html($vehiculo_modelo); ?></p>
                                <p><strong>Matrícula:</strong> <?php echo esc_html($vehiculo_matricula); ?></p>
                                <p><strong>Agente de Policía:</strong> <?php echo esc_html($vehiculo_policia); ?></p>
                            </div>
                        </div>

                    <?php if (!empty($nombre_contacto) || !empty($telefono_contacto) || !empty($observaciones_contacto)) : ?>
                            <div class="equipamiento-card full-width-card">
                                <div class="equipamiento-card-content">
                                    <h6 class="equipamiento-card-titulo">Información de Contacto del Solicitante</h6>
                                    <p><strong>Nombre:</strong> <?php echo esc_html($nombre_contacto); ?></p>
                                    <p><strong>Teléfono:</strong> <?php echo esc_html($telefono_contacto); ?></p>
                                    <?php if (!empty($observaciones_contacto) && $observaciones_contacto !== 'Sin observaciones adicionales.') : ?>
                                        <p><strong>Observaciones:</strong> <?php echo nl2br(esc_html($observaciones_contacto)); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($observaciones_rotulacion)) : ?>
                            <div class="equipamiento-card full-width-card">
                                <div class="equipamiento-card-content">
                                    <h6 class="equipamiento-card-titulo">Resumen del pedido</h6>
                                    <p><?php echo esc_html($observaciones_rotulacion); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php 
                    // Estos parámetros de producto_tipo, producto_nombre, etc., no se enviaron desde el JS anterior,
                    // por lo que permanecerán vacíos a menos que vengan de otro lugar.
                    // Si ya no se usan, puedes eliminar todo este bloque.
                    if (!empty($producto_tipo)) : ?>
                    <div class="row justify-content-center mt-4">
                        <div class="col-md-8">
                            <div class="equipamiento-card full-width-card">
                                <div class="equipamiento-card-content">
                                    <h6 class="equipamiento-card-titulo">Información Adicional del Producto</h6>
                                    <p><strong>Tipo:</strong> <?php echo esc_html($producto_tipo); ?></p>
                                    <p><strong>Modelo/Nombre:</strong> <?php echo esc_html($producto_nombre); ?></p>
                                    <p><strong>Descripción:</strong> <?php echo esc_html($producto_descripcion); ?></p>
                                    <?php if (!empty($producto_imagen_url)) : ?>
                                        <div class="image-container mt-3">
                                            <a href="<?php echo esc_url($producto_imagen_url); ?>" class="lightbox-link" data-title="<?php echo esc_attr($producto_nombre); ?>">
                                                <img src="<?php echo esc_url($producto_imagen_url); ?>" alt="<?php echo esc_attr($producto_nombre); ?>" class="amplificador-img" style="max-width: 200px; height: auto;">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                   
                    <div class="">
                        <a href="/consulta-de-vehiculo" class="btn-asistencia">Volver a la Consulta de Vehículos</a>
                    </div>
                </div>

            </div>
        </article>
    </main>
</div>

<?php
get_footer(); // Incluye el pie de página de tu tema
?>