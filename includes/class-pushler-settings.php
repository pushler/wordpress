<?php
/**
 * Settings → Pushler: the public key, and whether the install is working.
 *
 * @package Pushler
 */

defined( 'ABSPATH' ) || exit;

class Pushler_Settings {

	const PAGE = 'pushler';

	public static function register(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_init', array( __CLASS__, 'privacy_policy' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_notices', array( __CLASS__, 'missing_key_notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( PUSHLER_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function register_setting(): void {
		register_setting(
			self::PAGE,
			PUSHLER_OPTION,
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);
	}

	/**
	 * @param mixed $value The submitted key.
	 */
	public static function sanitize( $value ): string {
		$key = pushler_sanitize_key( $value );

		if ( '' === $key && is_string( $value ) && '' !== trim( $value ) ) {
			add_settings_error(
				PUSHLER_OPTION,
				'pushler_invalid_key',
				__( 'That does not look like a Pushler public key. It starts with "pk_". Copy it from the Install page in Pushler.', 'pushler' )
			);

			return pushler_public_key();
		}

		return $key;
	}

	public static function add_page(): void {
		add_options_page(
			__( 'Pushler', 'pushler' ),
			__( 'Pushler', 'pushler' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * @param string[] $links The plugin's links on the Plugins page.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( self::url() ), esc_html__( 'Settings', 'pushler' ) )
		);

		return $links;
	}

	public static function url(): string {
		return admin_url( 'options-general.php?page=' . self::PAGE );
	}

	/** On the Plugins page only, until a key is saved. */
	public static function missing_key_notice(): void {
		$screen = get_current_screen();

		if ( '' !== pushler_public_key() || ! $screen || 'plugins' !== $screen->id || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Pushler is active, but not connected yet.', 'pushler' ),
			esc_url( self::url() ),
			esc_html__( 'Add your public key', 'pushler' )
		);
	}

	/** Text site owners can add to their privacy policy (Settings → Privacy). */
	public static function privacy_policy(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = '<p>' . esc_html__( 'This site uses Pushler to send web push notifications. If you allow notifications, your browser\'s push subscription, browser type, language and time zone are sent to Pushler. You can stop notifications at any time in your browser\'s settings.', 'pushler' ) . '</p>';

		wp_add_privacy_policy_content( __( 'Pushler', 'pushler' ), wp_kses_post( $content ) );
	}

	/**
	 * Asks this site for the worker file, the way a browser would.
	 *
	 * @return array{ok: bool|null, detail: string} ok is null when the site could not reach itself.
	 */
	private static function check_worker(): array {
		$response = wp_remote_get(
			pushler_worker_url(),
			array(
				'timeout'     => 5,
				'redirection' => 0,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'ok'     => null,
				'detail' => $response->get_error_message(),
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$type = (string) wp_remote_retrieve_header( $response, 'content-type' );
		$body = (string) wp_remote_retrieve_body( $response );

		if ( 200 === $code && false !== strpos( $type, 'javascript' ) && false !== strpos( $body, 'importScripts' ) ) {
			return array(
				'ok'     => true,
				'detail' => '',
			);
		}

		return array(
			'ok'     => false,
			/* translators: 1: HTTP status code, 2: content type. */
			'detail' => sprintf( __( 'The address answered with status %1$d and type "%2$s".', 'pushler' ), $code, $type ),
		);
	}

	private static function status_row( string $label, string $state, string $text ): void {
		$icons = array(
			'ok'      => 'dashicons-yes-alt',
			'warning' => 'dashicons-warning',
			'error'   => 'dashicons-dismiss',
		);
		$colors = array(
			'ok'      => '#00a32a',
			'warning' => '#dba617',
			'error'   => '#d63638',
		);

		printf(
			'<tr><th scope="row">%s</th><td><span class="dashicons %s" style="color:%s" aria-hidden="true"></span> %s</td></tr>',
			esc_html( $label ),
			esc_attr( $icons[ $state ] ),
			esc_attr( $colors[ $state ] ),
			wp_kses(
				$text,
				array(
					'a'    => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
					'code' => array(),
					'br'   => array(),
				)
			)
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$key        = pushler_public_key();
		$worker_url = pushler_worker_url();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Pushler', 'pushler' ); ?></h1>
			<p>
				<?php esc_html_e( 'Web push notifications for your Klaviyo flows and segments. This plugin adds the Pushler script to every page and serves the service worker from your domain, so there are no files to upload.', 'pushler' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="pushler-public-key"><?php esc_html_e( 'Public key', 'pushler' ); ?></label></th>
						<td>
							<input
								type="text"
								id="pushler-public-key"
								name="<?php echo esc_attr( PUSHLER_OPTION ); ?>"
								value="<?php echo esc_attr( $key ); ?>"
								class="regular-text code"
								placeholder="pk_..."
								autocomplete="off"
								spellcheck="false"
							/>
							<p class="description">
								<?php
								printf(
									/* translators: %s: link to the Pushler app. */
									esc_html__( 'In %s, open Settings → Install and copy the script. Paste the key, or the whole script, here.', 'pushler' ),
									'<a href="' . esc_url( pushler_app_url() ) . '" target="_blank" rel="noopener">Pushler</a>'
								);
								?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Status', 'pushler' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				if ( '' === $key ) {
					self::status_row( __( 'Script', 'pushler' ), 'warning', esc_html__( 'Not added yet. Save your public key first.', 'pushler' ) );
				} else {
					self::status_row( __( 'Script', 'pushler' ), 'ok', esc_html__( 'Added to every page of your site.', 'pushler' ) );
				}

				$check = self::check_worker();
				$link  = '<a href="' . esc_url( $worker_url ) . '" target="_blank" rel="noopener"><code>' . esc_html( $worker_url ) . '</code></a>';

				if ( true === $check['ok'] ) {
					/* translators: %s: the service worker's address. */
					self::status_row( __( 'Service worker', 'pushler' ), 'ok', sprintf( esc_html__( 'Served from %s.', 'pushler' ), $link ) );
				} elseif ( null === $check['ok'] ) {
					self::status_row(
						__( 'Service worker', 'pushler' ),
						'warning',
						/* translators: %s: the service worker's address. */
						sprintf( esc_html__( 'Your site could not check itself. Open %s: you should see one line that starts with importScripts.', 'pushler' ), $link )
					);
				} else {
					self::status_row(
						__( 'Service worker', 'pushler' ),
						'error',
						sprintf(
							/* translators: 1: the service worker's address, 2: what the address answered. */
							esc_html__( '%1$s is not reaching WordPress. %2$s Your web server may answer .js addresses itself. Ask your host to pass this address to WordPress, or upload the file from the Install page in Pushler to your site\'s root folder.', 'pushler' ),
							$link,
							esc_html( $check['detail'] )
						)
					);
				}

				if ( pushler_in_subfolder() ) {
					self::status_row(
						__( 'Site address', 'pushler' ),
						'warning',
						esc_html__( 'WordPress runs in a folder, not at the root of your domain. Pushler needs the service worker at the root of the domain, so push may not work on this site.', 'pushler' )
					);
				}
				?>
			</table>

			<?php if ( '' !== $key ) : ?>
				<p>
					<?php
					printf(
						/* translators: %s: link to the Pushler app. */
						esc_html__( 'Last step: in %s, open Settings → Install, check that the service worker path is /pushler-sw.js, and select "Check now".', 'pushler' ),
						'<a href="' . esc_url( pushler_app_url() ) . '" target="_blank" rel="noopener">Pushler</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
