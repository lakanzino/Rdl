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
 * فونت‌ها و استایل بلوک‌ها: هم در سایت و هم داخل ویرایشگر.
 */
function dlc_child_enqueue_block_assets() {
	wp_enqueue_style(
		'dlc-blocks',
		DLC_CHILD_URI . 'assets/css/dlc.css',
		array(),
		DLC_CHILD_VERSION
	);
}
add_action( 'enqueue_block_assets', 'dlc_child_enqueue_block_assets' );

/**
 * پیش‌بارگذاری فونت اصلی فارسی.
 */
function dlc_child_preload_font() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( DLC_CHILD_URI . 'assets/fonts/Vazirmatn-wght.woff2' )
	);
}
add_action( 'wp_head', 'dlc_child_preload_font', 1 );

/**
 * استایل «تأکیدی» برای بلوک دکمه (قابل انتخاب در ویرایشگر).
 */
function dlc_child_register_block_styles() {
	register_block_style(
		'core/button',
		array(
			'name'  => 'dlc-accent',
			'label' => 'تأکیدی',
		)
	);
}
add_action( 'init', 'dlc_child_register_block_styles' );

/**
 * به‌روزرسانی قالب فرزند از گیت‌هاب (فقط در پیشخوان / کرون / WP-CLI).
 */
require_once DLC_CHILD_DIR . 'inc/updater.php';

/**
 * راه‌اندازی اولیه‌ی سایت (ساخت برگه‌ها، مقالات، فهرست و رنگ‌ها) — فقط در پیشخوان.
 */
if ( is_admin() ) {
	require_once DLC_CHILD_DIR . 'inc/setup.php';
}
