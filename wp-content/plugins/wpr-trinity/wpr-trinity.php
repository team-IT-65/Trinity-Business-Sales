<?php
/**
 * Plugin Name: Rest-Trinity Listing
 * Author: SilverWebBuzz
 * Description: Custom plugin to fetch details from Trinity Business Sale React Site.
 * Version: 1.0.0
 * Text Domain: wpr-trinity
 * Modification Date: 05-17-2024
 */

define('API_URL', 'https://cms.trinityretailsales.com/api/properties');
define('WPR_TRINITY', 'wpr-trinity');
define('IMAGE_URL', 'https://cms.trinityretailsales.com');

class WPR_TRINITY {

    public function __construct() {
        $this->init();
    }

    private function init() {
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
        add_action('init', [$this, 'register_async']);
    }

    public function register_async() {
        add_shortcode('sc-wpr-trinity', [$this, 'render']);
        add_shortcode('sc-wpr-trinity-offer', [$this, 'render']);
    }

    public function register_scripts() {
        $wpr_css = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css'));
        $wpr_css1 = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/css/slick-theme.css'));
        $wpr_css2 = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/css/slick.css'));

        $jsver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/js/script.js'));
        $jsver1 = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/js/jquery-3.2.1.slim.min.js'));  
        $jsver2 = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/js/jquery-migrate-1.2.1.min.js'));
        $jsver3 = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/js/slick.min.js'));

        wp_register_style('wpr-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), $wpr_css);
        wp_register_style('wpr-responsive', plugin_dir_url(__FILE__) . 'assets/css/responsive.css', array(), $wpr_css);
        wp_register_style('wpr-slick', plugin_dir_url(__FILE__) . 'assets/css/slick.css', array(), $wpr_css2);
        wp_enqueue_style('wpr-slick-theme', plugin_dir_url(__FILE__) . 'assets/css/slick-theme.css', array(), $wpr_css1);
        wp_register_script('wpr-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), $jsver, true);
        wp_register_script('wpr-script-jquery', plugin_dir_url(__FILE__) . 'assets/js/jquery-3.2.1.slim.min.js', array('jquery'), $jsver1, true);
        wp_register_script('wpr-script-jquery-migrate', plugin_dir_url(__FILE__) . 'assets/js/jquery-migrate-1.2.1.min.js', array('jquery'), $jsver2, true);
        wp_register_script('wpr-slick-min', plugin_dir_url(__FILE__) . 'assets/js/slick.min.js', array('jquery'), $jsver3, true);

        wp_localize_script('wpr-script', 'wprs', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'plugin_dir' => plugin_dir_url(__FILE__),
            'empty_boat_message' => __('No more properties are available!', WPR_TRINITY),
            'permalink' => get_permalink(),
            'api_url' => defined('API_URL') ? API_URL : '',
        ));
    }

    private function getProperties($param = []) {
        $type = !empty($param['type']) ? $param['type'] : '';
        $id = !empty($param['id']) ? '/' . $param['id'] . '/?' . $type : '';

        if (!empty($id)) {
            $callBackUrl = defined('API_URL') ? API_URL . $id : '';
        } else {
            $callBackUrl = defined('API_URL') ? API_URL . '?' . $type : '';
        }

        $per_page = !empty($param['page']) ? '&per_page=' . $param['page'] : '';
        $page_no = !empty($param['pageSize']) ? '&page_no=' . $param['pageSize'] : '';
        $page_count = !empty($param['pageCount']) ? '&page_count=' . $param['pageCount'] : '';

        $response = wp_remote_get($callBackUrl, ['sslverify' => true, 'timeout' => 30]);
        if (is_wp_error($response)) {
            error_log("API request failed: " . $response->get_error_message());
            return "Error: Failed to fetch data from the API.";
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log("API request failed with status code: " . $response_code);
                return "Error: API returned status code " . $response_code;
            }
            return ['body' => wp_remote_retrieve_body($response)];
        }
    }

