<?php
/**
 * Plugin Name: Loft Booking Sync
 * Description: Assessment and discovery tooling for Loft1325 booking synchronization and ButterflyMX categorization.
 * Version: 0.1.0
 * Author: Loft1325
 * Text Domain: loft-booking-sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Loft_Booking_Sync {

	const OPTION_GROUP         = 'loft_booking_sync_settings_group';
	const OPTION_NAME          = 'loft_booking_sync_settings';
	const OPTION_AUDIT_REPORT  = 'loft_booking_sync_audit_report';
	const OPTION_LAST_RESULTS  = 'loft_booking_sync_last_results';
	const TRANSIENT_CATEGORIZE = 'loft_categorization_cache';
	const NONCE_ACTION         = 'loft_booking_sync_nonce_action';
	const TABLE_SUFFIX         = 'lofts';

	/**
	 * Boot plugin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );

		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_loft_booking_sync_test_connection', array( __CLASS__, 'ajax_test_connection' ) );
		add_action( 'admin_post_loft_booking_sync_run_categorization', array( __CLASS__, 'handle_run_categorization' ) );
	}

	/**
	 * Activation lifecycle callback.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_lofts_table();
		self::run_setup_audit();
		self::run_loft_categorization( true );
	}

	/**
	 * Create or update wp_lofts table.
	 *
	 * @return void
	 */
	private static function create_lofts_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . self::TABLE_SUFFIX;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			loft_id varchar(191) NOT NULL,
			type enum('Resident','Rental') NOT NULL DEFAULT 'Rental',
			status enum('Free','Busy','Tentative') NOT NULL DEFAULT 'Free',
			butterfly_unit_id varchar(191) DEFAULT '',
			last_sync datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY loft_id (loft_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Determine if the current environment is staging.
	 *
	 * @return bool
	 */
	private static function is_staging() {
		return defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE;
	}

	/**
	 * Run plugin/integration discovery audit.
	 *
	 * @return array<string,mixed>
	 */
	private static function run_setup_audit() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$active_plugins  = (array) get_option( 'active_plugins', array() );
		$search_patterns = array(
			'butterflymx',
			'wp_remote_get',
			'api.butterflymx.com',
			'keychains',
			'tenants',
			'visitor passes',
			'/v4/tenants',
			'/v4/keychains',
		);
		$plugins_dir     = WP_PLUGIN_DIR;
		$findings        = array();

		foreach ( $active_plugins as $plugin_file ) {
			$base_path = trailingslashit( $plugins_dir ) . dirname( $plugin_file );
			if ( ! is_dir( $base_path ) ) {
				$base_path = trailingslashit( $plugins_dir ) . $plugin_file;
			}

			$iterator = array();
			if ( is_dir( $base_path ) ) {
				$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base_path ) );
			} elseif ( is_file( $base_path ) ) {
				$iterator = array( $base_path );
			}

			foreach ( $iterator as $file ) {
				$file_path = is_string( $file ) ? $file : $file->getPathname();
				if ( ! is_readable( $file_path ) || '.php' !== substr( $file_path, -4 ) ) {
					continue;
				}

				$content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( false === $content ) {
					continue;
				}

				foreach ( $search_patterns as $pattern ) {
					if ( false !== stripos( $content, $pattern ) ) {
						$findings[] = array(
							'plugin'  => $plugin_file,
							'file'    => str_replace( trailingslashit( ABSPATH ), '', $file_path ),
							'pattern' => $pattern,
						);
					}
				}
			}
		}

		global $wpdb;
		$tables_like_loft    = $wpdb->get_col( "SHOW TABLES LIKE '%loft%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
		$tables_like_booking = $wpdb->get_col( "SHOW TABLES LIKE '%booking%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared

		$suggestions = array();
		if ( ! in_array( $wpdb->prefix . self::TABLE_SUFFIX, $tables_like_loft, true ) ) {
			$suggestions[] = sprintf( 'Recommended new table: %s%s', $wpdb->prefix, self::TABLE_SUFFIX );
		}

		$report = array(
			'ran_at'                => current_time( 'mysql' ),
			'environment'           => defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : 'not-defined',
			'active_plugins'        => $active_plugins,
			'butterflymx_findings'  => $findings,
			'loft_tables'           => $tables_like_loft,
			'booking_tables'        => $tables_like_booking,
			'table_recommendations' => $suggestions,
		);

		update_option( self::OPTION_AUDIT_REPORT, $report, false );
		error_log( 'Loft Booking Sync audit completed.' );

		return $report;
	}

	/**
	 * Register plugin settings and fields.
	 *
	 * @return void
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'loft_booking_sync_main_section',
			__( 'ButterflyMX / PMS Credentials', 'loft-booking-sync' ),
			'__return_false',
			'loft-booking-sync'
		);

		$fields = array(
			'api_key'       => __( 'API Key / Client ID', 'loft-booking-sync' ),
			'client_secret' => __( 'Client Secret', 'loft-booking-sync' ),
			'base_url'      => __( 'Base URL', 'loft-booking-sync' ),
			'building_id'   => __( 'Building ID', 'loft-booking-sync' ),
			'pms_username'  => __( 'PMS Username', 'loft-booking-sync' ),
			'pms_password'  => __( 'PMS Password', 'loft-booking-sync' ),
		);

		foreach ( $fields as $key => $label ) {
			add_settings_field(
				$key,
				$label,
				array( __CLASS__, 'render_settings_field' ),
				'loft-booking-sync',
				'loft_booking_sync_main_section',
				array(
					'key' => $key,
				)
			);
		}
	}

	/**
	 * Sanitize and optionally encrypt sensitive values.
	 *
	 * @param array<string,mixed> $input Raw input values.
	 *
	 * @return array<string,string>
	 */
	public static function sanitize_settings( $input ) {
		$settings = self::get_settings();
		$clean    = array();
		$existing = array(
			'api_key'       => $settings['api_key'] ?? '',
			'client_secret' => $settings['client_secret'] ?? '',
			'pms_username'  => $settings['pms_username'] ?? '',
			'pms_password'  => $settings['pms_password'] ?? '',
		);

		$api_key_input = isset( $input['api_key'] ) ? sanitize_text_field( wp_unslash( $input['api_key'] ) ) : '';
		$secret_input  = isset( $input['client_secret'] ) ? sanitize_text_field( wp_unslash( $input['client_secret'] ) ) : '';
		$pms_user      = isset( $input['pms_username'] ) ? sanitize_text_field( wp_unslash( $input['pms_username'] ) ) : '';
		$pms_pass      = isset( $input['pms_password'] ) ? sanitize_text_field( wp_unslash( $input['pms_password'] ) ) : '';

		$clean['api_key']       = ( '' === $api_key_input || '********' === $api_key_input ) ? $existing['api_key'] : self::encrypt_value( $api_key_input );
		$clean['client_secret'] = ( '' === $secret_input || '********' === $secret_input ) ? $existing['client_secret'] : self::encrypt_value( $secret_input );
		$clean['base_url']      = isset( $input['base_url'] ) ? esc_url_raw( wp_unslash( $input['base_url'] ) ) : 'https://api.butterflymx.com/v4/';
		$clean['building_id']   = isset( $input['building_id'] ) ? sanitize_text_field( wp_unslash( $input['building_id'] ) ) : '';
		$clean['pms_username']  = ( '' === $pms_user || '********' === $pms_user ) ? $existing['pms_username'] : self::encrypt_value( $pms_user );
		$clean['pms_password']  = ( '' === $pms_pass || '********' === $pms_pass ) ? $existing['pms_password'] : self::encrypt_value( $pms_pass );

		return $clean;
	}

	/**
	 * Render settings form field.
	 *
	 * @param array<string,string> $args Field args.
	 *
	 * @return void
	 */
	public static function render_settings_field( $args ) {
		$key      = $args['key'];
		$settings = self::get_settings();
		$value    = $settings[ $key ] ?? '';
		$type     = in_array( $key, array( 'api_key', 'client_secret', 'pms_password' ), true ) ? 'password' : 'text';
		$display  = in_array( $key, array( 'api_key', 'client_secret', 'pms_username', 'pms_password' ), true ) && ! empty( $value ) ? '********' : $value;

		echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( self::OPTION_NAME . '[' . $key . ']' ) . '" value="' . esc_attr( $display ) . '" class="regular-text" autocomplete="off" />';
		if ( in_array( $key, array( 'api_key', 'client_secret', 'pms_username', 'pms_password' ), true ) ) {
			echo '<p class="description">' . esc_html__( 'Stored encrypted in wp_options (autoload disabled). Re-enter to update.', 'loft-booking-sync' ) . '</p>';
		}
	}

	/**
	 * Register admin menu entries.
	 *
	 * @return void
	 */
	public static function register_admin_menu() {
		add_menu_page(
			__( 'Loft Sync', 'loft-booking-sync' ),
			__( 'Loft Sync', 'loft-booking-sync' ),
			'manage_options',
			'loft-booking-sync',
			array( __CLASS__, 'render_settings_page' ),
			'dashicons-building',
			58
		);
	}

	/**
	 * Enqueue assets for settings page.
	 *
	 * @param string $hook Current hook.
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_loft-booking-sync' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Render settings/admin page.
	 *
	 * @return void
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$report       = get_option( self::OPTION_AUDIT_REPORT, array() );
		$last_results = get_option( self::OPTION_LAST_RESULTS, array() );
		$env_note     = self::is_staging() ? __( 'Staging safeguards are active.', 'loft-booking-sync' ) : __( 'Not in staging; discovery actions remain read-only.', 'loft-booking-sync' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Loft Booking Sync', 'loft-booking-sync' ); ?></h1>
			<?php settings_errors( 'loft-booking-sync' ); ?>
			<p><strong><?php echo esc_html( $env_note ); ?></strong></p>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( 'loft-booking-sync' );
				submit_button( __( 'Save Settings', 'loft-booking-sync' ) );
				?>
			</form>

			<hr />
			<h2><?php esc_html_e( 'Connectivity & Discovery', 'loft-booking-sync' ); ?></h2>
			<p>
				<button id="loft-booking-sync-test" class="button button-secondary"><?php esc_html_e( 'Test Connection', 'loft-booking-sync' ); ?></button>
				<span id="loft-booking-sync-result"></span>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="loft_booking_sync_run_categorization" />
				<?php wp_nonce_field( self::NONCE_ACTION, 'loft_booking_sync_nonce' ); ?>
				<?php submit_button( __( 'Run Loft Categorization', 'loft-booking-sync' ), 'secondary', 'submit', false ); ?>
			</form>

			<?php self::render_audit_report( $report ); ?>
			<?php self::render_categorization_results( $last_results ); ?>
		</div>
		<script>
		jQuery(function($){
			$('#loft-booking-sync-test').on('click', function(e){
				e.preventDefault();
				$('#loft-booking-sync-result').text('Testing...');
				$.post(ajaxurl, {
					action: 'loft_booking_sync_test_connection',
					nonce: '<?php echo esc_js( wp_create_nonce( self::NONCE_ACTION ) ); ?>'
				}, function(response){
					if (response.success) {
						$('#loft-booking-sync-result').text(response.data.message);
					} else {
						$('#loft-booking-sync-result').text(response.data.message || 'Connection failed.');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render discovery audit section.
	 *
	 * @param array<string,mixed> $report Audit report.
	 *
	 * @return void
	 */
	private static function render_audit_report( $report ) {
		if ( empty( $report ) ) {
			return;
		}
		?>
		<h2><?php esc_html_e( 'Activation Audit Report', 'loft-booking-sync' ); ?></h2>
		<p><?php echo esc_html( sprintf( 'Ran at: %s | Environment: %s', $report['ran_at'] ?? '-', $report['environment'] ?? '-' ) ); ?></p>
		<p><strong><?php esc_html_e( 'Table Recommendations:', 'loft-booking-sync' ); ?></strong></p>
		<ul>
			<?php foreach ( (array) ( $report['table_recommendations'] ?? array() ) as $note ) : ?>
				<li><?php echo esc_html( $note ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p><strong><?php esc_html_e( 'ButterflyMX Integration Findings:', 'loft-booking-sync' ); ?></strong></p>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Plugin', 'loft-booking-sync' ); ?></th><th><?php esc_html_e( 'File', 'loft-booking-sync' ); ?></th><th><?php esc_html_e( 'Pattern', 'loft-booking-sync' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( (array) ( $report['butterflymx_findings'] ?? array() ) as $finding ) : ?>
				<tr>
					<td><?php echo esc_html( $finding['plugin'] ); ?></td>
					<td><?php echo esc_html( $finding['file'] ); ?></td>
					<td><?php echo esc_html( $finding['pattern'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render categorization results.
	 *
	 * @param array<int,array<string,string>> $rows Result rows.
	 *
	 * @return void
	 */
	private static function render_categorization_results( $rows ) {
		if ( empty( $rows ) ) {
			return;
		}
		?>
		<h2><?php esc_html_e( 'Loft Categorization Results', 'loft-booking-sync' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Loft ID', 'loft-booking-sync' ); ?></th>
					<th><?php esc_html_e( 'Type', 'loft-booking-sync' ); ?></th>
					<th><?php esc_html_e( 'Status', 'loft-booking-sync' ); ?></th>
					<th><?php esc_html_e( 'Butterfly Unit ID', 'loft-booking-sync' ); ?></th>
					<th><?php esc_html_e( 'Last Sync', 'loft-booking-sync' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row['loft_id'] ); ?></td>
					<td><?php echo esc_html( $row['type'] ); ?></td>
					<td><?php echo esc_html( $row['status'] ); ?></td>
					<td><?php echo esc_html( $row['butterfly_unit_id'] ); ?></td>
					<td><?php echo esc_html( $row['last_sync'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * AJAX callback for test connection button.
	 *
	 * @return void
	 */
	public static function ajax_test_connection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'loft-booking-sync' ) ), 403 );
		}

		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$response = self::butterfly_request( 'buildings' );
		if ( is_wp_error( $response ) ) {
			error_log( 'Loft Booking Sync connection test failed: ' . $response->get_error_message() );
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			wp_send_json_success( array( 'message' => __( 'Connection successful.', 'loft-booking-sync' ) ) );
		}

		$body = wp_remote_retrieve_body( $response );
		error_log( 'Loft Booking Sync connection HTTP error: ' . $body );
		wp_send_json_error( array( 'message' => sprintf( __( 'Connection failed. HTTP %d', 'loft-booking-sync' ), $code ) ) );
	}

	/**
	 * Handle categorization action.
	 *
	 * @return void
	 */
	public static function handle_run_categorization() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'loft-booking-sync' ) );
		}

		check_admin_referer( self::NONCE_ACTION, 'loft_booking_sync_nonce' );

		self::run_loft_categorization( false, true );
		wp_safe_redirect( admin_url( 'admin.php?page=loft-booking-sync' ) );
		exit;
	}

	/**
	 * Execute initial loft categorization routine.
	 *
	 * @param bool $force_refresh Bypass transient cache.
	 * @param bool $manual_trigger Triggered by user action.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function run_loft_categorization( $force_refresh = false, $manual_trigger = false ) {
		global $wpdb;

		if ( ! $force_refresh ) {
			$cached = get_transient( self::TRANSIENT_CATEGORIZE );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$tenants_response = self::butterfly_request( 'tenants' );
		if ( is_wp_error( $tenants_response ) ) {
			error_log( 'Loft categorization failed to fetch tenants: ' . $tenants_response->get_error_message() );
			return array();
		}

		$keychains_response = self::butterfly_request( 'keychains' );
		if ( is_wp_error( $keychains_response ) ) {
			error_log( 'Loft categorization failed to fetch keychains: ' . $keychains_response->get_error_message() );
			return array();
		}

		$tenants_data  = json_decode( wp_remote_retrieve_body( $tenants_response ), true );
		$keychains     = json_decode( wp_remote_retrieve_body( $keychains_response ), true );
		$tenant_rows   = isset( $tenants_data['tenants'] ) && is_array( $tenants_data['tenants'] ) ? $tenants_data['tenants'] : array();
		$keychain_rows = isset( $keychains['keychains'] ) && is_array( $keychains['keychains'] ) ? $keychains['keychains'] : array();
		$now           = current_time( 'mysql' );
		$table_name    = $wpdb->prefix . self::TABLE_SUFFIX;
		$results       = array();

		foreach ( $tenant_rows as $tenant ) {
			$name         = isset( $tenant['name'] ) ? (string) $tenant['name'] : '';
			$tenant_id    = isset( $tenant['id'] ) ? (string) $tenant['id'] : '';
			$unit_id      = isset( $tenant['unit_id'] ) ? (string) $tenant['unit_id'] : '';
			$expires_at   = isset( $tenant['expires_at'] ) ? (string) $tenant['expires_at'] : '';
			$is_permanent = empty( $expires_at ) || ! empty( $tenant['is_permanent'] );
			$is_named_temp = (bool) preg_match( '/\d{4}-\d{2}-\d{2}|PLETHORA|GUEST|TEMP/i', $name );

			$has_temp_keychain = false;
			foreach ( $keychain_rows as $keychain ) {
				$matches_tenant = isset( $keychain['tenant_id'] ) && (string) $keychain['tenant_id'] === $tenant_id;
				$keychain_type  = isset( $keychain['type'] ) ? (string) $keychain['type'] : '';
				if ( $matches_tenant && in_array( $keychain_type, array( 'custom', 'recurring', 'one_time' ), true ) ) {
					$has_temp_keychain = true;
					break;
				}
			}

			$type   = ( $is_permanent && ! $is_named_temp && ! $has_temp_keychain ) ? 'Resident' : 'Rental';
			$status = self::derive_status_from_tenant( $tenant );

			if ( ! empty( $tenant_id ) ) {
				$wpdb->replace(
					$table_name,
					array(
						'loft_id'            => $tenant_id,
						'type'               => $type,
						'status'             => $status,
						'butterfly_unit_id'  => $unit_id,
						'last_sync'          => $now,
					),
					array( '%s', '%s', '%s', '%s', '%s' )
				);
			}

			$results[] = array(
				'loft_id'           => $tenant_id,
				'type'              => $type,
				'status'            => $status,
				'butterfly_unit_id' => $unit_id,
				'last_sync'         => $now,
			);
		}

		set_transient( self::TRANSIENT_CATEGORIZE, $results, HOUR_IN_SECONDS );
		update_option( self::OPTION_LAST_RESULTS, $results, false );

		if ( $manual_trigger ) {
			add_settings_error( 'loft-booking-sync', 'categorization-run', __( 'Loft categorization completed.', 'loft-booking-sync' ), 'updated' );
		}

		error_log( sprintf( 'Loft categorization completed with %d records.', count( $results ) ) );
		return $results;
	}

	/**
	 * Derive status from tenant data.
	 *
	 * @param array<string,mixed> $tenant Tenant row.
	 *
	 * @return string
	 */
	private static function derive_status_from_tenant( $tenant ) {
		if ( ! empty( $tenant['confirmed'] ) || ! empty( $tenant['active'] ) ) {
			return 'Busy';
		}
		if ( ! empty( $tenant['tentative'] ) || ! empty( $tenant['pending'] ) ) {
			return 'Tentative';
		}
		return 'Free';
	}

	/**
	 * Execute a ButterflyMX API GET request.
	 *
	 * @param string $endpoint Relative endpoint.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private static function butterfly_request( $endpoint ) {
		$settings = self::get_settings();
		$api_key  = self::decrypt_value( $settings['api_key'] ?? '' );
		$secret   = self::decrypt_value( $settings['client_secret'] ?? '' );
		$base_url = ! empty( $settings['base_url'] ) ? trailingslashit( $settings['base_url'] ) : 'https://api.butterflymx.com/v4/';

		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Missing API key/client ID.', 'loft-booking-sync' ) );
		}

		$headers = array(
			'Authorization' => 'Bearer ' . $api_key,
			'Accept'        => 'application/json',
		);

		if ( ! empty( $secret ) ) {
			$headers['X-Client-Secret'] = $secret;
		}

		$url = esc_url_raw( $base_url . ltrim( $endpoint, '/' ) );

		$args = array(
			'timeout' => 20,
			'headers' => $headers,
		);

		return wp_remote_get( $url, $args );
	}

	/**
	 * Read stored settings.
	 *
	 * @return array<string,string>
	 */
	private static function get_settings() {
		$settings = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $settings ) ) {
			return array();
		}
		return $settings;
	}

	/**
	 * Encrypt value before storing.
	 *
	 * @param string $value Value to encrypt.
	 *
	 * @return string
	 */
	private static function encrypt_value( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return $value;
		}

		$key    = hash( 'sha256', wp_salt( 'auth' ) );
		$iv     = substr( hash( 'sha256', AUTH_KEY ), 0, 16 );
		$packed = openssl_encrypt( $value, 'AES-256-CBC', $key, 0, $iv );

		return $packed ? 'enc:' . $packed : $value;
	}

	/**
	 * Decrypt stored value.
	 *
	 * @param string $value Stored value.
	 *
	 * @return string
	 */
	private static function decrypt_value( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( 0 !== strpos( $value, 'enc:' ) ) {
			return $value;
		}

		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return '';
		}

		$key = hash( 'sha256', wp_salt( 'auth' ) );
		$iv  = substr( hash( 'sha256', AUTH_KEY ), 0, 16 );

		$decrypted = openssl_decrypt( substr( $value, 4 ), 'AES-256-CBC', $key, 0, $iv );
		return $decrypted ? $decrypted : '';
	}
}

Loft_Booking_Sync::init();
