<?php
/**
 * به‌روزرسانی قالب فرزند از مخزن گیت‌هاب.
 *
 * تنظیمات از wp-config.php خوانده می‌شود:
 *   define( 'DLC_GITHUB_TOKEN', 'github_pat_...' ); // برای مخزن خصوصی الزامی است.
 *   define( 'DLC_GITHUB_REPO', 'lakanzino/Rdl' );   // اختیاری
 *   define( 'DLC_GITHUB_BRANCH', 'main' );          // اختیاری
 *   define( 'DLC_GITHUB_PATH', 'dlc-child' );       // اختیاری: پوشه قالب داخل مخزن
 *
 * روش کار: نسخه‌ی style.css روی گیت‌هاب با نسخه‌ی نصب‌شده مقایسه می‌شود و در صورت
 * بالاتر بودن، به‌روزرسانی به سیستم استاندارد به‌روزرسانی قالب وردپرس معرفی می‌شود.
 *
 * @package dlc-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DLC_UPDATER_CACHE = 'dlc_child_remote_version';
const DLC_UPDATER_PAGE  = 'dlc-child-update';

/**
 * تنظیمات اتصال به گیت‌هاب.
 *
 * @return array{repo:string,branch:string,path:string,token:string}
 */
function dlc_updater_config() {
	return array(
		'repo'   => trim( defined( 'DLC_GITHUB_REPO' ) ? (string) DLC_GITHUB_REPO : 'lakanzino/Rdl', "/ \t" ),
		'branch' => trim( defined( 'DLC_GITHUB_BRANCH' ) ? (string) DLC_GITHUB_BRANCH : 'main' ),
		'path'   => trim( defined( 'DLC_GITHUB_PATH' ) ? (string) DLC_GITHUB_PATH : 'dlc-child', "/ \t" ),
		'token'  => trim( defined( 'DLC_GITHUB_TOKEN' ) ? (string) DLC_GITHUB_TOKEN : '' ),
	);
}

/**
 * آدرس پایه‌ی API مخزن.
 */
function dlc_updater_api_base() {
	$c = dlc_updater_config();
	return 'https://api.github.com/repos/' . $c['repo'] . '/';
}

/**
 * آدرس بسته‌ی zip شاخه.
 */
function dlc_updater_package_url() {
	$c = dlc_updater_config();
	return dlc_updater_api_base() . 'zipball/' . rawurlencode( $c['branch'] );
}

/**
 * نسخه‌ی نصب‌شده (مستقیم از style.css خوانده می‌شود).
 */
function dlc_updater_installed_version() {
	return (string) wp_get_theme( get_stylesheet() )->get( 'Version' );
}

/**
 * افزودن توکن به همه‌ی درخواست‌هایی که به API همین مخزن می‌روند
 * (هم بررسی نسخه و هم دانلود بسته توسط وردپرس).
 *
 * @param array  $args آرگومان‌های درخواست.
 * @param string $url  آدرس.
 * @return array
 */
function dlc_updater_http_args( $args, $url ) {
	if ( 0 !== strpos( (string) $url, dlc_updater_api_base() ) ) {
		return $args;
	}
	$c = dlc_updater_config();
	if ( ! isset( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
		$args['headers'] = array();
	}
	if ( '' !== $c['token'] ) {
		$args['headers']['Authorization'] = 'Bearer ' . $c['token'];
	}
	$args['headers']['X-GitHub-Api-Version'] = '2022-11-28';
	return $args;
}
add_filter( 'http_request_args', 'dlc_updater_http_args', 10, 2 );

/**
 * گرفتن نسخه‌ی موجود روی گیت‌هاب (با کش).
 *
 * @param bool $force       نادیده گرفتن کش.
 * @param bool $allow_fetch اگر کش خالی بود درخواست جدید بزند یا نه.
 * @return array{version:string,error:string,checked:int}|null
 */
function dlc_updater_remote( $force = false, $allow_fetch = true ) {
	if ( ! $force ) {
		$cached = get_site_transient( DLC_UPDATER_CACHE );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}
	if ( ! $allow_fetch ) {
		return null;
	}

	$c      = dlc_updater_config();
	$result = array(
		'version' => '',
		'error'   => '',
		'checked' => time(),
	);

	$url = dlc_updater_api_base() . 'contents/' . implode( '/', array_map( 'rawurlencode', explode( '/', $c['path'] . '/style.css' ) ) )
		. '?ref=' . rawurlencode( $c['branch'] );

	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 15,
			'headers' => array( 'Accept' => 'application/vnd.github.raw+json' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		$result['error'] = 'ارتباط با گیت‌هاب برقرار نشد: ' . $response->get_error_message();
	} else {
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );
		if ( 200 === $code ) {
			if ( preg_match( '/^[ \t\/*#@]*Version:(.*)$/mi', $body, $m ) && '' !== trim( $m[1] ) ) {
				$result['version'] = trim( $m[1] );
			} else {
				$result['error'] = 'فایل style.css روی گیت‌هاب پیدا شد اما سطر Version ندارد.';
			}
		} elseif ( 401 === $code ) {
			$result['error'] = 'توکن گیت‌هاب نامعتبر یا منقضی است (خطای 401).';
		} elseif ( 403 === $code || 429 === $code ) {
			$result['error'] = 'گیت‌هاب درخواست را رد کرد (خطای ' . $code . '): محدودیت تعداد درخواست یا نبودِ دسترسی توکن.';
		} elseif ( 404 === $code ) {
			$result['error'] = 'فایل ' . $c['path'] . '/style.css در شاخه‌ی ' . $c['branch'] . ' مخزن ' . $c['repo'] . ' پیدا نشد، یا توکن به مخزن دسترسی ندارد (خطای 404).';
		} else {
			$result['error'] = 'پاسخ غیرمنتظره از گیت‌هاب (کد ' . $code . ').';
		}
	}

	// خطاها کوتاه‌مدت کش می‌شوند تا پیشخوان کند نشود.
	set_site_transient( DLC_UPDATER_CACHE, $result, '' === $result['error'] ? 6 * HOUR_IN_SECONDS : 10 * MINUTE_IN_SECONDS );

	return $result;
}

