<?php
function zaito_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array(
        'primary' => '主要メニュー',
    ) );
}
add_action( 'after_setup_theme', 'zaito_setup' );

function zaito_scripts() {
    wp_enqueue_style(
        'zaito-fonts',
        'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;800;900&display=swap',
        array(),
        null
    );
    wp_enqueue_style(
        'zaito-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'zaito_scripts' );

/**
 * WP Job Manager の求人一覧を取得するヘルパー。
 * プラグイン未導入時は空配列を返す。
 */
function zaito_get_featured_jobs( $limit = 3 ) {
    if ( ! post_type_exists( 'job_listing' ) ) {
        return array();
    }
    return get_posts( array(
        'post_type'      => 'job_listing',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
}
