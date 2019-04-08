<?php
/**
 * Schema Pro - Schema Wizard
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CartFlows_Wizard' ) ) :

	/**
	 * CartFlows_Wizard class.
	 */
	class CartFlows_Wizard {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			if ( apply_filters( 'cartflows_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_init', array( $this, 'setup_wizard' ) );
				add_action( 'admin_notices', array( $this, 'show_setup_wizard' ) );
				add_action( 'wp_ajax_page_builder_step_save', array( $this, 'page_builder_step_save' ) );

				add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );
			}
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function show_setup_wizard() {

			$status = get_option( 'wcf_setup_complete', false );

			if ( false === $status ) { ?>
				<div class="notice notice-info">
					<p><b><?php _e( 'Thanks for installing and using CartFlows!', 'cartflows' ); ?></b></p>
					<p><?php _e( 'It is easy to use the CartFlows. Please use the setup wizard to quick start setup.', 'cartflows' ); ?></p>
					<p><a href="<?php echo esc_url( admin_url( 'index.php?page=cartflow-setup' ) ); ?>" class="button button-primary">Start Wizard!</a></p>
				</div>
				<?php
			}
		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {
			add_dashboard_page( '', '', 'manage_options', 'cartflow-setup', '' );
		}

		/**
		 * Show the setup wizard.
		 */
		public function setup_wizard() {

			if ( empty( $_GET['page'] ) || 'cartflow-setup' !== $_GET['page'] ) {
				return;
			}

			$this->steps = array(
				'basic-config' => array(
					'name'    => __( 'Welcome', 'cartflows' ),
					'view'    => array( $this, 'welcome_step' ),
					'handler' => array( $this, 'welcome_step_save' ),
				),
				'page-builder' => array(
					'name' => __( 'Page Builder', 'cartflows' ),
					'view' => array( $this, 'page_builder_step' ),
					// 'handler' => array( $this, 'page_builder_step_save' ),
				),
				'setup-ready'  => array(
					'name'    => __( 'Ready!', 'cartflows' ),
					'view'    => array( $this, 'ready_step' ),
					'handler' => '',
				),
			);

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_enqueue_style( 'cartflows-setup', CARTFLOWS_URL . 'admin/assets/css/setup-wizard.css', array( 'dashicons' ), CARTFLOWS_VER );
			wp_style_add_data( 'cartflows-setup', 'rtl', 'replace' );
			wp_enqueue_script( 'cartflows-setup', CARTFLOWS_URL . 'admin/assets/js/setup-wizard.js', array( 'jquery', 'wp-util', 'updates' ), CARTFLOWS_VER );

			wp_enqueue_media();

			if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				call_user_func( $this->steps[ $this->step ]['handler'] );
			}

			ob_start();
			$this->setup_wizard_header();
			$this->setup_wizard_steps();
			$this->setup_wizard_content();
			$this->setup_wizard_footer();
			exit;
		}

		/**
		 * Get current step slug
		 */
		public function get_current_step_slug() {
			$keys = array_keys( $this->steps );
			return $keys[ array_search( $this->step, array_keys( $this->steps ) ) ];
		}

		/**
		 * Get previous step link
		 */
		public function get_prev_step_link() {
			$keys = array_keys( $this->steps );
			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) - 1 ] );
		}

		/**
		 * Get next step link
		 */
		public function get_next_step_link() {
			$keys = array_keys( $this->steps );
			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
		}

		/**
		 * Get next step link
		 */
		public function get_next_step_plain_link() {
			$keys = array_keys( $this->steps );
			$step = $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ];
			return admin_url( 'index.php?page=cartflow-setup&step=' . $step );
		}

		/**
		 * Setup Wizard Header.
		 */
		public function setup_wizard_header() {
			?>
			<!DOCTYPE html>
			<html>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php _e( 'CartFlows Setup', 'cartflows' ); ?></title>

				<script type="text/javascript">
					addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
					var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
					var pagenow = '';
				</script>
				<?php wp_print_scripts( array( 'cartflows-setup' ) ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="cartflows-setup wp-core-ui cartflows-step-<?php echo esc_attr( $this->get_current_step_slug() ); ?>">
				<div id="cartflows-logo">
					<h1>CartFlows</h1>
				</div>
			<?php
		}

		/**
		 * Setup Wizard Footer.
		 */
		public function setup_wizard_footer() {

				$admin_url = admin_url( 'admin.php?page=cartflows_settings' );
			?>
				<div class="close-button-wrapper">
					<a href="<?php echo esc_url( $admin_url ); ?>" class="wizard-close-link" ><?php _e( 'Exit Setup Wizard', 'cartflows' ); ?></a>
				</div>
				</body>
			</html>
			<?php
		}

		/**
		 * Output the steps.
		 */
		public function setup_wizard_steps() {

			$ouput_steps = $this->steps;
			?>
			<ol class="cartflows-setup-steps">
				<?php
				foreach ( $ouput_steps as $step_key => $step ) :
					$classes   = '';
					$activated = false;
					if ( $step_key === $this->step ) {
						$classes   = 'active';
						$activated = true;
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						$classes   = 'done';
						$activated = true;
					}
					?>
					<li class="<?php echo esc_attr( $classes ); ?>">
						<span><?php echo esc_html( $step['name'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step.
		 */
		public function setup_wizard_content() {
			echo '<div class="cartflows-setup-content">';
			call_user_func( $this->steps[ $this->step ]['view'] );
			echo '</div>';
		}

		/**
		 * Introduction step.
		 */
		public function welcome_step() {
			?>
			<h1><?php _e( 'Thank you for choosing CartFlows!', 'cartflows' ); ?></h1>
			<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
			<form method="post">				
				<div class="cartflows-setup-actions step">
					<div class="button-prev-wrap">
					</div>
					<div class="button-next-wrap">
						<input type="submit" class="uct-activate button-primary button button-large button-next" value="<?php _e( 'Lets Go »', 'cartflows' ); ?>" name="save_step" />
					</div>
					<?php wp_nonce_field( 'cartflow-setup' ); ?>
				</div>
			</form>
			<?php
		}

		/**
		 * Save Locale Settings.
		 */
		public function welcome_step_save() {
			check_admin_referer( 'cartflow-setup' );

			// Update site title & tagline.
			$redirect_url = $this->get_next_step_link();

			wp_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Locale settings
		 */
		public function page_builder_step() {
			?>

			<h1><?php _e( 'Page Builder', 'cartflows' ); ?></h1>
			<p class="description"><?php _e( 'Select a page builder which you want to use for creating your new flows.', 'cartflows' ); ?></p>
			<form method="post">
				<table class="cartflows-table widefat">
					<tr class="cartflows-row">
						<td class="cartflows-row-heading">
							<label><?php esc_html_e( 'Select Page Builder', 'cartflows' ); ?></label>
							<i class="cartflows-heading-help dashicons dashicons-editor-help" title="<?php echo esc_attr__( 'Add locations for where this Schema should appear.', 'cartflows' ); ?>"></i>
						</td>
						<td class="cartflows-row-content">
							<?php
							$installed_plugins = get_plugins();
							$plugins           = array(
								array(
									'title' => __( 'Elementor', 'cartflows' ),
									'value' => 'elementor',
									'data'  => array(
										'slug'    => 'elementor',
										'init'    => 'elementor/elementor.php',
										'active'  => is_plugin_active( 'elementor/elementor.php' ) ? 'yes' : 'no',
										'install' => isset( $installed_plugins['elementor/elementor.php'] ) ? 'yes' : 'no',
									),
								),
							);
							?>
							<select name="page-builder" class="page-builder-list" data-redirect-link="<?php echo esc_url_raw( $this->get_next_step_plain_link() ); ?>">
								<?php
								foreach ( $plugins as $key => $plugin ) {
									echo '<option value="' . esc_attr( $plugin['value'] ) . '" data-install="' . esc_attr( $plugin['data']['install'] ) . '" data-active="' . esc_attr( $plugin['data']['active'] ) . '" data-slug="' . esc_attr( $plugin['data']['slug'] ) . '" data-init="' . esc_attr( $plugin['data']['init'] ) . '">' . esc_html( $plugin['title'] ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
				</table>
				<p><?php _e( 'The above plugin will be installed and activated for you!', 'cartflows' ); ?></p>
				<div class="cartflows-setup-actions step">
					<div class="button-prev-wrap">
						<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" ><?php _e( '« Previous', 'cartflows' ); ?></a>
					</div>
					<div class="button-next-wrap">
						<a href="#" class="button button-primary wcf-install-plugins"><?php _e( 'Next »', 'cartflows' ); ?></a>
						<!-- <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Next »', 'cartflows' ); ?>" name="save_step" /> -->
					</div>
					<?php wp_nonce_field( 'cartflow-setup' ); ?>
				</div>
			</form>
			<?php
		}

		/**
		 * Save Locale Settings.
		 */
		function page_builder_step_save() {

			check_ajax_referer( 'wcf-page-builder-step-save', 'security' );

			$stored       = get_option( 'wcf_setup', array() );
			$page_builder = isset( $_POST['page_builder'] ) ? sanitize_text_field( $_POST['page_builder'] ) : '';
			$plugin_init  = isset( $_POST['plugin_init'] ) ? sanitize_text_field( $_POST['plugin_init'] ) : '';

			if ( $page_builder ) {
				$stored['page-builder'] = $page_builder;
			}

			update_option( 'wcf_setup', $stored );

			$activate = activate_plugin( $plugin_init, '', false, true );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $activate->get_error_message(),
					)
				);
			}

			wp_send_json_success( $stored );
		}

		/**
		 * Get Location rules of schema for Custom meta box.
		 *
		 * @param  array $enabled_on   Enabled on rules.
		 * @param  array $exclude_from Exlcude on rules.
		 * @return array
		 */
		public static function get_display_rules_for_meta_box( $enabled_on, $exclude_from ) {
			$locations        = array();
			$enabled_location = array();
			$exclude_location = array();

			$args       = array(
				'public'   => true,
				'_builtin' => true,
			);
			$post_types = get_post_types( $args );
			unset( $post_types['attachment'] );

			$args['_builtin'] = false;
			$custom_post_type = get_post_types( $args );
			$post_types       = array_merge( $post_types, $custom_post_type );

			if ( ! empty( $enabled_on ) && isset( $enabled_on['rule'] ) ) {
				$enabled_location = $enabled_on['rule'];
			}
			if ( ! empty( $exclude_from ) && isset( $exclude_from['rule'] ) ) {
				$exclude_location = $exclude_from['rule'];
			}

			if ( in_array( 'specifics', $enabled_location ) || ( in_array( 'basic-singulars', $enabled_location ) && ! in_array( 'basic-singulars', $exclude_location ) ) ) {
				foreach ( $post_types as $post_type ) {
					$locations[ $post_type ] = 1;
				}
			} else {
				foreach ( $post_types as $post_type ) {
					$key = $post_type . '|all';
					if ( in_array( $key, $enabled_location ) && ! in_array( $key, $exclude_location ) ) {
						$locations[ $post_type ] = 1;
					}
				}
			}
			return $locations;
		}

		/**
		 * Final step.
		 */
		public function ready_step() {

			// Set setup wizard status to complete.
			update_option( 'wcf_setup_complete', true );
			?>
			<h1><?php _e( 'Congratulations!', 'cartflows' ); ?></h1>

			<div class="cartflows-setup-next-steps">
				<div class="cartflows-setup-next-steps-last">

					<p class="success">
						<?php
						_e( 'You\'ve successfully completed the setup before you begin setting now you can use it.', 'cartflows' )
						?>
					</p>


					<ul class="wcf-wizard-next-steps">
						<li class="wcf-wizard-next-step-item">
							<div class="wcf-wizard-next-step-description">
								<p class="next-step-heading">Next step</p>
								<h3 class="next-step-description">Create First Flow</h3>
								<p class="next-step-extra-info">You're ready to add flows to your website.</p>
							</div>
							<div class="wcf-wizard-next-step-action">
								<p class="wc-setup-actions step">
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cartflows_flow&add-new-flow' ) ); ?>" type="button" class="button button-primary button-hero" ><?php _e( 'Create a flow', 'cartflows' ); ?></a>
								</p>
							</div>
						</li>
					</ul>

				</div>
			</div>
			<?php
		}

		/**
		 * Localize variables in admin
		 *
		 * @param array $vars variables.
		 */
		function localize_vars( $vars ) {

			$ajax_actions = array(
				'wcf_page_builder_step_save',
			);

			foreach ( $ajax_actions as $action ) {

				$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
			}

			return $vars;
		}
	}

	new CartFlows_Wizard();

endif;
