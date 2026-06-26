<?php
/**
 * UAEL Cloudflare Turnstile Integration
 *
 * @package UAEL
 * @since 1.42.2
 */

namespace UltimateElementor\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class UAEL_Turnstile
 *
 * @since 1.42.2
 */
class UAEL_Turnstile {

	/**
	 * Instance
	 *
	 * @since 1.42.2
	 * @var UAEL_Turnstile
	 */
	private static $instance;

	/**
	 * Get Instance
	 *
	 * @since 1.42.2
	 * @return UAEL_Turnstile
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.42.2
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize Turnstile integration
	 *
	 * @since 1.42.2
	 */
	public function init() {
		
		// Hook into login validation.
		add_action( 'uael_login_validation', array( $this, 'validate_login_turnstile' ), 5, 1 );
		
		// Hook into registration validation.
		add_action( 'uael_registration_validation', array( $this, 'validate_registration_turnstile' ), 5, 1 );
		
		// Enqueue scripts when needed.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_turnstile_scripts' ) );
		
		// Hook into UAE AJAX submissions to inject Turnstile token.
		add_action( 'wp_ajax_uael_login_form_submit', array( $this, 'inject_turnstile_token' ), 1 );
		add_action( 'wp_ajax_nopriv_uael_login_form_submit', array( $this, 'inject_turnstile_token' ), 1 );
		add_action( 'wp_ajax_uael_register_form_submit', array( $this, 'inject_turnstile_token' ), 1 );
		add_action( 'wp_ajax_nopriv_uael_register_form_submit', array( $this, 'inject_turnstile_token' ), 1 );
	}

	/**
	 * Check if Turnstile is enabled for login forms.
	 * Turnstile is enabled if site key and secret key are configured.
	 * Individual forms can control whether to use it via widget settings.
	 *
	 * @since 1.42.2
	 * @return bool
	 */
	public function is_login_turnstile_enabled() {
		$settings = $this->get_turnstile_settings();
		return ! empty( $settings['site_key'] ) && ! empty( $settings['secret_key'] );
	}

	/**
	 * Check if Turnstile is enabled for registration forms.
	 * Turnstile is enabled if site key and secret key are configured.
	 * Individual forms can control whether to use it via widget settings.
	 *
	 * @since 1.42.2
	 * @return bool
	 */
	public function is_registration_turnstile_enabled() {
		$settings = $this->get_turnstile_settings();
		return ! empty( $settings['site_key'] ) && ! empty( $settings['secret_key'] );
	}

	/**
	 * Get Turnstile settings
	 *
	 * @since 1.42.2
	 * @return array
	 */
	public function get_turnstile_settings() {
		$integration_settings = \UltimateElementor\Classes\UAEL_Helper::get_admin_settings_option( '_uael_integration', array(), true );
		
		$default_settings = array(
			'enable_login'        => false,
			'enable_registration' => false,
			'site_key'            => '',
			'secret_key'          => '',
			'theme'               => 'auto',
			'size'                => 'normal',
		);

		// Map from integration settings to our format.
		$turnstile_settings = array(
			'enable_login'        => false, // Will be controlled per form widget.
			'enable_registration' => false, // Will be controlled per form widget.
			'site_key'            => isset( $integration_settings['cloudflare_turnstile_site_key'] ) ? $integration_settings['cloudflare_turnstile_site_key'] : '',
			'secret_key'          => isset( $integration_settings['cloudflare_turnstile_secret_key'] ) ? $integration_settings['cloudflare_turnstile_secret_key'] : '',
			'theme'               => 'auto',
			'size'                => 'normal',
		);

		return wp_parse_args( $turnstile_settings, $default_settings );
	}


	/**
	 * Validate Turnstile for login forms
	 *
	 * @since 1.42.2
	 * @param array $credentials Login credentials (unused).
	 */
	public function validate_login_turnstile( $credentials ) {
		// Prevent unused parameter warning.
		unset( $credentials );
		
		if ( ! $this->is_login_turnstile_enabled() ) {
			return;
		}

		$this->validate_turnstile_response( 'login' );
	}

