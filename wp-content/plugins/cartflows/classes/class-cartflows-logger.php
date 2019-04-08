<?php
/**
 * Logger.
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Logger {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var logger
	 */
	public $logger;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		/* Load WC Logger */
		add_action( 'init', array( $this, 'init_wc_logger' ), 99 );
	}

	/**
	 * Inint Logger.
	 *
	 * @since 1.0.0
	 */
	function init_wc_logger() {
		$this->logger = new WC_Logger();
	}

	/**
	 * Write log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	function log( $message, $level = 'info' ) {

		$enable_log = apply_filters( 'cartflows_enable_log', 'enable' );

		if ( 'enable' === $enable_log &&
			is_a( $this->logger, 'WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows' ) );
		}
	}

	/**
	 * Write log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	function import_log( $message, $level = 'info' ) {

		if ( defined( 'WP_DEBUG' ) &&
			WP_DEBUG &&
			is_a( $this->logger, 'WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows-import' ) );
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Logger::get_instance();
