/**
 * End-to-end tests for the members-area page.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

const { test, expect } = require( '@playwright/test' );

test.describe( 'Members Area Page', () => {
	test( 'logged-out user sees login form', async ( { page } ) => {
		await page.goto( '/members-area/' );
		await expect(
			page.locator( '#loginform, .bdc-members-login form' )
		).toBeVisible();
	} );

	test( 'bdc_members user sees page content after login', async ( {
		page,
	} ) => {
		await page.goto( '/members-area/' );

		// Fill in the login form.
		await page.fill( '#user_login', 'bdcmember' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );

		// Should land back on members-area with content visible.
		await page.waitForURL( /members-area/ );
		await expect( page.locator( 'body' ) ).toContainText(
			'Welcome to the members area'
		);
	} );

	test( 'subscriber sees permission denied message', async ( { page } ) => {
		// Log in as subscriber via wp-login.php.
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'testsubscriber' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		// Visit members-area.
		await page.goto( '/members-area/' );
		await expect( page.locator( 'body' ) ).toContainText(
			'You do not have permission'
		);
	} );

	test( 'failed login shows error message', async ( { page } ) => {
		await page.goto( '/members-area/' );

		await page.fill( '#user_login', 'nonexistent' );
		await page.fill( '#user_pass', 'wrongpassword' );
		await page.click( '#wp-submit' );

		// Should redirect back with error.
		await page.waitForLoadState( 'networkidle' );

		// The error may show on the members-area page or wp-login.php.
		const url = page.url();
		if ( url.includes( 'members-area' ) ) {
			await expect( page.locator( 'body' ) ).toContainText(
				'Login failed'
			);
		} else {
			// WordPress default login error page is also acceptable.
			expect( url ).toContain( 'wp-login.php' );
		}
	} );
} );
