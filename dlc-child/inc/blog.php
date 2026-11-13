<?php
/**
 * نمایش مقالات.
 *
 * - رفع «%آسترا%»: آسترا نشانه‌ی فنی %astra% را «ترجمه‌نشده» می‌خواهد؛ بسته‌ی فارسی آن را ترجمه کرده است.
 * - نام نویسنده‌ی مقالات و جعبه‌ی نویسنده در پایان هر مقاله.
 * - انتقال ۳۰۱ نشانی مقالات بازنشسته به مقاله‌ی جایگزین.
 *
 * @package dlc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DLC_AUTHOR_NAME = 'یونس دلفانی';
const DLC_AUTHOR_ROLE = 'مترجم و کارشناس زبان انگلیسی';

/**
 * نشانه‌ی %astra% نباید ترجمه شود؛ وگرنه آسترا خودِ ترجمه را چاپ می‌کند.
 */
function dlc_blog_keep_astra_token( $translation, $text, $context, $domain ) {
	return ( 'astra' === $domain && '%astra%' === $text ) ? $text : $translation;
}
add_filter( 'gettext_with_context', 'dlc_blog_keep_astra_token', 10, 4 );

/**
 * نام نویسنده‌ی مقالات در سایت (نه در پیشخوان).
 */
function dlc_blog_author_name( $name ) {
	if ( is_admin() || is_feed() ) {
		return $name;
	}
	return 'post' === get_post_type() ? DLC_AUTHOR_NAME : $name;
}
add_filter( 'the_author', 'dlc_blog_author_name' );

/**
 * جعبه‌ی نویسنده در پایان هر مقاله.
 */
function dlc_blog_author_box( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	return $content . sprintf(
		'<aside class="dlc-author"><span class="dlc-author__label">نویسنده</span><strong class="dlc-author__name">%s</strong><span class="dlc-author__role">%s</span></aside>',
		esc_html( DLC_AUTHOR_NAME ),
		esc_html( DLC_AUTHOR_ROLE )
	);
}
add_filter( 'the_content', 'dlc_blog_author_box', 20 );

/**
 * نشانی مقاله‌ی بازنشسته → ۳۰۱ به مقاله‌ی جایگزین.
 */
function dlc_blog_redirect_retired() {
	if ( ! is_404() || ! function_exists( 'dlc_setup_retired' ) ) {
		return;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$slug = basename( untrailingslashit( $path ) );
	if ( '' === $slug ) {
		return;
	}
	foreach ( dlc_setup_retired() as $info ) {
		if ( $slug !== $info[0] ) {
			continue;
		}
		$target = dlc_setup_find( $info[1] );
		if ( $target && 'publish' === get_post_status( $target ) ) {
			wp_safe_redirect( get_permalink( $target ), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'dlc_blog_redirect_retired' );
