<?php
/**
 * سئوی پایه: توضیح متا از «خلاصه»ی نوشته/برگه.
 *
 * اگر یکی از افزونه‌های سئو (Yoast، Rank Math، AIOSEO، SEOPress) فعال باشد،
 * این فایل هیچ خروجی‌ای نمی‌دهد تا تگ تکراری ساخته نشود.
 *
 * @package dlc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * آیا افزونه‌ی سئو فعال است؟
 */
function dlc_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * چاپ توضیح متا در <head>.
 */
function dlc_seo_meta_description() {
	if ( dlc_seo_plugin_active() ) {
		return;
	}

	$description = '';
	if ( is_singular() && has_excerpt() ) {
		$description = get_the_excerpt();
	} elseif ( is_front_page() || is_home() ) {
		$description = get_bloginfo( 'description' );
	}

	$description = trim( wp_strip_all_tags( (string) $description ) );
	if ( '' === $description ) {
		return;
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
}
add_action( 'wp_head', 'dlc_seo_meta_description', 2 );
