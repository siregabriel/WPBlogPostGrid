<?php
/**
 * Atlas Senior Living - Custom Blog Grid Plugin
 * Plugin Name: Custom Blog Grid
 * Description: Displays a grid of 5 blog posts with pagination and a settings panel for button customization.
 * Version: 1.2.0
 * Author: Gabriel Rosales 
 * Author URI: https://www.gabrielrosales.org/plugins/custom-blog-grid
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * grosales@atlasseniorliving.com
 */

// Seguridad: Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ========================================================================
// SISTEMA DE ACTUALIZACIONES VÍA GITHUB (Plugin Update Checker)
// ========================================================================
require_once plugin_dir_path( __FILE__ ) . 'plugin-update-checker-master/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$miActualizador = PucFactory::buildUpdateChecker(
    'https://github.com/siregabriel/WPBlogPostGrid/', 
    __FILE__, 
    'custom-blog-grid' 
);
$miActualizador->setBranch('main');


// ========================================================================
// ENLACES DE ACCIÓN DEL PLUGIN (Tabla de Plugins)
// ========================================================================
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cbg_add_settings_link' );

function cbg_add_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=cbg-settings' ) . '">' . __( 'Settings', 'custom-blog-grid' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}


// ========================================================================
// PANEL DE AJUSTES (Settings API & Color Picker)
// ========================================================================

// 1. Crear el menú
add_action( 'admin_menu', 'cbg_create_menu' );
function cbg_create_menu() {
    add_options_page( 'Blog Grid Settings', 'Blog Grid', 'manage_options', 'cbg-settings', 'cbg_settings_page' );
}

// 2. Registrar las opciones
add_action( 'admin_init', 'cbg_register_settings' );
function cbg_register_settings() {
    register_setting( 'cbg-settings-group', 'cbg_button_text' );
    // Normal State Colors
    register_setting( 'cbg-settings-group', 'cbg_button_bg_color' );
    register_setting( 'cbg-settings-group', 'cbg_button_text_color' );
    register_setting( 'cbg-settings-group', 'cbg_button_border_color' );
    // Hover State Colors
    register_setting( 'cbg-settings-group', 'cbg_button_hover_bg_color' );
    register_setting( 'cbg-settings-group', 'cbg_button_hover_text_color' );
    register_setting( 'cbg-settings-group', 'cbg_button_hover_border_color' );
}

// 3. Cargar Color Picker
add_action( 'admin_enqueue_scripts', 'cbg_enqueue_admin_scripts' );
function cbg_enqueue_admin_scripts( $hook_suffix ) {
    if ( $hook_suffix !== 'settings_page_cbg-settings' ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    $custom_js = "
        jQuery(document).ready(function($){
            $('.cbg-color-picker').wpColorPicker();
        });
    ";
    wp_add_inline_script( 'wp-color-picker', $custom_js );
}

// 4. Interfaz del panel (Traducida al inglés + Nuevos campos)
function cbg_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom Blog Grid Settings</h1>
        <p>Customize the appearance of the grid buttons and the pagination system.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'cbg-settings-group' ); ?>
            <?php do_settings_sections( 'cbg-settings-group' ); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Button Text</th>
                    <td>
                        <input type="text" name="cbg_button_text" value="<?php echo esc_attr( get_option('cbg_button_text', 'Read the full article &rarr;') ); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>

            <h2 class="title">Normal State Colors</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Background Color</th>
                    <td>
                        <input type="text" name="cbg_button_bg_color" value="<?php echo esc_attr( get_option('cbg_button_bg_color', '') ); ?>" class="cbg-color-picker" data-default-color="#ffffff" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Text Color</th>
                    <td>
                        <input type="text" name="cbg_button_text_color" value="<?php echo esc_attr( get_option('cbg_button_text_color', '#1a1a1a') ); ?>" class="cbg-color-picker" data-default-color="#1a1a1a" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Border Color</th>
                    <td>
                        <input type="text" name="cbg_button_border_color" value="<?php echo esc_attr( get_option('cbg_button_border_color', '#1a569d') ); ?>" class="cbg-color-picker" data-default-color="#1a569d" />
                    </td>
                </tr>
            </table>

            <h2 class="title">Hover State Colors</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Hover Background Color</th>
                    <td>
                        <input type="text" name="cbg_button_hover_bg_color" value="<?php echo esc_attr( get_option('cbg_button_hover_bg_color', '') ); ?>" class="cbg-color-picker" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Hover Text Color</th>
                    <td>
                        <input type="text" name="cbg_button_hover_text_color" value="<?php echo esc_attr( get_option('cbg_button_hover_text_color', '') ); ?>" class="cbg-color-picker" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Hover Border Color</th>
                    <td>
                        <input type="text" name="cbg_button_hover_border_color" value="<?php echo esc_attr( get_option('cbg_button_hover_border_color', '') ); ?>" class="cbg-color-picker" />
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save Changes'); ?>
        </form>
    </div>
    <?php
}


// ========================================================================
// FUNCIONALIDAD DEL FRONT-END (Estilos y Shortcode)
// ========================================================================

