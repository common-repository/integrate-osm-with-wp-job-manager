<?php
/*
Plugin Name: Integrate OSM with WP Job Manager
Plugin URI: https://wordpress.org/plugins/integrate-osm-with-wp-job-manager/
Description: Integrate an OpenStreetMap map into your job applications of WP Job Manager.
Version: 1.2.2
Author: Marcel Pol
Author URI: https://timelord.nl
License: GPLv2 or later
Text Domain: integrate-osm-with-wp-job-manager
Domain Path: /lang/
Requires Plugins: osm, wp-job-manager


Copyright 2022 - 2024  Marcel Pol  (marcel@timelord.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
 * Todo:
 * - Add more customization, like width/height and shortcode parameters.
 *
 */




// Plugin Version
define('OSMWPJOB_VER', '1.2.2');
define('OSMWPJOB_FOLDER', plugin_basename(dirname(__FILE__)));
define('OSMWPJOB_DIR', WP_PLUGIN_DIR . '/' . OSMWPJOB_FOLDER);
define('OSMWPJOB_URL', plugins_url( '/', __FILE__ ));
define('OSMWPJOB_OSM_URL', plugins_url() . '/osm');


/*
 * Add our form field to submit job form.
 *
 * @param array $job_fields List of fields for the form.
 * @return array List of fields for the form.
 *
 * @since 1.0.0
 */
function osmwpjob_submit_job_form_fields( $job_fields ) {

	$active_plugins = get_option('active_plugins');
	if ( ! in_array( 'osm/osm.php', $active_plugins, true ) ) {
		return $job_fields;
	}

	$job_fields['job']['OSM_geo_data'] = array(
			'label'       => esc_attr__( 'Location on the map', 'integrate-osm-with-wp-job-manager' ),
			'type'        => 'text',
			'required'    => false,
			'priority'    => 7,
		);

	return $job_fields;

}
add_filter( 'submit_job_form_fields', 'osmwpjob_submit_job_form_fields' );


/*
 * Add map to form to the end of the submit job form.
 *
 * @since 1.0.0
 */
function osmwpjob_submit_job_form_job_fields_end() {

	$active_plugins = get_option('active_plugins');
	if ( ! in_array( 'osm/osm.php', $active_plugins, true ) ) {
		return;
	}

	if ( defined( 'Osm_OL_3_LibraryLocation' ) ) {
		wp_enqueue_script( 'Osm_OL_3', Osm_OL_3_LibraryLocation, array(), false, true ); // versioned in osm plugin in url.
	} else {
		//echo 'oops 1'; // debug in case the name changes.
	}
	if ( defined( 'Osm_OL_3_CSS' ) ) {
		wp_enqueue_style( 'Osm_OL_3_CSS', Osm_OL_3_CSS ); // versioned in osm plugin in url.
	} else {
		//echo 'oops 2'; // debug in case the name changes.
	}
	?>

	<div class="osmwpjob-osm-submit">
		<?php esc_attr_e( 'Choose the correct location on the map.', 'integrate-osm-with-wp-job-manager' ); ?>

		<div id="osmwpjob-map" class="map" style="width:400px;height:400px;"></div>
		<div id="mouse-position"></div>
		<?php /* Is already part of a form element. */ ?>
		<label style="display:none;" for="projection">Projection </label>
		<select style="display:none;" id="projection">
			<option value="EPSG:4326">EPSG:4326</option>
			<option value="EPSG:3857">EPSG:3857</option>
		</select>
		<label style="display:none;" for="precision">Precision</label>
		<input style="display:none;" id="precision" type="number" min="0" max="12" value="4"/>

	</div>
	<?php

}
add_action( 'submit_job_form_job_fields_end', 'osmwpjob_submit_job_form_job_fields_end' );


/*
 * Save data from Job Manager field to the post meta of OSM.
 *
 * @param int $job_id ID of the submitted job.
 * @param array $values list of values from the form fields, currently unused here.
 *
 * @since 1.0.0
 */
function osmwpjob_job_manager_update_job_data( $job_id, $values ) {

	$_osm_geo_data = sanitize_text_field( get_post_meta( $job_id, '_OSM_geo_data', true ) ); // job manager plugin meta in latlon
	update_post_meta( $job_id, 'OSM_geo_data', sanitize_text_field( $_osm_geo_data ) ); // osm plugin meta in latlon
	update_post_meta( $job_id, 'OSM_Marker_01_LatLon', sanitize_text_field( $_osm_geo_data ) ); // osm plugin meta in latlon
	update_post_meta( $job_id, 'OSM_Marker_01_Icon', 'mic_red_pinother_02.png' ); // osm plugin icon name, is really needed for single marker.

}
add_action( 'job_manager_update_job_data', 'osmwpjob_job_manager_update_job_data', 10, 2 );


/*
 * Enqueue JavaScript on frontend.
 *
 * @since 1.0.0
 */
