<?php
/**
 * Astra functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Constants
 */
define( 'ASTRA_THEME_VERSION', '3.7.3' );
define( 'ASTRA_THEME_SETTINGS', 'astra-settings' );
define( 'ASTRA_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'ASTRA_THEME_URI', trailingslashit( esc_url( get_template_directory_uri() ) ) );


/**
 * Minimum Version requirement of the Astra Pro addon.
 * This constant will be used to display the notice asking user to update the Astra addon to the version defined below.
 */
define( 'ASTRA_EXT_MIN_VER', '3.6.0' );

/**
 * Setup helper functions of Astra.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-theme-options.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-theme-strings.php';
require_once ASTRA_THEME_DIR . 'inc/core/common-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-icons.php';

/**
 * Update theme
 */
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-update.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/astra-update-functions.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-background-updater.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-pb-compatibility.php';


/**
 * Fonts Files
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-font-families.php';
if ( is_admin() ) {
	require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts-data.php';
}

require_once ASTRA_THEME_DIR . 'inc/lib/webfont/class-astra-webfont-loader.php';
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts.php';

require_once ASTRA_THEME_DIR . 'inc/dynamic-css/custom-menu-old-header.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/container-layouts.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/astra-icons.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/block-editor-compatibility.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-walker-page.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-enqueue-scripts.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-gutenberg-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/inline-on-mobile.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/content-background.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-dynamic-css.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-global-palette.php';

/**
 * Custom template tags for this theme.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-attr.php';
require_once ASTRA_THEME_DIR . 'inc/template-tags.php';

require_once ASTRA_THEME_DIR . 'inc/widgets.php';
require_once ASTRA_THEME_DIR . 'inc/core/theme-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/admin-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/sidebar-manager.php';

/**
 * Markup Functions
 */
require_once ASTRA_THEME_DIR . 'inc/markup-extras.php';
require_once ASTRA_THEME_DIR . 'inc/extras.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog-config.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog.php';
require_once ASTRA_THEME_DIR . 'inc/blog/single-blog.php';

/**
 * Markup Files
 */
require_once ASTRA_THEME_DIR . 'inc/template-parts.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-loop.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-mobile-header.php';

/**
 * Functions and definitions.
 */
require_once ASTRA_THEME_DIR . 'inc/class-astra-after-setup-theme.php';

// Required files.
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-helper.php';

require_once ASTRA_THEME_DIR . 'inc/schema/class-astra-schema.php';

if ( is_admin() ) {

	/**
	 * Admin Menu Settings
	 */
	require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-settings.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/astra-notices/class-astra-notices.php';

	/**
	 * Metabox additions.
	 */
	require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-boxes.php';
}

require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-box-operations.php';

/**
 * Customizer additions.
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-customizer.php';

/**
 * Astra Modules.
 */
require_once ASTRA_THEME_DIR . 'inc/modules/related-posts/class-astra-related-posts.php';

/**
 * Compatibility
 */
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gutenberg.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-jetpack.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/woocommerce/class-astra-woocommerce.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/edd/class-astra-edd.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/lifterlms/class-astra-lifterlms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/learndash/class-astra-learndash.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bb-ultimate-addon.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-contact-form-7.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-visual-composer.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-site-origin.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gravity-forms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bne-flyout.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-ubermeu.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-divi-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-amp.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-yoast-seo.php';
require_once ASTRA_THEME_DIR . 'inc/addons/transparent-header/class-astra-ext-transparent-header.php';
require_once ASTRA_THEME_DIR . 'inc/addons/breadcrumbs/class-astra-breadcrumbs.php';
require_once ASTRA_THEME_DIR . 'inc/addons/heading-colors/class-astra-heading-colors.php';
require_once ASTRA_THEME_DIR . 'inc/builder/class-astra-builder-loader.php';

// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor-pro.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-web-stories.php';
}

// Beaver Themer compatibility requires PHP 5.3 for anonymus functions.
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-themer.php';
}

require_once ASTRA_THEME_DIR . 'inc/core/markup/class-astra-markup.php';

/**
 * Load deprecated functions
 */
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-filters.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-functions.php';

// Testing new form record
add_action( 'elementor_pro/forms/trust-prospect-list-form', function( $record, $handler ) {
	//make sure its our form
	$form_name = $record->get_form_settings( 'trust-prospect-list-form' );
  
	// Replace MY_FORM_NAME with the name you gave your form
	if ( 'trust-prospect-list-form' !== $form_name ) {
		return;
	}
  
	$raw_fields = $record->get( 'fields' );
	$fields = [];
	foreach ( $raw_fields as $id => $field ) {
		$fields[ $id ] = $field['value'];
	}
  
	// Once these inputs had been retrieved from the form submission, store them here then makethe api call to the charity comission to search the 
	// database for the tags of the charity
	$inputtedName = $fields['email'];
	$inputtedCharityNumber = $fields['charityNumber'];
	$var = CopyDatabase($inputtedCharityNumber);
});
		   
