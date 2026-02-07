<?php
/**
 * Plugin Name: BB Portfolio Gallery
 * Plugin URI: https://github.com/BusinessBloomHU/BB-Portfolio-Gallery
 * Description: Egyszerű galéria plugin képekkel és YouTube shortokkal, masonry layout és lightbox megjelenítéssel. Tökéletes szépségipari szakembereknek.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Tested up to: 6.7
 * Requires Plugins: advanced-custom-fields
 * Author: Business Bloom Consulting®
 * Author URI: https://businessbloom.consulting
 * License: MIT License
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: bbloom-gallery
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

class BBloom_Gallery {
    
    private static $instance = null;
    const VERSION = '1.0.0';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('acf/init', [$this, 'register_acf_fields']);
        add_action('admin_notices', [$this, 'check_acf_dependency']);
        add_filter('manage_gallery_item_posts_columns', [$this, 'admin_columns']);
        add_action('manage_gallery_item_posts_custom_column', [$this, 'admin_column_content'], 10, 2);
        add_shortcode('bbloom_gallery', [$this, 'gallery_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * ACF függőség ellenőrzése
     */
    public function check_acf_dependency() {
        if (!function_exists('acf_add_local_field_group')) {
            $screen = get_current_screen();
            if ($screen && $screen->post_type === 'gallery_item') {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>BusinessBloom Galéria:</strong> Az Advanced Custom Fields plugin szükséges a működéshez. ';
                echo '<a href="' . admin_url('plugin-install.php?s=Advanced+Custom+Fields&tab=search&type=term') . '">Telepítsd innen</a>';
                echo '</p></div>';
            }
        }
    }
    
    /**
     * Custom Post Type regisztráció
     */
    public function register_post_type() {
        $labels = [
            'name'               => 'Galéria',
            'singular_name'      => 'Galéria elem',
            'menu_name'          => 'Galéria',
            'add_new'            => 'Új elem',
            'add_new_item'       => 'Új galéria elem hozzáadása',
            'edit_item'          => 'Galéria elem szerkesztése',
            'new_item'           => 'Új galéria elem',
            'view_item'          => 'Galéria elem megtekintése',
            'search_items'       => 'Galéria elemek keresése',
            'not_found'          => 'Nem található galéria elem',
            'not_found_in_trash' => 'Kukában nem található galéria elem',
        ];
        
        $args = [
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-format-gallery',
            'supports'            => ['title', 'thumbnail'],
            'show_in_rest'        => true,
        ];
        
        register_post_type('gallery_item', $args);
    }
    
    /**
     * ACF mezők regisztrálása
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        acf_add_local_field_group([
            'key' => 'group_bbloom_gallery',
            'title' => 'Galéria Beállítások',
            'fields' => [
                [
                    'key' => 'field_youtube_id',
                    'label' => 'YouTube Videó ID',
                    'name' => 'youtube_id',
                    'type' => 'text',
                    'instructions' => '<strong>Videó elem esetén:</strong> Másold be a YouTube videó ID-t (pl: dQw4w9WgXcQ)<br><strong>Kép elem esetén:</strong> Hagyd üresen ezt a mezőt és állítsd be a kiemelt képet lentebb.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'placeholder' => 'dQw4w9WgXcQ',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'gallery_item',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ]);
    }
    
    /**
     * Admin oszlopok
     */
    public function admin_columns($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['media_type'] = 'Típus';
                $new_columns['preview'] = 'Előnézet';
            }
        }
        return $new_columns;
    }
    
    /**
     * Admin oszlop tartalom
     */
    public function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'media_type':
                $yt_id = get_field('youtube_id', $post_id);
                $has_thumb = has_post_thumbnail($post_id);
                
                if ($yt_id) {
                    echo '<span style="font-size:18px;">🎥</span> YouTube Short';
                } elseif ($has_thumb) {
                    echo '<span style="font-size:18px;">🖼️</span> Kép';
                } else {
                    echo '<span style="font-size:18px;">⚠️</span> Üres';
                }
                break;
                
            case 'preview':
                $yt_id = get_field('youtube_id', $post_id);
                if ($yt_id) {
                    echo '<img src="https://img.youtube.com/vi/' . esc_attr($yt_id) . '/mqdefault.jpg" style="max-width:60px;height:auto;border-radius:4px;">';
                } elseif (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, [60, 60]);
                }
                break;
        }
    }
    
    /**
     * Assets betöltése
     */
    public function enqueue_assets() {
        if (!is_admin()) {
            // GLightbox CSS
            wp_enqueue_style(
                'glightbox',
                plugin_dir_url(__FILE__) . 'assets/css/glightbox.min.css',
                [],
                '3.3.0'
            );
            
            // Galéria CSS
            wp_add_inline_style('glightbox', $this->get_gallery_css());
            
            // GLightbox JS
            wp_enqueue_script(
                'glightbox',
                plugin_dir_url(__FILE__) . 'assets/js/glightbox.min.js',
                [],
                '3.3.0',
                true
            );
            
            // Galéria inicializálás
            wp_enqueue_script(
                'bbloom-gallery-init',
                plugin_dir_url(__FILE__) . 'assets/js/gallery-init.js',
                ['glightbox'],
                self::VERSION,
                true
            );
        }
    }
    
    /**
     * Shortcode
     */
    public function gallery_shortcode($atts) {
        $atts = shortcode_atts([
            'limit' => -1,
            'order' => 'rand',
            'class' => '',
        ], $atts, 'bbloom_gallery');
        
        $query_args = [
            'post_type' => 'gallery_item',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['order'],
        ];
        
        $items = new WP_Query($query_args);
        
        if (!$items->have_posts()) {
            return '<p>Nincs megjeleníthető galéria elem.</p>';
        }
        
        $extra_class = !empty($atts['class']) ? ' ' . esc_attr($atts['class']) : '';
        
        ob_start();
        ?>
        <div class="bbloom-gallery gallery<?php echo $extra_class; ?>">
            <?php while ($items->have_posts()) : $items->the_post();
                $yt_id = get_field('youtube_id');
                $has_thumb = has_post_thumbnail();
                
                if ($yt_id) {
                    // YouTube Short
                    $embed_url = "https://www.youtube.com/embed/{$yt_id}?enablejsapi=1&autoplay=1&mute=1&loop=1&playlist={$yt_id}&controls=0&modestbranding=1&rel=0";
                    ?>
                    <div class="gallery-item gallery-item--video">
                        <a href="<?php echo esc_url($embed_url); ?>" 
                           data-glightbox="type: video"
                           data-gallery="bbloom-main">
                            <div class="video-thumb">
                                <?php if ($has_thumb) : ?>
                                    <?php the_post_thumbnail('large'); ?>
                                <?php else : ?>
                                    <img src="https://img.youtube.com/vi/<?php echo esc_attr($yt_id); ?>/maxresdefault.jpg" 
                                         alt="<?php the_title_attribute(); ?>" 
                                         loading="lazy">
                                <?php endif; ?>
                                <span class="play-icon">▶</span>
                            </div>
                        </a>
                    </div>
                    <?php
                } elseif ($has_thumb) {
                    // Kép
                    $img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    ?>
                    <div class="gallery-item gallery-item--image">
                        <a href="<?php echo esc_url($img_url); ?>">
                            <?php the_post_thumbnail('large'); ?>
                        </a>
                    </div>
                    <?php
                }
            endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
    
    /**
     * Galéria CSS
     */
    private function get_gallery_css() {
        return <<<CSS
/* BusinessBloom Galéria - Masonry Layout */
.bbloom-gallery {
    column-count: 4;
    column-gap: 1rem;
    padding: 2rem 0;
}

.bbloom-gallery .gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: #f5f5f5;
    margin-bottom: 1rem;
    break-inside: avoid;
    page-break-inside: avoid;
}

.bbloom-gallery .gallery-item a {
    display: block;
    width: 100%;
}

.bbloom-gallery .gallery-item img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.bbloom-gallery .gallery-item:hover img {
    transform: scale(1.05);
}

/* Videó specifikus - ÁLLÓ 9:16 arány */
.bbloom-gallery .video-thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 9 / 16;
    overflow: hidden;
}

