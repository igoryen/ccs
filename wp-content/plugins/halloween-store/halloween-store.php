<?php
/**
* Plugin Name: Halloween Store
*/

register_activation_hook( _FILE_, 'halloween_store_install' );

function halloween_store_install() {
    $hween_options_arr = array(
      'currency_sign' => '$'  
    );

    update_option( 'halloween_options', '$hween_options_arr' );
}


add_action( 'init', 'halloween_store_init' );

function halloween_store_init() {

    $labels = array(
        'name'                  => __( 'Products',                      'halloween-plugin'),
        'singular_name'         => __( 'Product',                       'halloween-plugin'),
        'add_new'               => __( 'Add New',                       'halloween-plugin'),
        'add_new_item'          => __( 'Add New Product',               'halloween-plugin'),
        'edit_item'             => __( 'Edit Product',                  'halloween-plugin'),
        'new_item'              => __( 'New Product',                   'halloween-plugin'),
        'all_items'             => __( 'All Products',                  'halloween-plugin'),
        'view_item'             => __( 'View Product',                  'halloween-plugin'),
        'search_items'          => __( 'Search Products',               'halloween-plugin'),
        'not_found'             => __( 'No Products Found',             'halloween-plugin'),
        'not_found_in_trash'    => __( 'No Products found in trash',    'halloween-plugin'),
        'menu_name'             => __( 'Products',                      'halloween-plugin')
    );

    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'publicly_queryable'=> true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'query_var'         => true,
        'rewrite'           => true,
        'capability_type'   => 'post',
        'has_archive'       => true,
        'hierarchical'      => false,
        'menu_position'     => null,
        'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt')
    );    

    register_post_type( 'halloween_products', $args );
}

add_action( 'admin_menu', 'halloween_store_menu' );

// 1
function halloween_store_menu() {
    add_options_page(
        __( 'Halloween Store Settings Page',    'halloween-plugin' ),
        __( 'Halloween Store Settings',         'halloween-plugin' ),
        'manage_options',
        'halloween-store-settings',
        'halloween_store_settings_page'
    );
}

function halloween_store_settings_page() {
    $hween_options_arr = get_option( 'halloween_options' );

    $hs_inventory = (! empty( $hween_options_arr[ 'show_inventory' ] )) ? $hween_options_arr[ 'show_inventory' ] : '';
    $hs_currency_sign = $hween_options_arr[ 'currency_sign' ];
    ?>
    <div class="wrap">
        <h2><?php _e( 'Halloween Store Options', 'halloween-plugin' ) ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'halloween-settings-group' ); // 2 ?>
            <table class="form-table">
                
                <tr valign="top">
                    <th scope="row"><?php _e( 'Show Product Inventory', 'halloween-plugin' ) ?></th>
                    <td>
                        <input type="checkbox" name="halloween_options[show_inventory]"
                            <?php echo checked( $hs_inventory, 'on' ); ?>
                        />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e( 'Currency Sign', 'halloween-plugin' ) ?></th>
                    <td>
                        <input type="text" 
                               name="halloween-options[currency_sign]" 
                               value="<?php echo esc_attr( $hs_currency_sign ); ?>"
                               size="1"
                               maxlength="1"
                        />
                    </td>
                </tr>

            </table>

            <p class="submit">
                <input type="submit"
                       class="button-primary"
                       value="<?php _e('Save Changes', 'halloween-plugin'); ?>"
                />
            </p>

        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'halloween_store_register_settings' );

function halloween_store_register_settings(){
    // 3
    register_setting( 
        'halloween-settings-group',
        'halloween_options',
        'halloween_sanitize_options' 
    );
}
// 4
function halloween_sanitize_options() {
    $options[ 'show_inventory' ] = ( ! empty( $options[ 'show_inventory' ] )) ? sanitize_text_field( $options[ 'show_inventory' ] ) : ''; 
    $options[ 'currency_sign' ] = ( ! empty( $options[ 'currency_sign' ] )) ? sanitize_text_field( $options[ 'currency_sign' ] ) : ''; 
}

add_action( 'add_meta_boxes', 'halloween_store_register_meta_box' );

function halloween_store_register_meta_box() {
    add_meta_box(
        'halloween-product-meta',
        __( 'Product Information', 'halloween-plugin' ),
        'halloween_meta_box',
        'halloween_products',
        'side',
        'default'
    );
}

