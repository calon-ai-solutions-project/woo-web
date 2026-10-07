<?php
/**
 * Wholesale enquiry and contact forms: [clo_form id="wholesale"] and [clo_form id="contact"].
 *
 * Each message is emailed to the shared inbox (clo_enquiry_inbox() in the
 * theme's inc/site-config.php, or the site admin email until that is set),
 * a copy goes to the sender, and the message is kept under Enquiries in the
 * dashboard so nothing is lost if an email goes astray.
 *
 * Spam protection without third-party services: a hidden field that only
 * bots fill in, a minimum time to fill in the form, and a limit of a few
 * messages per visitor every ten minutes.
 *
 * @package clo-store
 */

defined( 'ABSPATH' ) || exit;

const CLO_ENQUIRY_TYPE      = 'clo_enquiry';
const CLO_FORM_MAX_FILE     = 5 * MB_IN_BYTES;
const CLO_FORM_MIN_SECONDS  = 3;
const CLO_FORM_RATE_LIMIT   = 5;
const CLO_FORM_RATE_WINDOW  = 10 * MINUTE_IN_SECONDS;
const CLO_FORM_UPLOAD_DIR   = 'clo-enquiries';

/**
 * The forms. Field keys are also the names used in emails and the dashboard.
 *
 * @return array<string,array<string,mixed>>
 */
function clo_forms() {
	$consent = sprintf(
		'I agree to %1$s keeping these details to reply to me, as set out in the <a href="%2$s">Privacy Policy</a>.',
		esc_html( get_bloginfo( 'name' ) ),
		esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacy-policy/' ) )
	);

	return array(
		'wholesale' => array(
			'title'          => 'Wholesale enquiry',
			'submit'         => 'Send enquiry',
			'notify_subject' => 'Wholesale enquiry from %s',
			'reply_subject'  => 'We have your wholesale enquiry',
			'reply_intro'    => 'Thank you for your wholesale enquiry. We will come back to you %s.',
			'success'        => 'Thank you. We have your enquiry and will come back to you %s. A copy has been sent to your email address.',
			'fields'         => array(
				'name'          => array(
					'label'        => 'Name',
					'type'         => 'text',
					'required'     => true,
					'autocomplete' => 'name',
				),
				'business'      => array(
					'label'        => 'Business name',
					'type'         => 'text',
					'autocomplete' => 'organization',
				),
				'email'         => array(
					'label'        => 'Email',
					'type'         => 'email',
					'required'     => true,
					'autocomplete' => 'email',
					'error'        => 'Please enter your email address.',
				),
				'phone'         => array(
					'label'        => 'Phone',
					'type'         => 'tel',
					'autocomplete' => 'tel',
				),
				'business_type' => array(
					'label'    => 'Business type',
					'type'     => 'select',
					'required' => true,
					'options'  => array( 'Reseller', 'Market trader', 'Online seller', 'Shop', 'Other' ),
				),
				'products'      => array(
					'label'    => 'Categories or products wanted',
					'type'     => 'textarea',
					'rows'     => 3,
					'required' => true,
					'error'    => 'Please tell us which categories or products you want.',
				),
				'quantity'      => array(
					'label'       => 'Quantity needed',
					'type'        => 'text',
					'placeholder' => 'For example 200 units, or 2 pallets',
				),
				'budget'        => array(
					'label' => 'Budget range',
					'type'  => 'text',
				),
				'frequency'     => array(
					'label'   => 'How often',
					'type'    => 'select',
					'options' => array( 'One-off', 'Monthly', 'Weekly' ),
				),
				'postcode'      => array(
					'label'        => 'Delivery postcode',
					'type'         => 'postcode',
					'autocomplete' => 'postal-code',
				),
				'message'       => array(
					'label' => 'Message',
					'type'  => 'textarea',
					'rows'  => 5,
				),
				'attachment'    => array(
					'label' => 'File',
					'type'  => 'file',
					'help'  => 'Optional: a product list or photos. PDF, image, spreadsheet or Word file, up to 5 MB.',
				),
				'consent'       => array(
					'label'    => $consent,
					'type'     => 'checkbox',
					'required' => true,
				),
			),
		),
		'contact'   => array(
			'title'          => 'Contact message',
			'submit'         => 'Send message',
			'notify_subject' => 'Message from %s',
			'reply_subject'  => 'We have your message',
			'reply_intro'    => 'Thank you for getting in touch. We will reply %s.',
			'success'        => 'Thank you. We have your message and will reply %s. A copy has been sent to your email address.',
			'fields'         => array(
				'name'    => array(
					'label'        => 'Name',
					'type'         => 'text',
					'required'     => true,
					'autocomplete' => 'name',
				),
				'email'   => array(
					'label'        => 'Email',
					'type'         => 'email',
					'required'     => true,
					'autocomplete' => 'email',
					'error'        => 'Please enter your email address.',
				),
				'phone'   => array(
					'label'        => 'Phone',
					'type'         => 'tel',
					'autocomplete' => 'tel',
				),
				'order'   => array(
					'label' => 'Order number',
					'type'  => 'text',
					'help'  => 'If your message is about an order.',
				),
				'message' => array(
					'label'    => 'Message',
					'type'     => 'textarea',
					'rows'     => 6,
					'required' => true,
				),
				'consent' => array(
					'label'    => $consent,
					'type'     => 'checkbox',
					'required' => true,
				),
			),
		),
	);
}

