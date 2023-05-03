<?php
/**
 * Plugin Name: Elementor Forms Multiple Email Action
 * Description: 
 * Plugin URI:  
 * Version:     1.0.0
 * Author:      Németh Csaba Tibor
 * Author URI:  https://finch.hu
 * Text Domain: elementor-forms-multiple-email-action
 *
 * Elementor tested up to: 3.7.0
 * Elementor Pro tested up to: 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add new form action after form submission.
 *
 * @since 1.0.0
 * @param ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $form_actions_registrar
 * @return void
 */
function add_new_email_action( $form_actions_registrar ) {

	include_once( __DIR__ .  '/form-actions/email.php' );

	$form_actions_registrar->register( new \Multiple_Email_Action_After_Submit() );

}
add_action( 'elementor_pro/forms/actions/register', 'add_new_email_action' );

