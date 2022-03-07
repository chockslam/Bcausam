<?php
/*
Plugin Name: Branda
Plugin URI: https://wpmudev.com/project/ultimate-branding/
Description: A complete white label and branding solution for multisite. Login images, favicons, remove WordPress links and branding, and much more.
Author: WPMU DEV
Version: 3.4.4
Author URI: https://wpmudev.com/
Requires PHP: 5.6
Text_domain: ub


Copyright 2009-2019 Incsub (https://incsub.com)

Lead Developer - Marcin Pietrzak (Incsub)

Contributors - Sam Najian (Incsub), Ve Bailovity (Incsub), Barry (Incsub), Andrew Billits, Ulrich Sossou, Marko Miljus, Joseph Fusco (Incsub), Calum Brash (Incsub), Joel James ( Incsub)

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License (Version 2 - GPLv2) as published
by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc., 51 Franklin
St, Fifth Floor, Boston, MA 02110-1301 USA

 */

/**
 * Branda Version
 */
$ub_version = null;

require_once 'build.php';

// Include the configuration library.
require_once dirname( __FILE__ ) . '/etc/config.php';
// Include the functions library.
if ( file_exists( 'inc/deprecated-functions.php' ) ) {
	require_once 'inc/deprecated-functions.php';
}
require_once 'inc/functions.php';
require_once 'inc/class-branda-helper.php';

// Set up my location.
set_ultimate_branding( __FILE__ );

/**
 * Set ub Version.
 */
function branda_set_ub_version() {
	global $ub_version;
	$data       = get_plugin_data( __FILE__, false, false );
	$ub_version = $data['Version'];
}

if ( ! defined( 'BRANDA_SUI_VERSION' ) ) {
	define( 'BRANDA_SUI_VERSION', '2.9.6' );
}

$dash_notification_path = dirname( __FILE__ ) . '/external/dash-notice/wpmudev-dash-notification.php';
if ( file_exists( $dash_notification_path ) ) {
	include_once $dash_notification_path;
}

register_activation_hook( __FILE__, 'branda_register_activation_hook' );
register_deactivation_hook( __FILE__, 'branda_register_deactivation_hook' );
register_uninstall_hook( __FILE__, 'branda_register_uninstall_hook' );