.bbloom-gallery .video-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bbloom-gallery .play-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 3rem;
    color: white;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    pointer-events: none;
    opacity: 0.9;
    transition: opacity 0.3s;
    z-index: 2;
}

.bbloom-gallery .gallery-item--video:hover .play-icon {
    opacity: 1;
}

/* Desktop normál - 3 oszlop */
@media (max-width: 1200px) {
    .bbloom-gallery {
        column-count: 3;
    }
}

/* Tablet - 2 oszlop */
@media (max-width: 900px) {
    .bbloom-gallery {
        column-count: 2;
        column-gap: 0.75rem;
    }
    
    .bbloom-gallery .gallery-item {
        margin-bottom: 0.75rem;
    }
}

/* Mobil - 2 oszlop, kisebb gap */
@media (max-width: 640px) {
    .bbloom-gallery {
        column-gap: 0.5rem;
        padding: 1rem 0;
    }
    
    .bbloom-gallery .gallery-item {
        margin-bottom: 0.5rem;
        border-radius: 6px;
    }
    
    .bbloom-gallery .play-icon {
        font-size: 2.5rem;
    }
}
CSS;
    }
}

// Plugin inicializálás
function bbloom_gallery_init() {
    return BBloom_Gallery::get_instance();
}
add_action('plugins_loaded', 'bbloom_gallery_init');

// Aktiváláskor
register_activation_hook(__FILE__, function() {
    BBloom_Gallery::get_instance()->register_post_type();
    flush_rewrite_rules();
});

// Deaktiváláskor
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