function cbg_enqueue_styles() {
    $css_file = plugin_dir_path( __FILE__ ) . 'style.css';
    $css_version = file_exists( $css_file ) ? filemtime( $css_file ) : '1.2.0';
    
    wp_enqueue_style( 'cbg-styles', plugin_dir_url( __FILE__ ) . 'style.css', array(), $css_version );

    // Obtener colores normales
    $bg_color = get_option('cbg_button_bg_color');
    $text_color = get_option('cbg_button_text_color');
    $border_color = get_option('cbg_button_border_color');
    
    // Obtener colores hover
    $hover_bg = get_option('cbg_button_hover_bg_color');
    $hover_text = get_option('cbg_button_hover_text_color');
    $hover_border = get_option('cbg_button_hover_border_color');
    
    $custom_css = "";
    
    // --- ESTILOS ESTADO NORMAL ---
    if ( !empty($bg_color) ) {
        $custom_css .= ".cbg-button { background-color: {$bg_color}; } ";
        $custom_css .= ".cbg-pagination span.current { background-color: {$bg_color}; } ";
    }
    if ( !empty($text_color) ) {
        $custom_css .= ".cbg-button { color: {$text_color}; } ";
        $custom_css .= ".cbg-pagination span.current { color: {$text_color}; } ";
    }
    if ( !empty($border_color) ) {
        $custom_css .= ".cbg-button { border: 1px solid {$border_color}; } ";
        $custom_css .= ".cbg-pagination span.current { border-color: {$border_color}; } ";
        $custom_css .= ".cbg-pagination a { border-color: {$border_color}; color: {$border_color}; } ";
    }

    // --- ESTILOS ESTADO HOVER ---
    if ( !empty($hover_bg) || !empty($hover_text) || !empty($hover_border) ) {
        // Para los botones del grid
        $custom_css .= ".cbg-button:hover { ";
        if ( !empty($hover_bg) ) $custom_css .= "background-color: {$hover_bg}; ";
        if ( !empty($hover_text) ) $custom_css .= "color: {$hover_text}; ";
        if ( !empty($hover_border) ) $custom_css .= "border-color: {$hover_border}; ";
        $custom_css .= "} ";
        
        // Para la paginación (enlaces clickeables, no la página actual)
        $custom_css .= ".cbg-pagination a:hover { ";
        if ( !empty($hover_bg) ) $custom_css .= "background-color: {$hover_bg}; ";
        if ( !empty($hover_text) ) $custom_css .= "color: {$hover_text}; ";
        if ( !empty($hover_border) ) $custom_css .= "border-color: {$hover_border}; ";
        $custom_css .= "} ";
    }
    
    if ( !empty($custom_css) ) {
        wp_add_inline_style( 'cbg-styles', $custom_css );
    }
}
add_action( 'wp_enqueue_scripts', 'cbg_enqueue_styles' );

// Crear el Shortcode con Paginación
function cbg_render_grid( $atts ) {
    $atts = shortcode_atts( array(
        'category'          => '',
        'featured_position' => 'left',
    ), $atts, 'blog_grid' );

    if ( get_query_var( 'paged' ) ) {
        $paged = get_query_var( 'paged' );
    } elseif ( get_query_var( 'page' ) ) {
        $paged = get_query_var( 'page' );
    } else {
        $paged = 1;
    }

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 5, 
        'post_status'    => 'publish',
        'paged'          => $paged
    );

    if ( ! empty( $atts['category'] ) ) {
        $args['category_name'] = $atts['category'];
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p>No posts found.</p>';
    }

    $layout_class = ( $atts['featured_position'] === 'right' ) ? ' cbg-layout-right' : '';
    
    $button_text = get_option('cbg_button_text', 'Read the full article &rarr;');

    $output = '<div class="cbg-wrapper">'; 
    $output .= '<div class="cbg-container' . $layout_class . '">';
    $post_count = 0;

    while ( $query->have_posts() ) {
        $query->the_post();
        $post_count++;

        $title = get_the_title();
        $link = get_permalink();
        $excerpt = wp_trim_words( get_the_excerpt(), 15, '...' );
        $category = get_the_category();
        $cat_name = ! empty( $category ) ? esc_html( $category[0]->name ) : '';

        $button = '<a href="' . esc_url( $link ) . '" class="cbg-button">' . esc_html( $button_text ) . '</a>';

        if ( $post_count === 1 ) {
            $image = get_the_post_thumbnail( get_the_ID(), 'medium_large' );
            $output .= '<div class="cbg-card cbg-featured">';
            if ( $image ) $output .= '<div class="cbg-image">' . $image . '</div>';
            $output .= '<div class="cbg-content"><span class="cbg-category">' . strtoupper( $cat_name ) . '</span><h3>' . esc_html( $title ) . '</h3><p>' . esc_html( $excerpt ) . '</p>' . $button . '</div></div>';
        } else {
            $output .= '<div class="cbg-card cbg-secondary"><div class="cbg-content"><h3>' . esc_html( $title ) . '</h3><p>' . esc_html( $excerpt ) . '</p>' . $button . '</div></div>';
        }
    }
    $output .= '</div>'; 

    $total_pages = $query->max_num_pages;
    if ( $total_pages > 1 ) {
        $current_page = max( 1, $paged );
        $pagination_links = paginate_links( array(
            'base'      => get_pagenum_link( 1 ) . '%_%',
            'format'    => 'page/%#%/',
            'current'   => $current_page,
            'total'     => $total_pages,
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
        ) );
        $output .= '<div class="cbg-pagination">' . $pagination_links . '</div>';
    }

    $output .= '</div>'; 

    wp_reset_postdata();
    return $output;
}
add_shortcode( 'blog_grid', 'cbg_render_grid' );