/**
 * آیا نسخه‌ی جدیدتری روی گیت‌هاب هست؟
 *
 * @param array|null $remote خروجی dlc_updater_remote().
 */
function dlc_updater_has_update( $remote ) {
	return is_array( $remote )
		&& '' !== $remote['version']
		&& version_compare( $remote['version'], dlc_updater_installed_version(), '>' );
}

/**
 * معرفی به‌روزرسانی به وردپرس.
 *
 * @param mixed $transient مقدار update_themes.
 * @return mixed
 */
function dlc_updater_inject( $transient ) {
	if ( ! is_object( $transient ) ) {
		return $transient;
	}

	$allow_fetch = is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI );
	$remote      = dlc_updater_remote( false, $allow_fetch );
	if ( ! is_array( $remote ) || '' === $remote['version'] ) {
		return $transient;
	}

	$slug = get_stylesheet();
	$c    = dlc_updater_config();
	$item = array(
		'theme'        => $slug,
		'new_version'  => $remote['version'],
		'url'          => 'https://github.com/' . $c['repo'],
		'package'      => dlc_updater_package_url(),
		'requires'     => '',
		'requires_php' => '',
	);

	if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
		$transient->response = array();
	}
	if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
		$transient->no_update = array();
	}

	if ( dlc_updater_has_update( $remote ) ) {
		$transient->response[ $slug ] = $item;
		unset( $transient->no_update[ $slug ] );
	} else {
		$transient->no_update[ $slug ] = $item;
		unset( $transient->response[ $slug ] );
	}

	return $transient;
}
add_filter( 'site_transient_update_themes', 'dlc_updater_inject' );

/**
 * بسته‌ی گیت‌هاب کل مخزن است؛ فقط پوشه‌ی قالب فرزند را به وردپرس تحویل می‌دهیم.
 *
 * @param string|WP_Error $source        پوشه‌ی استخراج‌شده.
 * @param string          $remote_source پوشه‌ی کاری.
 * @param WP_Upgrader     $upgrader      نمونه‌ی upgrader.
 * @param array           $hook_extra    اطلاعات اضافه.
 * @return string|WP_Error
 */
function dlc_updater_source_selection( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;

	if ( is_wp_error( $source ) || empty( $hook_extra['theme'] ) || get_stylesheet() !== $hook_extra['theme'] ) {
		return $source;
	}

	$slug = get_stylesheet();
	$c    = dlc_updater_config();
	$from = untrailingslashit( $source ) . '/' . $c['path'];

	if ( ! $wp_filesystem || ! $wp_filesystem->exists( $from . '/style.css' ) || ! $wp_filesystem->exists( $from . '/functions.php' ) ) {
		return new WP_Error( 'dlc_bad_package', 'پوشه‌ی «' . $c['path'] . '» با فایل‌های style.css و functions.php در بسته‌ی دریافتی از گیت‌هاب پیدا نشد. به‌روزرسانی انجام نشد.' );
	}

	$to = untrailingslashit( $remote_source ) . '/' . $slug;
	if ( untrailingslashit( $source ) === $to ) {
		return $source;
	}
	if ( $wp_filesystem->exists( $to ) ) {
		$wp_filesystem->delete( $to, true );
	}
	if ( ! $wp_filesystem->move( $from, $to, true ) ) {
		return new WP_Error( 'dlc_move_failed', 'جابه‌جایی فایل‌های قالب در پوشه‌ی موقت ممکن نشد. به‌روزرسانی انجام نشد.' );
	}

	return trailingslashit( $to );
}
add_filter( 'upgrader_source_selection', 'dlc_updater_source_selection', 9, 4 );

