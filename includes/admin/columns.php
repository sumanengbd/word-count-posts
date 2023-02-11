<?php
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