<?php
/**
 * Atlas Senior Living - Custom Blog Grid Plugin
 * Plugin Name: Custom Blog Grid
 * Description: Muestra un grid de 5 posts (1 destacado con imagen, 4 secundarios) mediante el shortcode [blog_grid category="slug-categoria"].
 * Version: 1.0
 * Author: Gabriel Rosales 
 * Attached: style.css
 * grosales@atlasseniorliving.com
 */


// Seguridad: Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Cargar la librería Plugin Update Checker
require_once plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';

// 2. Usar el namespace correcto de la librería (versión 5)
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// 3. Inicializar el actualizador con la URL de tu repositorio en GitHub
$miActualizador = PucFactory::buildUpdateChecker(
	'https://github.com/siregabriel/WPBlogPostGrid.git', 
	__FILE__, // Archivo principal del plugin
	'custom-blog-grid' // Slug del plugin
);

// Opcional: Si vas a usar "main" o "master" como rama principal
$miActualizador->setBranch('main');

// Opcional: SI TU REPOSITORIO ES PRIVADO, necesitas un Token de acceso personal (PAT) de GitHub
// $miActualizador->setAuthentication('tu_token_generado_en_github');

//INICIAMOS PLUGIN

// Seguridad: Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar los estilos CSS
function cbg_enqueue_styles() {
    wp_enqueue_style( 'cbg-styles', plugin_dir_url( __FILE__ ) . 'style.css' );
}
add_action( 'wp_enqueue_scripts', 'cbg_enqueue_styles' );

// Crear el Shortcode
function cbg_render_grid( $atts ) {
    // Atributos por defecto (si no se pone categoría, muestra los últimos de cualquier categoría)
    $atts = shortcode_atts( array(
        'category' => '', // slug de la categoría
    ), $atts, 'blog_grid' );

    // Argumentos de la consulta
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 5, // Exactamente los 5 que necesitas
        'post_status'    => 'publish',
    );

    if ( ! empty( $atts['category'] ) ) {
        $args['category_name'] = $atts['category'];
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p>No se encontraron artículos.</p>';
    }

    // Iniciar el contenedor principal
    $output = '<div class="cbg-container">';
    $post_count = 0;

    while ( $query->have_posts() ) {
        $query->the_post();
        $post_count++;

        $title = get_the_title();
        $link = get_permalink();
        $excerpt = wp_trim_words( get_the_excerpt(), 15, '...' );
        $category = get_the_category();
        $cat_name = ! empty( $category ) ? esc_html( $category[0]->name ) : '';

        // Botón reutilizable
        $button = '<a href="' . esc_url( $link ) . '" class="cbg-button">Read the full article &rarr;</a>';

        if ( $post_count === 1 ) {
            // --- POST DESTACADO (Izquierda) ---
            $image = get_the_post_thumbnail( get_the_ID(), 'medium_large' );
            
            $output .= '<div class="cbg-card cbg-featured">';
            if ( $image ) {
                $output .= '<div class="cbg-image">' . $image . '</div>';
            }
            $output .= '<div class="cbg-content">';
            $output .= '<span class="cbg-category">' . strtoupper( $cat_name ) . '</span>';
            $output .= '<h3>' . esc_html( $title ) . '</h3>';
            $output .= '<p>' . esc_html( $excerpt ) . '</p>';
            $output .= $button;
            $output .= '</div>'; // fin content
            $output .= '</div>'; // fin card
        } else {
            // --- POSTS SECUNDARIOS (Derecha) ---
            $output .= '<div class="cbg-card cbg-secondary">';
            $output .= '<div class="cbg-content">';
            $output .= '<h3>' . esc_html( $title ) . '</h3>';
            $output .= '<p>' . esc_html( $excerpt ) . '</p>';
            $output .= $button;
            $output .= '</div>'; // fin content
            $output .= '</div>'; // fin card
        }
    }

    $output .= '</div>'; // Fin cbg-container

    wp_reset_postdata();

    return $output;
}
add_shortcode( 'blog_grid', 'cbg_render_grid' );