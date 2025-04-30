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

/* *********************************************************************************************
 Encolar archivo de JavaScript personalizado
********************************************************************************************* */
function configurator_custom_scripts() {
    wp_enqueue_script(
        'configurator-scripts', // Identificador del script
        get_stylesheet_directory_uri() . '/js/configurator-scripts.js', // Ruta del archivo JS
        array('jquery'), // Dependencias (jQuery en este caso)
        '1.0', // Versión
        true // Cargar en el footer
    );
}
add_action('wp_enqueue_scripts', 'configurator_custom_scripts');

/* ******************************************************************************************** */

add_action( 'genesis_entry_content', 'featured_post_image', 8 );
function featured_post_image() {
    if ( ! is_singular( 'post' ) )  return;
    the_post_thumbnail('post-image');
}