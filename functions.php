<?php

// --- Carga de Estilos del Tema Hijo ---
function configurator_child_themestyles() {
    wp_enqueue_style(
        'child-theme-style',
        get_stylesheet_directory_uri() . '/child-theme-style.css',
        [],
        '1.0'
    );
}
add_action('wp_enqueue_scripts', 'configurator_child_themestyles');


// --- Imagen Destacada Genesis ---
add_action( 'genesis_entry_content', 'featured_post_image', 8 );
function featured_post_image() {
    if (! is_singular( 'post' ) )  return;
    the_post_thumbnail('post-image');
}