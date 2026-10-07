<?php
/**
 * Local development only: send all email to Mailpit (http://localhost:8025).
 *
 * Does nothing unless the CLO_SMTP_HOST environment variable is set, which
 * only docker-compose.yml does.
 *
 * @package clo-store
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'phpmailer_init',
	function ( $phpmailer ) {
		$host = getenv( 'CLO_SMTP_HOST' );
		if ( ! $host ) {
			return;
		}
		$phpmailer->isSMTP();
		$phpmailer->Host     = $host; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$phpmailer->Port     = (int) ( getenv( 'CLO_SMTP_PORT' ) ? getenv( 'CLO_SMTP_PORT' ) : 1025 ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$phpmailer->SMTPAuth    = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$phpmailer->SMTPAutoTLS = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	}
);

// On http://localhost WordPress sends from wordpress@localhost, which the mailer rejects.
add_filter(
	'wp_mail_from',
	function ( $from ) {
		if ( getenv( 'CLO_SMTP_HOST' ) && '@localhost' === substr( $from, -10 ) ) {
			return 'wordpress@localhost.test';
		}

		return $from;
	}
);
