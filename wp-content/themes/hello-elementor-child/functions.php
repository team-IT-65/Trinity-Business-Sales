<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {
	wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', ['hello-elementor-theme-style',], HELLO_ELEMENTOR_CHILD_VERSION);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 *  Enqueue scripts and styles:-
*/
function child_enqueue_styles() {
  wp_enqueue_style( 'slick', get_stylesheet_directory_uri() . '/assets/css/slick.css');
  wp_enqueue_style( 'slick-theme', get_stylesheet_directory_uri() . '/assets/css/slick-theme.css');
  wp_enqueue_style( 'app', get_stylesheet_directory_uri() . '/assets/css/app.css');
  wp_enqueue_style( 'responsive', get_stylesheet_directory_uri() . '/assets/css/responsive.css');
   wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css');
  wp_enqueue_style( 'bootstrap-min-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
  wp_enqueue_script( 'script-boostrap-min-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
  //  wp_enqueue_script( 'bootstrap-min-js', get_stylesheet_directory_uri() . '/assets/js/bootstrap.bundle.min.js');
    
 }
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 9,2);

/* -----------Enqueue js----------- */

/* Theme script enqueue */
add_action('wp_footer', 'plugin_assets');
function plugin_assets() {
  wp_enqueue_script('app-js', get_stylesheet_directory_uri() . '/assets/js/app.js');
  wp_enqueue_script('slick-js', get_stylesheet_directory_uri() . '/assets/js/slick.min.js');
 }

function custom_html_shortcode() {
   ob_start();
   // Corrected typo in 'required_once'
   require_once('map-svg.html');
   // Corrected typo in 'ob_get_clean()'
   return ob_get_clean();
}
add_shortcode( 'map_svg', 'custom_html_shortcode' );


/*-------Sectors category in Homepage------*/

function list_sectors_taxonomy_terms_shortcode($atts) {
    // Attributes
    $atts = shortcode_atts(
        array(
            'taxonomy' => 'categories', // Default taxonomy
        ),
        $atts,
        'list_sectors_taxonomy_terms'
    );

    // Get terms
    $terms = get_terms(array(
        'taxonomy' => $atts['taxonomy'],
        'hide_empty' => false,
    ));

    // Check if there are terms
    if (!empty($terms) && !is_wp_error($terms)) {
        $output .= '<div class="all_product dev_product">';
        $output .= '<div class="row">';

        // Loop through the terms
        foreach ($terms as $term) {
            $image = get_field('image', $term); // Replace 'image' with your ACF field name

            if ($image) {
                $output .= '<div class="col-md-4 single_card_main">';
                $output .= '<img class= "fetaure-sector-img" src="' . esc_url($image['url']) . '" alt="' . esc_attr($term->name) . '">'; // Display image
                $output .= '<h4 class="sector-titles"><a href="' .  get_category_link($term->term_id) . '">' . $term->name . '</a></h4>';

                $output .= '</div>';
            }
        }

        $output .= '</div>'; // Close row
        $output .= '</div>'; // Close all_product div

        return $output;
    } else {
        return '<p>No terms found</p>';
    }
}
add_shortcode('list_sectors_taxonomy_terms', 'list_sectors_taxonomy_terms_shortcode');



//------Post for Sector by mail-------// 

function get_custom_post_type_title_shortcode() {
    if (is_singular('sectors')) {
        global $post;
        return $post->post_title;
    }
    return '';
}
add_shortcode('custom_post_title', 'get_custom_post_type_title_shortcode');






