=== IONOS Assistant ===
Contributors: 1and1, ionos, markoheijnen, pfefferle, gdespoulain
Tags: assistant, plugins, themes, recommendation
Requires at least: 3.5
Tested up to: 5.5.1
Stable tag: 6.3.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Install a WordPress Assistant to set up plugins & themes and help configure the website.

== Description ==

Install a WordPress Assistant to set up plugins & themes and help configure the website.

== Changelog ==

= 6.3.0 =
* Assets dynamically built download link
* Fix cache bug for plugins not available on WordPress.org
* Fix visual bug making the congratulations screen invisible
* Improve performance by removing redundant plugins update during setup

= 6.2.0 =
* Replace direct installation on thumbnail click with a theme overview
* Fix missing viewport tag that causes Chrome mobile view to bug
- Fix PHP 8 compatibility issue

= 6.1.2 =
* Add "Kadence" Theme & Plugin to recommendations

= 6.1.1 =
* Add "Real Cookie Banner" to plugin recommendations

= 6.1.0 =
- Update theme recommendations
- Temporarily warning due to PHP 8 incompatibility

= 6.0.2 =
- Fix "Close" button being hidden from screen

= 6.0.1 =
- Fix WordPress 5.6 installation bug

= 6.0.0 =
- Update Grunt builder
- Add Grunt package build job
- Add Jenkins deployment job
- Use generic prefixed names in code/files
- Remove cron to update deactivated plugins
- Move special "managed" code to config feature

= 5.7.0 =
* Remove cron-update-deactivated-plugins.php
* Add WordPress readme.txt with changelog

= 5.6.3 =
* Cleanup cron job to delete DB garbage from plugins/themes

= 5.6.2 =
* Remove "BeOnePage" from theme recommendations

= 5.6.1 =
* Add filter hook on theme auto-update hint

= 5.6.0 =
* Activate mandatory plugin & theme auto-updates
* Remove "a3 Lazy Load" from plugin recommendations
* On/off Option for styling on the login page

= 5.5.1 =
* Cleanup cron job to delete old transients

= 5.5.0 =
* Add "Extension", "BusinesStar", "NOSH STW" & "Refresh Blog" to theme recommendations
* Remove "Belise Lite", "Busiprof" & "Mazino" from theme recommendations
* Fix & update integration tests
* Remove Gutenberg dashboard panel
* Fix empty items in cache files

= 5.4.4 =
* Remove "Pure & Simple" from theme recommendations

= 5.4.3 =
* Fix market value for UK

= 5.4.2 =
* Rebranding to "IONOS by 1&1"

= 5.4.1 =
* Remove Dashboard links when not configured

= 5.4.0 =
* Initial (history truncated)
