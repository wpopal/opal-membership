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

/**
 * @class Opalmembership_Gateway_Pp_standard
 *
 * @version 1.0
 */
class Opalmembership_Gateway_Stripe extends Opalmembership_Abstract_Gateway{

	/**
	 * @var String $title
	 */
	public $title;

	/**
	 * @var String $title
	 */
	public $description;

	/**
	 * @var String $title
	 */
	public $icon;

	/**
	 * @var String $title
	 */
	public $testmode = false;

	/**
	 * @var String $title
	 */
	protected $log;

	/**
	 * @var String $secret_key
	 */
	protected $secret_key;

	/**
	 * @var String $publish_key
	 */
	protected $publish_key;

	protected $api_uri = 'https://api.stripe.com/v1';

	/**
	 * @var
	 */
	protected $debug = true;

	protected $card_number;
	protected $card_cvc;
	protected $card_exp_year;
	protected $card_exp_month;

	/**
	 * Constructor
	 */
	public function __construct(){

		$this->title       = esc_html__( 'Stripe', 'opalmembership' );
		$this->icon   	   =  apply_filters( 'opalmembership_stripe_icon', '' );
		$this->description = esc_html__( 'Credit Card Payment', 'opalmembership' );

		$this->testmode    =  opalmembership_options('test_mode');

		// Check the current payment mode
		$this->secret_key = opalmembership_options('stripe_live_secret_key');
		$this->publish_key = opalmembership_options('stripe_live_publish_key');
		if ( $this->testmode || $this->testmode == 'on' ) {
			$this->secret_key = opalmembership_options('stripe_test_secret_key');
			$this->publish_key = opalmembership_options('stripe_test_publish_key');
		}
		add_filter( 'opalmembership_settings_gateways', array( $this, 'admin_fields' ) );
	}

	/**
	 * render admin setting fields
	 */
	public function admin_fields( $fields ){

		$stripe_fields = apply_filters('opalmembership_settings_gateway_stripe',
			array(
				array(
					'name' => esc_html__( 'Stripe Setting', 'opalmembership' ),
					'desc' => '<hr>',
					'type' => 'opalmembership_title',
					'id'   => 'opalmembership_title_gateway_settings_4',
					'default' => '1'
				),
				array(
					'id' 	=> 'stripe_test_secret_key',
					'name'  => esc_html__( 'Test Secret Key', 'opalmembership' ),
					'desc'  => esc_html__( 'Enter your test secret key', 'opalmembership' ),
					'type'  => 'text',
				),
				array(
					'id' => 'stripe_test_publish_key',
					'name' => esc_html__( 'Test Publish Key', 'opalmembership' ),
					'desc' => esc_html__( 'Enter your test publish key', 'opalmembership' ),
					'type' => 'text'
				),
				array(
					'id' 	=> 'stripe_live_secret_key',
					'name'  => esc_html__( 'Live Secret Key', 'opalmembership' ),
					'desc'  => esc_html__( 'Enter your live secret key', 'opalmembership' ),
					'type'  => 'text',
				),
				array(
					'id' => 'stripe_live_publish_key',
					'name' => esc_html__( 'Live Publish Key', 'opalmembership' ),
					'desc' => esc_html__( 'Enter your live stripe publish key.', 'opalmembership' ),
					'type' => 'text'
				)

			)
		);

		return array_merge( $fields, $stripe_fields );
	}

	public function form() {
		ob_start();
		?>
			<div id="opalmembership-stripe-form">
		    <p class="form-row validate-required">
		        <label for="cc-number" class="label-field">
		        	<?php esc_html_e( 'Card Number', 'opalmembership' ) ?>
		        	<abbr class="required" title="required">*</abbr>
		        </label>
		        <input name="payment-info[cc-number]" id="cc-number" type="tel" class="required input-text stripe-cc-number" autocomplete="cc-number" placeholder="•••• •••• •••• ••••" />
		    </p>

		    <p class="form-row validate-required">
		        <label for="cc-exp" class="label-field">
		        	<?php esc_html_e( 'Expires (MM/YY)', 'opalmembership' ) ?>
		        	<abbr class="required" title="required">*</abbr>
		        </label>
		        <input name="payment-info[cc-exp]" id="cc-exp" type="tel" class="required input-text stripe-cc-exp" autocomplete="cc-exp" placeholder="•• / ••••" />
		    </p>

		    <p class="form-row validate-required">
		        <label for="cc-cvc" class="label-field">
		        	<?php esc_html_e( 'Card Code (CVC)', 'opalmembership' ) ?>
		        	<abbr class="required" title="required">*</abbr>
		        </label>
		        <input name="payment-info[cc-cvc]" id="cc-cvc" type="tel" class="required input-text stripe-cc-cvc" autocomplete="off" placeholder="•••" />
		    </p>

		</div>
		<?php
		return ob_get_clean();
	}

