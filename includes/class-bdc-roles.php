<?php
/**
 * Role and capability management for BDC Members.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Handles creation of the bdc_members role and mapping of custom
 * capabilities to editorial roles.
 *
 * @since 1.0.0
 */
final class BDC_Roles {

	/**
	 * Internal version for capability schema. Bump this when ROLE_CAPS changes
	 * to force re-application of capabilities on the next request.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private const CAPS_DB_VERSION = 3;

	/**
	 * Primitive capability used for front-end access gating.
	 *
	 * `read_member` is a meta capability (mapped from `read_post` by
	 * `map_meta_cap`) and requires a post ID when checked. This dedicated
	 * primitive cap can be checked without a post ID to gate access to
	 * the members area and members CPT archive/single views.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const ACCESS_CAP = 'bdc_access_members';

	/**
	 * Capabilities mapped per role.
	 *
	 * With `map_meta_cap => true` and `capability_type => array( 'member', 'members' )`,
	 * WordPress generates meta-cap mappings that require the full set of primitive
	 * capabilities below. Missing any of these causes "item doesn't exist" errors
	 * in the admin or failed `current_user_can()` checks on the front end.
	 *
	 * `bdc_access_members` is a custom primitive cap used only for front-end
	 * access gating — it is NOT part of the CPT meta-cap chain.
	 *
	 * @since 1.0.0
	 * @var array<string, string[]>
	 */
	private const ROLE_CAPS = array(
		'administrator' => array(
			'bdc_access_members',
			'read_member',
			'read_private_members',
			'edit_members',
			'edit_others_members',
			'edit_published_members',
			'edit_private_members',
			'publish_members',
			'delete_members',
			'delete_others_members',
			'delete_published_members',
			'delete_private_members',
			'create_members',
		),
		'editor'        => array(
			'bdc_access_members',
			'read_member',
			'read_private_members',
			'edit_members',
			'edit_others_members',
			'edit_published_members',
			'edit_private_members',
			'publish_members',
			'delete_members',
			'delete_others_members',
			'delete_published_members',
			'delete_private_members',
			'create_members',
		),
		'author'        => array(
			'bdc_access_members',
			'read_member',
			'read_private_members',
			'edit_members',
			'edit_published_members',
			'publish_members',
			'delete_members',
			'delete_published_members',
			'create_members',
		),
		'bdc_members'   => array(
			'bdc_access_members',
			'read_member',
		),
	);

	/**
	 * Run on plugin activation. Creates the bdc_members role and maps
	 * custom capabilities to editorial roles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function activate(): void {
		$existing = get_role( 'bdc_members' );

		if ( null === $existing ) {
			add_role(
				'bdc_members',
				__( 'BDC Member', 'bdc-members' ),
				array( 'read' => true )
			);
		}

		foreach ( self::ROLE_CAPS as $role_name => $caps ) {
			$role = get_role( $role_name );

			if ( null === $role ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'BDC Members: expected role "%s" not found during activation.', $role_name ) );
				continue;
			}

			foreach ( $caps as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Ensures roles and capabilities are in place on every request.
	 *
	 * Uses a version-based option so it only writes to the database once
	 * per plugin version, rather than on every page load.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function maybe_activate(): void {
		if ( (int) get_option( 'bdc_members_caps_version' ) === self::CAPS_DB_VERSION ) {
			return;
		}

		self::activate();
		update_option( 'bdc_members_caps_version', self::CAPS_DB_VERSION );
	}

	/**
	 * Returns the list of role slugs that are allowed to view members content.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public static function get_allowed_roles(): array {
		return array( 'administrator', 'editor', 'author', 'bdc_members' );
	}

	/**
	 * Checks whether a user has access to members content.
	 *
	 * Prefers capability checks over role name checks for compatibility
	 * with multi-role plugins.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id Optional. User ID to check. Defaults to current user.
	 * @return bool
	 */
	public static function user_has_members_access( int $user_id = 0 ): bool {
		if ( 0 === $user_id ) {
			return current_user_can( self::ACCESS_CAP );
		}

		return user_can( $user_id, self::ACCESS_CAP );
	}
}
