<?php
/*
Plugin Name: Easy Digital Downloads - Hide Download
Plugin URI: http://sumobi.com/shop/hide-download/
Description: Prevents a download appearing on the custom post type archive page or [downloads] listing.
Version: 1.0
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/


if ( !class_exists( 'EDD_Hide_Download' ) ) {

	class EDD_Hide_Download {

		function __construct() {
			add_action( 'init', array( $this, 'textdomain' ) );
			add_action( 'edd_meta_box_fields', array( $this, 'add_metabox' ), 10 );
			add_action( 'edd_metabox_fields_save', array( $this, 'save_metabox' ) );
			add_action( 'pre_get_posts',  array( $this, 'pre_get_posts' ) );
			add_filter( 'edd_downloads_query', array( $this, 'shortcode_query' ) );
		}

		/**
		 * Constants
		 *
		 * @since 1.0
		*/
		function constants() {
			if ( !defined( 'EDD_HD_VERSION' ) )
				define( 'EDD_HD_VERSION', '1.0' );
		}

		/**
		 * Internationalization
		 *
		 * @since 1.0
		 */
		function textdomain() {
			load_plugin_textdomain( 'edd-hd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add Metabox
		 *
		 * @since 1.0
		*/
		function add_metabox( $post_id ) {
			$checked = (boolean) get_post_meta( $post_id, '_edd_hide_download', true );
		?>
			<p>
				<label for="edd_hide_download">
					<input type="checkbox" name="_edd_hide_download" id="edd_hide_download" value="1" <?php checked( true, $checked ); ?> />
					<?php printf( __( 'Hide %s', 'edd-hd' ), edd_get_label_singular() ); ?>
				</label>
			</p>
			
		<?php
		}

		/**
		 * Add to save function
		 *
		 * @since 1.0
		*/
		function save_metabox( $fields ) {
			$fields[] = '_edd_hide_download';
			return $fields;
		}
		

		/**
		 * Get array hidden downloads
		 *
		 * @since 1.0
		*/
		function get_hidden_downloads() {
			$args = array(
				'post_type' => 'download',
				'meta_key' => '_edd_hide_download',
			);

			$downloads = get_posts( $args );

			$hidden_downloads = array();

			foreach ($downloads as $download) {
				$hidden_downloads[] = $download->ID;
			}

			return $hidden_downloads;
		}

		/**
		 * Hook into shortcode query and modify
		 *
		 * @since 1.0
		*/
		function shortcode_query( $query ) {
			$query['post__not_in'] = $this->get_hidden_downloads();

			return $query;
		}

		/**
		 * Alter the main loop to hide download using pre_get_posts
		 * @since 1.0
		 */
		function pre_get_posts( $query ) {

			// bail if in the admin or we're not working with the main WP query
			if ( is_admin() || ! $query->is_main_query() )
				return;

			// modify the query to hide the following downloads IDs
			if( is_post_type_archive( 'download' ) ) {
				$query->set( 'post__not_in', $this->get_hidden_downloads() ); 
			}

		}
	}
}

$EDD_Hide_Download = new EDD_Hide_Download();