	/**
	 * Validate Turnstile for registration forms
	 *
	 * @since 1.42.2
	 * @param array $user_data Registration data (unused).
	 */
	public function validate_registration_turnstile( $user_data ) {
		// Prevent unused parameter warning.
		unset( $user_data );
		
		if ( ! $this->is_registration_turnstile_enabled() ) {
			return;
		}

		$this->validate_turnstile_response( 'registration' );
	}

	/**
	 * Validate Turnstile response
	 *
	 * @since 1.42.2
	 * @param string $form_type Type of form (login/registration).
	 */
	private function validate_turnstile_response( $form_type ) {
		// Prevent unused parameter warning.
		unset( $form_type );
		$settings = $this->get_turnstile_settings();
		
		$turnstile_response = '';
		
		// Try to get turnstile response from different possible locations.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handled by form plugin.
		if ( isset( $_POST['data']['cf-turnstile-response'] ) && ! empty( $_POST['data']['cf-turnstile-response'] ) ) {
			// UAE forms nest field data within a data array structure.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by form plugin.
			$turnstile_response = sanitize_text_field( wp_unslash( $_POST['data']['cf-turnstile-response'] ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by form plugin.
		} elseif ( isset( $_POST['cf-turnstile-response'] ) && ! empty( $_POST['cf-turnstile-response'] ) ) {
			// Direct POST structure: $_POST['field_name'].
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled by form plugin.
			$turnstile_response = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) );
		} else {
			// Check if it's in the parsed data array (UAE sometimes structures it differently).
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handled by form plugin.
			if ( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handled by form plugin.
				foreach ( $_POST['data'] as $key => $value ) {
					if ( strpos( $key, 'cf-turnstile-response' ) !== false && ! empty( $value ) ) {
						$turnstile_response = sanitize_text_field( wp_unslash( $value ) );
						break;
					}
				}
			}
			
			if ( empty( $turnstile_response ) ) {
				wp_die( 
					esc_html__( 'Please complete the security verification.', 'uael' ), 
					esc_html__( 'Security Verification Required', 'uael' ), 
					array( 'back_link' => true ) 
				);
			}
		}
		$remote_ip = $this->get_client_ip();

		$validation_result = $this->verify_turnstile_token( 
			$settings['secret_key'], 
			$turnstile_response, 
			$remote_ip 
		);

		if ( ! $validation_result['success'] ) {
			$error_message = isset( $validation_result['error'] ) ? $validation_result['error'] : __( 'Security verification failed. Please try again.', 'uael' );
			
			wp_die( 
				esc_html( $error_message ), 
				esc_html__( 'Security Verification Failed', 'uael' ), 
				array( 'back_link' => true ) 
			);
		}
	}

	/**
	 * Verify Turnstile token with Cloudflare API
	 *
	 * @since 1.42.2
	 * @param string $secret_key Secret key.
	 * @param string $response Turnstile response.
	 * @param string $remote_ip Remote IP.
	 * @return array
	 */
	private function verify_turnstile_token( $secret_key, $response, $remote_ip ) {
		if ( empty( $secret_key ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Turnstile secret key is not configured.', 'uael' ),
			);
		}

		if ( empty( $response ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Turnstile response is missing.', 'uael' ),
			);
		}

		$body = array(
			'secret'   => $secret_key,
			'response' => $response,
			'remoteip' => $remote_ip,
		);

		$args = array(
			'body'    => $body,
			'timeout' => 15,
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
		);

		$api_response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', $args );

		if ( is_wp_error( $api_response ) ) {
			return array(
				'success' => false,
				'error'   => $api_response->get_error_message(),
			);
		}

		$response_body = wp_remote_retrieve_body( $api_response );
		$result        = json_decode( $response_body, true );

		if ( ! is_array( $result ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid response from Turnstile API.', 'uael' ),
			);
		}

		if ( empty( $result['success'] ) ) {
			$error_codes   = isset( $result['error-codes'] ) ? $result['error-codes'] : array();
			$error_message = $this->get_turnstile_error_message( $error_codes );
			
			return array(
				'success'     => false,
				'error'       => $error_message,
				'error-codes' => $error_codes,
			);
		}

		return array( 'success' => true );
	}