	public function validate( $fields = array() ) {
		$fields = wp_parse_args( $fields, array(
				'cc-number' => '',
				'cc-exp' 	=> '',
				'cc-cvc' 	=> ''
			) );

		if ( ! class_exists( 'CreditCard' ) ) {
			require_once OPALMEMBERSHIP_PLUGIN_DIR . '/inc/gateways/stripe/CreditCard.php';
		}

		$errors = array();

		/**
		 * validate Card Number
		 */
		$card = CreditCard::validCreditCard( $fields['cc-number'] );
		if ( empty( $fields['cc-number'] ) ) {
			$errors[] = array(
					'field'		=> 'cc-number',
					'message'	=> esc_html__( 'Credit Card number is required.', 'opalmembership' )
				);
		} else if( ! $card['valid'] ) {
			$errors[] = array(
					'field'		=> 'cc-number',
					'message'	=> esc_html__( 'Credit Card number is invalid.', 'opalmembership' )
				);
		} else {
			$this->card_number = $fields['cc-number'];
		}

		/**
		 * validate Expiry card
		 */
		list( $month, $year ) = array_map( 'trim', explode( '/', $fields['cc-exp'] ) );
		if ( empty( $fields['cc-exp'] ) ) {
			$errors[] = array(
					'field'		=> 'cc-exp',
					'message'	=> esc_html__( 'Credit Card expiry is required.', 'opalmembership' )
				);
		} else if( ! CreditCard::validDate( $year, $month ) ) {
			$errors[] = array(
					'field'		=> 'cc-exp',
					'message'	=> esc_html__( 'Credit Card expiry is invalid.', 'opalmembership' )
				);
		} else {
			$this->card_exp_year = $year;
			$this->card_exp_month = $month;
		}

		/**
		 * validate CVC card
		 */
		if ( empty( $fields['cc-cvc'] ) ) {
			$errors[] = array(
					'field'		=> 'cc-cvc',
					'message'	=> esc_html__( 'Credit Card CVC is required.', 'opalmembership' )
				);
		} else if ( ! $card['type'] || ! CreditCard::validCvc( $fields['cc-cvc'], $card['type'] ) ){
			$errors[] = array(
					'field'		=> 'cc-cvc',
					'message'	=> esc_html__( 'Credit Card CVC is invalid.', 'opalmembership' )
				);
		} else {
			$this->card_cvc = $fields['cc-cvc'];
		}

		return $errors;
	}

	/**
	 *
	 */
	public function process( $payment_id, $posted ){

		if ( ! $this->secret_key || ! $this->publish_key ) {
			opalmembership_add_notice( 'error', '<strong>ERROR</strong>: ' . esc_html__( 'Invalid Secret, Publish Stripe key.', 'opalmembership' ) );
            return;
        }
        $valid = $this->validate( $posted['payment-info'] );
        if ( empty( $posted['payment-info'] ) || ! empty( $valid ) ) {
        	opalmembership_add_notice( 'error', '<strong>ERROR</strong>: ' . esc_html__( 'Credit Card details is empty.', 'opalmembership' ) );
            return;
        }

        /**
         * Generate Token
         */
        $tokens = $this->stripe_request( 'tokens', array(
			            'card' => array(
			                'number' 	=> $this->card_number,
			                'exp_month' => $this->card_exp_month,
			                'exp_year' 	=> $this->card_exp_year,
			                'cvc' 		=> $this->card_cvc,
			            )
                ) );
        if ( is_wp_error( $tokens ) || ! $tokens->id ) {
        	opalmembership_add_notice( 'error', '<strong>ERROR</strong>: ' . esc_html__( 'Stripe counld not create tooken for your Credit Card Information.', 'opalmembership' ) );
            return;
        }

        $token = $tokens->id;

        $payment = new Opalmembership_Payment( $payment_id );
        $customer_id = $payment->get_meta( 'stripe_id' );
        if ( ! $customer_id ) {
            $params = array(
                'description' => sprintf( '#%s - %s', $payment->user_id, $payment->email ),
                'source' => $token
            );
            // create customer
            $response = $this->stripe_request( 'customers', $params );

            if ( is_wp_error( $response ) ) {
            	opalmembership_add_notice( 'error', '<strong>ERROR</strong>: ' . $response->get_error_message() );
            	return;
            }

            $customer_id = $response->id;

            update_post_meta( $payment_id, OPALMEMBERSHIP_PAYMENT_PREFIX . 'stripe_id', $customer_id );
        }

        $params = array(
            'amount' => round( $payment->get_payment_total() * 100 ),
            'currency' => $payment->get_payment_currency(),
            'customer' => $customer_id,
            'description' => sprintf(
                    esc_html__( 'Payment ID ', 'opalmembership' ) . '%s', $payment_id
            )
        );

        // insert new charges stripe
        $response = $this->stripe_request( 'charges', $params );
        if ( $response && ! is_wp_error( $response ) && $response->id ) {
            $payment->update_status( 'completed', esc_html__( 'Payment to be made upon delivery.', 'opalmembership' ) );
        } else {
        	$payment->update_status( 'on-hold', esc_html__( 'Payment to be made upon delivery.', 'opalmembership' ) );
            opalmembership_add_notice( 'error', '<strong>ERROR</strong>: ' . $response->get_error_message() );
            return;
        }
		return true;
	}

    /**
     * Create Stripe Request API
     */
    protected function stripe_request( $api = 'charges', $params = array() ) {
        $response = wp_safe_remote_post( $this->api_uri . '/' . $api, array(
			            'method' => 'POST',
			            'headers' => array(
			                'Authorization' => 'Basic ' . base64_encode( $this->secret_key . ':' )
			            ),
			            'body' => $params,
			            'timeout' => 70,
			            'sslverify' => false,
			            'user-agent' => 'Opal Membership ' . OPALMEMBERSHIP_VERSION
        ) );

        if ( ! is_wp_error( $response ) ) {
        	/**
        	 * Retrieve body response
        	 */
            $body = wp_remote_retrieve_body( $response );
            if ( $body )
                $body = json_decode( $body );

            if ( ! empty( $body->error ) ) {
                return new WP_Error( 'stripe_error', $body->error->message );
            }

            if ( empty( $body->id ) ) {
                return new WP_Error( 'stripe_error', esc_html__( 'OOP. Process Error', 'opalmembership' ) );
            }

            return $body;
        }

        return new WP_Error( 'stripe_error', $response->get_error_message() );
    }

}
