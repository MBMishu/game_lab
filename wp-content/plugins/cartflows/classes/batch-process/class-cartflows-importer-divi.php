<?php
/**
 * Divi Importer
 *
 * @package CartFlows
 * @since 1.1.1
 */

if ( ! class_exists( 'CartFlows_Importer_Divi' ) ) :

	/**
	 * CartFlows Import Divi
	 *
	 * @since 1.1.1
	 */
	class CartFlows_Importer_Divi {

		/**
		 * Instance
		 *
		 * @since 1.1.1
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.1.1
		 * @return object initialized object of class.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.1.1
		 */
		public function __construct() {
			add_action( 'admin_head', array( $this, 're_process_data_flow' ) );
			add_action( 'admin_head', array( $this, 're_process_data_step' ) );
		}

		/**
		 * Rest Data.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		function reset_data( $post_id ) {

			$content = get_post_meta( $post_id, 'divi_content_processed', true );

			if ( ! empty( $content ) ) {

				$content = CartFlows_Importer::get_instance()->get_content( $content );

				// Update post content.
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $content,
					)
				);
			}
		}

		/**
		 * Re process data.
		 */
		function re_process_data_flow() {
			wcf()->logger->import_log( ' Without batch started.. ' );

			if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type() ) {
				return;
			}

			global $pagenow;

			if ( 'post.php' !== $pagenow ) {
				return;
			}

			// Is Highlight step then return!
			// Because, It process only for the first flow import.
			if ( isset( $_GET['highlight-step-id'] ) ) {
				return;
			}

			$post_id = get_the_ID();

			/* If Imported Flow */
			if ( 'yes' !== get_post_meta( $post_id, 'cartflows_imported_flow', true ) ) {
				return;
			}

			// wcf()->logger->import_log( ' Reprocessing Divi Data.. ' . $post_id );.
			$flow_processed = get_post_meta( $post_id, 'wcf_divi_flow_data_processed', true );

			if ( ! $flow_processed ) {

				$steps         = get_post_meta( $post_id, 'wcf-steps', true );
				$steps_count   = count( $steps );
				$steps_remaing = $steps_count;

				foreach ( $steps as $key => $step ) {

					$step_processed = get_post_meta( $step['id'], 'wcf_divi_step_data_processed', true );

					if ( ! $step_processed ) {

						$content = get_post_meta( $step['id'], 'divi_content_processed', true );
						// It means that batch is not complete yet.
						if ( ! empty( $content ) ) {
							$this->reset_data( $step['id'] );

							wcf()->logger->import_log( ' Processing without batch.. ' . $step['id'] );

							// Step processed!
							update_post_meta( $step['id'], 'wcf_divi_step_data_processed', true );

							$steps_remaing--;
						}
					}
				}

				// All step data processed.
				if ( 0 === $steps_remaing ) {
					wcf()->logger->import_log( ' ----------- FLOW COMPLETE ----------------- ' . $post_id );
					update_post_meta( $post_id, 'wcf_divi_flow_data_processed', true );
				}
			} else {
				wcf()->logger->import_log( ' Already processed.. ' );
			}

			wcf()->logger->import_log( ' Without batch end.. ' );

		}

		/**
		 * Re process data for step.
		 */
		function re_process_data_step() {

			wcf()->logger->import_log( ' Without batch started.. ' );

			if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type() ) {
				return;
			}

			$post_id = get_the_ID();

			/* If Imported Step */
			if ( 'yes' !== get_post_meta( $post_id, 'cartflows_imported_step', true ) ) {
				return;
			}

			$step_processed = get_post_meta( $post_id, 'wcf_divi_step_data_processed', true );

			if ( ! $step_processed ) {

				$content = get_post_meta( $post_id, 'divi_content_processed', true );
				// It means that batch is not complete yet.
				if ( ! empty( $content ) ) {
					$this->reset_data( $post_id );

					wcf()->logger->import_log( ' Processing without batch.. ' . $post_id );

					// Step processed!
					update_post_meta( $post_id, 'wcf_divi_step_data_processed', true );
				}
			}
		}

		/**
		 * Update post meta.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function import_single_post( $post_id = 0 ) {

			// Download and replace images.
			$content = get_post_meta( $post_id, 'divi_content', true );

			if ( empty( $content ) ) {
				wcf()->logger->import_log( '(✕) Not have "Divi" Data. Post content is empty!' );
			} else {

				wcf()->logger->import_log( '(✓) Processing Request..' );

				// Update hotlink images.
				$content = CartFlows_Importer::get_instance()->get_content( $content );

				// Save processed data.
				update_post_meta( $post_id, 'divi_content_processed', $content );

				// Delete temporary meta key.
				wcf()->logger->import_log( '(✓) Process Complete' );
			}
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer_Divi::get_instance();

endif;
