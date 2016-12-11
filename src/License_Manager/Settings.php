<?php
/**
 * Settings init for this plugin
 *
 * This class handle the settings initialization for the required fields.
 *
 * @link http://italystrap.com
 * @since 1.0.0
 *
 * @package ItalyStrap\License_Manager
 */

namespace ItalyStrap\License_Manager;

use ItalyStrap\Settings\Settings_Base;

/**
 * Class description
 */
class Settings extends Settings_Base {

	/**
	 * [$var description]
	 *
	 * @var null
	 */
	private $var = null;

	/**
	 * [__construct description]
	 *
	 * @param [type] $argument [description].
	 */
	function __construct( $product_id, $product_name, $text_domain, $api_url,
								 $type = 'theme', $plugin_file = '' ) {

		// Store setup data
		$this->product_id = $product_id;
		$this->product_name = $product_name;
		$this->text_domain = $text_domain;
		$this->api_endpoint = $api_url;
		$this->type = $type;
		$this->plugin_file = $plugin_file;

		// Add the menu screen for inserting license information
		add_action( 'admin_menu', array( $this, 'add_license_settings_page' ) );
		add_action( 'admin_init', array( $this, 'add_license_settings_fields' ) );

		// Add a nag text for reminding the user to save the license information
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Add style for ItalyStrap admin page
	 *
	 * @param  string $hook The admin page name (admin.php - tools.php ecc).
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	 */
	public function enqueue_admin_style_script( $hook ) {

		return;
	}

	//
	// LICENSE SETTINGS
	//

	/**
	 * Creates the settings items for entering license information (email + license key).
	 *
	 * NOTE:
	 * If you want to move the license settings somewhere else (e.g. your theme / plugin
	 * settings page), we suggest you override this function in a subclass and
	 * initialize the settings fields yourself. Just make sure to use the same
	 * settings fields so that ItalyStrap\License_Manager\Client() can still find the settings values.
	 */
	public function add_license_settings_page() {
		$title = sprintf( __( '%s License', $this->text_domain ), $this->product_name );

		add_options_page(
			$title,
			$title,
			'read',
			$this->get_settings_page_slug(),
			array( $this, 'render_licenses_menu' )
		);
	}

	/**
	 * Creates the settings fields needed for the license settings menu.
	 */
	public function add_license_settings_fields() {
		$settings_group_id = $this->product_id . '-license-settings-group';
		$settings_section_id = $this->product_id . '-license-settings-section';

		register_setting( $settings_group_id, $this->get_settings_field_name() );

		add_settings_section(
			$settings_section_id,
			__( 'License', $this->text_domain ),
			array( $this, 'render_settings_section' ),
			$settings_group_id
		);

		add_settings_field(
			$this->product_id . '-license-email',
			__( 'License e-mail address', $this->text_domain ),
			array( $this, 'render_email_settings_field' ),
			$settings_group_id,
			$settings_section_id
		);

		add_settings_field(
			$this->product_id . '-license-key',
			__( 'License key', $this->text_domain ),
			array( $this, 'render_license_key_settings_field' ),
			$settings_group_id,
			$settings_section_id
		);
	}

	/**
	 * Renders the description for the settings section.
	 */
	public function render_settings_section() {
		_e( 'Insert your license information to enable updates.', $this->text_domain );
	}

	/**
	 * Renders the settings page for entering license information.
	 */
	public function render_licenses_menu() {
		$title = sprintf( __( '%s License', $this->text_domain ), $this->product_name );
		$settings_group_id = $this->product_id . '-license-settings-group';

		?>
		<div class="wrap">
			<form action='options.php' method='post'>

				<h2><?php echo $title; ?></h2>

				<?php
				settings_fields( $settings_group_id );
				do_settings_sections( $settings_group_id );
				submit_button();
				?>

			</form>
		</div>
	<?php
	}

	/**
	 * Renders the email settings field on the license settings page.
	 */
	public function render_email_settings_field() {
		$settings_field_name = $this->get_settings_field_name();
		$options = get_option( $settings_field_name );
		?>
		<input type='text' name='<?php echo $settings_field_name; ?>[email]'
			   value='<?php echo $options['email']; ?>' class='regular-text'>
	<?php
	}

	/**
	 * Renders the license key settings field on the license settings page.
	 */
	public function render_license_key_settings_field() {
		$settings_field_name = $this->get_settings_field_name();
		$options = get_option( $settings_field_name );
		?>
		<input type='text' name='<?php echo $settings_field_name; ?>[license_key]'
			   value='<?php echo $options['license_key']; ?>' class='regular-text'>
	<?php
	}

	/**
	 * If the license has not been configured properly, display an admin notice.
	 */
	public function show_admin_notices() {
		if ( ! $this->get_license_key() ) {
			$msg = __( 'Please enter your email and license key to enable updates to %s.', $this->text_domain );
			$msg = sprintf( $msg, $this->product_name );
			?>
				<div class="update-nag">
					<p>
						<?php echo $msg; ?>
					</p>

					<p>
						<a href="<?php echo admin_url( 'options-general.php?page=' . $this->get_settings_page_slug() ); ?>">
							<?php _e( 'Complete the setup now.', $this->text_domain ); ?>
						</a>
					</p>
				</div>
			<?php
		}
	}

	/**
	 * @return string   The name of the settings field storing all license manager settings.
	 */
	protected function get_settings_field_name() {
		return $this->product_id . '-license-settings';
	}

	/**
	 * @return string   The slug id of the licenses settings page.
	 */
	protected function get_settings_page_slug() {
		return $this->product_id . '-licenses';
	}

	private function get_license_key() {
		// First, check if configured in wp-config.php
		$license_email = ( defined( 'FOURBASE_LICENSE_EMAIL' ) ) ? FOURBASE_LICENSE_EMAIL : '';
		$license_key = ( defined( 'FOURBASE_LICENSE_KEY' ) ) ? FOURBASE_LICENSE_KEY : '';

		// If not found, look up from database
		if ( ! $license_key || strlen( $license_key ) < 8 ) {
			$options = get_option( $this->get_settings_field_name() );

			if ( $options
				 && isset( $options['email'] )
				 && isset( $options['license_key'] )
				 && strlen( $options['email'] ) > 0
				 && strlen( $options['license_key'] ) >= 8 ) {
				$license_email = $options['email'];
				$license_key = $options['license_key'];
			} else {
				$license_email = '';
				$license_key = '';
			}
		}

		if ( strlen( $license_email ) > 0 && strlen( $license_key ) >= 8 ) {
			return array( 'key' => $license_key, 'email' => $license_email );
		}

		// No license key found
		return false;
	}
}