function osmwpjob_wp_enqueue_scripts() {

	wp_register_script( 'osmwpjob_js', OSMWPJOB_URL . 'osm-wpjobmanager.js', array( 'jquery' ), OSMWPJOB_VER, true );

	$osm_default_lat  = get_option( 'osm_default_lat', '43.758629' );
	$osm_default_lon  = get_option( 'osm_default_lon', '6.924225' );
	$osm_default_zoom = (int) get_option( 'osm_default_zoom', 11 );

	$data_to_be_passed = array(
		'osm_default_lat'  => sanitize_text_field( $osm_default_lat ),
		'osm_default_lon'  => sanitize_text_field( $osm_default_lon ),
		'osm_default_zoom' => (int) $osm_default_zoom,
		'iconurl'          => OSMWPJOB_OSM_URL . '/icons/mic_red_pinother_02.png',
	);
	wp_localize_script( 'osmwpjob_js', 'osmwpjob_js', $data_to_be_passed );

	wp_enqueue_script( 'osmwpjob_js' );

}
add_action('wp_enqueue_scripts', 'osmwpjob_wp_enqueue_scripts');


/*
 * Add map to WP Job Manager post on frontend.
 *
 * @since 1.0.0
 */
function osmwpjob_the_content( $content ) {

	$active_plugins = get_option('active_plugins');
	if ( ! in_array( 'osm/osm.php', $active_plugins, true ) ) {
		return $content;
	}

	$post_id = get_the_ID();
	$post_type = get_post_type();
	if ( $post_type !== 'job_listing' || is_admin() || ! is_singular() ) {
		return $content;
	}

	$osm_default_zoom = (int) get_option( 'osm_default_zoom', 11 );

	$content .= '
	<div class="osmwpjob-osm-map">
		';

	$osm_geo_data = sanitize_text_field( get_post_meta( $post_id, 'OSM_geo_data', true ) );
	$latlon = explode( ',', $osm_geo_data );
	if ( isset( $latlon[0] ) && isset( $latlon[1] ) ) {
		$lat = trim( $latlon[0] );
		$lon = trim( $latlon[1] );
		if ( strlen( $lat ) > 0 && strlen( $lon ) > 0 ) {
			// echo '[osm_map_v3 map_center="' . $OSM_geo_data . '" zoom="11" width="500" height="500" post_markers="1" map_border="thin solid #ea1410" ]';
			$content .= do_shortcode( '[osm_map_v3 map_center="' . sanitize_text_field( $osm_geo_data ) . '" zoom="' . (int) $osm_default_zoom . '" width="500" height="500" post_markers="1" map_border="thin solid #ea1410" ]' );
		}
	}

	$content .= '
	</div>
		';

	return $content;

}
add_filter( 'the_content', 'osmwpjob_the_content', 12 );



/*
 * Updates OSM location data when submitting a job with the Google Maps api enabled (paid option).
 *
 * @param int   $job_id
 * @param array $values
 *
 * @since 1.2.0
 */
function osmwpjob_update_location_data( $job_id, $values ) {

	if ( apply_filters( 'job_manager_geolocation_enabled', true ) && isset( $values['job']['job_location'] ) && class_exists('WP_Job_Manager_Geocode') ) {
		$address = WP_Job_Manager_Geocode::get_location_data( $values['job']['job_location'] );

		if ( is_array( $address ) && isset( $address['lat'] ) && isset( $address['long'] ) ) {
			$lat = $address['lat'];
			$lon = $address['long'];
			$latlon = $lat . ', ' . $lon;

			update_post_meta( $job_id, 'OSM_geo_data', sanitize_text_field( $latlon ) ); // osm plugin meta in latlon
			update_post_meta( $job_id, 'OSM_Marker_01_LatLon', sanitize_text_field( $latlon ) ); // osm plugin meta in latlon
			update_post_meta( $job_id, 'OSM_Marker_01_Icon', 'mic_red_pinother_02.png' ); // osm plugin icon name, is really needed for single marker.
		}
	}

}
add_action( 'job_manager_update_job_data', 'osmwpjob_update_location_data', 99, 2 );


/*
 * Changes OSM location data when editing a job with the Google Maps api enabled (paid option).
 *
 * @param  int    $job_id
 * @param  string $new_location
 *
 * @since 1.2.0
 */
function osmwpjob_change_location_data( $job_id, $new_location ) {

	if ( apply_filters( 'job_manager_geolocation_enabled', true ) && class_exists('WP_Job_Manager_Geocode') ) {
		$address = WP_Job_Manager_Geocode::get_location_data( $new_location );

		if ( is_array( $address ) && isset( $address['lat'] ) && isset( $address['long'] ) ) {
			$lat = $address['lat'];
			$lon = $address['long'];
			$latlon = $lat . ', ' . $lon;

			update_post_meta( $job_id, 'OSM_geo_data', sanitize_text_field( $latlon ) ); // osm plugin meta in latlon
			update_post_meta( $job_id, 'OSM_Marker_01_LatLon', sanitize_text_field( $latlon ) ); // osm plugin meta in latlon
			update_post_meta( $job_id, 'OSM_Marker_01_Icon', 'mic_red_pinother_02.png' ); // osm plugin icon name, is really needed for single marker.
		}
	}

}
add_action( 'job_manager_job_location_edited', 'osmwpjob_change_location_data', 99, 2 );
