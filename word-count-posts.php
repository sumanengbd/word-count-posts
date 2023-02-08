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
	 **/
	public $version = '1.0';

    /**
	 * The plugin construct function.
	 *
	 **/
    function __construct() {
        add_action( 'admin_menu', array($this, 'wcp_admin_page') );
        add_action( 'admin_init', array( $this, 'wcp_settings' ) );
        add_filter( 'the_content', array( $this, 'wcp_if_wraping' ) );
        add_action( 'init', array( $this, 'wcp_languages' ) );

        /** 
         * Load Admin CSS
         * 
         **/
        add_action( 'admin_enqueue_scripts', array( $this, 'wcp_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wcp_frontend_assets' ) );
    }

    function wcp_languages() {
        load_plugin_textdomain( 'wcp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /** 
     * Load Admin Assets
     * 
     **/
    function wcp_admin_assets( $hook ) {
        if ( 'settings_page_word-count-posts-settings' != $hook ) {
            return;
        }
        // CSS File
        wp_enqueue_style( 'wcp-admin-select2', plugins_url( 'assets/admin/css/select2.min.css', __FILE__ ), array(), $this->version, 'all' );
        wp_enqueue_style( 'wcp-admin-style', plugins_url( 'assets/admin/css/admin.css', __FILE__ ), array(), $this->version, 'all' );
        
        // JavaScripts File
        wp_enqueue_script( 'wcp-admin-select2', plugins_url( 'assets/admin/js/select2.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'wcp-admin-scripts', plugins_url( 'assets/admin/js/scripts.js', __FILE__ ), array( 'jquery' ), $this->version, true );
    }

    /** 
     * Load Frontend Assets
     * 
     **/
    function wcp_frontend_assets() {
        wp_enqueue_style( 'wcp-front-style', plugins_url( 'assets/front/css/front.css', __FILE__ ), array(), $this->version, 'all' );
    }

    /** 
     * Check Settings and Content Return
     * 
     **/
    function wcp_if_wraping( $content ) {
        $post_type = get_post_type();
        $wcp_post_types = get_option( 'wcp_post_types', array() );

        if ( is_main_query() && in_array( $post_type, $wcp_post_types ) && (get_option( 'wcp_wordcount', 1 ) || get_option( 'wcp_charactercount', 1 ) || get_option( 'wcp_readtime', 1 )) ) {
            return $this->wcp_create_html($content);
        }

        return $content;
    }

    /** 
     * Word Count New Content Return
     * 
     **/
    function wcp_create_html( $content ) {

        $post_type = get_post_type();

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

    function wcp_settings() {
        add_settings_section( 'wcp_first_section', null, null, 'word-count-posts-settings' );

        /** 
         * Post Type Field
         * 
         **/
        add_settings_field( 'wcp_post_types', esc_html__( 'Post Type', 'wcp' ), array( $this, 'wcp_post_types_html' ), 'word-count-posts-settings', 'wcp_first_section' );
        register_setting( 'wordcountposts', 'wcp_post_types', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_post_types' ), 'default' => 'post' ) );

        /** 
         * Display Specific Field
         * 
         **/
        add_settings_field( 'wcp_display_posts', esc_html__( 'Display Specific', 'wcp' ), array( $this, 'wcp_display_posts_html' ), 'word-count-posts-settings', 'wcp_first_section' );
        register_setting( 'wordcountposts', 'wcp_display_posts', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_display_posts' ), 'default' => '' ) );

        /** 
         * Display Locations Field
         * 
         **/
        add_settings_field( 'wcp_location', esc_html__( 'Display Locations', 'wcp' ), array( $this, 'wcp_location_html' ), 'word-count-posts-settings', 'wcp_first_section' );
        register_setting( 'wordcountposts', 'wcp_location', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_location' ), 'default' => '0' ) );

        /** 
         * Headline Field
         * 
         **/
        add_settings_field( 'wcp_headline', esc_html__( 'Headline Text', 'wcp' ), array( $this, 'wcp_headline_html' ), 'word-count-posts-settings', 'wcp_first_section' );
        register_setting( 'wordcountposts', 'wcp_headline', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics' ) );
        
        /** 
         * Word Count Field
         * 
         **/
        add_settings_field( 'wcp_wordcount', esc_html__( 'Word Count', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings', 'wcp_first_section', array( 'fieldName' => 'wcp_wordcount' ) );
        register_setting( 'wordcountposts', 'wcp_wordcount', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );
        
        /** 
         * Character Count Field
         * 
         **/
        add_settings_field( 'wcp_charactercount', esc_html__( 'Character Count', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings', 'wcp_first_section', array( 'fieldName' => 'wcp_charactercount' ) );
        register_setting( 'wordcountposts', 'wcp_charactercount', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );
        
        /** 
         * Read Time Field
         * 
         **/
        add_settings_field( 'wcp_readtime', esc_html__( 'Read Time', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings', 'wcp_first_section', array( 'fieldName' => 'wcp_readtime' ) );
        register_setting( 'wordcountposts', 'wcp_readtime', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );
    }

    /** 
     * Display Post Type HTML Select
     * 
     **/
    function wcp_post_types_html() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $selected_post_types = get_option('wcp_post_types', array());

        ?>
            <select name="wcp_post_types[]" multiple="multiple" class="wcp-select2" data-placeholder="Select Post Types">
                <?php
                    foreach ( $post_types as $post_type ) {

                        if ( $post_type->name === 'attachment' ) {
                            continue;
                        }

                        echo '<option value="' . $post_type->name . '" ' . selected(in_array($post_type->name, $selected_post_types), true, false) . '>' . $post_type->labels->singular_name . '</option>';
                    }
                ?>
            </select>    
        <?php
    }

    /** 
     * Sanitize Post Type HTML Select
     * 
     **/
    function wcp_sanitize_post_types( $input ) {

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $post_types_list_pluck = wp_list_pluck( $post_types, 'label', 'name' );

        if ( ! $input ) {
            return get_option( 'wcp_post_types' );
        }

        if ( is_array( $input ) ) {
            foreach ( $input as $post_type ) {
                if ( ! array_key_exists( $post_type, $post_types_list_pluck ) ) {
                    add_settings_error('wcp_post_types', 'wcp_post_types_error', esc_html__( 'The post type name must be one of the options provided in the lists.', 'wcp' ) );
    
                    return get_option( 'wcp_post_types' );
                }
            }
        } elseif ( ! array_key_exists( $input, $post_types_list_pluck ) ) {
            add_settings_error('wcp_post_types', 'wcp_post_types_error', esc_html__( 'The post type name must be one of the options provided in the lists.', 'wcp' ) );
    
            return get_option( 'wcp_post_types' );
        }
    
        return $input;
    }

    /** 
     * Display All Posts HTML Select
     * 
     **/
    function wcp_display_posts_html() {

        $selected_post_types = get_option('wcp_post_types', array());
        $selected_wcp_display_posts = get_option('wcp_display_posts', array());

        $all_post_ids = get_posts( array(
            'post_type' => $selected_post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ) );

        echo "<pre>";
        var_dump( $selected_post_types );
        var_dump( $all_post_ids );
        var_dump( $selected_wcp_display_posts );
        // var_dump( selected(in_array( "0", $selected_wcp_display_posts ), true, false) );
        echo "</pre>";
        ?>
            <select name="wcp_display_posts[]" multiple="multiple" class="wcp-select2" data-placeholder="Select Posts">
                <?php
                    if ( !empty( $selected_post_types ) && array_filter( $selected_post_types ) ) {  
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
        <?php
    }

    /** 
     * Sanitize Specific Post Type HTML Select
     * 
     **/
    function wcp_sanitize_display_posts( $input ) {

        $selected_post_types = get_option('wcp_post_types', array());

        $all_post_ids = get_posts( array(
            'post_type' => $selected_post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ) );
        var_dump( $input );

        if ( ! $input ) {
            return get_option( 'wcp_display_posts' );
        }


        if ( is_array( $input ) ) {
            foreach ( $input as $post ) {
                if ( ! array_key_exists( $post, $all_post_ids ) ) {
                    add_settings_error('wcp_display_posts', 'wcp_display_posts_error', esc_html__( 'The post id must be one of the options provided in the lists.', 'wcp' ) );
    
                    return get_option( 'wcp_display_posts' );
                }
            }
        } elseif ( ! array_key_exists( $input, $all_post_ids ) ) {
            add_settings_error('wcp_display_posts', 'wcp_display_posts_error', esc_html__( 'The post id must be one of the options provided in the listsss.', 'wcp' ) );
    
            return get_option( 'wcp_display_posts' );
        }
    
        return $input;
    }

    /** 
     * Display Location HTML Select
     * 
     **/
    function wcp_location_html() {
        ?>
            <select name="wcp_location">
                <option value="0" <?php selected( get_option( 'wcp_location' ), 0 ); ?>>Beginning of Post</option>
                <option value="1" <?php selected( get_option( 'wcp_location' ), 1 ); ?>>End of Post</option>
            </select>    
        <?php
    }

    /** 
     * Sanitize Display Location HTML Select
     * 
     **/
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
     **/
    function wcp_headline_html() {
        ?>
            <input type="text" name="wcp_headline" value="<?php echo esc_attr( get_option( 'wcp_headline' ) ); ?>">
        <?php
    }

    /** 
     * Dynamic Checkbox Function for all Checkbox Field
     * 
     **/
    function wcp_checkbox_html( $args ) {
        ?>
            <input type="checkbox" name="<?php echo $args['fieldName']; ?>" value="1" <?php checked( get_option( $args['fieldName'] ), '1' ); ?>>
        <?php
    }

    /*
    function wcp_wordcount_html() {
        ?>
            <input type="checkbox" name="wcp_wordcount" value="1" <?php checked( get_option( 'wcp_location' ), '1' ); ?>>
        <?php
    }

    function wcp_charactercount_html() {
        ?>
            <input type="checkbox" name="wcp_charactercount" value="1" <?php checked( get_option( 'wcp_charactercount' ), '1' ); ?>>
        <?php
    }

    function wcp_readtime_html() {
        ?>
            <input type="checkbox" name="wcp_readtime" value="1" <?php checked( get_option( 'wcp_readtime' ), '1' ); ?>>
        <?php
    }

    */

    /** 
     * Register Word Count Setting Under Admin Setting Menu
     * 
     **/
    function wcp_admin_page() {
        add_options_page( esc_html__('Word Count Plugin Settings', 'wcp' ), esc_html__('Word Count', 'wcp'), 'manage_options', 'word-count-posts-settings', array( $this, 'wcp_settings_html' ) );
    }

    /** 
     * Word Count Settings Form
     * 
     **/
    function wcp_settings_html() {
        ?>
        <div class="wrap">
            <h1><?php echo __( 'Word Count Posts Settings', 'wcp' ); ?></h1>

            <form action="options.php" method="POST">
                <?php
                    settings_fields( 'wordcountposts' );

                    do_settings_sections( 'word-count-posts-settings' );
                    
                    submit_button();
                ?>
            </form>
        </div> 
        <?php
    }
}

$WordCountPosts = new WordCountPosts();