/**
 * When we will reply, as shown to customers: "within one working day", using
 * the time set in site-config.php, or "as soon as we can" until it is set.
 *
 * @return string
 */
function clo_form_response_time() {
	$time = function_exists( 'clo_response_time' ) ? trim( clo_response_time() ) : '';

	return ( '' === $time || false !== strpos( $time, '[' ) ) ? 'as soon as we can' : 'within ' . $time;
}

/**
 * The address that receives messages.
 *
 * @return string
 */
function clo_form_inbox() {
	$inbox = function_exists( 'clo_enquiry_inbox' ) ? clo_enquiry_inbox() : '';

	return is_email( $inbox ) ? $inbox : get_option( 'admin_email' );
}

/**
 * Errors and submitted values for a form during this request.
 *
 * @param string     $id    Form ID.
 * @param array|null $state New state to store.
 * @return array{errors:array<string,string>,values:array<string,string>}
 */
function clo_form_state( $id, $state = null ) {
	static $states = array();
	if ( null !== $state ) {
		$states[ $id ] = $state;
	}

	return isset( $states[ $id ] ) ? $states[ $id ] : array(
		'errors' => array(),
		'values' => array(),
	);
}

/**
 * A signed timestamp, so the form can tell how long a visitor took to fill it in.
 *
 * @return string
 */
function clo_form_token() {
	$time = (string) time();

	return $time . '.' . wp_hash( 'clo_form|' . $time );
}

/*
 * Handle a submitted form before the page is drawn.
 */
add_action( 'template_redirect', 'clo_handle_form' );

/**
 * Validate, store, email and redirect, or keep the errors for the page to show.
 */