	/**
	 * Get user-friendly error message for Turnstile error codes
	 *
	 * @since 1.42.2
	 * @param array $error_codes Error codes from API.
	 * @return string
	 */
	private function get_turnstile_error_message( $error_codes ) {
		if ( empty( $error_codes ) || ! is_array( $error_codes ) ) {
			return __( 'Security verification failed. Please try again.', 'uael' );
		}

		$error_messages = array(
			'missing-input-secret'   => __( 'The secret parameter was not passed.', 'uael' ),
			'invalid-input-secret'   => __( 'The secret parameter was invalid or did not exist.', 'uael' ),
			'missing-input-response' => __( 'The response parameter (token) was not passed.', 'uael' ),
			'invalid-input-response' => __( 'The response parameter (token) is invalid or has expired.', 'uael' ),
			'bad-request'            => __( 'The request was rejected because it was malformed.', 'uael' ),
			'timeout-or-duplicate'   => __( 'The response parameter (token) has already been validated or has expired.', 'uael' ),
			'internal-error'         => __( 'An internal error happened while validating the response.', 'uael' ),
		);

		$error_code = $error_codes[0];
		
		if ( isset( $error_messages[ $error_code ] ) ) {
			return $error_messages[ $error_code ];
		}

		return __( 'Security verification failed. Please try again.', 'uael' );
	}

