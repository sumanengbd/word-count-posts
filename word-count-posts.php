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

class WordCountPlugin {

    function __construct() {
        add_action( 'admin_menu', array($this, 'wcp_admin_page') );
        add_action( 'admin_init', array( $this, 'wpc_settings' ) );
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
    function wcp_admin_assets() {
        wp_enqueue_style( 'wcp-admin-style', plugins_url( 'assets/admin/admin.css', __FILE__ ), array(), '1.0.0', 'all' );
    }

    /** 
     * Load Frontend Assets
     * 
     **/
    function wcp_frontend_assets() {
        wp_enqueue_style( 'wcp-front-style', plugins_url( 'assets/front/front.css', __FILE__ ), array(), '1.0.0', 'all' );
    }

    /** 
     * Check Settings and Content Return
     * 
     **/
    function wcp_if_wraping( $content ) {

        if ( is_main_query() && is_single() && (get_option( 'wcp_wordcount', 1 ) || get_option( 'wpc_charactercount', 1 ) || get_option( 'wpc_readtime', 1 )) ) {
            return $this->wcp_create_html($content);
        }

        return $content;
    }

    /** 
     * Word Count New Content Return
     * 
     **/
    function wcp_create_html( $content ) {

        $html = '<div class="wcp_statistics">';
        
        if ( get_option( 'wpc_headline' ) ) {
            $html .= '<span class="wcp_title">' . esc_html( get_option( 'wpc_headline', 'Post Statistics' ), 'wcp' ) . '</span>';
        }

        $html .= '<ul>';
        // get word count one because both wordcount and read time will need it.
        if ( get_option( 'wpc_wordcount', '1' ) || get_option( 'wcp_readtime', '1' ) ) {
            $word_count = str_word_count( strip_tags( $content ) );
        }

        if ( get_option( 'wpc_wordcount' ) ) {
            $html .= '<li class="wpc_wordcount">'.__( 'This post has', 'wcp' ).' <strong>' . $word_count . '</strong> '.__( 'words', 'wcp' ).'</li>';
        }

        if ( get_option( 'wpc_charactercount' ) ) {
            $html .= '<li class="wpc_charactercount">'.__( 'This post has', 'wcp' ).' <strong>' . strlen( strip_tags( $content ) ) . '</strong> '.__( 'characters', 'wcp' ).'</li>';
        }

        if ( get_option( 'wpc_readtime' ) ) {
            $minutes = round( $word_count/225 );
            
            $html .= '<li class="wpc_readtime">'.__( 'This post will take about', 'wcp' ).' <strong>' . $minutes . '</strong> ' . ($minutes > 1 ? __( 'minutes', 'wcp' ) : __( 'minute', 'wcp' )) . ' '.__( 'to read', 'wcp' ).'.</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        if ( get_option( 'wpc_location', '0' ) == '0' ) {
            return $html . $content;
        }

        return $content . $html;
    }

    function wpc_settings() {
        add_settings_section( 'wcp_first_section', null, null, 'word-count-plugin-settings' );

        /** 
         * Post Type Field
         * 
         **/
        add_settings_field( 'wpc_post_types', __( 'Post Type', 'wpc' ), array( $this, 'wpc_post_types_html' ), 'word-count-plugin-settings', 'wcp_first_section' );
        register_setting( 'wordcountplugin', 'wpc_post_types', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '0' ) );

        /** 
         * Display Locations Field
         * 
         **/
        add_settings_field( 'wpc_location', __( 'Display Locations', 'wpc' ), array( $this, 'wpc_location_html' ), 'word-count-plugin-settings', 'wcp_first_section' );
        register_setting( 'wordcountplugin', 'wpc_location', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_location' ), 'default' => '0' ) );

        /** 
         * Headline Field
         * 
         **/
        add_settings_field( 'wpc_headline', __( 'Headline Text', 'wpc' ), array( $this, 'wpc_headline_html' ), 'word-count-plugin-settings', 'wcp_first_section' );
        register_setting( 'wordcountplugin', 'wpc_headline', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics' ) );
        
        /** 
         * Word Count Field
         * 
         **/
        add_settings_field( 'wpc_wordcount', __( 'Word Count', 'wpc' ), array( $this, 'wcp_checkbox_html' ), 'word-count-plugin-settings', 'wcp_first_section', array( 'fieldName' => 'wpc_wordcount' ) );
        register_setting( 'wordcountplugin', 'wpc_wordcount', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );
        
        /** 
         * Character Count Field
         * 
         **/
        add_settings_field( 'wpc_charactercount', __( 'Character Count', 'wpc' ), array( $this, 'wcp_checkbox_html' ), 'word-count-plugin-settings', 'wcp_first_section', array( 'fieldName' => 'wpc_charactercount' ) );
        register_setting( 'wordcountplugin', 'wpc_charactercount', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );
        
        /** 
         * Read Time Field
         * 
         **/
        add_settings_field( 'wpc_readtime', __( 'Read Time', 'wpc' ), array( $this, 'wcp_checkbox_html' ), 'word-count-plugin-settings', 'wcp_first_section', array( 'fieldName' => 'wpc_readtime' ) );
        register_setting( 'wordcountplugin', 'wpc_readtime', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );
    }

    /** 
     * Display Location HTML Select
     * 
     **/
    function wpc_post_types_html() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );

        ?>
            <select name="wpc_post_types">
                <?php
                    foreach ( $post_types as $post_type ) {

                        if ( $post_type->name === 'attachment' ) {
                            continue;
                        }

                        echo '<option value="' . $post_type->name . '" ' . selected( get_option( 'wcp_post_types' ), $post_type->name ) . '>' . $post_type->labels->singular_name . '</option>';
                    }
                ?>
            </select>    
        <?php
    }

