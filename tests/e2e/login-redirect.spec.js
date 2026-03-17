/**
 * End-to-end tests for login redirect behaviour.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

const { test, expect } = require( '@playwright/test' );

test.describe( 'Login Redirect', () => {
	test( 'bdc_members user logging in via wp-login.php is redirected to members-area', async ( {
		page,
	} ) => {
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'bdcmember' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		expect( page.url() ).toContain( 'members-area' );
	} );

	test( 'administrator logging in via wp-login.php is redirected to dashboard', async ( {
		page,
	} ) => {
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'admin' );
		await page.fill( '#user_pass', 'password' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		expect( page.url() ).toContain( 'wp-admin' );
	} );

	test( 'bdc_members user logging in via members-area login form stays on members-area', async ( {
		page,
	} ) => {
		await page.goto( '/members-area/' );
		await page.fill( '#user_login', 'bdcmember' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		expect( page.url() ).toContain( 'members-area' );
	} );
} );
