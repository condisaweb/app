<?php

/* *********************************************************************************************
 PLEASE DO NOT DELETE THIS FUNCTION BECAUSE THIS FUNCTION IS CALLING CHILD THEME CSS FILE
********************************************************************************************* */

function configurator_child_themestyles() {
    // Encolar los estilos del tema hijo
    wp_enqueue_style(
        'child-theme-style', // Identificador del estilo
        get_stylesheet_directory_uri() . '/child-theme-style.css', // Ruta al archivo CSS del tema hijo
        [], // Sin dependencias (Pure.css eliminado)
        '1.0' // Versión del estilo
    );
}
// Remover el hook de registro si ya no es necesario
remove_action('wp_enqueue_scripts', 'configurator_child_themestyles');

/* ******************************************************************************************** 
      manejar el envío del formulario y enviar el correo    */

add_action( 'genesis_entry_content', 'featured_post_image', 8 );
function featured_post_image() {
    if ( ! is_singular( 'post' ) )  return;
    the_post_thumbnail('post-image');
}

add_action('wp_ajax_send_selected_pieces', 'send_selected_pieces');
add_action('wp_ajax_nopriv_send_selected_pieces', 'send_selected_pieces');

function send_selected_pieces() {
    // Obtener las piezas seleccionadas desde el formulario
    $selected_pieces = sanitize_text_field($_POST['selected_pieces']);

    // Configurar el correo
    $to = 'comunicacion@condisatransformaciones.com';
    $subject = 'Selección de piezas de rotulación';
    $message = "Las siguientes piezas han sido seleccionadas:\n" . $selected_pieces;
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    // Enviar el correo
    wp_mail($to, $subject, $message, $headers);

    // Respuesta de éxito
    wp_send_json_success('Correo enviado exitosamente.');
}