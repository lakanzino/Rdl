<?php
/**
 * راه‌اندازی اولیه‌ی سایت: برگه‌ها، مقالات، فهرست، صفحه‌ی نخست، رنگ‌ها و پاورقی.
 *
 * همه‌چیز فقط با زدن دکمه در «نمایش ← راه‌اندازی اولیه DLC» انجام می‌شود و
 * تکرارپذیر است: هر مورد یک‌بار ساخته می‌شود و بعد از آن دست نمی‌خورد،
 * پس ویرایش‌های شما در پیشخوان هرگز بازنویسی نمی‌شود.
 *
 * @package dlc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DLC_SETUP_PAGE = 'dlc-child-setup';
const DLC_SEED_META  = '_dlc_seed';
const DLC_SEED_REV   = '_dlc_seed_rev';

/**
 * نسخه‌ی محتوای اولیه. با بالا رفتن این عدد، راه‌اندازی یک‌بار به‌طور خودکار
 * در پیشخوان اجرا می‌شود (ساخت برگه‌های تازه و جایگزینی مواردی که rev بالاتر دارند).
 */
const DLC_SEED_VERSION = '2';

/**
 * پالت رنگ سایت به ترتیب ۹ خانه‌ی پالت سراسری آسترا.
 */
function dlc_setup_palette() {
	return array(
		'#1D4E89', // 0: رنگ اصلی (پیوندها و دکمه‌ها).
		'#163B68', // 1: حالت hover رنگ اصلی.
		'#14243A', // 2: تیترها.
		'#3F4A5A', // 3: متن.
		'#FFFFFF', // 4: زمینه.
		'#FAF6EE', // 5: زمینه‌ی دوم (کاغذی).
		'#14243A', // 6: زمینه‌ی تیره.
		'#E4DCCB', // 7: خط‌ها و حاشیه‌ها.
		'#111111', // 8: مشکی.
	);
}

/**
 * متن پاورقی.
 */
function dlc_setup_footer_text() {
	$links = array();
	foreach (
		array(
			'privacy' => 'حریم خصوصی',
			'terms'   => 'قوانین و مقررات',
			'faq'     => 'پرسش‌های متداول',
		) as $key => $label
	) {
		$id = dlc_setup_find( $key );
		if ( $id && 'publish' === get_post_status( $id ) ) {
			$links[] = '<a href="' . esc_url( (string) get_permalink( $id ) ) . '">' . $label . '</a>';
		}
	}

	// span های dir برای این است که ترتیب تکه‌های فارسی و انگلیسی در یک خط به هم نریزد.
	$text = '<span dir="rtl"><span dir="ltr">[copyright] [current_year] [site_title]</span>';
	if ( $links ) {
		$text .= ' | ' . implode( ' | ', $links );
	}
	return $text . '</span>';
}

/**
 * آیا متن فعلی پاورقی را خود این قالب نوشته (یا هنوز پیش‌فرض آستراست)؟
 * فقط در این صورت اجازه داریم به‌طور خودکار جایگزینش کنیم.
 *
 * @param string $text متن فعلی.
 */
function dlc_setup_footer_is_ours( $text ) {
	$text = trim( (string) $text );
	return '' === $text
		|| false !== strpos( $text, '[theme_author]' )
		|| false !== strpos( $text, 'Mohamadreza Lak' )
		|| 0 === strpos( $text, '<span dir="rtl"><span dir="ltr">[copyright]' );
}

/**
 * نوشتن متن پاورقی در تنظیمات آسترا.
 */