// function callAPI($url, $header)
//     {
//         $response = wp_remote_get($url, $header);
//         //print_r($response);
//         if($response->errors['http_request_failed'])
//         {
//             callAPI($url, $header);
//         }
//         else{
//             $body = wp_remote_retrieve_body($response);
//             return $body;
//         }
//     }

// function CopyDatabase($inputtedCharityNumber)
//     {   
        
//         // TODO: Charity commission API call
        
//         // Setting headers and arguments for API request
//         $header = array(
//             'headers' => array(
//                 'Cache-Control' => 'no-cache',
//                 'Ocp-Apim-Subscription-Key' => '6ee601d9b98f4a7eb9a73a57e7e366d1',
//             )
//         );
        
//         // Creating the URL and adding the user inputted Charity Number
//         $url = 'https://api.charitycommission.gov.uk/register/api/allcharitydetails/';
//         $url = $url . $inputtedCharityNumber . "/0";
        
//         // Handling the response
//         //$response = wp_remote_get($url, $header);
        
//         //$body = wp_remote_retrieve_body($response);
        

//         $body = callAPI($url, $header);
//         //return $body;

//         //Filtering the response to just the "What" category
//         //$response = $response["body"];  
        
//         $response = json_decode($body, true);
//         gettype($response);
//         //return $response;
//         $response = $response["who_what_where"];
        
//         $validClassCodes = array();
//         foreach($response as $arr)
//         {
//             array_push($validClassCodes, $arr['classification_code']);
//         }

//         $validClassCodes = array_filter($validClassCodes, function($code){
//             //if(str_contains($code, '10' || '11'))
            
//             return ($code < 200);
            
//         });
//         //return $validClassCodes;

//         //Init db
//         global $wpdb;
//         //Query the db
//         $sqlQuery = "SELECT * FROM charities_classifications";
//         //Prepare the sql query for safe execution
//         $preparation = $wpdb->prepare($sqlQuery);
//         //Retrieves an entire sql result set from database
//         $result = $wpdb->get_results($preparation);
//         //Create new array
//         $res = array();
//         //Copy the current key and value of result to key and entry
//         foreach($result as $key => $entry)
//         {
//             //Every entry ID remove ';' from classification code
//             $res[$entry->id] = explode(';', $entry->class_codes);
//         }
//         //return $res;
//         //Call query class code function, put '$validClassCodes' in for first parameter
//         $finish = QueryClassCode($validClassCodes, $res);
//         //Return query class code function
//         //return $finish;

//         $end = QueryIDs($finish);
//         return $end;
//     }
    
// //Function for comparing the class code
// function QueryClassCode($classCodes, $arrayOfObj) 
//     {
//         //Create an array for valid IDs
//         $validIDs = array();
//         //Copy the current key and value to id and arr
//         foreach($arrayOfObj as $id => $arr)
//         {
//             //Create intersect array
//             $x = array();
//             //Intersect class code and array of objects
//             $x = array_intersect($classCodes, $arr);
//             //Check through size of array $x
//             if(count($x) > 0)
//             {
//                 //Push the matching IDs to the validIDs array
//                 array_push($validIDs, $id);
//             }
//         }
//         //Return the validIDs array
//         return $validIDs;
//     }

// //Change function so it does not query api to db
// function QueryIDs($arrayOfActiveIDs)
//     {
//         //Init db
//         global $wpdb;
//         //Query the db
//         $sqlQuery = "SELECT * FROM charities_classifications WHERE id IN (". implode(',', $arrayOfActiveIDs) .")";
//         //Prepare the sql query for safe execution
//         $preparation = $wpdb->prepare($sqlQuery);
//         //Retrieves an entire sql result set from database
//         $result = $wpdb->get_results($preparation);
//         //return $result;
//         //Create new array
//         $res = array();
//         //Converting into associative array
//         $res = json_decode(json_encode($result), true);
        
// 		return $res;
//         // echo "<table>";
//         // echo "<tr>";
//         // echo "<th>Name</th>";
//         // echo "<th>Number</th>";
//         // echo "<th>Contact info</th>";
//         // echo "<th>Expenditure</th>";
//         // echo "</tr>";
//         // for($i = 0; $i < count($res); $i++)
//         // {
//         //     echo "<tr>";
//         //     echo "<td>" .  $res[$i]['name'] . "</td>";
//         //     echo "<td>" .  $res[$i]['id'] . "</td>";
//         //     echo "<td>Email: " .  $res[$i]['email'] . 
//         //             "<br>Tel. Number: " .  $res[$i]['phone'] . 
//         //             "<br>Web: " .  $res[$i]['web'] . "</td>";
//         //     echo "<td>" . $res[$i]['expenditure'] . "</td>";
//         //     echo "</tr>";
//         // }
//         // echo "</table>";
//     }
		   
