function halloween_meta_box( $post ) {
    $hs_meta = get_post_meta( $post->ID, '_halloween_product_data', true ); 

    $hween_sku      = ( ! empty($hs_meta['sku']) )      ? $hs_meta['sku'] : '';
    $hween_price    = ( ! empty($hs_meta['price']) )    ? $hs_meta['price'] : ''; 
    $hween_weight   = ( ! empty($hs_meta['weight']) )   ? $hs_meta['weight'] : ''; 
    $hween_color    = ( ! empty($hs_meta['color']) )    ? $hs_meta['color'] : ''; 
    $hween_inventory= ( ! empty($hs_meta['inventory'])) ? $hs_meta['inventory'] : ''; 

    wp_nonce_field( 'meta-box-save', 'halloween-plugin' );

    $bag = array();
    array_push($bag, '<table>');

    array_push($bag, '<tr>');
    array_push($bag, '<td>' . __('Sku', 'halloween-plugin') .':</td>');
    array_push($bag, '<td><input type="text" name="halloween_product[sku]" value="'.esc_attr( $hween_sku ) .'" size="10"></td>');
    array_push($bag, '</tr>');

    array_push($bag, '<tr>');
    array_push($bag, '<td>' . __('Price', 'halloween-plugin') .':</td>');
    array_push($bag, '<td><input type="text" name="halloween_product[price]" value="'.esc_attr( $hween_price ) .'" size="5"></td>');
    array_push($bag, '</tr>');

    array_push($bag, '<tr>');
    array_push($bag, '<td>' . __('Weight', 'halloween-plugin') .':</td>');
    array_push($bag, '<td><input type="text" name="halloween_product[weight]" value="'.esc_attr( $hween_weight ) .'" size="5"></td>');
    array_push($bag, '</tr>');

    array_push($bag, '<tr>');
    array_push($bag, '<td>' . __('Color', 'halloween-plugin') .':</td>');
    array_push($bag, '<td><input type="text" name="halloween_product[color]" value="'.esc_attr( $hween_color ) .'" size="5"></td>');
    array_push($bag, '</tr>');

    array_push($bag, '<tr>');
    array_push($bag, '<td>Inventory:</td>');
    array_push($bag, '<td>');
    array_push($bag, '<select name="halloween_product[inventory]" id="halloween_product[inventory]">');
    array_push($bag, '<option value="In Stock"'     . selected($hween_inventory, 'In Stock', false)     . '>' . __('In Stock',     'halloween-plugin') . '</option>');
    array_push($bag, '<option value="Backordered"'  . selected($hween_inventory, 'Backordered', false)  . '>' . __('Backordered',  'halloween-plugin') . '</option>');
    array_push($bag, '<option value="Out of Stock"' . selected($hween_inventory, 'Out of Stock', false) . '>' . __('Out of Stock', 'halloween-plugin') . '</option>');
    array_push($bag, '<option value="Discontinued"' . selected($hween_inventory, 'Discontinued', false) . '>' . __('Discontinued', 'halloween-plugin') . '</option>');
    array_push($bag, '</select>');
    array_push($bag, '</td>');
    array_push($bag, '</tr>');

    array_push($bag, '<tr><td colspan="2"><hr></td></tr>');
    array_push($bag, '<tr><td colspan="2"><strong>'.__('Shortcode Legend', 'halloween-plugin').'</strong></td></tr>');
    array_push($bag, '<tr><td>'. __('Sku',       'halloween-plugin'). ':</td><td>[hs show=sku]</td></tr>');
    array_push($bag, '<tr><td>'. __('Price',     'halloween-plugin'). ':</td><td>[hs show=price]</td></tr>');
    array_push($bag, '<tr><td>'. __('Weight',    'halloween-plugin'). ':</td><td>[hs show=weight]</td></tr>');
    array_push($bag, '<tr><td>'. __('Color',     'halloween-plugin'). ':</td><td>[hs show=color]</td></tr>');
    array_push($bag, '<tr><td>'. __('Inventory', 'halloween-plugin'). ':</td><td>[hs show=inventory]</td></tr>');

    array_push($bag, '</table>');

    foreach ($bag as $item) {
        echo $item;
    }

    $bag = '';

    add_action( 'save_post', 'halloween_store_save_meta_box' );

    function halloween_store_save_meta_box( $post_id ) {
        if( get_post_type( $post_id ) == 'halloween-products' && isset( $_POST[ 'halloween_product' ] ) ) {
            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            wp_verify_nonce( 'meta-box-save', 'halloween-plugin' );
            $halloween_product_data = $_POST[ 'halloween_product' ];
            $halloween_product_data = array_map( 'sanitize_text_field', $halloween_product_data );
            update_post_meta( $post_id, '_halloween_product_data', $halloween_product_data );
        }
    }

    add_shortcode( 'hs', 'halloween_store_shortcode' );
    function halloween_store_shortcode( $atts, $content = null ) {
        global $post;
        extract( shortcode_atts( array( "show" => '' ), $atts ) );
        $hween_options_arr  = get_option( 'halloween_options' );
        $hween_product_data = get_post_meta( $post->ID, '_halloween_product_data', true );
        if ( $show == 'sku' ) {
            $hs_show = ( ! empty( $hween_product_data['sku'] ) ) ? $hween_product_data['sku'] : '';
        }
        elseif ( $show == 'price' ) {
            $hs_show = $hween_options_arr['currency_sign'];
            $hs_show = ( ! empty( $hween_product_data['price'] ) ) ? $hween_product_data['price'] : '';
        }
        elseif ( $show == 'weight' ) {
            $hs_show = ( ! empty( $hween_product_data['weight'] ) ) ? $hween_product_data['weight'] : '';
        }
        elseif ( $show == 'color' ) {
            $hs_show = ( ! empty( $hween_product_data['color'] ) ) ? $hween_product_data['color'] : '';
        }
        elseif ( $show == 'inventory' ) {
            $hs_show = ( ! empty( $hween_product_data['inventory'] ) ) ? $hween_product_data['inventory'] : '';
        }
        return $hs_show;
    }

    add_action( 'widgets_init', 'halloween_store_register_widgets' );
    function halloween_store_register_widgets() {
        register_widget( 'hs_widget' );
    }
    
    class hs_widget extends WP_Widget {
        
        function __construct() {
            $widget_ops = array(
                'classname'     => 'hs-widget-class',
                'description'   => __( 'Display Halloween Products', 'halloween-plugin' ),
            );
            parent::__construct(
                'hs_widget',
                __( 'Products Widget', 'halloween-plugin' ),
                $widget_ops
            );
        }

        function form( $instance ) {

            $defaults = array(
                'title'             => __( 'Products', 'halloween-plugin' ),
                'number_products'   => '3'
            );

            $instance = wp_parse_args( (array) $instance, $defaults );
            $title = $instance['instance'];
            $number_products = $instance['number_products'];
            ?>
                <p><?php _e('Title', 'halloween-plugin') ?>:
                    <input  class="widefat" 
                            name="<?php echo $this->get_field_name( 'title' ); ?>"
                            type="text"
                            value="<?php echo esc_attr( $title ); ?>"
                            />
                </p>
                <p><?php _e('Number of Products', 'halloween-plugin') ?>
                    <input  name="<?php echo $this->get_field_name( 'number_products' ); ?>"
                            type="text"
                            value="<?php echo absint($number_products); ?>"
                            size="2"
                            maxlength="2"
                    />
                </p>
            <?php

        }

        function update( $new_instance, $old_instance ) {
            $instance = $old_instance;
            $instance['title'] = sanitize_text_field( $new_instance['title'] );
            $instance['number_products'] = absint( $new_instance['number_products'] );
            return $instance;
        }

        function widget( $args, $instance ) {
            global $post;
            extract( $args );
            echo $before_widget;
            $title = apply_filters( 'widget_title', $instance['title'] );
            $number_products = $instance['number_products'];

            if( ! empty( $title ) ) {
                echo $before_title . esc_html( $title ) . $after_title;
            };

            $args = array(
                'post_type'      => 'halloween-products',
                'posts_per_page' => absint($number_products) 
            );

            $dispProducts = new WP_Query();
            $dispProducts->query( $args );

            while ( $dispProducts->have_posts() ) : $dispProducts->the_post();

                $hween_options_arr = get_option( 'halloween_options' );
                $hween_product_data = get_post_meta( $post->ID, '_halloween_product_data', true );

                $hs_price     = ( ! empty( $hween_product_data['price'] ))      ? hween_product_data['price'] : '';
                $hs_inventory = ( ! empty( $hween_product_data['inventory'] ))  ? hween_product_data['inventory'] : '';
                ?>
                <p>
                    <a  href="<?php the_permalink(); ?>"
                        rel="bookmark"
                        title="<?php the_title_attribute(); ?>Product Information">
                    </a>
                </p>
                <?php 
                    echo '<p>' . __( 'Price', 'halloween-plugin' ) . ': ' . $hween_options_arr['currency_sign'] . $hs_price . '</p>';
                    if ($hween_options_arr['show_inventory']) {
                        echo '<p>'. __('Stock', 'halloween-plugin'). ': '. $hs_inventory . '</p>';
                    }
                    echo '<hr>';
            endwhile;
            wp_reset_postdata();
            echo $after_widget;
        }
    }
}