/**
 * Playwright configuration for BDC Members end-to-end tests.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: './tests/e2e',
	timeout: 30000,
	retries: 1,
	use: {
		baseURL: 'http://localhost:8889',
		headless: true,
		viewport: { width: 1280, height: 720 },
		actionTimeout: 10000,
	},
	globalSetup: './tests/e2e/global-setup.js',
} );
