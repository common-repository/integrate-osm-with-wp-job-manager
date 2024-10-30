/*
 * WordPress Plugin: Integrate OSM with WP Job Manager
 * Plugin URI: https://wordpress.org/plugins/integrate-osm-with-wp-job-manager/
 *
 * Copyright 2022 - 2024  Marcel Pol  (marcel@timelord.nl)
 */

jQuery( document ).ready( function( $ ) {

	if ( typeof ol === 'undefined' ) {
		return;
	}

	const mousePositionControl = new ol.control.MousePosition({
		coordinateFormat: ol.coordinate.createStringXY(4),
		projection: 'EPSG:4326',
		// comment the following two lines to have the mouse position
		// be placed within the map.
		className: 'custom-mouse-position',
		target: document.getElementById('mouse-position'),
	});

	var osm_default_lat = osmwpjob_js.osm_default_lat;
	var osm_default_lon = osmwpjob_js.osm_default_lon;
	var osm_default_zoom = osmwpjob_js.osm_default_zoom;

	const map = new ol.Map({
		controls: ol.control.defaults.defaults().extend([mousePositionControl]),
		layers: [
			new ol.layer.Tile({
				source: new ol.source.OSM(),
			}),
		],
		target: 'osmwpjob-map',
		view: new ol.View({
			center: ol.proj.fromLonLat([osm_default_lon, osm_default_lat]),
			zoom: osm_default_zoom,
		}),
	});

	const projectionSelect = document.getElementById('projection');
	if ( projectionSelect !== null ) {
		projectionSelect.addEventListener('change', function (event) {
			mousePositionControl.setProjection(event.target.value);
		});
	}

	const precisionInput = document.getElementById('precision');
	if ( precisionInput !== null ) {
		precisionInput.addEventListener('change', function (event) {
			const format = createStringXY(event.target.valueAsNumber);
			mousePositionControl.setCoordinateFormat(format);
		});
	}


	/* OSM plugin saves latlon. Openlayers uses lonlat. */
	var OSM_geo_data = jQuery( '#OSM_geo_data' ).val();
	if ( typeof( OSM_geo_data ) !== 'undefined' && OSM_geo_data.length > 0 ) {

		latlon = OSM_geo_data.split(',');
		var lat = latlon[0].trim();
		var lon = latlon[1].trim();
		var lonlat = [lon, lat];
		osmwpjob_addmarkerlayer( map, lonlat );

	}

	jQuery( map ).on( 'click', map, function(e) {

		mouse_position = document.getElementById('mouse-position');
		if ( mouse_position !== null ) {
			var mouse_value = mouse_position.innerText;
			lonlat = mouse_value.split(',');
			var lon = lonlat[0].trim();
			var lat = lonlat[1].trim();

			osmwpjob_addmarkerlayer( map, lonlat );

			var OSM_geo_data = lat + ', ' + lon;
			jQuery( '#OSM_geo_data' ).val( OSM_geo_data );
		}

	});


	function osmwpjob_addmarkerlayer( map, lonlat ) {

		/* delete the layer with an old marker */
		var layers = map.getLayers().getArray();
		if (layers.length > 1) {
			map.removeLayer(layers[1]);
		}

		var iconurl = osmwpjob_js.iconurl;
		var iconFeature = new ol.Feature({
			geometry: new ol.geom.Point(
				ol.proj.fromLonLat( lonlat ) ),
				/* ol.proj.transform( lonlat, "EPSG:4326", "EPSG:3857" ) ), */
		});
		var iconStyle = new ol.style.Style({
			image: new ol.style.Icon(({
				anchor: [0.5, 0.5],
				anchorOrigin: 'bottom-left',
				anchorYUnits: 'pixels',
				opacity: 0.9,
				src: iconurl,
			})),
		});
		iconFeature.setStyle(iconStyle);

		var vectorMarkerSource = new ol.source.Vector({
			features: [iconFeature],
		});

		var vectorMarkerLayer = new ol.layer.Vector({
			source: vectorMarkerSource,
			zIndex: 92,
		});

		map.addLayer(vectorMarkerLayer);

	}

});
