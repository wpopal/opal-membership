<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    $package$
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2014 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

opalmembership_print_notices();

if ( is_user_logged_in() ) {
	esc_html_e( 'You are currently logged in.', 'opalmembership' );
	return;
}

?>
<div class="opalesate-form-wrapper">
	<?php if ( $hide_title === false ) : ?>
		<h2><?php esc_html_e( 'Register', 'opalmembership' ); ?></h2>
	<?php endif; ?>

	<?php if ( $message ) : ?>
		<p><?php printf( '%s', $message ) ?></p>
	<?php endif; ?>

	<form method="POST" class="opalmembership-login-form opalmembership-member-form">

		<?php do_action( 'opalmembership_member_before_register_form' ); ?>

		<p class="form-row opalmembership-form-field username validate-required">
			<label for="reg_username"><?php esc_html_e( 'Username', 'opalmembership' ); ?> <span class="required">*</span></label>
			<input type="text" class="opalmembership-input input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
		</p>

		<p class="form-row opalmembership-form-field email validate-required">
			<label for="reg_email"><?php esc_html_e( 'Email address', 'opalmembership' ); ?> <span class="required">*</span></label>
			<input type="email" class="opalmembership-input input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
		</p>

		<p class="form-row opalmembership-form-field password validate-required">
			<label for="reg_password"><?php esc_html_e( 'Password', 'opalmembership' ); ?> <span class="required">*</span></label>
			<input type="password" class="opalmembership-input input-text" name="password" id="reg_password" />
		</p>

		<p class="form-row opalmembership-form-field password confirm-password validate-required">
			<label for="reg_password1"><?php esc_html_e( 'Repeat-Password', 'opalmembership' ); ?> <span class="required">*</span></label>
			<input type="password" class="opalmembership-input input-text" name="password1" id="reg_password1" />
		</p>

		<?php do_action( 'opalmembership_register_form' ); ?>
		<?php do_action( 'register_form' ); ?>

		<p class="form-row opalmembership-form-field submit">
			<?php wp_nonce_field( 'opalmembership-register', 'opalmembership-register-nonce' ); ?>
			<?php if ( $redirect ) : ?>
				<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>">
			<?php endif; ?>
			<input type="submit" class="opalmembership-button button" name="register" value="<?php esc_attr_e( 'Register', 'opalmembership' ); ?>" />
		</p>

		<?php do_action( 'opalmembership_member_after_register_form' ); ?>

	</form>
</div>
