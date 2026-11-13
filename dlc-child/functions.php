<?php
/**
 * DLC Language Center — قالب فرزند آسترا.
 *
 * @package dlc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DLC_CHILD_VERSION', wp_get_theme( get_stylesheet() )->get( 'Version' ) );
define( 'DLC_CHILD_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'DLC_CHILD_URI', trailingslashit( get_stylesheet_directory_uri() ) );

/**
 * بارگذاری استایل قالب فرزند بعد از استایل آسترا.
 */
function dlc_child_enqueue_styles() {
	wp_enqueue_style(
		'dlc-child-style',
		get_stylesheet_uri(),
		array( 'astra-theme-css' ),
		DLC_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'dlc_child_enqueue_styles', 15 );

/**
 * به‌روزرسانی قالب فرزند از گیت‌هاب (فقط در پیشخوان / کرون / WP-CLI).
 */
require_once DLC_CHILD_DIR . 'inc/updater.php';