function dlc_setup_write_footer() {
	$settings = get_option( 'astra-settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	$settings['footer-copyright-editor'] = dlc_setup_footer_text();
	update_option( 'astra-settings', $settings );
}

/**
 * فهرست محتوای اولیه: نوع، عنوان، نامک، فایل محتوا، شماره‌ی بازنگری (rev).
 *
 * اگر rev یک مورد از rev ذخیره‌شده روی سایت بالاتر باشد، محتوای آن برگه با
 * نسخه‌ی تازه جایگزین می‌شود (نسخه‌ی قبلی در «رونوشت‌ها»ی برگه می‌ماند).
 * rev را فقط وقتی بالا ببرید که جایگزینی واقعاً خواسته شده باشد.
 */
function dlc_setup_items() {
	return array(
		'front'           => array( 'page', 'صفحه نخست', 'home', 'front', 2 ),
		'courses'         => array( 'page', 'دوره‌ها و برنامه‌ی آموزشی', 'courses', 'courses', 1 ),
		'blog'            => array( 'page', 'مقالات', 'blog', '', 1 ),
		'about'           => array( 'page', 'درباره ما', 'about', 'about', 2 ),
		'contact'         => array( 'page', 'تماس با ما', 'contact', 'contact', 2 ),
		'faq'             => array( 'page', 'پرسش‌های متداول', 'faq', 'faq', 1 ),
		'privacy'         => array( 'page', 'سیاست حفظ حریم خصوصی', 'privacy', 'privacy', 1 ),
		'terms'           => array( 'page', 'قوانین و مقررات', 'terms', 'terms', 1 ),
		'post-start'      => array( 'post', 'یادگیری زبان انگلیسی را از کجا شروع کنیم؟', 'how-to-start-learning-english', 'post-start', 1 ),
		'post-speaking'   => array( 'post', '۵ عادت روزانه برای تقویت مکالمه‌ی انگلیسی', 'daily-habits-for-english-speaking', 'post-speaking', 1 ),
		'post-vocabulary' => array( 'post', 'چطور لغت‌های انگلیسی را فراموش نکنیم؟', 'how-to-remember-english-vocabulary', 'post-vocabulary', 1 ),
		'post-levels'     => array( 'post', 'سطح‌های زبان انگلیسی از A1 تا C2 به زبان ساده', 'english-levels-cefr', 'post-levels', 1 ),
	);
}

/**
 * پیدا کردن موردی که قبلاً ساخته شده (حتی اگر در زباله‌دان باشد).
 *
 * @param string $key کلید مورد.
 * @return int شناسه یا صفر.
 */
function dlc_setup_find( $key ) {
	$ids = get_posts(
		array(
			'post_type'        => array( 'page', 'post' ),
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'meta_key'         => DLC_SEED_META, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts'      => 1,
			'fields'           => 'ids',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'suppress_filters' => true,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * خواندن فایل محتوا و جای‌گذاری نشانی برگه‌ها.
 *
 * @param string $file نام فایل بدون پسوند.
 * @param array  $ids  شناسه‌ی موارد ساخته‌شده.
 */
function dlc_setup_content( $file, $ids ) {
	if ( '' === $file ) {
		return '';
	}
	$path = DLC_CHILD_DIR . 'content/' . $file . '.html';
	if ( ! is_readable( $path ) ) {
		return '';
	}
	$html = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	foreach ( array( 'courses', 'contact', 'blog', 'about', 'faq', 'privacy', 'terms' ) as $key ) {
		if ( false === strpos( $html, '{{url:' . $key . '}}' ) ) {
			continue;
		}
		if ( empty( $ids[ $key ] ) ) {
			$ids[ $key ] = dlc_setup_find( $key );
		}
		$url  = ! empty( $ids[ $key ] ) ? get_permalink( $ids[ $key ] ) : home_url( '/' );
		$html = str_replace( '{{url:' . $key . '}}', esc_url( $url ), $html );
	}
	return trim( $html );
}

/**
 * مرحله‌ی ۱: ساخت برگه‌ها و مقالات.
 *
 * @param array $log گزارش.
 * @return array شناسه‌ها.
 */
function dlc_setup_create_content( &$log ) {
	$items   = dlc_setup_items();
	$ids     = array();
	$created = array();
	$replace = array();
	$author  = get_current_user_id();

	// گام اول: ساخت پوسته‌ی موارد تا نشانی‌شان مشخص شود.
	$n = 0;
	foreach ( $items as $key => $item ) {
		list( $type, $title, $slug ) = $item;
		$existing                    = dlc_setup_find( $key );
		if ( $existing ) {
			$ids[ $key ] = $existing;
			$stored_rev  = max( 1, (int) get_post_meta( $existing, DLC_SEED_REV, true ) );
			if ( $item[4] > $stored_rev && 'trash' !== get_post_status( $existing ) ) {
				$replace[ $key ] = $existing;
			} else {
				$log[] = 'از قبل وجود داشت و دست نخورد: ' . $title;
			}
			continue;
		}
		++$n;
		$id = wp_insert_post(
			wp_slash(
				array(
					'post_type'      => $type,
					'post_title'     => $title,
					'post_name'      => $slug,
					'post_status'    => 'publish',
					'post_author'    => $author,
					'post_content'   => '',
					'comment_status' => 'post' === $type ? get_default_comment_status( 'post' ) : 'closed',
					// ترتیب مقالات ثابت بماند.
					'post_date'      => wp_date( 'Y-m-d H:i:s', time() - $n * MINUTE_IN_SECONDS ),
				)
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			$log[] = 'خطا در ساخت «' . $title . '»: ' . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, DLC_SEED_META, $key );
		update_post_meta( $id, DLC_SEED_REV, $item[4] );
		$ids[ $key ]     = (int) $id;
		$created[ $key ] = (int) $id;
		$log[]           = 'ساخته شد: ' . $title;
	}

	// گام دوم: نوشتن محتوا فقط برای موارد تازه‌ساخت.
	foreach ( $created as $key => $id ) {
		$content = dlc_setup_content( $items[ $key ][3], $ids );
		if ( '' !== $content ) {
			wp_update_post(
				wp_slash(
					array(
						'ID'           => $id,
						'post_content' => $content,
					)
				)
			);
		}
	}

	// موارد قدیمی که نسخه‌ی تازه‌تری دارند: محتوا جایگزین می‌شود، نسخه‌ی قبلی در رونوشت‌ها می‌ماند.
	foreach ( $replace as $key => $id ) {
		$content = dlc_setup_content( $items[ $key ][3], $ids );
		if ( '' === $content ) {
			continue;
		}
		$result = wp_update_post(
			wp_slash(
				array(
					'ID'           => $id,
					'post_content' => $content,
				)
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			$log[] = 'خطا در به‌روزرسانی «' . $items[ $key ][1] . '»: ' . $result->get_error_message();
			continue;
		}
		update_post_meta( $id, DLC_SEED_REV, $items[ $key ][4] );
		$log[] = 'با نسخه‌ی تازه جایگزین شد: ' . $items[ $key ][1];
	}

	// برگه‌ی حریم خصوصیِ وردپرس: اگر برگه‌ی منتشرشده‌ای برایش تعیین نشده، برگه‌ی ما تعیین شود.
	if ( ! empty( $ids['privacy'] ) && 'publish' === get_post_status( $ids['privacy'] ) ) {
		$current = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( ! $current || 'publish' !== get_post_status( $current ) ) {
			update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
			$log[] = 'برگه‌ی «سیاست حفظ حریم خصوصی» در تنظیمات ← حریم خصوصی ثبت شد.';
		}
	}

	// پاورقی: اگر متن فعلی را خودمان نوشته‌ایم، با پیوند برگه‌های تازه به‌روز شود.
	$astra_settings = get_option( 'astra-settings', array() );
	$footer_now     = is_array( $astra_settings ) && isset( $astra_settings['footer-copyright-editor'] ) ? $astra_settings['footer-copyright-editor'] : '';
	if ( dlc_setup_footer_is_ours( $footer_now ) && dlc_setup_footer_text() !== $footer_now ) {
		dlc_setup_write_footer();
		$log[] = 'متن پاورقی به‌روز شد.';
	}

	// صفحه‌ی نخست: تمام‌عرض، بدون عنوان و بدون ستون کناری (تنظیمات برگه در آسترا).
	if ( isset( $created['front'] ) ) {
		update_post_meta( $created['front'], 'site-post-title', 'disabled' );
		update_post_meta( $created['front'], 'ast-site-content-layout', 'full-width-container' );
		update_post_meta( $created['front'], 'site-content-style', 'unboxed' );
		update_post_meta( $created['front'], 'site-sidebar-layout', 'no-sidebar' );
	}

	// دسته‌ی مقالات.
	$post_ids = array();
	foreach ( $created as $key => $id ) {
		if ( 'post' === $items[ $key ][0] ) {
			$post_ids[] = $id;
		}
	}
	if ( $post_ids ) {
		$term = term_exists( 'learning-english', 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( 'آموزش زبان انگلیسی', 'category', array( 'slug' => 'learning-english' ) );
		}
		if ( ! is_wp_error( $term ) && ! empty( $term['term_id'] ) ) {
			foreach ( $post_ids as $id ) {
				wp_set_post_categories( $id, array( (int) $term['term_id'] ) );
			}
		}
	}

	return $ids;
}

/**
 * مرحله‌ی ۲: صفحه‌ی نخست ثابت، برگه‌ی نوشته‌ها و فهرست اصلی.
 *
 * @param array $log گزارش.
 */
function dlc_setup_site_settings( &$log ) {
	$front = dlc_setup_find( 'front' );
	$blog  = dlc_setup_find( 'blog' );

	if ( $front && 'publish' === get_post_status( $front ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front );
		$log[] = 'برگه‌ی «صفحه نخست» به‌عنوان صفحه‌ی اول سایت تنظیم شد.';
		if ( $blog && 'publish' === get_post_status( $blog ) ) {
			update_option( 'page_for_posts', $blog );
			$log[] = 'برگه‌ی «مقالات» به‌عنوان برگه‌ی نوشته‌ها تنظیم شد.';
		}
	} else {
		$log[] = 'برگه‌ی «صفحه نخست» منتشرشده پیدا نشد؛ تنظیم صفحه‌ی اول انجام نشد.';
	}

	$menu_name = 'منوی اصلی DLC';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) ) {
			$log[] = 'خطا در ساخت فهرست: ' . $menu_id->get_error_message();
			return;
		}
		foreach ( array( 'front', 'courses', 'blog', 'about', 'contact' ) as $key ) {
			$id = dlc_setup_find( $key );
			if ( $id && 'publish' === get_post_status( $id ) ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-object-id' => $id,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
		}
		$log[] = 'فهرست «' . $menu_name . '» ساخته شد.';
	} else {
		$menu_id = (int) $menu->term_id;
		$log[]   = 'فهرست «' . $menu_name . '» از قبل وجود داشت و دست نخورد.';
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$registered = get_registered_nav_menus();
	foreach ( array( 'primary', 'mobile_menu' ) as $location ) {
		if ( isset( $registered[ $location ] ) && empty( $locations[ $location ] ) ) {
			$locations[ $location ] = (int) $menu_id;
			$log[]                  = 'فهرست در جایگاه «' . $registered[ $location ] . '» قرار گرفت.';
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * مرحله‌ی ۳: پالت رنگ و متن پاورقی در تنظیمات آسترا.
 *
 * @param array $log گزارش.
 */
function dlc_setup_design( &$log ) {
	$palette = dlc_setup_palette();

	$palettes = get_option( 'astra-color-palettes', false );
	if ( ! is_array( $palettes ) || empty( $palettes['palettes'] ) ) {
		$palettes = class_exists( 'Astra_Global_Palette' ) ? Astra_Global_Palette::get_default_color_palette() : array( 'palettes' => array() );
	}
	unset( $palettes['presets'] );
	$palettes['currentPalette']        = 'palette_1';
	$palettes['palettes']['palette_1'] = $palette;
	update_option( 'astra-color-palettes', $palettes );

	$settings = get_option( 'astra-settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	$settings['global-color-palette'] = array( 'palette' => $palette );
	update_option( 'astra-settings', $settings );
	dlc_setup_write_footer();

	$log[] = 'پالت رنگ DLC در «سفارشی‌سازی ← سراسری ← رنگ‌ها» (پالت ۱) ثبت و فعال شد.';
	$log[] = 'متن پاورقی ثبت شد.';
}

/**
 * پردازش فرم.
 */
function dlc_setup_handle() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'شما اجازه‌ی این کار را ندارید.' );
	}
	check_admin_referer( 'dlc_setup' );

	$log = array();

	$name = isset( $_POST['dlc_author_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dlc_author_name'] ) ) : '';
	if ( '' !== $name && wp_get_current_user()->display_name !== $name ) {
		$result = wp_update_user(
			array(
				'ID'           => get_current_user_id(),
				'display_name' => $name,
			)
		);
		$log[]  = is_wp_error( $result ) ? 'خطا در تغییر نام نمایشی: ' . $result->get_error_message() : 'نام نمایشی شما «' . $name . '» شد.';
	}

	if ( ! empty( $_POST['dlc_do_content'] ) ) {
		dlc_setup_create_content( $log );
	}
	if ( ! empty( $_POST['dlc_do_site'] ) ) {
		dlc_setup_site_settings( $log );
	}
	if ( ! empty( $_POST['dlc_do_design'] ) ) {
		dlc_setup_design( $log );
	}
	if ( ! $log ) {
		$log[] = 'هیچ گزینه‌ای انتخاب نشده بود.';
	}

	update_option( 'dlc_seed_version', DLC_SEED_VERSION );
	set_transient( 'dlc_setup_log_' . get_current_user_id(), $log, 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( admin_url( 'themes.php?page=' . DLC_SETUP_PAGE ) );
	exit;
}
add_action( 'admin_post_dlc_setup', 'dlc_setup_handle' );

/**
 * اجرای خودکار بعد از به‌روزرسانی قالب: اولین باری که مدیر سایت وارد پیشخوان شود،
 * برگه‌های تازه ساخته و موارد بازنگری‌شده جایگزین می‌شوند. فقط یک‌بار برای هر نسخه.
 */
function dlc_setup_auto_run() {
	if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) ) {
		return;
	}
	if ( get_option( 'dlc_seed_version' ) === DLC_SEED_VERSION || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	// قبل از شروع ثبت می‌شود تا درخواست‌های هم‌زمان دوباره اجرا نکنند.
	update_option( 'dlc_seed_version', DLC_SEED_VERSION );

	$log = array();
	dlc_setup_create_content( $log );

	// تنظیمات سایت و رنگ‌ها فقط اگر تا حالا هیچ‌وقت تنظیم نشده‌اند.
	if ( 'page' !== get_option( 'show_on_front' ) ) {
		dlc_setup_site_settings( $log );
	}
	if ( false === get_option( 'astra-color-palettes', false ) ) {
		dlc_setup_design( $log );
	}

	set_transient( 'dlc_setup_auto_log', $log, DAY_IN_SECONDS );
}
add_action( 'admin_init', 'dlc_setup_auto_run' );

/**
 * اعلان نتیجه‌ی اجرای خودکار (یک‌بار).
 */
function dlc_setup_auto_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$log = get_transient( 'dlc_setup_auto_log' );
	if ( ! is_array( $log ) || ! $log ) {
		return;
	}
	delete_transient( 'dlc_setup_auto_log' );
	echo '<div class="notice notice-success is-dismissible"><p><strong>راه‌اندازی خودکار قالب DLC انجام شد:</strong></p><ul style="list-style:disc;padding-inline-start:20px">';
	foreach ( $log as $line ) {
		echo '<li>' . esc_html( $line ) . '</li>';
	}
	echo '</ul></div>';
}
add_action( 'admin_notices', 'dlc_setup_auto_notice' );

/**
 * الگوی بلوکی «DLC — صفحه نخست» برای استفاده‌ی دوباره در ویرایشگر.
 */
function dlc_setup_register_pattern() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}
	// الگوها فقط در پیشخوان و درخواست‌های REST ویرایشگر لازم‌اند، نه در بازدید عادی سایت.
	$uri     = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$is_rest = false !== strpos( $uri, '/' . rest_get_url_prefix() . '/' ) || false !== strpos( $uri, 'rest_route=' );
	if ( ! is_admin() && ! $is_rest ) {
		return;
	}
	register_block_pattern_category( 'dlc', array( 'label' => 'DLC' ) );
	register_block_pattern(
		'dlc/front-page',
		array(
			'title'       => 'DLC — صفحه نخست',
			'description' => 'چیدمان کامل صفحه‌ی نخست آموزشگاه.',
			'categories'  => array( 'dlc' ),
			'content'     => dlc_setup_content( 'front', array() ),
		)
	);
}
add_action( 'init', 'dlc_setup_register_pattern' );

/**
 * صفحه‌ی پیشخوان: نمایش ← راه‌اندازی اولیه DLC.
 */
function dlc_setup_menu() {
	add_theme_page(
		'راه‌اندازی اولیه DLC',
		'راه‌اندازی اولیه DLC',
		'manage_options',
		DLC_SETUP_PAGE,
		'dlc_setup_render_page'
	);
}
add_action( 'admin_menu', 'dlc_setup_menu' );

/**
 * خروجی صفحه.
 */
function dlc_setup_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$log_key = 'dlc_setup_log_' . get_current_user_id();
	$log     = get_transient( $log_key );
	delete_transient( $log_key );

	$type_labels = array(
		'page' => 'برگه',
		'post' => 'مقاله',
	);
	?>
	<div class="wrap">
		<h1>راه‌اندازی اولیه DLC</h1>

		<?php if ( is_array( $log ) && $log ) : ?>
			<div class="notice notice-success">
				<ul style="list-style:disc;padding-inline-start:20px">
					<?php foreach ( $log as $line ) : ?>
						<li><?php echo esc_html( $line ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<p>هر مورد فقط یک‌بار ساخته می‌شود. اگر دوباره دکمه را بزنید، چیزی که قبلاً ساخته شده (و ویرایش‌های شما) دست نمی‌خورد. بعد از هر به‌روزرسانی قالب، برگه‌های تازه خودکار ساخته می‌شوند.</p>

		<table class="widefat striped" style="max-width:720px">
			<thead><tr><th>مورد</th><th>نوع</th><th>وضعیت</th></tr></thead>
			<tbody>
			<?php foreach ( dlc_setup_items() as $key => $item ) : ?>
				<?php $id = dlc_setup_find( $key ); ?>
				<tr>
					<td><?php echo esc_html( $item[1] ); ?></td>
					<td><?php echo esc_html( $type_labels[ $item[0] ] ); ?></td>
					<td>
						<?php if ( $id && 'trash' === get_post_status( $id ) ) : ?>
							در زباله‌دان
						<?php elseif ( $id ) : ?>
							ساخته شده —
							<a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>">ویرایش</a>
						<?php else : ?>
							هنوز ساخته نشده
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:720px;margin-top:20px">
			<input type="hidden" name="action" value="dlc_setup">
			<?php wp_nonce_field( 'dlc_setup' ); ?>

			<p><label><input type="checkbox" name="dlc_do_content" value="1" checked> ساخت برگه‌ها و مقالات اولیه (به نام شما به‌عنوان نویسنده)</label></p>
			<p><label><input type="checkbox" name="dlc_do_site" value="1" checked> تنظیم صفحه‌ی نخست، برگه‌ی مقالات و فهرست اصلی</label></p>
			<p><label><input type="checkbox" name="dlc_do_design" value="1" checked> ثبت پالت رنگ DLC و متن پاورقی در تنظیمات آسترا <strong>(رنگ‌های فعلی پالت ۱ و متن فعلی پاورقی جایگزین می‌شود)</strong></label></p>

			<p>
				<label for="dlc_author_name">نام نمایشی شما (نویسنده‌ی مقالات):</label><br>
				<input type="text" class="regular-text" dir="ltr" id="dlc_author_name" name="dlc_author_name" value="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>">
				<br><span class="description">برای نمایش «Younes Delfan» زیر مقالات، همین نام را اینجا بنویسید. خالی بگذارید تا تغییری نکند.</span>
			</p>

			<p><button type="submit" class="button button-primary button-hero">اجرای راه‌اندازی</button></p>
		</form>

		<h2>پالت رنگ DLC</h2>
		<p>
			<?php foreach ( array_merge( dlc_setup_palette(), array( '#C8452F' ) ) as $color ) : ?>
				<span style="display:inline-block;width:64px;margin:0 0 8px 8px;text-align:center;font-size:11px" dir="ltr">
					<span style="display:block;height:40px;border:1px solid #ccc;border-radius:6px;background:<?php echo esc_attr( $color ); ?>"></span>
					<?php echo esc_html( $color ); ?>
				</span>
			<?php endforeach; ?>
		</p>
		<p class="description">رنگ آخر (<span dir="ltr">#C8452F</span>) رنگ تأکیدی دکمه‌هاست و در فایل استایل قالب تعریف شده است.</p>
	</div>
	<?php
}