    public function render($atts, $content = null, $tag) {
        wp_enqueue_style('wpr-style');
        wp_enqueue_style('wpr-responsive');
        wp_enqueue_script('wpr-script');

        ob_start();

        if (isset($_GET['id']) && !empty($_GET['id'])) {
            require_once plugin_dir_path(__FILE__) . 'inc/properties-single.php';
        } else {
            $param = ['type' => 'populate=*'];
            $properties = $this->getProperties($param);
            $data = json_decode($properties['body'], true);
            $image_url = IMAGE_URL;

            $output = '<section><div class="row properties-list">';

            foreach ($data['data'] as $property) {
                $category_names = [];
                $show_button = true;
                $show_property = false;
                $content = $property['content'];

                 if (isset($property['categories']) && is_array($property['categories'])) {
                    foreach ($property['categories'] as $category) {
                        if (isset($category['name'])) {
                            $category_names[] = strtolower($category['name']);
                            if ($tag == 'sc-wpr-trinity' && in_array($category['name'], ['Under Offer', 'Acquisition', 'On the market'])) {
                                $show_property = true;
                                if ($category['name'] === 'Under Offer') {
                                    $button_text = 'Call for Information';
                                } elseif ($category['name'] === 'Acquisition') {
                                    $button_text = 'Call for Agreement';
                                } elseif ($category['name'] === 'On the market') {
                                    $show_property = true;
                                    $button_text = 'View Details';
                                    // $button_class = 'sold-class';
                            }
                            } elseif ($tag == 'sc-wpr-trinity-offer' && $category['name'] === 'Sold') {
                                $show_property = true;
                                $button_text = 'View Details';
                                $button_class = 'sold-class';
                            }
                        }
                    }
                }

                if ($show_property) {
                    $category_classes = implode(' ', $category_names);
                    $output .= '<div class="col-md-4 property ' . $category_classes . '">';
                    $output .= '<div class="cat-property">';

                      if (in_array('under offer', $category_names)) {
                        $output .= '<h5>' . esc_html('Under Offer') . '</h5>';
                        $output .= '<div class="category-image under-offer">';
                        $output .= '<img src="https://www.trinityretailsales.com/images/padlock.png" width="50" height="50">';
                        $output .= '</div>';
                    } elseif (in_array('acquisition', $category_names)) {
                        $output .= '<h5>' . esc_html('Acquisition') . '</h5>';
                        $output .= '<div class="category-image acquisition">';
                        $output .= '<img src="https://www.trinityretailsales.com/images/tlogo.png" width="50" height="50">';
                        $output .= '</div>';
                    } elseif (in_array('on the market', $category_names)) {
                        $output .= '<h5>' . esc_html('On the market') . '</h5>';
                        // $output .= '<div class="category-image acquisition">';
                        // $output .= '<img src="https://www.trinityretailsales.com/images/tlogo.png" width="50" height="50">';
                        // $output .= '</div>';
                    } elseif (in_array('sold', $category_names)) {
                        $output .= '<h5 class="sold-namings">' . esc_html('Sold') . '</h5>';
                        $output .= '<div class="category-image sold"></div>';
                    }

                    $output .= '</div>';

                    if (!empty($property['media']['formats']['medium']['url'])) {
                        $output .= '<div class="property-gallery">';
                        $thumbnail_url = $image_url . $property['media']['formats']['medium']['url'];
                        $output .= '<img src="' . $thumbnail_url . '" alt="' . esc_attr($property['title']) . '" class="product-main-img">';
                    }

                    $output .= '<div class="product-description">';
                    $output .= '<h3 class="product-title">' . esc_html(strtoupper($property['title'])) . ' ' . esc_html($property['location']) . '</h3>';
                    $output .= '<h4 class="product-type ' . sanitize_html_class($property['type']) . '">' . esc_html($property['type']) . '</h4>';
                    $output .= '<h6 class="weekly_sale_price">';
                    if (!empty($property['sales'])) {
                        $output .= '<strong>Weekly sales:</strong> £' . esc_html($property['sales']);
                    }
                    $output .= '</h6>';

                if ($show_button) {
                    if (in_array('under offer', $category_names) || in_array('acquisition', $category_names)) {
                        $redirect_url = get_permalink() . 'contact';
                        $output .= '<a href="' . $redirect_url . '" class="view-details-button">' . $button_text . '</a>';
                    } elseif ((in_array('sold', $category_names) || in_array('on the market', $category_names)) && $content != '') {
                       $property_id = $property['id'];
                        $output .= '<a href="' . get_permalink() . '?id=' . esc_html($property_id) . '" class="view-details-button ' . esc_attr($button_class) . '">' . $button_text . '</a>';
                     }
                  }

                    $output .= '<h6 class="selling_price">';
                    if (in_array('acquisition', $category_names) && empty($property['price'])) {
                        $output .= '£POA';
                    } else {
                        if (!empty($property['price'])) {
                            $output .= '£' . esc_html($property['price']);
                        }
                    }
                    $output .= '</h6>';
                  
                    $output .= '<img src="https://www.trinityretailsales.com/_next/image?url=https%3A%2F%2Fcms.trinityretailsales.com%2Fuploads%2Flogo_web_small_85a5233cf9.png&w=640&q=75" class="logo_retails">';
                    
                    $output .= '</div></div></div>';
                }
            }

            $output .= '</div></section>';
        }

        return $output;
    }
}

new WPR_TRINITY();