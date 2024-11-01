<?php
/**
 * Plugin Name: Simple Export Page
 * Plugin URI: http://wordpress.org/plugins/hello-dolly/
 * Description: Easily export one or more pages.
 * Author: Ervan Nur Adhitiya
 * Version: 1.1.0
 * Author URI: https://duosweb.com/about/
 *
 * @package SimpleExportPage
 */

defined( 'ABSPATH' ) || exit;

// get all custom post types.
$post_types = array_diff_assoc(
	get_post_types(
		array(
			'show_ui' => true,
		)
	),
	array(
		'attachment' => 'attachment',
		'wp_block'   => 'wp_block',
	)
);

/**
 * Register Export action for page.
 *
 * @param  array $bulk_actions Array of bolk action.
 * @return array               Array of bolk action.
 */
function duosep_register_export_bulk_action( $bulk_actions ) {
	$bulk_actions['duosep_export'] = __( 'Export', 'simple_export_page' );
	return $bulk_actions;
}
foreach ( $post_types as $post_type ) {
	add_filter( 'bulk_actions-edit-' . $post_type, 'duosep_register_export_bulk_action' );
}

/**
 * Export action handler.
 *
 * @param  string $redirect_to Redirect URI.
 * @param  string $doaction    Action.
 * @param  array  $post_ids    Array of post ids.
 * @return string              Redirect URI.
 */
function duosep_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
	if ( 'duosep_export' !== $doaction ) {
		return $redirect_to;
	}

	if ( ! is_user_logged_in() || ! current_user_can( 'export' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to export the content of this site.', 'simple_export_page' ) );
	}

	if ( ! defined( 'WXR_VERSION' ) ) {
		define( 'WXR_VERSION', '1.2' );
	}

	include_once dirname( __FILE__ ) . '/class-duosep-export.php';

	$duosep_export = new Duosep_Export();
	$duosep_export->export_page( $post_ids );
	die();
}
foreach ( $post_types as $post_type ) {
	add_filter( 'handle_bulk_actions-edit-' . $post_type, 'duosep_bulk_action_handler', 10, 3 );
}
