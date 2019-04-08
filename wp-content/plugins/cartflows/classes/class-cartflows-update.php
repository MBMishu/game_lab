<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

if ( ! class_exists( 'Cartflows_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 *  Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', __CLASS__ . '::init' );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function init() {

			do_action( 'cartflows_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-version', CARTFLOWS_VER );
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_VER, '=' ) ) {
				return;
			}

			// Update auto saved version number.
			update_option( 'cartflows-version', CARTFLOWS_VER );

			do_action( 'cartflows_update_after' );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Update::get_instance();

endif;