	/**
	 * Get client IP address
	 *
	 * @since 1.42.2
	 * @return string
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		// phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders,WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__REMOTE_ADDR__ -- IP validation for security verification.
		$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
			return $remote_addr;
		}
		return '';
	}

	/**
	 * Render Turnstile widget HTML
	 *
	 * @since 1.42.2
	 * @param string $form_type Type of form.
	 * @param string $node_id Node ID.
	 */
	public function render_turnstile_widget( $form_type, $node_id ) {
		$settings  = $this->get_turnstile_settings();
		$widget_id = 'uael-turnstile-' . $form_type . '-' . $node_id;

		// Enqueue Turnstile API script.
		wp_enqueue_script(
			'cloudflare-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=uaelTurnstileOnLoad',
			array( 'jquery' ),
			UAEL_VER,
			false
		);

		// Enqueue UAE Turnstile handler script.
		$folder = \UltimateElementor\Classes\UAEL_Helper::get_js_folder();
		$suffix = \UltimateElementor\Classes\UAEL_Helper::get_js_suffix();

		wp_enqueue_script(
			'uael-turnstile',
			UAEL_URL . 'assets/' . $folder . '/uael-turnstile' . $suffix . '.js',
			array( 'jquery' ),
			UAEL_VER,
			true
		);

		?>
		<div class="uael-turnstile-container">
			<div class="uael-turnstile-widget">
				<div id="<?php echo esc_attr( $widget_id ); ?>"
					class="cf-turnstile"
					data-sitekey="<?php echo esc_attr( $settings['site_key'] ); ?>"
					data-theme="<?php echo esc_attr( $settings['theme'] ); ?>"
					data-size="<?php echo esc_attr( $settings['size'] ); ?>"
					data-action="uael_<?php echo esc_attr( $form_type ); ?>_form"
					data-callback="onUAELTurnstileCallback"
					data-error-callback="onUAELTurnstileError"
					data-widget-id="<?php echo esc_attr( $widget_id ); ?>"
					data-node-id="<?php echo esc_attr( $node_id ); ?>"
					data-retry="auto"
					data-retry-interval="8000">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue Turnstile scripts
	 *
	 * @since 1.42.2
	 */
	public function enqueue_turnstile_scripts() {
		// Only enqueue if at least one form type is enabled.
		if ( ! $this->is_login_turnstile_enabled() && ! $this->is_registration_turnstile_enabled() ) {
			return;
		}

		// Don't load on admin pages.
		if ( is_admin() ) {
			return;
		}

		// Enqueue Turnstile API script.
		wp_enqueue_script(
			'cloudflare-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js',
			array(),
			UAEL_VER,
			array(
				'strategy' => 'defer',
			)
		);

		// Add data attribute to prevent Cloudflare Rocket Loader issues.
		add_filter( 'script_loader_tag', array( $this, 'add_turnstile_script_attributes' ), 10, 2 );

		// Enqueue UAE Turnstile handler script.
		$folder = \UltimateElementor\Classes\UAEL_Helper::get_js_folder();
		$suffix = \UltimateElementor\Classes\UAEL_Helper::get_js_suffix();

		wp_enqueue_script(
			'uael-turnstile',
			UAEL_URL . 'assets/' . $folder . '/uael-turnstile' . $suffix . '.js',
			array( 'jquery' ),
			UAEL_VER,
			true
		);

		// Create nonce for secure AJAX communication.
		wp_localize_script(
			'uael-turnstile',
			'uaelTurnstileData',
			array(
				'nonce'    => wp_create_nonce( 'uael_turnstile_nonce' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Add data attributes to Turnstile script tag
	 *
	 * @since 1.42.2
	 * @param string $tag Script tag.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function add_turnstile_script_attributes( $tag, $handle ) {
		if ( 'cloudflare-turnstile' === $handle ) {
			$tag = str_replace( '<script ', '<script data-cfasync="false" ', $tag );
		}
		return $tag;
	}



	/**
	 * Inject Turnstile token into UAE AJAX form submissions
	 *
	 * @since 1.42.2
	 */
	public function inject_turnstile_token() {

		// Only process if we have POST data.
		if ( empty( $_POST ) ) {
			return;
		}

		// Verify nonce for security.
		// Note: Nonce verification is optional for backward compatibility.
		// If forms don't include the nonce, we allow the request to proceed.
		$nonce = '';
		if ( isset( $_POST['uael_turnstile_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['uael_turnstile_nonce'] ) );
		}

		// Verify the nonce if present - failure is allowed for backward compatibility.
		wp_verify_nonce( $nonce, 'uael_turnstile_nonce' );

		// Look for Turnstile token in various locations and inject it into the data array.
		$turnstile_token = '';
		
		// Check direct POST first.
		if ( isset( $_POST['cf-turnstile-response'] ) && ! empty( $_POST['cf-turnstile-response'] ) ) {
			$turnstile_token = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) );
		}

		// Check if there's a token in the data array already.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data sanitized when used.
		if ( empty( $turnstile_token ) && isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data sanitized when used.
			foreach ( $_POST['data'] as $key => $value ) {
				if ( strpos( $key, 'cf-turnstile-response' ) !== false && ! empty( $value ) ) {
					$turnstile_token = $value;
					break;
				}
			}
		}

		// Check for tokens that might be nested deeper or have different names.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data sanitized when used.
		if ( empty( $turnstile_token ) && isset( $_POST['data'] ) && is_array( $_POST['data'] ) ) {
			// Check for any field that might contain a Turnstile-like token.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data sanitized when used.
			foreach ( $_POST['data'] as $key => $value ) {
				if ( is_string( $value ) && strlen( $value ) > 100 && preg_match( '/^[A-Za-z0-9._-]+$/', $value ) ) {
					$turnstile_token = sanitize_text_field( wp_unslash( $value ) );
					break;
				}
			}
		}

		// If we found a token, ensure it's in the data array where UAE expects form fields.
		if ( ! empty( $turnstile_token ) ) {
			if ( ! isset( $_POST['data'] ) ) {
				$_POST['data'] = array();
			}
			$_POST['data']['cf-turnstile-response'] = $turnstile_token;
		}
	}
}

// Initialize the class.
UAEL_Turnstile::get_instance();