/**
 * بعد از هر به‌روزرسانی قالب، کش نسخه پاک شود.
 *
 * @param WP_Upgrader $upgrader نمونه‌ی upgrader.
 * @param array       $extra    اطلاعات اضافه.
 */
function dlc_updater_after_upgrade( $upgrader, $extra ) {
	if ( isset( $extra['type'] ) && 'theme' === $extra['type'] ) {
		delete_site_transient( DLC_UPDATER_CACHE );
	}
}
add_action( 'upgrader_process_complete', 'dlc_updater_after_upgrade', 10, 2 );

/**
 * صفحه‌ی پیشخوان: نمایش ← به‌روزرسانی قالب DLC.
 */
function dlc_updater_menu() {
	add_theme_page(
		'به‌روزرسانی قالب DLC',
		'به‌روزرسانی قالب DLC',
		'update_themes',
		DLC_UPDATER_PAGE,
		'dlc_updater_render_page'
	);
}
add_action( 'admin_menu', 'dlc_updater_menu' );

/**
 * دکمه‌ی «بررسی نسخه‌ی جدید».
 */
function dlc_updater_handle_check() {
	if ( ! current_user_can( 'update_themes' ) ) {
		wp_die( 'شما اجازه‌ی این کار را ندارید.' );
	}
	check_admin_referer( 'dlc_check_update' );
	dlc_updater_remote( true );
	wp_safe_redirect( admin_url( 'themes.php?page=' . DLC_UPDATER_PAGE . '&checked=1' ) );
	exit;
}
add_action( 'admin_post_dlc_check_update', 'dlc_updater_handle_check' );

/**
 * خروجی صفحه.
 */
function dlc_updater_render_page() {
	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}

	$c         = dlc_updater_config();
	$slug      = get_stylesheet();
	$installed = dlc_updater_installed_version();
	$remote    = dlc_updater_remote();
	$has       = dlc_updater_has_update( $remote );

	$update_url = wp_nonce_url(
		self_admin_url( 'update.php?action=upgrade-theme&theme=' . rawurlencode( $slug ) ),
		'upgrade-theme_' . $slug
	);
	?>
	<div class="wrap">
		<h1>به‌روزرسانی قالب DLC</h1>

		<?php if ( isset( $_GET['checked'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-info is-dismissible"><p>بررسی انجام شد.</p></div>
		<?php endif; ?>

		<?php if ( '' !== $remote['error'] ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $remote['error'] ); ?></p></div>
		<?php elseif ( $has ) : ?>
			<div class="notice notice-warning"><p>نسخه‌ی جدید <strong dir="ltr"><?php echo esc_html( $remote['version'] ); ?></strong> آماده‌ی نصب است.</p></div>
		<?php else : ?>
			<div class="notice notice-success"><p>قالب به‌روز است.</p></div>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:720px;margin-top:16px">
			<tbody>
				<tr><th scope="row">نسخه‌ی نصب‌شده</th><td dir="ltr" style="text-align:right"><?php echo esc_html( $installed ); ?></td></tr>
				<tr><th scope="row">نسخه‌ی روی گیت‌هاب</th><td dir="ltr" style="text-align:right"><?php echo esc_html( '' !== $remote['version'] ? $remote['version'] : '—' ); ?></td></tr>
				<tr><th scope="row">آخرین بررسی</th><td><?php echo esc_html( wp_date( 'Y/m/d H:i', $remote['checked'] ) ); ?></td></tr>
				<tr><th scope="row">مخزن</th><td dir="ltr" style="text-align:right"><?php echo esc_html( $c['repo'] . ' @ ' . $c['branch'] . ' / ' . $c['path'] ); ?></td></tr>
				<tr><th scope="row">توکن (wp-config.php)</th><td><?php echo '' !== $c['token'] ? 'تنظیم شده ✔' : 'تنظیم نشده (فقط برای مخزن عمومی کار می‌کند)'; ?></td></tr>
			</tbody>
		</table>

		<p style="margin-top:20px">
			<?php if ( $has ) : ?>
				<a class="button button-primary button-hero" href="<?php echo esc_url( $update_url ); ?>">
					به‌روزرسانی به نسخه‌ی <span dir="ltr"><?php echo esc_html( $remote['version'] ); ?></span>
				</a>
			<?php endif; ?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dlc_check_update">
			<?php wp_nonce_field( 'dlc_check_update' ); ?>
			<button type="submit" class="button">بررسی نسخه‌ی جدید</button>
		</form>
	</div>
	<?php
}
