<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( is_user_logged_in() ) {
	esc_html_e( 'You are currently logged in.', 'opalmembership' );
	return;
}

?>

<div class="opalesate-form-wrapper">
	<?php if ( $hide_title === false ) : ?>
		<h2><?php esc_html_e( 'Login', 'opalmembership' ); ?></h2>
	<?php endif; ?>

	<?php if ( $message ) : ?>
		<p><?php printf( '%s', $message ) ?></p>
	<?php endif; ?>

	<form method="POST" class="opalmembership-login-form opalmembership-member-form">
		<?php do_action( 'opalmembership_member_before_login_form' ); ?>

		<p class="opalmembership-form-field form-row username validate-required">
			<label for="username"><?php esc_html_e( 'Username or email address', 'opalmembership' ); ?> <span class="required">*</span></label>
			<input type="text" class="opalmembership-input text input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
		</p>

		<p class="opalmembership-form-field form-row password validate-required">
			<label for="password"><?php esc_html_e( 'Password', 'opalmembership' ); ?> <span class="required">*</span></label>
			<input class="opalmembership-input text input-text" type="password" name="password" id="password" />
		</p>

		<?php do_action( 'opalmembership_member_login_form' ); ?>

		<p class="opalmembership-form-field form-row remberme">
			<label for="rememberme" id="rememberme-label">
				<input class="opalmembership-input checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php esc_html_e( 'Remember me', 'opalmembership' ); ?>
			</label>
		</p>
		<p class="opalmembership-form-field form-row submit">
			<?php wp_nonce_field( 'opalmembership-login', 'opalmembership-login-nonce' ); ?>
			<?php if ( $redirect ) : ?>
				<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>">
			<?php endif; ?>
			<input type="submit" class="opalmembership-button button btn btn-primary" name="login" value="<?php esc_attr_e( 'Login', 'opalmembership' ); ?>" />
			<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'opalmembership' ); ?></a>
		</p>
		<p class="opalmembership-form-field form-row register">
			<a href="<?php echo esc_url( opalmembership_get_register_page_uri() ); ?>"><?php esc_html_e( 'Register now!', 'opalmembership' ); ?></a>
		</p>
		<?php do_action('login_form'); ?>
		<?php do_action( 'opalmembership_member_after_login_form' ); ?>
	</form>
</div>
