<?php 

/*
Copyright (c) 2005-2011 Dylan Kuhn

This program is free software; you can redistribute it
and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation;
either version 2 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE. See the GNU General Public License for more
details.
*/

/**
 * The Geo Mashup Custom class.
 */
if ( !class_exists( 'GeoMashupCustom' ) ) {
class GeoMashupCustom {
	var $files = array();
	var $found_files;
	var $dir_path;
	var $url_path;
	var $basename;
	var $warnings = '';

	/**
	 * PHP4 Constructor
	 */
	function GeoMashupCustom() {

		// Initialize members
		$this->dir_path = dirname( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );
		$dir_name = substr( $this->basename, 0, strpos( $this->basename, '/' ) );
		$this->url_path = path_join( plugins_url(), $dir_name );
		
		// Inventory custom files
		if ( $dir_handle = @ opendir( $this->dir_path ) ) {
			$self_file = basename( __FILE__ );
			while ( ( $custom_file = readdir( $dir_handle ) ) !== false ) {
				if ( $self_file != $custom_file && !strpos( $custom_file, '-sample' ) && !is_dir( $custom_file ) ) {
					$this->files[$custom_file] = trailingslashit( $this->url_path ) . $custom_file;
				}
			}
		}

		// Output messages
		add_action( 'init', array( $this, 'action_init' ) );
	}

	function action_init() {
		add_filter( 'geo_mashup_locations_json_object', array( $this, 'filter_geo_mashup_locations_json_object' ), 10, 2 );
	}

	// Add BetaReno data to map objects
	function filter_geo_mashup_locations_json_object( $properties, $object ) {
		if ( 'post' == $properties['object_name'] ) {
			$properties['status'] = 'proposal';
			$properties['when'] = get_post_meta( $object->object_id, 'when', true );
			if ( $properties['when'] ) {
				$when_time = strtotime( $properties['when'] );
				if ( $when_time ) {
					if ( $when_time < time() ) {
						// Need voting data to determine feature/flop
						$properties['status'] = 'feature';
					} else {
						$properties['status'] = 'plan';
					}
				}
			}
		}
		return $properties;
	}

	/**
	 * Get the URL of a custom file if it exists.
	 *
	 * @param string $file The custom file to check for.
	 * @return URL or false if the file is not found.
	 */
	function file_url( $file ) {
		$url = false;
		if ( isset( $this->files[$file] ) ) {
			$url = $this->files[$file];
		}
		return $url;
	}

} // end Geo Mashup Custom class

// Instantiate
$geo_mashup_custom = new GeoMashupCustom();

} // end if Geo Mashup Custom class exists
