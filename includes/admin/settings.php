<?php
add_settings_section( 'wcp_first_section', null, null, 'word-count-posts-settings' );
add_settings_section( 'wcp_second_section', null, null, 'word-count-posts-settings-layout' );

/** 
 * Post Type Field
 * 
 */
add_settings_field( 'wcp_post_types', esc_html__( 'Post Type', 'wcp' ), array( $this, 'wcp_post_types_html' ), 'word-count-posts-settings', 'wcp_first_section' );
register_setting( 'wordcountposts', 'wcp_post_types', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_post_types' ), 'default' => 'post' ) );

/** 
 * Display Specific Field
 * 
 */
add_settings_field( 'wcp_display_posts', esc_html__( 'Display Specific', 'wcp' ), array( $this, 'wcp_display_posts_html' ), 'word-count-posts-settings', 'wcp_first_section' );
register_setting( 'wordcountposts', 'wcp_display_posts', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_display_posts' ), 'default' => '' ) );

/** 
 * Display Locations Field
 * 
 */
add_settings_field( 'wcp_location', esc_html__( 'Display Locations', 'wcp' ), array( $this, 'wcp_location_html' ), 'word-count-posts-settings', 'wcp_first_section' );
register_setting( 'wordcountposts', 'wcp_location', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_location' ), 'default' => '0' ) );

/** 
 * Headline Field
 * 
 */
add_settings_field( 'wcp_headline', esc_html__( 'Headline Text', 'wcp' ), array( $this, 'wcp_headline_html' ), 'word-count-posts-settings', 'wcp_first_section' );
register_setting( 'wordcountposts', 'wcp_headline', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics' ) );

/** 
 * Word Count Field
 * 
 */
add_settings_field( 'wcp_wordcount', esc_html__( 'Word Count', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings', 'wcp_first_section', array( 'fieldName' => 'wcp_wordcount' ) );
register_setting( 'wordcountposts', 'wcp_wordcount', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );

/** 
 * Character Count Field
 * 
 */
add_settings_field( 'wcp_charactercount', esc_html__( 'Character Count', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings', 'wcp_first_section', array( 'fieldName' => 'wcp_charactercount' ) );
register_setting( 'wordcountposts', 'wcp_charactercount', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );

/** 
 * Read Time Field
 * 
 */
add_settings_field( 'wcp_readtime', esc_html__( 'Read Time', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings', 'wcp_first_section', array( 'fieldName' => 'wcp_readtime' ) );
register_setting( 'wordcountposts', 'wcp_readtime', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '1' ) );


/** 
 * Progress Bar Field
 * 
 */
add_settings_field( 'wcp_progress_bar', esc_html__( 'Progress Bar', 'wcp' ), array( $this, 'wcp_checkbox_html' ), 'word-count-posts-settings-layout', 'wcp_second_section', array( 'fieldName' => 'wcp_progress_bar', 'fieldID' => 'wcp_progress_bar' ) );
register_setting( 'wordcountposts', 'wcp_progress_bar', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '0' ) );

/** 
 * Color Picker Field
 * 
 */
add_settings_field( 'wcp_progress_bar_background', esc_html__( 'Progress Bar Background', 'wcp' ), array( $this, 'wcp_color_picker_html' ), 'word-count-posts-settings-layout', 'wcp_second_section',  array( 'fieldName' => 'wcp_progress_bar_background', 'fieldID' => 'wcp_progress_bar_background' ) );
register_setting( 'wordcountposts', 'wcp_progress_bar_background', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '#2271b1' ) );

/** 
 * Color Picker Field
 * 
 */
add_settings_field( 'wcp_progress_bar_foreground', esc_html__( 'Progress Bar Foreground', 'wcp' ), array( $this, 'wcp_color_picker_html' ), 'word-count-posts-settings-layout', 'wcp_second_section',  array( 'fieldName' => 'wcp_progress_bar_foreground', 'fieldID' => 'wcp_progress_bar_foreground' ) );
register_setting( 'wordcountposts', 'wcp_progress_bar_foreground', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '#DDDDDD' ) );

/** 
 * Progress Bar Locations Field
 * 
 */
add_settings_field( 'wcp_progress_bar_thickness', esc_html__( 'Progress Bar Thickness', 'wcp' ), array( $this, 'wcp_progress_bar_thickness_html' ), 'word-count-posts-settings-layout', 'wcp_second_section' );
register_setting( 'wordcountposts', 'wcp_progress_bar_thickness', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '2' ) );

/** 
 * Progress Bar Locations Field
 * 
 */
add_settings_field( 'wcp_progress_bar_location', esc_html__( 'Progress Bar Locations', 'wcp' ), array( $this, 'wcp_progress_bar_location_html' ), 'word-count-posts-settings-layout', 'wcp_second_section' );
register_setting( 'wordcountposts', 'wcp_progress_bar_location', array( 'sanitize_callback' => array( $this, 'wcp_sanitize_progress_bar_location' ), 'default' => '0' ) );

/** 
 * Progress Bar Locations Field
 * 
 */
add_settings_field( 'wcp_progress_bar_location_class', esc_html__( 'Progress Bar Locations Class', 'wcp' ), array( $this, 'wcp_progress_bar_location_class_html' ), 'word-count-posts-settings-layout', 'wcp_second_section' );
register_setting( 'wordcountposts', 'wcp_progress_bar_location_class', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );