<?php
/**
 * Custom Post Type registration for BDC Members.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Registers the hierarchical `members` custom post type.
 *
 * @since 1.0.0
 */
final class BDC_CPT {

	/**
	 * Transient key used to trigger a one-time rewrite flush.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private const FLUSH_TRANSIENT = 'bdc_members_flush_rewrite';

	/**
	 * Registers the members CPT on the `init` hook.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		$labels = array(
			'name'               => _x( 'Members', 'post type general name', 'bdc-members' ),
			'singular_name'      => _x( 'Member', 'post type singular name', 'bdc-members' ),
			'add_new'            => __( 'Add New', 'bdc-members' ),
			'add_new_item'       => __( 'Add New Member', 'bdc-members' ),
			'edit_item'          => __( 'Edit Member', 'bdc-members' ),
			'new_item'           => __( 'New Member', 'bdc-members' ),
			'view_item'          => __( 'View Member', 'bdc-members' ),
			'search_items'       => __( 'Search Members', 'bdc-members' ),
			'not_found'          => __( 'No members found', 'bdc-members' ),
			'not_found_in_trash' => __( 'No members found in Trash', 'bdc-members' ),
			'parent_item_colon'  => __( 'Parent Member:', 'bdc-members' ),
			'all_items'          => __( 'All Members', 'bdc-members' ),
			'menu_name'          => __( 'Members', 'bdc-members' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'hierarchical'       => true,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'members',
				'with_front' => false,
			),
			'supports'           => array(
				'title',
				'editor',
				'thumbnail',
				'page-attributes',
				'revisions',
				'custom-fields',
			),
			'capability_type'    => array( 'member', 'members' ),
			'map_meta_cap'       => true,
			'menu_icon'          => 'dashicons-groups',
		);

		$result = register_post_type( 'members', $args );

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'BDC Members: failed to register members CPT — ' . $result->get_error_message() );
		}
	}

	/**
	 * Sets a transient flag so that rewrite rules are flushed on the
	 * next `init` request. Called during plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function flush_rewrite_rules_on_activation(): void {
		set_transient( self::FLUSH_TRANSIENT, '1', 60 );
	}

	/**
	 * Checks for the flush transient on `init` and flushes rewrite rules
	 * if present. Deletes the transient afterward.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules(): void {
		if ( get_transient( self::FLUSH_TRANSIENT ) ) {
			flush_rewrite_rules();
			delete_transient( self::FLUSH_TRANSIENT );
		}
	}
}
