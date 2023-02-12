<?php
/*
Plugin Name: Word Count Posts
Plugin URI: http://github.com/sumanengbd/
Description: The Word Count Posts plugin is an exceptional tool for counting words in your posts.
Version: 1.0
Author: Suman Ali
Author URI: http://github.com/sumanengbd/
Text Domain: wcp
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WordCountPosts {

    /**
	 * The plugin version number.
	 *
	 */
	public $version = '1.0';

    /**
	 * The plugin construct function.
	 *
	 */
    function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_menu', array($this, 'wcp_admin_page') );
        add_action( 'admin_init', array( $this, 'wcp_settings' ) );
        add_filter( 'the_content', array( $this, 'wcp_if_wraping' ) );
        add_action('wp_footer', array($this, 'wcp_progress_bar_wraping'));

        // Load Admin CSS
        add_action( 'admin_enqueue_scripts', array( $this, 'wcp_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wcp_frontend_assets' ) );

        // Selected Post Type Admin Column
        $wcp_post_types = get_option( 'wcp_post_types', array() );

        if ( !empty( $wcp_post_types ) ) {
            add_action( 'pre_get_posts', array( $this, 'wcp_sort_posts_by_word_count' ) );
            add_action('save_post', array ( $this, 'wcp_set_meta_save' ));

            foreach ( $wcp_post_types as $post_type ) {
                add_filter( "manage_{$post_type}_posts_columns", array( $this, 'wcp_post_type_columns' ) );
                add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'wcp_post_type_columns_rows' ), 10, 2 );

                add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'wcp_register_sortable_column' ) );
            }
        }
    }

    /**
     * Progress Bar HTML for Front-End
     * 
     */
    function wcp_progress_bar_wraping() {
        $currentPageId = get_queried_object_id();
        $post_type = get_post_type( $currentPageId );
    
        // check if a post type is available for the current page
        if (!empty($post_type)) {
            $wcp_post_types = get_option( 'wcp_post_types', array() );
            $wcp_display_posts = get_option( 'wcp_display_posts', array() );
    
            // check if both options are available
            if (!empty($wcp_post_types) && !empty($wcp_display_posts)) {
                if (in_array($post_type, $wcp_post_types) && in_array($currentPageId, $wcp_display_posts)) {
                    include_once( plugin_dir_path( __FILE__ ) . 'includes/front/progress-bar.php' );
                }
            } else if (!empty($wcp_post_types)) {
                // only check for post type if wcp_display_posts is not available
                if (in_array($post_type, $wcp_post_types)) {
                    include_once( plugin_dir_path( __FILE__ ) . 'includes/front/progress-bar.php' );
                }
            }
        }
    }

    /**
     * Register Admin Column Post Type
     * 
     */
    function wcp_post_type_columns( $original_columns ) {
        $new_columns = $original_columns;
        array_splice( $new_columns, -1, 1 );
        $new_columns['wcp_columns'] = esc_html__( 'Word Count', 'wcp' );
        return array_merge( $new_columns, $original_columns );
    }

    function wcp_post_type_columns_rows( $column_name, $post_id ) {
        $html = '';
        $content = get_post_field( 'post_content', $post_id );

        if ( get_option( 'wcp_wordcount' ) || get_option( 'wcp_readtime' ) ) {
            $word_count = str_word_count( strip_tags( $content ) );
        }

        if ( get_option( 'wcp_wordcount' ) ) {
            $html .= sprintf( esc_html__( 'Words - %s', 'wcp' ), $word_count ) . '<br>';
        }

        if ( get_option( 'wcp_charactercount' ) ) {
            $html .= sprintf( esc_html__( 'Characters - %s', 'wcp' ), strlen( strip_tags( $content ) ) ) . '<br>';
        }

        if ( get_option( 'wcp_readtime' ) ) {
            
            $minutes = round( $word_count/189 );

            $text = ( $minutes <= 1 ? esc_html__( '<1', 'wcp' ) : $minutes ) . ' ' . ( $minutes > 1 ? esc_html__( 'minutes', 'wcp' ) : esc_html__( 'minute', 'wcp' ) );
            
            $html .= sprintf( esc_html__( 'Time - %s', 'wcp' ), $text ) ;
        }

        if ( 'wcp_columns' === $column_name ) {
            echo $html;
        }
    }

    /**
     * Column Sorting
     */
    function wcp_register_sortable_column( $columns ) {
        $columns['wcp_columns'] = 'wcp_word_number';
        return $columns;
    }
    
    /**
     * Post sort query for words
     */
    function wcp_sort_posts_by_word_count( $query ) {
        if ( ! is_admin() ) {
          return;
        }
    
        $orderby = $query->get( 'orderby' );
    
        if ( 'wcp_word_number' === $orderby ) {
          $query->set( 'meta_key', 'wcp_word_number' );
          $query->set( 'orderby', 'meta_value_num' );
        }
    }

    /**
     * Update meta init Function Inside
     */
    function wcp_set_meta() {
        $wcp_post_types = get_option( 'wcp_post_types', array() );

        $selected_posts = get_posts( array(
            'posts_per_page' => - 1,
            'post_status'    => 'any',
            'post_type'      => $wcp_post_types,
        ) );

        foreach ( $selected_posts as $post ) {
            $content = $post->post_content;
            $word_number   = str_word_count( strip_tags( $content ) );
            update_post_meta( $post->ID, 'wcp_word_number', $word_number );
        }
    }

    /**
     * Update meta on save
     */
    function wcp_set_meta_save($post_id){
        $post = get_post( $post_id );
        $content = $post->post_content;
        $word_number = str_word_count( strip_tags( $content ) );
        update_post_meta( $post->ID, 'wcp_word_number', $word_number );
    }

    /** 
     * init Function
     * 
     */
    function init() {
        load_plugin_textdomain( 'wcp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // update post meta for Admin Column
        $this->wcp_set_meta();
    }

    /** 
     * Load Admin Assets
     * 
     */
    function wcp_admin_assets( $hook ) {
        // if ( 'settings_page_word-count-posts-settings' != $hook ) {
        //     return;
        // }
        
        // CSS File
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'wcp-admin-select2', plugins_url( 'assets/admin/css/select2.min.css', __FILE__ ), array(), $this->version, 'all' );
        wp_enqueue_style( 'wcp-admin-style', plugins_url( 'assets/admin/css/admin.css', __FILE__ ), array(), $this->version, 'all' );
        
        // JavaScripts File
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'wcp-admin-select2', plugins_url( 'assets/admin/js/select2.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'wcp-admin-scripts', plugins_url( 'assets/admin/js/scripts.js', __FILE__ ), array( 'jquery' ), $this->version, true );
    }

    /** 
     * Load Frontend Assets
     * 
     */
    function wcp_frontend_assets() {
        // CSS File
        wp_enqueue_style( 'wcp-front-style', plugins_url( 'assets/front/css/front.css', __FILE__ ), array(), $this->version, 'all' );

        // JavaScripts File
        wp_enqueue_script( 'wcp-front-scripts', plugins_url( 'assets/front/js/scripts.js', __FILE__ ), array( 'jquery' ), $this->version, true );
    }

    /** 
     * Register Word Count Setting Under Admin Setting Menu
     * 
     */
    function wcp_admin_page() {
        add_options_page( esc_html__('Word Count Plugin Settings', 'wcp' ), esc_html__('Word Count', 'wcp'), 'manage_options', 'word-count-posts-settings', array( $this, 'wcp_settings_html' ) );
    }

    /** 
     * Word Count Settings Form
     * 
     */
    function wcp_settings_html() {
        ?>
        <div class="wrap wcp-wrap">
            <div class="wcp-header">
                <div class="wcp-header__top">
                    <div class="wcp-header__top-left">
                        <span class="h1"><?php echo esc_html__( 'Word Count Posts Settings', 'wcp' ); ?></span>
                    </div>

                    <div class="wcp-header__top-right">
                        <button type="button" id="wcp-form-submit-button" class="button button-primary"><?php echo esc_html__( 'Save Changes', 'wcp'); ?></button>
                    </div>
                </div>

                <div class="wcp-header__bottom">
                    <ul class="wcp-tab__nav">
                        <li class="active"><a href="#basic-settings"><?php echo esc_html__( 'Basic Settings', 'wcp'); ?></a></li>
                        <li><a href="#progress-settings"><?php echo esc_html__( 'Progress Settings', 'wcp'); ?></a></li>
                        <li><a href="#layout-settings"><?php echo esc_html__( 'Layout Settings', 'wcp'); ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="wcp-content">
                <h1 class="wcp-hidden"></h1>
                <form action="options.php" id="wcp-form" method="POST">
                    <?php 
                        settings_fields( 'wordcountposts' ); 
                    ?>
                    <div id="basic-settings" class="wcp-tab__content">
                        <?php
                            do_settings_sections( 'word-count-posts-settings' );
                        ?>
                    </div>

                    <div id="progress-settings" class="wcp-tab__content">
                        <?php 
                            do_settings_sections( 'word-count-posts-settings-progress' );
                        ?>
                    </div>

                    <div id="layout-settings" class="wcp-tab__content">
                        <?php 
                            do_settings_sections( 'word-count-posts-settings-layout' );
                        ?>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div> 
        <?php
    }

    /** 
     * Check Settings and Content Return
     * 
     */
    function wcp_if_wraping( $content ) {
        $currentPageId = get_queried_object_id();
        $post_type = get_post_type( $currentPageId );
        $wcp_post_types = get_option( 'wcp_post_types', array() );
        $wcp_display_posts = get_option( 'wcp_display_posts', array() );

        if ( is_main_query() && !is_home() && (get_option( 'wcp_wordcount', 1 ) || get_option( 'wcp_charactercount', 1 ) || get_option( 'wcp_readtime', 1 )) ) 
        {   
            if (empty($wcp_post_types)) {
                return $content;
            } else if (!empty($wcp_post_types) && !empty($wcp_display_posts)) {
                if (in_array($post_type, $wcp_post_types) && in_array($currentPageId, $wcp_display_posts)) {
                    return $this->wcp_create_html($content);
                }
            } else if (empty($wcp_display_posts)) {
                if (in_array($post_type, $wcp_post_types)) {
                    return $this->wcp_create_html($content);
                }
            }
        }

        return $content;
    }

    /** 
     * Word Count New Content Return
     * 
     */
    function wcp_create_html( $content ) {
        $currentPageId = get_queried_object_id();
        $post_type = get_post_type( $currentPageId );

        $html = '<div class="wcp_statistics">';
        
        if ( get_option( 'wcp_headline' ) ) {
            $html .= '<span class="wcp_title">' . esc_html( get_option( 'wcp_headline', 'Post Statistics' ), 'wcp' ) . '</span>';
        }

        $html .= '<ul>';
        // get word count one because both wordcount and read time will need it.
        if ( get_option( 'wcp_wordcount', '1' ) || get_option( 'wcp_readtime', '1' ) ) {
            $word_count = str_word_count( strip_tags( $content ) );
        }

        if ( get_option( 'wcp_wordcount' ) ) {
            $html .= '<li class="wcp_wordcount">'.sprintf( esc_html__( 'This %s has', 'wcp' ), $post_type ).' <strong>' . $word_count . '</strong> '.esc_html__( 'words', 'wcp' ).'</li>';
        }

        if ( get_option( 'wcp_charactercount' ) ) {
            $html .= '<li class="wcp_charactercount">'.sprintf( esc_html__( 'This %s has', 'wcp' ), $post_type ).' <strong>' . strlen( strip_tags( $content ) ) . '</strong> '.esc_html__( 'characters', 'wcp' ).'</li>';
        }

        if ( get_option( 'wcp_readtime' ) ) {
            
            $minutes = round( $word_count/189 );

            $text = sprintf( esc_html__( 'This %s will take about', 'wcp' ), $post_type ) . ' <strong>' . ( $minutes <= 1 ? esc_html__( '<1', 'wcp' ) : $minutes ) . '</strong> ' . ($minutes > 1 ? esc_html__( 'minutes', 'wcp' ) : esc_html__( 'minute', 'wcp' )) . ' ' . esc_html__( 'to read', 'wcp' ) . '.';
            $text = apply_filters( 'wcp_read_time_text', $text, $minutes );
            
            $html .= '<li class="wcp_readtime">' . $text . '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        if ( get_option( 'wcp_location', '0' ) == '0' ) {
            return $html . $content;
        }

        return $content . $html;
    }

    /** 
     * Load Admin Settings
     * 
     */
    function wcp_settings() {
        require_once( plugin_dir_path( __FILE__ ) . 'includes/admin/settings.php' );
    }

    /** 
     * Display Post Type HTML Select
     * 
     */
    function wcp_post_types_html() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $selected_post_types = get_option('wcp_post_types', array());

        ?>
            <div class="wcp-select">
                <select name="wcp_post_types[]" multiple="multiple" class="wcp-select2" data-placeholder="Select Post Types">
                    <?php
                        foreach ( $post_types as $post_type ) {

                            if ( $post_type->name === 'attachment' ) {
                                continue;
                            }

                            if ( empty ( $selected_post_types ) ) {
                                echo '<option value="' . $post_type->name . '">' . $post_type->labels->singular_name . '</option>';
                            } else {
                                echo '<option value="' . $post_type->name . '" ' . selected(in_array($post_type->name, $selected_post_types), true, false) . '>' . $post_type->labels->singular_name . '</option>';
                            }
                        }
                    ?>
                </select>
            </div>    
        <?php
    }

    /** 
     * Sanitize Post Type HTML Select
     * 
     */
    function wcp_sanitize_post_types( $input ) {

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $post_types_list_pluck = wp_list_pluck( $post_types, 'label', 'name' );

        if ( empty( $input ) ) {
            return '';
        }

        if ( is_array( $input ) ) {
            foreach ( $input as $post_type ) {
                if ( ! array_key_exists( $post_type, $post_types_list_pluck ) ) {
                    add_settings_error('wcp_post_types', 'wcp_post_types_error', esc_html__( 'The post type name must be one of the options provided in the lists.', 'wcp' ) );

                    return get_option( 'wcp_post_types' );
                }
            }
        }

        return $input;
    }

    /** 
     * Display All Posts HTML Select
     * 
     */
    function wcp_display_posts_html() {

        $selected_post_types = get_option('wcp_post_types', array());
        $selected_wcp_display_posts = get_option('wcp_display_posts', array());

        $all_post_ids = get_posts( array(
            'post_type' => $selected_post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ) );
        ?>
            <div class="wcp-select">
                <select name="wcp_display_posts[]" multiple="multiple" class="wcp-select2" data-placeholder="Select Posts">
                    <?php
                        if ( !empty( $selected_post_types ) && !empty( $all_post_ids ) && array_filter( $selected_post_types ) ) {  
                            foreach ( $selected_post_types as $post_type ) {
                                
                                echo '<optgroup label="' . get_post_type_object( $post_type )->label . '">';
                                
                                $args = array(
                                    'post_type' => $post_type,
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1
                                );
                                
                                $posts = get_posts( $args );
                                
                                foreach ( $posts as $post ) {
                                    if ( empty ( $selected_wcp_display_posts ) ) {
                                        echo '<option value="' . $post->ID . '">' . $post->post_title . '</option>';
                                    } else {
                                        echo '<option value="' . $post->ID . '" ' . selected(in_array( $post->ID, $selected_wcp_display_posts), true, false) . '>' . $post->post_title . '</option>';
                                    }
                                }
                                
                                echo '</optgroup>';
                            }
                        }
                    ?>
                </select> 

                <p class="description"><?php echo esc_html__( 'If you choose a specific item from the \'Display Specific\' option, it will not be displayed on all pages of the selected \'Post Type\'. If you don\'t make a selection, it will appear on all pages of the chosen \'Post Type\'.', 'wcp'); ?></p>
            </div>   
        <?php
    }

    /** 
     * Sanitize Specific Post Type HTML Select
     * 
     */
    function wcp_sanitize_display_posts( $input ) {

        $selected_post_types = get_option('wcp_post_types', array());

        $all_post_ids = get_posts( array(
            'post_type' => $selected_post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ) );
        
        if ( empty( $input ) && empty( $selected_post_types ) ) {
            return '';
        }

        if ( is_array( $input ) ) {
            foreach ( $input as $post_id ) {
                if ( ! in_array( $post_id, $all_post_ids ) ) {
                    add_settings_error('wcp_display_posts', 'wcp_display_posts_error', esc_html__( 'The post id must be one of the options provided in the lists.', 'wcp' ) );

                    return get_option( 'wcp_display_posts' );
                }
            }
        }

        return $input;
    }

    /** 
     * Display Location HTML Select
     * 
     */
    function wcp_location_html() {
        ?>
            <div class="wcp-select">
                <select name="wcp_location">
                    <option value="0" <?php selected( get_option( 'wcp_location' ), 0 ); ?>><?php echo esc_html__( 'Beginning of Post', 'wcp'); ?></option>
                    <option value="1" <?php selected( get_option( 'wcp_location' ), 1 ); ?>><?php echo esc_html__( 'End of Post', 'wcp'); ?></option>
                </select>    
            </div>
        <?php
    }

    /** 
     * Sanitize Display Location HTML Select
     * 
     */
    function wcp_sanitize_location( $input ) {

        if ( $input != '0' && $input != '1' ) {
            add_settings_error('wcp_location', 'wcp_location_error', esc_html__( 'Display location must be either beginning or end.', 'wcp' ) );

            return get_option( 'wcp_location' );
        }

        return $input;
    }

    /** 
     * Headline HTML Text Field
     * 
     */
    function wcp_headline_html() {
        ?>  
            <div class="wcp-input">
                <input type="text" name="wcp_headline" value="<?php echo esc_html__( get_option( 'wcp_headline' ), 'wcp' ); ?>">
            </div>
        <?php
    }

    /** 
     * Dynamic Checkbox Function for all Checkbox Field
     * 
     */
    function wcp_checkbox_html( $args ) {
        ?>
            <label class="wcp-checkbox" data-prefix="<?php echo esc_html__( 'Yes', 'wcp'); ?>" data-postfix="<?php echo esc_html__( 'No', 'wcp'); ?>"><input type="checkbox" name="<?php echo $args['fieldName']; ?>" <?php echo !empty( $args['fieldID'] ) && array_key_exists( 'fieldID', $args ) ? 'id="'.$args['fieldID'].'"' : ''; ?> value="1" <?php checked( get_option( $args['fieldName'] ), '1' ); ?>></label>
        <?php
    }

    /** 
     * Progress Bar Background HTML Field
     * 
     */
    function wcp_color_picker_html( $args ) {
        ?>  
            <div class="wcp-input">
                <input type="text" name="<?php echo $args['fieldName']; ?>" <?php echo !empty( $args['fieldID'] ) && array_key_exists( 'fieldID', $args ) ? 'id="'.$args['fieldID'].'"' : ''; ?> class="wcp_color_picker" value="<?php echo esc_html__( get_option( $args['fieldName'] ), 'wcp' ); ?>">
            </div>
        <?php
    }

    /** 
     * Progress Bar Thickness HTML Field
     * 
     */
    function wcp_progress_bar_thickness_html( $args ) {
        ?>  
            <div class="wcp-input">
                <input type="number" name="wcp_progress_bar_thickness" value="<?php echo esc_html__( get_option( 'wcp_progress_bar_thickness' ), 'wcp' ); ?>">
            </div>
        <?php
    }

    /** 
     * Progress Bar Display Location HTML Select
     * 
     */
    function wcp_progress_bar_location_html() {
        ?>
            <div class="wcp-select">
                <select name="wcp_progress_bar_location">
                    <option value="0" <?php selected( get_option( 'wcp_progress_bar_location' ), 0 ); ?>><?php echo esc_html__( 'Top', 'wcp'); ?></option>
                    <option value="1" <?php selected( get_option( 'wcp_progress_bar_location' ), 1 ); ?>><?php echo esc_html__( 'Bottom', 'wcp'); ?></option>
                    <option value="2" <?php selected( get_option( 'wcp_progress_bar_location' ), 2 ); ?>><?php echo esc_html__( 'Custom', 'wcp'); ?></option>
                </select>    
            </div>
        <?php
    }

    /** 
     * Sanitize Progress Bar Display Location HTML Select
     * 
     */
    function wcp_sanitize_progress_bar_location( $input ) {

        if ( $input != '0' && $input != '1' && $input != '2' ) {
            add_settings_error('wcp_progress_bar_location', 'wcp_progress_bar_location_error', esc_html__( 'Progress Bar Display Locations must be Top, Bottom or Custom.', 'wcp' ) );

            return get_option( 'wcp_progress_bar_location' );
        }

        return $input;
    }

    /** 
     * Progress Bar Locations Position HTML Select
     * 
     */
    function wcp_progress_bar_location_position_html() {
        ?>
            <div class="wcp-select">
                <select name="wcp_progress_bar_location_position">
                    <option value="0" <?php selected( get_option( 'wcp_progress_bar_location_position' ), 0 ); ?>><?php echo esc_html__( 'Top', 'wcp'); ?></option>
                    <option value="1" <?php selected( get_option( 'wcp_progress_bar_location_position' ), 1 ); ?>><?php echo esc_html__( 'Bottom', 'wcp'); ?></option>
                </select>    
            </div>
        <?php
    }

    /** 
     * Sanitize Progress Bar Locations Position HTML Select
     * 
     */
    function wcp_sanitize_progress_bar_location_position( $input ) {

        if ( $input != '0' && $input != '1' ) {
            add_settings_error('wcp_progress_bar_location_position', 'wcp_progress_bar_location_position_error', esc_html__( 'Progress Bar Locations Position must be Top or Bottom.', 'wcp' ) );

            return get_option( 'wcp_progress_bar_location_position' );
        }

        return $input;
    }

    /** 
     * Progress Bar Location Class HTML Field
     * 
     */
    function wcp_progress_bar_location_class_html( $args ) {
        ?>  
            <div class="wcp-input">
                <input type="text" name="wcp_progress_bar_location_class" value="<?php echo esc_html__( get_option( 'wcp_progress_bar_location_class' ), 'wcp' ); ?>">
                <p class="description"><?php echo sprintf( esc_html__( 'Save the class or ID of the desired location for the progress bar, using either a period (%s) followed by the class name or a hash (%s) symbol followed by the ID. The field for entering this information is located above.', 'wcp'), '<code>.ClassName</code>', '<code>#YourID</code>' ); ?></p>
            </div>
        <?php
    }
}

$WordCountPosts = new WordCountPosts();

function wcp_do_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url( 'options-general.php?page=word-count-posts-settings' ) ) );
    }
}
add_action( 'activated_plugin', 'wcp_do_activation_redirect' );
