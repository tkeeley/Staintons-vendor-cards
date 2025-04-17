<?php
/*
Plugin Name: Location Vendors
Description: Custom post type for generating vendor cards with modal pop-up and location filtering.
Version: 1.1
Author: Cup O Code
License: GPL2
*/

// Enqueue Font Awesome library
function location_vendors_enqueue_scripts() {
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'location_vendors_enqueue_scripts' );

// Register the custom vendor cards custom post type
function location_vendors_register_post_type() {
    $labels = array(
        'name' => 'Location Vendors',
        'singular_name' => 'Location Vendor',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'supports' => array( 'title', 'thumbnail' ),
        'menu_icon' => 'dashicons-cart', 
        'has_archive' => true,
        'rewrite' => array( 'slug' => 'location-vendors' ),
        'hierarchical' => false,
        'show_in_rest' => true,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    register_post_type( 'location-vendors', $args );
}
add_action( 'init', 'location_vendors_register_post_type' );

// Register a custom hierarchical taxonomy for vendor locations
function location_vendors_register_taxonomy() {
    $labels = array(
        'name'              => 'Vendor Locations',
        'singular_name'     => 'Vendor Location',
        'search_items'      => 'Search Vendor Locations',
        'all_items'         => 'All Vendor Locations',
        'parent_item'       => 'Parent Vendor Location',
        'parent_item_colon' => 'Parent Vendor Location:',
        'edit_item'         => 'Edit Vendor Location',
        'update_item'       => 'Update Vendor Location',
        'add_new_item'      => 'Add New Vendor Location',
        'new_item_name'     => 'New Vendor Location Name',
        'menu_name'         => 'Vendor Locations',
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true, // uses checkboxes in the meta box
        'public'            => true,
        'rewrite'           => array( 'slug' => 'vendor-location' ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
    );

    register_taxonomy( 'vendor_location', 'location-vendors', $args );
}
add_action( 'init', 'location_vendors_register_taxonomy' );

// On plugin activation, create the predefined location terms if they don't exist.
function location_vendors_activate() {
    // Ensure the taxonomy is registered
    location_vendors_register_taxonomy();

    $locations = array('Downtown', 'Boardwalk', 'Shoppes at the Asbury', '3 Little Birds', 'Shoobie Shack');
    foreach ($locations as $location) {
        if ( ! term_exists( $location, 'vendor_location' ) ) {
            wp_insert_term( $location, 'vendor_location' );
        }
    }
}
register_activation_hook( __FILE__, 'location_vendors_activate' );

// Add custom meta box for vendor card details
function location_vendors_add_meta_box() {
    add_meta_box(
        'location-vendors-details',
        'Details',
        'location_vendors_meta_box_callback',
        'location-vendors',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'location_vendors_add_meta_box' );

// Save vendor card details meta box data
function location_vendors_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['location_vendors_nonce'] ) || ! wp_verify_nonce( $_POST['location_vendors_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    if ( isset( $_POST['vendor_name'] ) ) {
        update_post_meta( $post_id, 'vendor_name', sanitize_text_field( $_POST['vendor_name'] ) );
    }

    if ( isset( $_POST['vendor_description'] ) ) {
        update_post_meta( $post_id, 'vendor_description', wp_kses_post( $_POST['vendor_description'] ) );
    }
}
add_action( 'save_post', 'location_vendors_save_meta_box_data' );

// Callback function for the vendor card details meta box
function location_vendors_meta_box_callback($post) {
    wp_nonce_field(basename(__FILE__), 'location_vendors_nonce');
    $vendor_name = get_post_meta($post->ID, 'vendor_name', true);
    $vendor_description = get_post_meta($post->ID, 'vendor_description', true);

    ?>
    <p>
        <label for="vendor_name">Owner Name:</label>
        <input type="text" name="vendor_name" id="vendor_name" value="<?php echo esc_attr($vendor_name); ?>">
    </p>
    <p>
        <label for="vendor_description">Description:</label>
        <textarea name="vendor_description" id="vendor_description" rows="4"><?php echo esc_html($vendor_description); ?></textarea>
    </p>
    <?php
    // The vendor locations meta box is automatically handled by WordPress for the "vendor_location" taxonomy.
}

// Shortcode to display vendor cards with optional location filtering
function location_vendors_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count'    => -1,
        'location' => '' // e.g., location="Downtown"
    ), $atts, 'location_vendors');

    $args = array(
        'post_type'      => 'location-vendors',
        'posts_per_page' => $atts['count'],
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    // If a location is specified, add a taxonomy query
    if ( ! empty( $atts['location'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'vendor_location',
                'field'    => 'name',
                'terms'    => sanitize_text_field( $atts['location'] ),
            ),
        );
    }

    $vendor_cards = new WP_Query($args);

    ob_start();
    if ($vendor_cards->have_posts()) {
        ?>
        <div class="location-vendors-wrapper">
            <?php 
                $counter = 0;
                while ($vendor_cards->have_posts()) : $vendor_cards->the_post(); 
                    $counter++;
            ?>
                <div class="location-vendor-card" data-vendor-id="<?php the_ID(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="location-vendor-image">
                            <?php the_post_thumbnail(); ?>
                        </div>
                    <?php endif; ?>
                    <div class="location-vendor-content">
                        <h3 class="location-vendor-title"><?php the_title(); ?></h3>
                        <p class="location-vendor-name"><?php echo esc_html(get_post_meta(get_the_ID(), 'vendor_name', true)); ?></p>
                    </div>
                </div>
                <div id="vendor-<?php the_ID(); ?>-modal-content" class="location-vendor-modal" style="display: none;">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <div class="modal-left">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="location-vendor-image">
                                    <?php the_post_thumbnail(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-right">
                            <div class="location-vendor-content">
                                <h3 class="location-vendor-title"><?php the_title(); ?></h3>
                                <p class="location-vendor-name"><?php echo esc_html(get_post_meta(get_the_ID(), 'vendor_name', true)); ?></p>
                                <p class="location-vendor-description"><?php echo esc_html(get_post_meta(get_the_ID(), 'vendor_description', true)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                    if ($counter % 4 == 0) {
                        echo '<div class="clearfix"></div>';
                    }
                endwhile; 
            ?>
        </div>

        <style>
        .location-vendors-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
        }
        /* Styles for modal */
        .location-vendor-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            overflow: auto;
            padding-top: 60px;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 9998;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            margin-top: 5%;
            padding: 20px;
            border: 1px solid #888;
            max-width: 80%;
            position: relative;
            z-index: 9999;
            display: flex;
            align-items: center;
            flex-direction: row;
            justify-content: center;
        }
        .modal-left {
            flex: 1;
        }
        .modal-right {
            flex: 2;
            padding-left: 20px;
        }
        .close {
            color: #aaa;
            position: absolute;
            top: 0px;
            right: 5px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
        .location-vendor-card h3 {
            font-size: 1.2em;
            padding-top: 5px;
            margin: 0;
        }
        .location-vendor-card {
            width: 23%;
            float: left;
            margin-right: 1%;
            margin-bottom: 20px;
            border: 2px solid #75BBCC;
        }
        .location-vendor-card:nth-child(4n) {
            margin-right: 0;
        }
        @media (max-width: 1200px) {
            .location-vendor-card {
                width: 31.3333%;
                margin-right: 1%;
            }
            .location-vendor-card:nth-child(3n) {
                margin-right: 0;
            }
        }
        @media (max-width: 768px) {
            .location-vendor-card {
                width: 48%;
                margin-right: 2%;
            }
            .location-vendor-card:nth-child(2n) {
                margin-right: 0;
            }
        }
        @media (max-width: 480px) {
            .location-vendor-card {
                width: 100%;
                margin-right: 0;
            }
        }
        @media (max-width: 768px) {
            .modal-content {
                flex-direction: column;
                align-items: center;
                max-width: 80%;
                margin-top: 5%;
            }
            .modal-left,
            .modal-right {
                flex: 1;
                text-align: center;
                padding: 20px;
            }
            .modal-right {
                padding-top: 20px;
            }
            .modal-right p {
                margin: 10px 0;
            }
            .close {
                top: 0px;
                right: 5px;
                font-size: 32px;
                font-weight: bold;
                cursor: pointer;
            }
        }
        .clearfix {
            clear: both;
        }
        html.modal-open {
            overflow: hidden;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Function to open modal
            $('.location-vendor-card').click(function() {
                var vendor_id = $(this).data('vendor-id');
                $('#vendor-' + vendor_id + '-modal-content').show();
                $('body').addClass('modal-open');
                $('html').addClass('modal-open');
            });
            // Function to close modal
            $('.close, .modal-overlay').click(function() {
                $('.location-vendor-modal').hide();
                $('body').removeClass('modal-open');
                $('html').removeClass('modal-open');
            });
        });
        </script>
        <?php
    }
    $output = ob_get_clean();
    wp_reset_postdata();
    return $output;
}
add_shortcode('location_vendors', 'location_vendors_shortcode');
