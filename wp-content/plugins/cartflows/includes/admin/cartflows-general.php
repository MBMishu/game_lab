<?php
/**
 * General settings
 *
 * @package CartFlows
 */

$settings = Cartflows_Helper::get_common_settings();

?>

<form method="post" class="wrap wcf-clear" action="" >
<div class="wrap wcf-addon-wrap wcf-clear wcf-container">
	<input type="hidden" name="action" value="wcf_save_common_settings">
	<h1 class="screen-reader-text"><?php _e( 'General Settings', 'cartflows' ); ?></h1>

	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<div class="postbox introduction">
					<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
						<span><?php _e( 'Getting Started', 'cartflows' ); ?></span>
					</h2>
					<div class="inside">
						<div class="iframe-wrap">
							<iframe width="560" height="315" src="https://www.youtube.com/embed/SlE0moPKjMY" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>
						<p>
						<?php
							esc_attr_e( 'Modernizing WordPress eCommerce!', 'cartflows' );
						?>
						</p>
					</div>
				</div>

				<div class="general-settings-form postbox">
					<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
						<span><?php _e( 'General Settings', 'cartflows' ); ?></span>
					</h2>
					<div class="inside">
						<div class="form-wrap">
							<?php

							do_action( 'cartflows_before_settings_fields', $settings );

							echo Cartflows_Admin_Fields::checkobox_field(
								array(
									'id'    => 'wcf_disallow_indexing',
									'name'  => '_cartflows_common[disallow_indexing]',
									'title' => __( 'Disallow search engines from indexing flows', 'cartflows' ),
									'value' => $settings['disallow_indexing'],
								)
							);
							echo Cartflows_Admin_Fields::flow_checkout_selection_field(
								array(
									'id'    => 'wcf_global_checkout',
									'name'  => '_cartflows_common[global_checkout]',
									'title' => __( 'Global Checkout', 'cartflows' ),
									'value' => $settings['global_checkout'],
								)
							);

							echo Cartflows_Admin_Fields::select_field(
								array(
									'id'          => 'wcf_default_page_builder',
									'name'        => '_cartflows_common[default_page_builder]',
									'title'       => __( 'Show Templates designed with', 'cartflows' ),
									'description' => __( 'CartFlows offers flow templates that can be imported in one click. These templates are available in few different page builders. Please choose your preferred page builder from the list so you will only see templates that are made using that page builder..', 'cartflows' ),
									'value'       => $settings['default_page_builder'],
									'options'     => array(
										'elementor'      => __( 'Elementor', 'cartflows' ),
										'beaver-builder' => __( 'Beaver Builder', 'cartflows' ),
										'divi'           => __( 'Divi', 'cartflows' ),
										'other'          => __( 'Other', 'cartflows' ),
									),
								)
							);

							do_action( 'cartflows_after_settings_fields', $settings );

							?>
						</div>
						<?php submit_button( __( 'Save Changes', 'cartflows' ), 'cartflows-common-setting-save-btn button-primary button', 'submit', false ); ?>
						<?php wp_nonce_field( 'cartflows-common-settings', 'cartflows-common-settings-nonce' ); ?>
					</div>
				</div>
			</div>
			<div class="postbox-container" id="postbox-container-1">
				<div id="side-sortables">

					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-book"></span>
							<span><?php esc_html_e( 'Knowledge Base', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<p>
								<?php esc_html_e( 'Not sure how something works? Take a peek at the knowledge base and learn.', 'cartflows' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( 'https://cartflows.com/docs' ); ?>" target="_blank" rel="noopener"><?php _e( 'Visit Knowledge Base »', 'cartflows' ); ?></a>
							</p>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-groups"></span>
							<span><?php esc_html_e( 'Community', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<p>
								<?php esc_html_e( 'Join the community of super helpful CartFlows users. Say hello, ask questions, give feedback and help each other!', 'cartflows' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( 'https://www.facebook.com/groups/cartflows/' ); ?>" target="_blank" rel="noopener"><?php _e( 'Join Our Facebook Group »', 'cartflows' ); ?></a>
							</p>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-sos"></span>
							<span><?php esc_html_e( 'Five Star Support', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<p>
								<?php esc_html_e( 'Got a question? Get in touch with CartFlows developers. We\'re happy to help!', 'cartflows' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( 'https://cartflows.com/contact' ); ?>" target="_blank" rel="noopener"><?php _e( 'Submit a Ticket »', 'cartflows' ); ?></a>
							</p>
						</div>
					</div>

				</div>
			</div>
		</div>
		<!-- /post-body -->
		<br class="clear">
	</div>
</div>
</form>
