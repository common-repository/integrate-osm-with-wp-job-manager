=== Integrate OSM with WP Job Manager ===
Contributors: mpol
Tags: osm, map, openstreetmap, job manager,
Requires at least: 4.4
Tested up to: 6.6
Stable tag: 1.2.2
License: GPLv2 or later
Requires PHP: 7.0

Integrate an OpenStreetMap map into your job applications of WP Job Manager.

== Description ==

Integrate an OpenStreetMap map into your job applications of WP Job Manager.
This plugin will add a map to the job submit form where you can choose the location.
It will show this location on a map for the job application.

= Dependencies =

* [OSM](https://wordpress.org/plugins/osm/).
* [WP Job Manager](https://wordpress.org/plugins/wp-job-manager/).

= Support =

If you have a problem or a feature request, please post it on the plugin's support forum on [wordpress.org](https://wordpress.org/support/plugin/integrate-osm-with-wp-job-manager). I will do my best to respond as soon as possible.

If you send me an email, I will not reply. Please use the support forum.

= Translations =

Translations can be added very easily through [GlotPress](https://translate.wordpress.org/projects/wp-plugins/integrate-osm-with-wp-job-manager).
You can start translating strings there for your locale. They need to be validated though, so if there's no validator yet, and you want to apply for being validator (PTE), please post it on the support forum.
I will make a request on make/polyglots to have you added as validator for this plugin/locale.

= Compatibility =

This plugin is compatible with [ClassicPress](https://www.classicpress.net).

= Contributions =

This plugin is also available in [Codeberg](https://codeberg.org/cyclotouriste/integrate-osm-with-wp-job-manager).


== Installation ==

= Installation =

* Install the plugin through the admin page "Plugins".
* Alternatively, unpack and upload the contents of the zipfile to your '/wp-content/plugins/' directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Install and activate the plugins OSM and WP Job Manager.
* That's it.

= License =

The plugin itself is released under the GNU General Public License. A copy of this license can be found at the license homepage or in the osm-wpjobmanager.php file at the top.


== Frequently Asked Questions ==


== Screenshots ==

1. Test


== Changelog ==

= 1.2.2 =
* 2024-03-05
* Add plugin dependencies for WP 6.5.
* Remove admin notices for dependencies.

= 1.2.1 =
* 2023-12-21
* Fix a few warnings if plugin OSM is not enabled.

= 1.2.0 =
* 2023-11-18
* Add filter to catch location updates through Google Maps api.

= 1.1.0 =
* 2022-12-13
* Support OpenLayers 7.1.0 and OSM 6.0.1.
* Fix some integration issues.

= 1.0.0 =
* 2022-09-21
* Initial release
