<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

class Ionos_Assistant_Modify_Settings_Page {

	public function __construct() {
		if ( Ionos_Assistant_Config::get( 'domain_settings', 'features' ) ) {
			add_action( 'admin_head', array( $this, 'change_home_description' ) );
		}
	}

	public function change_home_description() {
		global $pagenow;

		$cp_application_link = Ionos_Assistant_Config::get(
			'control_panel_applications_{market}',
			'links'
		);

		if ( is_admin() && $pagenow == 'options-general.php' && $cp_application_link ) {

			$websiteUrlDescription = sprintf(
				__( 'Website-Url-Description', 'ionos-assistant' ),
				$cp_application_link,
				Ionos_Assistant_Branding::get_brand_name()
			);

			?>
			<style type="text/css">
				#home-description {
					display: none;
				}
			</style>
			<script type="text/javascript">
				(function ($) {
					$(document).ready(function () {
						$('#siteurl').parent().append('<p class="description"><?php echo addslashes( $websiteUrlDescription ); ?></p>');
						$('#home').parent().append('<p class="description"><?php echo addslashes( $websiteUrlDescription ); ?></p>');
						$('.update-nag').hide();
					});
				})(jQuery);
			</script>
			<?php
		}
	}
}

new Ionos_Assistant_Modify_Settings_Page();
