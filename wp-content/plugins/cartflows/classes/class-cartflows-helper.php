<?php
/**
 * CARTFLOWS Helper.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Helper.
 */
class Cartflows_Helper {

	/**
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $common = null;

	/**
	 * Installed Plugins
	 *
	 * @since 1.1.4
	 *
	 * @access private
	 * @var array Installed plugins list.
	 */
	private static $installed_plugins = null;

	/**
	 * Checkout Fields
	 *
	 * @var checkout_fields
	 */
	private static $checkout_fields = null;

	/**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @param  string  $key     The option key.
	 * @param  mixed   $default Option default value if option is not available.
	 * @param  boolean $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return string           Return the option value
	 */
	public static function get_admin_settings_option( $key, $default = false, $network_override = false ) {

		// Get the site-wide option if we're in the network admin.
		if ( $network_override && is_multisite() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		return $value;
	}

	/**
	 * Updates an option from the admin settings page.
	 *
	 * @param string $key       The option key.
	 * @param mixed  $value     The value to update.
	 * @param bool   $network   Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	static public function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}

	/**
	 * Get single setting
	 *
	 * @since 1.1.4
	 *
	 * @param  string $key Option key.
	 * @param  string $default Option default value if not exist.
	 * @return mixed
	 */
	static public function get_common_setting( $key = '', $default = '' ) {
		$settings = self::get_common_settings();

		if ( $settings && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $default;
	}

	/**
	 * Get required plugins for page builder
	 *
	 * @since 1.1.4
	 *
	 * @param  string $page_builder_slug Page builder slug.
	 * @param  string $default Default page builder.
	 * @return array selected page builder required plugins list.
	 */
	public static function get_required_plugins_for_page_builder( $page_builder_slug = '', $default = 'elementor' ) {
		$plugins = self::get_plugins_groupby_page_builders();

		if ( array_key_exists( $page_builder_slug, $plugins ) ) {
			return $plugins[ $page_builder_slug ];
		}

		return $plugins[ $default ];
	}

	/**
	 * Get Plugins list by page builder.
	 *
	 * @since 1.1.4
	 *
	 * @return array Required Plugins list.
	 */
	public static function get_plugins_groupby_page_builders() {

		$divi_status  = self::get_plugin_status( 'divi-builder/divi-builder.php' );
		$theme_status = 'not-installed';
		if ( $divi_status ) {
			if ( true === Cartflows_Compatibility::get_instance()->is_divi_theme_installed() ) {
				$theme_status = 'installed';
				if ( false === Cartflows_Compatibility::get_instance()->is_divi_enabled() ) {
					$theme_status = 'deactivate';
					$divi_status  = 'activate';
				} else {
					$divi_status = '';
				}
			}
		}

		$plugins = array(
			'elementor' => array(
				'title'   => 'Elementor',
				'plugins' => array(
					array(
						'slug'   => 'elementor', // For download from wp.org.
						'init'   => 'elementor/elementor.php',
						'status' => self::get_plugin_status( 'elementor/elementor.php' ),
					),
				),
			),
			'divi'      => array(
				'title'         => 'Divi',
				'theme-status'  => $theme_status,
				'plugin-status' => $divi_status,
				'plugins'       => array(
					array(
						'slug'   => 'divi-builder', // For download from wp.org.
						'init'   => 'divi-builder/divi-builder.php',
						'status' => $divi_status,
					),
				),
			),
		);

		$plugins['beaver-builder'] = array(
			'title'   => 'Beaver Builder',
			'plugins' => array(),
		);

		// Check Pro Exist.
		if ( file_exists( WP_PLUGIN_DIR . '/' . 'bb-plugin/fl-builder.php' ) && ! is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ) {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'bb-plugin',
				'init'   => 'bb-plugin/fl-builder.php',
				'status' => self::get_plugin_status( 'bb-plugin/fl-builder.php' ),
			);
		} else {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'beaver-builder-lite-version', // For download from wp.org.
				'init'   => 'beaver-builder-lite-version/fl-builder.php',
				'status' => self::get_plugin_status( 'beaver-builder-lite-version/fl-builder.php' ),
			);
		}

		if ( file_exists( WP_PLUGIN_DIR . '/' . 'bb-ultimate-addon/bb-ultimate-addon.php' ) && ! is_plugin_active( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ) ) {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'bb-ultimate-addon',
				'init'   => 'bb-ultimate-addon/bb-ultimate-addon.php',
				'status' => self::get_plugin_status( 'bb-ultimate-addon/bb-ultimate-addon.php' ),
			);
		} else {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'ultimate-addons-for-beaver-builder-lite', // For download from wp.org.
				'init'   => 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php',
				'status' => self::get_plugin_status( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ),
			);
		}

		return $plugins;
	}

	/**
	 * Get plugin status
	 *
	 * @since 1.1.4
	 *
	 * @param  string $plugin_init_file Plguin init file.
	 * @return mixed
	 */
	public static function get_plugin_status( $plugin_init_file ) {

		if ( null == self::$installed_plugins ) {
			self::$installed_plugins = get_plugins();
		}

		if ( ! isset( self::$installed_plugins[ $plugin_init_file ] ) ) {
			return 'install';
		} elseif ( ! is_plugin_active( $plugin_init_file ) ) {
			return 'activate';
		}

		return;
	}

	/**
	 * Get zapier settings.
	 *
	 * @return  array.
	 */
	static public function get_common_settings() {

		if ( null === self::$common ) {

			$common_default = apply_filters(
				'cartflows_common_settings_default',
				array(
					'disallow_indexing'    => 'disable',
					'global_checkout'      => '',
					'default_page_builder' => 'elementor',
				)
			);

			$common = Cartflows_Helper::get_admin_settings_option( '_cartflows_common', false, true );

			$common = wp_parse_args( $common, $common_default );

			if ( ! did_action( 'wp' ) ) {
				return $common;
			} else {
				self::$common = $common;
			}
		}

		return self::$common;
	}

	/**
	 * Get Checkout field.
	 *
	 * @param string $key Field key.
	 * @param int    $post_id Post id.
	 * @return array.
	 */
	static public function get_checkout_fields( $key, $post_id ) {

		$saved_fields = get_post_meta( $post_id, 'wcf_fields_' . $key, true );

		if ( ! $saved_fields ) {
			$saved_fields = array();
		}

		$fields = array_filter( $saved_fields );

		if ( empty( $fields ) ) {
			if ( 'billing' === $key || 'shipping' === $key ) {

				$fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), $key . '_' );

				update_post_meta( $post_id, 'wcf_fields_' . $key, $fields );
			}
		}

		return $fields;
	}

	/**
	 * Add Checkout field.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param array  $field_data Field data.
	 * @param int    $post_id Post id.
	 * @return  boolean.
	 */
	static public function add_checkout_field( $type, $field_key, $field_data = array(), $post_id ) {

		$fields = self::get_checkout_fields( $type, $post_id );

		$fields[ $field_key ] = $field_data;

		update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );

		return true;
	}

	/**
	 * Get checkout fields settings.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param int    $post_id Post id.
	 * @return  array.
	 */
	static public function delete_checkout_field( $type, $field_key, $post_id ) {

		$fields = self::get_checkout_fields( $type, $post_id );

		if ( isset( $fields[ $field_key ] ) ) {
			unset( $fields[ $field_key ] );
		}

		update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );

		return true;
	}

	/**
	 * Get checkout fields settings.
	 *
	 * @return  array.
	 */
	static public function get_checkout_fields_settings() {

		if ( null === self::$checkout_fields ) {
			$checkout_fields_default = array(
				'enable_customization'  => 'disable',
				'enable_billing_fields' => 'disable',
			);

			$billing_fields = self::get_checkout_fields( 'billing' );

			if ( is_array( $billing_fields ) && ! empty( $billing_fields ) ) {

				foreach ( $billing_fields as $key => $value ) {

					$checkout_fields_default[ $key ] = 'enable';
				}
			}

			$checkout_fields = Cartflows_Helper::get_admin_settings_option( '_wcf_checkout_fields', false, true );

			self::$checkout_fields = wp_parse_args( $checkout_fields, $checkout_fields_default );
		}

		return self::$checkout_fields;
	}

	/**
	 * Get meta options
	 *
	 * @since 1.0.0
	 * @param  int    $post_id     Product ID.
	 * @param  string $key      Meta Key.
	 * @param  string $default      Default value.
	 * @return string           Meta Value.
	 */
	static public function get_meta_option( $post_id, $key, $default = '' ) {

		$value = get_post_meta( $post_id, $key, true );

		if ( ! $value ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Save meta option
	 *
	 * @since 1.0.0
	 * @param  int   $post_id     Product ID.
	 * @param  array $args      Arguments array.
	 */
	static public function save_meta_option( $post_id, $args = array() ) {

		if ( is_array( $args ) && ! empty( $args ) ) {

			foreach ( $args as $key => $value ) {

				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Check if Elementor page builder is installed
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	static public function _is_elementor_installed() {
		$path    = 'elementor/elementor.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}

	/**
	 * Check if Step has product assigned.
	 *
	 * @since 1.0.0
	 * @param int $step_id step ID.
	 *
	 * @access public
	 */
	static public function has_product_assigned( $step_id ) {

		$step_type = get_post_meta( $step_id, 'wcf-step-type', true );

		if ( 'checkout' == $step_type ) {
			$product = get_post_meta( $step_id, 'wcf-checkout-products', true );
		} else {
			$product = get_post_meta( $step_id, 'wcf-offer-product', true );
		}

		if ( ! empty( $product ) ) {
			return true;
		}
		return false;

	}

	/**
	 * Get attributes for cartflows wrap.
	 *
	 * @since 1.1.4
	 *
	 * @access public
	 */
	static public function get_cartflows_container_atts() {

		$attributes  = apply_filters( 'cartflows_container_atts', array() );
		$atts_string = '';

		foreach ( $attributes as $key => $value ) {

			if ( ! $value ) {
				continue;
			}

			if ( true === $value ) {
				$atts_string .= esc_html( $key ) . ' ';
			} else {
				$atts_string .= sprintf( '%s="%s" ', esc_html( $key ), esc_attr( $value ) );
			}
		}

		return $atts_string;
	}
}