function clo_handle_form() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Public form; see the spam notes at the top of this file.
	if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) || empty( $_POST['clo_form'] ) ) {
		return;
	}
	$id    = sanitize_key( wp_unslash( $_POST['clo_form'] ) );
	$forms = clo_forms();
	if ( ! isset( $forms[ $id ] ) ) {
		return;
	}
	$form     = $forms[ $id ];
	$page_url = get_permalink( get_queried_object_id() );
	$done_url = add_query_arg( 'sent', $id, $page_url ) . '#clo-form-' . $id;

	// Bots fill in the hidden field. Show them the thank-you page and do nothing.
	if ( ! empty( $_POST['clo_website'] ) ) {
		wp_safe_redirect( $done_url, 303 );
		exit;
	}

	$errors = array();
	$values = array();

	$token = isset( $_POST['clo_t'] ) ? sanitize_text_field( wp_unslash( $_POST['clo_t'] ) ) : '';
	$parts = explode( '.', $token );
	if ( 2 !== count( $parts ) || ! hash_equals( wp_hash( 'clo_form|' . $parts[0] ), $parts[1] ) || time() - (int) $parts[0] < CLO_FORM_MIN_SECONDS ) {
		$errors['_form'] = 'Sorry, that did not go through. Please check your details and send the form again.';
	}

	$ip_key = 'clo_form_rate_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$sent   = (int) get_transient( $ip_key );
	if ( $sent >= CLO_FORM_RATE_LIMIT ) {
		$errors['_form'] = 'You have sent several messages in the last few minutes. Please wait a little and try again, or email us.';
	}

	foreach ( $form['fields'] as $key => $field ) {
		if ( 'file' === $field['type'] ) {
			continue;
		}
		$raw = isset( $_POST[ 'clo_' . $key ] ) ? wp_unslash( $_POST[ 'clo_' . $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised by type below.
		$raw = is_array( $raw ) ? '' : (string) $raw;

		switch ( $field['type'] ) {
			case 'textarea':
				$value = mb_substr( sanitize_textarea_field( $raw ), 0, 5000 );
				break;
			case 'email':
				$value = sanitize_email( $raw );
				if ( '' !== trim( $raw ) && ! is_email( $value ) ) {
					$errors[ $key ] = 'Please enter a valid email address, like name@example.com.';
				}
				break;
			case 'select':
				$value = sanitize_text_field( $raw );
				if ( '' !== $value && ! in_array( $value, $field['options'], true ) ) {
					$value = '';
				}
				break;
			case 'checkbox':
				$value = '' !== $raw ? 'Yes' : '';
				break;
			case 'postcode':
				$value = strtoupper( mb_substr( sanitize_text_field( $raw ), 0, 10 ) );
				if ( '' !== $value && class_exists( 'WC_Validation' ) ) {
					if ( WC_Validation::is_postcode( $value, 'GB' ) ) {
						$value = wc_format_postcode( $value, 'GB' );
					} else {
						$errors[ $key ] = 'Please enter a full UK postcode, like SW1A 1AA.';
					}
				}
				break;
			default:
				$value = mb_substr( sanitize_text_field( $raw ), 0, 200 );
		}

		$values[ $key ] = $value;
		if ( ! empty( $field['required'] ) && '' === $value && ! isset( $errors[ $key ] ) ) {
			if ( ! empty( $field['error'] ) ) {
				$errors[ $key ] = $field['error'];
			} elseif ( 'checkbox' === $field['type'] ) {
				$errors[ $key ] = 'Please tick this box so we can reply to you.';
			} elseif ( 'select' === $field['type'] ) {
				$errors[ $key ] = sprintf( 'Please choose your %s.', strtolower( $field['label'] ) );
			} else {
				$errors[ $key ] = sprintf( 'Please enter your %s.', strtolower( $field['label'] ) );
			}
		}
	}

	// Optional file.
	$file = null;
	if ( isset( $form['fields']['attachment'] ) && ! empty( $_FILES['clo_attachment']['name'] ) && ! $errors ) {
		$file = clo_form_store_upload( $_FILES['clo_attachment'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Checked in clo_form_store_upload().
		if ( is_wp_error( $file ) ) {
			$errors['attachment'] = $file->get_error_message();
			$file                 = null;
		}
	}
	// phpcs:enable

	if ( $errors ) {
		clo_form_state(
			$id,
			array(
				'errors' => $errors,
				'values' => $values,
			)
		);
		return;
	}

	set_transient( $ip_key, $sent + 1, CLO_FORM_RATE_WINDOW );

	$entry_id = clo_form_save( $id, $form, $values, $file );
	clo_form_send( $form, $values, $file, $entry_id );

	wp_safe_redirect( $done_url, 303 );
	exit;
}

/**
 * Check an uploaded file and move it to the private enquiries folder.
 *
 * @param array<string,mixed> $upload One entry from $_FILES.
 * @return array{path:string,name:string,type:string}|WP_Error
 */
function clo_form_store_upload( $upload ) {
	$too_big = new WP_Error( 'clo_file', 'That file is larger than 5 MB. Please send a smaller file, or email it to us instead.' );
	if ( ! isset( $upload['error'], $upload['tmp_name'], $upload['name'], $upload['size'] ) ) {
		return new WP_Error( 'clo_file', 'The file could not be uploaded. Please try again.' );
	}
	if ( in_array( (int) $upload['error'], array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) ) {
		return $too_big;
	}
	if ( UPLOAD_ERR_OK !== (int) $upload['error'] || ! is_uploaded_file( $upload['tmp_name'] ) ) {
		return new WP_Error( 'clo_file', 'The file could not be uploaded. Please try again.' );
	}
	if ( (int) $upload['size'] > CLO_FORM_MAX_FILE ) {
		return $too_big;
	}

	$allowed = array(
		'pdf'      => 'application/pdf',
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'webp'     => 'image/webp',
		'csv'      => 'text/csv',
		'xls'      => 'application/vnd.ms-excel',
		'xlsx'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'doc'      => 'application/msword',
		'docx'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);
	$name    = sanitize_file_name( wp_basename( $upload['name'] ) );
	$check   = wp_check_filetype_and_ext( $upload['tmp_name'], $name, $allowed );
	if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
		return new WP_Error( 'clo_file', 'Please send a PDF, image (JPG, PNG or WebP), spreadsheet (CSV, XLS or XLSX) or Word file.' );
	}
	if ( ! empty( $check['proper_filename'] ) ) {
		$name = $check['proper_filename'];
	}

	$dir = clo_form_upload_dir();
	if ( is_wp_error( $dir ) ) {
		return $dir;
	}
	// A random prefix makes the file name impossible to guess.
	$path = trailingslashit( $dir ) . bin2hex( random_bytes( 12 ) ) . '-' . $name;
	if ( ! move_uploaded_file( $upload['tmp_name'], $path ) ) {
		return new WP_Error( 'clo_file', 'The file could not be saved. Please try again, or email it to us instead.' );
	}
	chmod( $path, 0640 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod

	return array(
		'path' => $path,
		'name' => $name,
		'type' => $check['type'],
	);
}

/**
 * The folder for enquiry files, created on first use and closed to web browsers on Apache.
 *
 * @return string|WP_Error
 */
function clo_form_upload_dir() {
	$uploads = wp_upload_dir( null, false );
	if ( ! empty( $uploads['error'] ) ) {
		return new WP_Error( 'clo_file', 'The file could not be saved. Please try again, or email it to us instead.' );
	}
	$dir = trailingslashit( $uploads['basedir'] ) . CLO_FORM_UPLOAD_DIR;
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
	if ( ! file_exists( $dir . '/index.php' ) ) {
		file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	return $dir;
}

/**
 * Keep the message in the dashboard.
 *
 * @param string                                    $id     Form ID.
 * @param array<string,mixed>                       $form   Form definition.
 * @param array<string,string>                      $values Submitted values.
 * @param array{path:string,name:string,type:string}|null $file Uploaded file.
 * @return int Entry ID, or 0.
 */
function clo_form_save( $id, $form, $values, $file ) {
	$who = $values['name'];
	if ( ! empty( $values['business'] ) ) {
		$who .= ' (' . $values['business'] . ')';
	}
	$entry_id = wp_insert_post(
		array(
			'post_type'   => CLO_ENQUIRY_TYPE,
			'post_status' => 'private',
			'post_title'  => $form['title'] . ': ' . $who,
		)
	);
	if ( ! $entry_id || is_wp_error( $entry_id ) ) {
		return 0;
	}
	update_post_meta( $entry_id, '_clo_form', $id );
	update_post_meta( $entry_id, '_clo_email', $values['email'] );
	update_post_meta( $entry_id, '_clo_fields', $values );
	if ( $file ) {
		update_post_meta( $entry_id, '_clo_file', $file );
	}

	return (int) $entry_id;
}

/**
 * The submitted values as plain text, one "Label: value" per line.
 *
 * @param array<string,mixed>  $form   Form definition.
 * @param array<string,string> $values Submitted values.
 * @return string
 */
function clo_form_summary( $form, $values ) {
	$lines = array();
	foreach ( $form['fields'] as $key => $field ) {
		if ( in_array( $field['type'], array( 'file', 'checkbox' ), true ) || ! isset( $values[ $key ] ) || '' === $values[ $key ] ) {
			continue;
		}
		$lines[] = $field['label'] . ': ' . ( 'textarea' === $field['type'] ? "\n" : '' ) . $values[ $key ];
	}

	return implode( "\n", $lines );
}

/**
 * Email the shared inbox, and send the sender a copy.
 *
 * @param array<string,mixed>                       $form     Form definition.
 * @param array<string,string>                      $values   Submitted values.
 * @param array{path:string,name:string,type:string}|null $file File.
 * @param int                                       $entry_id Entry ID.
 */
function clo_form_send( $form, $values, $file, $entry_id ) {
	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$inbox   = clo_form_inbox();
	$summary = clo_form_summary( $form, $values );
	// Names go into email headers: keep to safe characters.
	$name = trim( preg_replace( '/[^\p{L}\p{N} .\'-]/u', '', $values['name'] ) );

	$from_name = function () use ( $site ) {
		return $site;
	};
	add_filter( 'wp_mail_from_name', $from_name );

	$body = $summary;
	if ( $file ) {
		$body .= "\n\nFile attached: " . $file['name'];
	}
	if ( $entry_id ) {
		$body .= "\n\nIn the dashboard: " . admin_url( 'post.php?post=' . $entry_id . '&action=edit' );
	}
	wp_mail(
		$inbox,
		sprintf( $form['notify_subject'], $name ),
		$body,
		array( sprintf( 'Reply-To: %s <%s>', $name, $values['email'] ) ),
		// A named key sends the file under its original name, not the stored one.
		$file ? array( $file['name'] => $file['path'] ) : array()
	);

	$first = strtok( $name, ' ' );
	wp_mail(
		$values['email'],
		$form['reply_subject'],
		sprintf( "Hello %s,\n\n", $first ? $first : 'there' )
			. sprintf( $form['reply_intro'], clo_form_response_time() )
			. "\n\nThis is what you sent us:\n\n" . $summary
			. "\n\n" . $site . "\n" . home_url( '/' ),
		array( 'Reply-To: ' . $inbox )
	);

	remove_filter( 'wp_mail_from_name', $from_name );
}

/*
 * The form on the page.
 */
add_shortcode( 'clo_form', 'clo_render_form' );

/**
 * Print a form, or the thank-you message after it has been sent.
 *
 * @param array<string,string> $atts Shortcode attributes: id.
 * @return string
 */
function clo_render_form( $atts ) {
	$atts  = shortcode_atts( array( 'id' => 'contact' ), $atts, 'clo_form' );
	$id    = sanitize_key( $atts['id'] );
	$forms = clo_forms();
	if ( ! isset( $forms[ $id ] ) ) {
		return '';
	}
	$form = $forms[ $id ];

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['sent'] ) && $id === $_GET['sent'] ) {
		return sprintf(
			'<div class="clo-form clo-form--sent" id="clo-form-%1$s" role="status"><p>%2$s</p></div>',
			esc_attr( $id ),
			esc_html( sprintf( $form['success'], clo_form_response_time() ) )
		);
	}

	$state    = clo_form_state( $id );
	$errors   = $state['errors'];
	$values   = $state['values'];
	$has_file = false;
	foreach ( $form['fields'] as $field ) {
		$has_file = $has_file || 'file' === $field['type'];
	}

	ob_start();
	?>
	<form class="clo-form" id="clo-form-<?php echo esc_attr( $id ); ?>" method="post" action="<?php echo esc_url( get_permalink() . '#clo-form-' . $id ); ?>"<?php echo $has_file ? ' enctype="multipart/form-data"' : ''; ?> novalidate>
		<?php if ( $errors ) : ?>
			<div class="clo-form__errors" role="alert" tabindex="-1">
				<p><strong>Please check the form:</strong></p>
				<ul>
					<?php foreach ( $errors as $key => $message ) : ?>
						<li>
							<?php if ( '_form' === $key ) : ?>
								<?php echo esc_html( $message ); ?>
							<?php else : ?>
								<a href="#clo-<?php echo esc_attr( $id . '-' . $key ); ?>"><?php echo esc_html( $message ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<p class="clo-form__note">Fields marked <span aria-hidden="true">*</span><span class="screen-reader-text">with a star</span> are required.</p>

		<?php foreach ( $form['fields'] as $key => $field ) : ?>
			<?php
			$field_id  = 'clo-' . $id . '-' . $key;
			$value     = isset( $values[ $key ] ) ? $values[ $key ] : '';
			$required  = ! empty( $field['required'] );
			$described = array();
			if ( ! empty( $field['help'] ) ) {
				$described[] = $field_id . '-help';
			}
			if ( isset( $errors[ $key ] ) ) {
				$described[] = $field_id . '-error';
			}
			$common = sprintf(
				'id="%1$s" name="clo_%2$s"%3$s%4$s%5$s',
				esc_attr( $field_id ),
				esc_attr( $key ),
				$required ? ' required aria-required="true"' : '',
				isset( $errors[ $key ] ) ? ' aria-invalid="true"' : '',
				$described ? ' aria-describedby="' . esc_attr( implode( ' ', $described ) ) . '"' : ''
			);
			$star   = $required ? ' <span class="clo-form__req" aria-hidden="true">*</span>' : '';
			?>
			<div class="clo-form__field clo-form__field--<?php echo esc_attr( $field['type'] ); ?><?php echo isset( $errors[ $key ] ) ? ' has-error' : ''; ?>">
				<?php if ( 'checkbox' === $field['type'] ) : ?>
					<input type="checkbox" value="1" <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php checked( 'Yes', $value ); ?>>
					<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo wp_kses( $field['label'], array( 'a' => array( 'href' => array() ) ) ) . $star; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
				<?php else : ?>
					<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ) . $star; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
					<?php if ( ! empty( $field['help'] ) ) : ?>
						<span class="clo-form__help" id="<?php echo esc_attr( $field_id . '-help' ); ?>"><?php echo esc_html( $field['help'] ); ?></span>
					<?php endif; ?>
					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea rows="<?php echo (int) ( isset( $field['rows'] ) ? $field['rows'] : 4 ); ?>" <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( $value ); ?></textarea>
					<?php elseif ( 'select' === $field['type'] ) : ?>
						<select <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<option value="">Choose one</option>
							<?php foreach ( $field['options'] as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>"<?php selected( $value, $option ); ?>><?php echo esc_html( $option ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'file' === $field['type'] ) : ?>
						<input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.csv,.xls,.xlsx,.doc,.docx" <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php else : ?>
						<?php
						$type = array(
							'email'    => 'email',
							'tel'      => 'tel',
							'postcode' => 'text',
						);
						?>
						<input type="<?php echo esc_attr( isset( $type[ $field['type'] ] ) ? $type[ $field['type'] ] : 'text' ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo ! empty( $field['autocomplete'] ) ? ' autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : ''; ?><?php echo ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : ''; ?> <?php echo $common; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( isset( $errors[ $key ] ) ) : ?>
					<span class="clo-form__error" id="<?php echo esc_attr( $field_id . '-error' ); ?>"><?php echo esc_html( $errors[ $key ] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<div class="clo-form__trap" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
			<label for="clo-<?php echo esc_attr( $id ); ?>-website">Leave this empty</label>
			<input type="text" id="clo-<?php echo esc_attr( $id ); ?>-website" name="clo_website" value="" tabindex="-1" autocomplete="off">
		</div>
		<input type="hidden" name="clo_form" value="<?php echo esc_attr( $id ); ?>">
		<input type="hidden" name="clo_t" value="<?php echo esc_attr( clo_form_token() ); ?>">
		<p class="clo-form__submit"><button type="submit" class="button wp-element-button"><?php echo esc_html( $form['submit'] ); ?></button></p>
	</form>
	<?php
	return ob_get_clean();
}

/*
 * Enquiries in the dashboard, for shop managers and administrators.
 */

/**
 * The capability needed to see enquiries.
 *
 * @return string
 */
function clo_enquiry_cap() {
	return class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options';
}

add_action(
	'init',
	function () {
		$cap = clo_enquiry_cap();
		register_post_type(
			CLO_ENQUIRY_TYPE,
			array(
				'labels'        => array(
					'name'          => 'Enquiries',
					'singular_name' => 'Enquiry',
					'menu_name'     => 'Enquiries',
					'all_items'     => 'All enquiries',
					'edit_item'     => 'Enquiry',
					'search_items'  => 'Search enquiries',
					'not_found'     => 'No enquiries yet.',
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_menu'  => true,
				'show_in_rest'  => false,
				'menu_position' => 56,
				'menu_icon'     => 'dashicons-email-alt',
				'supports'      => array( 'title' ),
				'map_meta_cap'  => false,
				'capabilities'  => array(
					'edit_post'              => $cap,
					'read_post'              => $cap,
					'delete_post'            => $cap,
					'edit_posts'             => $cap,
					'edit_others_posts'      => $cap,
					'edit_private_posts'     => $cap,
					'edit_published_posts'   => $cap,
					'read_private_posts'     => $cap,
					'delete_posts'           => $cap,
					'delete_others_posts'    => $cap,
					'delete_private_posts'   => $cap,
					'delete_published_posts' => $cap,
					'publish_posts'          => 'do_not_allow',
					'create_posts'           => 'do_not_allow',
				),
			)
		);
	}
);

add_filter(
	'manage_' . CLO_ENQUIRY_TYPE . '_posts_columns',
	function ( $columns ) {
		return array(
			'cb'        => $columns['cb'],
			'title'     => 'Enquiry',
			'clo_email' => 'Email',
			'clo_form'  => 'Form',
			'date'      => 'Received',
		);
	}
);

add_action(
	'manage_' . CLO_ENQUIRY_TYPE . '_posts_custom_column',
	function ( $column, $post_id ) {
		if ( 'clo_email' === $column ) {
			$email = get_post_meta( $post_id, '_clo_email', true );
			printf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
		} elseif ( 'clo_form' === $column ) {
			$forms = clo_forms();
			$id    = get_post_meta( $post_id, '_clo_form', true );
			echo esc_html( isset( $forms[ $id ] ) ? $forms[ $id ]['title'] : $id );
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes_' . CLO_ENQUIRY_TYPE,
	function () {
		add_meta_box( 'clo-enquiry-details', 'Details', 'clo_enquiry_details_box', CLO_ENQUIRY_TYPE, 'normal', 'high' );
	}
);

/**
 * The submitted details, read-only.
 *
 * @param WP_Post $post Enquiry.
 */
function clo_enquiry_details_box( $post ) {
	$forms  = clo_forms();
	$id     = get_post_meta( $post->ID, '_clo_form', true );
	$values = (array) get_post_meta( $post->ID, '_clo_fields', true );
	$file   = get_post_meta( $post->ID, '_clo_file', true );
	$fields = isset( $forms[ $id ] ) ? $forms[ $id ]['fields'] : array();

	echo '<table class="widefat striped"><tbody>';
	foreach ( $values as $key => $value ) {
		$label = isset( $fields[ $key ] ) ? wp_strip_all_tags( $fields[ $key ]['label'] ) : $key;
		if ( 'consent' === $key ) {
			$label = 'Agreed to the privacy policy';
		}
		printf( '<tr><th scope="row" style="width:220px">%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( $value ) ) );
	}
	if ( is_array( $file ) && ! empty( $file['path'] ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=clo_enquiry_file&entry=' . $post->ID ), 'clo_enquiry_file_' . $post->ID );
		printf( '<tr><th scope="row">File</th><td><a href="%s">%s</a></td></tr>', esc_url( $url ), esc_html( $file['name'] ) );
	}
	echo '</tbody></table>';
	if ( ! empty( $values['email'] ) ) {
		printf( '<p><a class="button button-primary" href="%s">Reply by email</a></p>', esc_url( 'mailto:' . $values['email'] ) );
	}
}

// Download an enquiry's file. The folder itself is closed to the web.
add_action(
	'admin_post_clo_enquiry_file',
	function () {
		$entry = isset( $_GET['entry'] ) ? absint( $_GET['entry'] ) : 0;
		check_admin_referer( 'clo_enquiry_file_' . $entry );
		if ( ! current_user_can( clo_enquiry_cap() ) || CLO_ENQUIRY_TYPE !== get_post_type( $entry ) ) {
			wp_die( 'You are not allowed to download this file.', 403 );
		}
		$file = get_post_meta( $entry, '_clo_file', true );
		$dir  = clo_form_upload_dir();
		if ( ! is_array( $file ) || is_wp_error( $dir ) || empty( $file['path'] ) || 0 !== strpos( wp_normalize_path( $file['path'] ), wp_normalize_path( $dir ) ) || ! is_readable( $file['path'] ) ) {
			wp_die( 'The file is no longer available.', 404 );
		}
		nocache_headers();
		header( 'Content-Type: ' . $file['type'] );
		header( 'Content-Disposition: attachment; filename="' . str_replace( '"', '', $file['name'] ) . '"' );
		header( 'Content-Length: ' . filesize( $file['path'] ) );
		readfile( $file['path'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}
);

// Deleting an enquiry for good also deletes its file.
add_action(
	'before_delete_post',
	function ( $post_id ) {
		if ( CLO_ENQUIRY_TYPE !== get_post_type( $post_id ) ) {
			return;
		}
		$file = get_post_meta( $post_id, '_clo_file', true );
		if ( is_array( $file ) && ! empty( $file['path'] ) && file_exists( $file['path'] ) ) {
			wp_delete_file( $file['path'] );
		}
	}
);

/*
 * Data protection: enquiries are included in WordPress's personal data
 * export and erasure tools (Tools > Export / Erase Personal Data).
 */

/**
 * Enquiries sent from an email address.
 *
 * @param string $email Email address.
 * @return int[]
 */
function clo_enquiries_for( $email ) {
	return get_posts(
		array(
			'post_type'      => CLO_ENQUIRY_TYPE,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_clo_email', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

add_filter(
	'wp_privacy_personal_data_exporters',
	function ( $exporters ) {
		$exporters['clo-enquiries'] = array(
			'exporter_friendly_name' => 'Enquiries',
			'callback'               => function ( $email ) {
				$items = array();
				$forms = clo_forms();
				foreach ( clo_enquiries_for( $email ) as $entry ) {
					$form_id = get_post_meta( $entry, '_clo_form', true );
					$fields  = isset( $forms[ $form_id ] ) ? $forms[ $form_id ]['fields'] : array();
					$data    = array(
						array(
							'name'  => 'Received',
							'value' => get_the_date( 'j F Y H:i', $entry ),
						),
					);
					foreach ( (array) get_post_meta( $entry, '_clo_fields', true ) as $key => $value ) {
						$data[] = array(
							'name'  => isset( $fields[ $key ] ) && 'consent' !== $key ? $fields[ $key ]['label'] : $key,
							'value' => $value,
						);
					}
					$items[] = array(
						'group_id'    => 'clo-enquiries',
						'group_label' => 'Enquiries',
						'item_id'     => 'enquiry-' . $entry,
						'data'        => $data,
					);
				}

				return array(
					'data' => $items,
					'done' => true,
				);
			},
		);

		return $exporters;
	}
);

add_filter(
	'wp_privacy_personal_data_erasers',
	function ( $erasers ) {
		$erasers['clo-enquiries'] = array(
			'eraser_friendly_name' => 'Enquiries',
			'callback'             => function ( $email ) {
				$removed = false;
				foreach ( clo_enquiries_for( $email ) as $entry ) {
					$removed = (bool) wp_delete_post( $entry, true ) || $removed;
				}

				return array(
					'items_removed'  => $removed,
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			},
		);

		return $erasers;
	}
);
