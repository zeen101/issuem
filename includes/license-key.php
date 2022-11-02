<?php

/**
 * Registers IssueM License Key class
 *
 * @package IssueM
 * @since 2.8.4
 */

/**
 * This class registers the IssueM license key activate/deactive functionality
 *
 * @since 2.8.4
 */

class Issuem_License_Key {


	private $plugin_slug;
	private $plugin_prefix; // underscored version of slug
	private $plugin_name;

	/**
	 * Class constructor, puts things in motion
	 *
	 * @since 2.8.4
	 */
	function __construct( $plugin_slug, $plugin_name ) {

		$this->plugin_slug   = $plugin_slug;
		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = str_replace( '-', '_', $plugin_slug );

		add_action( 'admin_init', array( $this, 'activate_license' ) );
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );

		add_action( 'issuem_after_licenses_settings', array( $this, 'license_key_settings_div' ) );
	}

	/**
	 * Get IssueM License Settings for this plugin
	 *
	 * @since 4.4.0
	 */
	public function get_settings() {

		$defaults = array(
			'license_key'    => '',
			'license_status' => '',
		);

		$defaults = apply_filters( $this->plugin_slug . '_default_license_settings', $defaults );
		$settings = get_option( $this->plugin_slug );
		$settings = wp_parse_args( $settings, $defaults );

		return apply_filters( $this->plugin_prefix . '_get_license_settings', $settings );
	}

	/**
	 * Update IssueM License Settings for this plugin
	 *
	 * @since 2.8.4
	 */
	public function update_settings( $settings ) {
		update_option( $this->plugin_slug, $settings );
	}

	/**
	 * Create and display license key on the IssueM settings page
	 *
	 * @since 2.8.4
	 */
	public function license_key_settings_div() {

		$settings = $this->get_settings();

		?>

		<div class="issuem-license-key-box">
			<h3 class="issuem-license-key-title"><span><?php esc_html_e( $this->plugin_name, $this->plugin_slug ); ?></span></h3>

			<table id="<?php echo esc_attr( $this->plugin_prefix ); ?>_license_key" class="form-table">
				<tr>
					<th rowspan="1">
						<?php esc_html_e( 'License Key', $this->plugin_slug ); ?>
					</th>

					<td>
						<input type="text" id="<?php echo esc_attr( $this->plugin_prefix ); ?>_license_key" class="regular-text" name="<?php echo esc_attr( $this->plugin_prefix ); ?>_license_key" value="<?php echo esc_attr( $settings['license_key'] ); ?>" />

						<?php 
						if (
							$settings['license_status'] !== false
							&& $settings['license_status'] == 'valid'
						) {
												   // license is active
							?>
							<span style="color:green;"><?php esc_html_e( 'active' ); ?></span>
							<input type="submit" class="button-secondary" name="<?php echo esc_attr( $this->plugin_prefix ); ?>_license_deactivate" value="<?php esc_attr_e( 'Deactivate License', $this->plugin_slug ); ?>" />

							<?php 
						} elseif ( $settings['license_status'] == 'invalid' ) {
							// license is invalid
							?>
							<span style="color:red;"><?php esc_html_e( 'invalid' ); ?></span>
							<input type="submit" class="button-secondary" name="<?php echo esc_attr( $this->plugin_prefix ); ?>_license_activate" value="<?php esc_attr_e( 'Activate License', $this->plugin_slug ); ?>" />

							<?php 
						} else {
							// license hasn't been entered yet
							?>
							<input type="submit" class="button-secondary" name="<?php echo esc_attr( $this->plugin_prefix ); ?>_license_activate" value="<?php esc_attr_e( 'Activate License', $this->plugin_slug ); ?>" />

						<?php } ?>

					</td>
				</tr>
			</table>

		</div>


		<?php
	}

	/**
	 * Activate the IssueM license if its valid for this plugin
	 *
	 * @since 2.8.4
	 */
	function activate_license() {

		if ( ! isset( $_POST[ $this->plugin_prefix . '_license_activate' ] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'verify', 'issuem_license_wpnonce' ) ) {
			return;
		}

		$settings = $this->get_settings();

		if ( ! empty( $_POST[ $this->plugin_prefix . '_license_key' ] ) ) {
			$settings['license_key'] = sanitize_text_field( $_POST[ $this->plugin_prefix . '_license_key' ] );
		}

		$license = trim( $settings['license_key'] );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->plugin_name ), // the name of our product in EDD
		);

		// Call the custom API.
		$response = wp_remote_get(
			esc_url_raw( add_query_arg( $api_params, ZEEN101_STORE_URL ) ),
			array(
				'sslverify' => false,
			) 
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "active" or "inactive"
		$settings['license_status'] = $license_data->license;
		$this->update_settings( $settings );
	}

	/**
	 * De-activate the IssueM license for this plugin
	 *
	 * @since 2.8.4
	 */
	public function deactivate_license() {

		if ( ! isset( $_POST[ $this->plugin_prefix . '_license_deactivate' ] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'verify', 'issuem_license_wpnonce' ) ) {
			return;
		}

		$settings = $this->get_settings();

		// retrieve the license from the database
		$license = trim( $settings['license_key'] );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->plugin_name ), // the name of our product in EDD
		);

		// Call the custom API.
		$response = wp_remote_get(
			esc_url_raw( add_query_arg( $api_params, ZEEN101_STORE_URL ) ),
			array(
				'sslverify' => false,
			) 
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data->license == 'deactivated' || $license_data->license == 'failed' ) {

			unset( $settings['license_key'] );
			unset( $settings['license_status'] );
			$this->update_settings( $settings );
		}
	}
}
