<?php
/**
 * Admin settings for BDC Members.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Adds a settings page under Settings > BDC Members where the
 * administrator can select which page serves as the members area.
 *
 * @since 1.0.0
 */
final class BDC_Admin {

	/**
	 * Option key for the members-area page ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private const OPTION_KEY = 'bdc_members_area_page_id';

	/**
	 * Registers the settings page and settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings(): void {
		add_options_page(
			__( 'BDC Members Settings', 'bdc-members' ),
			__( 'BDC Members', 'bdc-members' ),
			'manage_options',
			'bdc-members',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registers the setting, section, and field with the Settings API.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings_fields(): void {
		register_setting(
			'bdc_members_settings',
			self::OPTION_KEY,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);

		add_settings_section(
			'bdc_members_main',
			'',
			'__return_null',
			'bdc-members'
		);

		add_settings_field(
			self::OPTION_KEY,
			__( 'Members Area Page', 'bdc-members' ),
			array( $this, 'render_page_dropdown' ),
			'bdc-members',
			'bdc_members_main'
		);
	}

	/**
	 * Displays an admin notice if no members-area page has been configured.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_show_admin_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page_id = BDC_Access::get_members_area_page_id();

		if ( 0 === $page_id ) {
			echo '<div class="notice notice-warning"><p>';
			printf(
				/* translators: %s: URL to the settings page */
				wp_kses_post( __( 'BDC Members: No members-area page is configured. <a href="%s">Configure it now</a>.', 'bdc-members' ) ),
				esc_url( admin_url( 'options-general.php?page=bdc-members' ) )
			);
			echo '</p></div>';
		}
	}

	/**
	 * Renders the settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'bdc_members_settings' );
				do_settings_sections( 'bdc-members' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the page dropdown field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_page_dropdown(): void {
		$selected = (int) get_option( self::OPTION_KEY, 0 );

		wp_dropdown_pages(
			array(
				'name'             => self::OPTION_KEY,
				'selected'         => $selected,
				'show_option_none' => __( '— Select a page —', 'bdc-members' ),
				'option_none_value' => 0,
			)
		);

		echo '<p class="description">';
		echo esc_html__( 'Select the page that serves as the members area. A login form will be shown automatically when a visitor is not logged in.', 'bdc-members' );
		echo '</p>';
	}
}