    /** 
     * Display Location HTML Select
     * 
     **/
    function wpc_location_html() {
        ?>
            <select name="wpc_location">
                <option value="0" <?php selected( get_option( 'wpc_location' ), 0 ); ?>>Beginning of Post</option>
                <option value="1" <?php selected( get_option( 'wpc_location' ), 1 ); ?>>End of Post</option>
            </select>    
        <?php
    }

    /** 
     * Sanitize Display Location HTML Select
     * 
     **/
    function wcp_sanitize_location( $input ) {

        if ( $input != '0' && $input != '1' && $input != '2' && $input != '3' ) {
            add_settings_error('wpc_location', 'wcp_location_error', __( 'Display location must be either beginning or end.', 'wcp' ) );

            return get_option( 'wpc_location' );
        }

        return $input;
    }

    /** 
     * Headline HTML Text Field
     * 
     **/
    function wpc_headline_html() {
        ?>
            <input type="text" name="wpc_headline" value="<?php echo esc_attr( get_option( 'wpc_headline' ) ); ?>">
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
    function wpc_wordcount_html() {
        ?>
            <input type="checkbox" name="wpc_wordcount" value="1" <?php checked( get_option( 'wpc_location' ), '1' ); ?>>
        <?php
    }

    function wpc_charactercount_html() {
        ?>
            <input type="checkbox" name="wpc_charactercount" value="1" <?php checked( get_option( 'wpc_charactercount' ), '1' ); ?>>
        <?php
    }

    function wpc_readtime_html() {
        ?>
            <input type="checkbox" name="wpc_readtime" value="1" <?php checked( get_option( 'wpc_readtime' ), '1' ); ?>>
        <?php
    }

    */

    /** 
     * Register Word Count Setting Under Admin Setting Menu
     * 
     **/
    function wcp_admin_page() {
        add_options_page( __('Word Count Plugin Settings', 'wcp' ), __('Word Count', 'wcp'), 'manage_options', 'word-count-plugin-settings', array( $this, 'wcp_settings_html' ) );
    }

    /** 
     * Word Count Settings Form
     * 
     **/
    function wcp_settings_html() {
        ?>
        <div class="wrap wpc_wrap">
            <h1><?php echo __( 'Word Count Settings', 'wpc' ); ?></h1>

            <form action="options.php" method="POST">
                <?php
                    settings_fields( 'wordcountplugin' );

                    do_settings_sections( 'word-count-plugin-settings' );
                    
                    submit_button();
                ?>
            </form>
        </div> 
        <?php
    }
}

$wordCountPlugin = new WordCountPlugin();