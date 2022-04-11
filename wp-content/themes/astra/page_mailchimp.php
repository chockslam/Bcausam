<?php
/**
 * 
 * Template Name: Page Mailchimp
 * 
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

		<?php 
			require 'mandrill-transactional.php';
			require 'db-classcode-query.php';
		
			$test_user = array(
				"email" => "no.reply@bcausam.co.uk",
				"charityNumber" => "1088281",
			);
			print_r($test_user);
		
			// Once these inputs had been retrieved from the form submission, store them here then makethe api call to the charity comission to search the 
			// database for the tags of the charity
			$inputtedName = $test_user['email'];
			$inputtedCharityNumber = $test_user['charityNumber'];
			$var = CopyDatabase($inputtedCharityNumber);
			
			// MailChimp monkey
			if (!test_connection()) {
				echo "Failed to connect to MailChimp";
			}

			
			$funderlist_short = array_slice($var, 0, 5);

			$mc_csv = build_csv($var);
			// print_r($mc_csv);

			
			$mc_content = build_content($inputtedName, $funderlist_short, $mc_csv);
			// print_r($mc_content);

			$res = post_message($mc_content);

			print_r($res);
		?>

		<?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
