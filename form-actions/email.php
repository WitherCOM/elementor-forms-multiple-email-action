<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor form Sendy action.
 *
 * Custom Elementor form action which adds new subscriber to Sendy after form submission.
 *
 * @since 1.0.0
 */
class Multiple_Email_Action_After_Submit extends \ElementorPro\Modules\Forms\Classes\Action_Base {

	/**
	 * Get action name.
	 *
	 * Retrieve Multiple Email action name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return 'multiple_email';
	}

	/**
	 * Get action label.
	 *
	 * Retrieve Sendy action label.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Multiple Email', 'elementor-forms-multiple-email-action' );
	}

	/**
	 * Register action controls.
	 *
	 * Add input fields to allow the user to customize the action settings.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {

		$widget->start_controls_section(
			'section_multiple_email',
			[
				'label' => esc_html__( 'Multiple Email', 'elementor-forms-multiple-email-action' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'form_field_name',
			[
				'label' => esc_html__( 'Form field name', 'elementor-forms-multiple-email-action' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'The name of the radio/select field', 'elementor-forms-multiple-email-action' ),
			]
		);

        $default_message = sprintf( esc_html__( 'New message from "%s"', 'elementor-pro' ), get_option( 'blogname' ) );
		$widget->add_control(
			'multiple_email_bind',
			[
				'label' => esc_html__( 'Email bind', 'elementor-forms-multiple-email-action' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'description' => esc_html__( 'Write every email addresses in separate lines in form of (key|email)', 'elementor-forms-multiple-email-action' ),
			]
		);

		$widget->add_control(
			'multiple_email_subject',
			[
				'label' => esc_html__( 'Subject', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => $default_message,
				'placeholder' => $default_message,
				'label_block' => true,
				'render_type' => 'none',
			]
		);

        $widget->add_control(
			'multiple_email_content',
			[
				'label' => esc_html__( 'Message', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => sprintf( esc_html__( 'By default, all form fields are sent via %s shortcode. To customize sent fields, copy the shortcode that appears inside each field and paste it above.', 'elementor-pro' ), '<code>[all-fields]</code>' ),
				'render_type' => 'none',
			]
		);

        $widget->add_control(
			'multiple_email_from',
			[
				'label' => esc_html__( 'From Email', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'multiple_email_from_name',
			[
				'label' => esc_html__( 'From Name', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_bloginfo( 'name' ),
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'multiple_email_reply_to',
			[
				'label' => esc_html__( 'Reply-To', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'render_type' => 'none',
			]
		);

        $widget->add_control(
		    'multiple_form_metadata',
			[
				'label' => esc_html__( 'Meta Data', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'label_block' => true,
				'separator' => 'before',
				'default' => [
					'date',
					'time',
					'page_url',
					'user_agent',
					'remote_ip',
					'credit',
				],
				'options' => [
					'date' => esc_html__( 'Date', 'elementor-pro' ),
					'time' => esc_html__( 'Time', 'elementor-pro' ),
					'page_url' => esc_html__( 'Page URL', 'elementor-pro' ),
					'user_agent' => esc_html__( 'User Agent', 'elementor-pro' ),
					'remote_ip' => esc_html__( 'Remote IP', 'elementor-pro' ),
					'credit' => esc_html__( 'Credit', 'elementor-pro' ),
				],
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'multiple_email_content_type',
			[
				'label' => esc_html__( 'Send As', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'html',
				'render_type' => 'none',
				'options' => [
					'html' => esc_html__( 'HTML', 'elementor-pro' ),
					'plain' => esc_html__( 'Plain', 'elementor-pro' ),
				],
			]
		);

		$widget->end_controls_section();

	}

	/**
	 * Run action.
	 *
	 * Runs the Multiple email action after form submission.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {

		$settings = $record->get( 'form_settings' );

		//  Make sure that form_field_name is not empty
		if ( empty( $settings['form_field_name'] ) ) {
			return;
		}

		//  Make sure that email_bind is not empty
		if ( empty( $settings['multiple_email_bind'] ) ) {
			return;
		}

		// Get submitted form data.
		$raw_fields = $record->get( 'fields' );

        //get target email address

		$send_html = 'plain' !== $settings[ 'email_content_type' ];
		$line_break = $send_html ? '<br>' : "\n";

		$fields = [
			'multiple_email_to' => $this->get_target_email($raw_fields, $settings['form_field_name'], $settings['multiple_email_bind']),
			/* translators: %s: Site title. */
			'multiple_email_subject' => sprintf( esc_html__( 'New message from "%s"', 'elementor-pro' ), get_bloginfo( 'name' ) ),
			'multiple_email_content' => '[all-fields]',
			'multiple_email_from_name' => get_bloginfo( 'name' ),
			'multiple_email_from' => get_bloginfo( 'admin_email' ),
			'multiple_email_reply_to' => 'noreply@example.hu',
		];

		foreach ( $fields as $key => $default ) {
			$setting = trim( $settings[$key] ?? '' );
			$setting = $record->replace_setting_shortcodes( $setting );
			if ( ! empty( $setting ) ) {
				$fields[ $key ] = $setting;
			}
		}
		$fields['multiple_email_content'] = $this->replace_content_shortcodes( $fields['multiple_email_content'], $record, $line_break );

		$email_meta = '';

		$form_metadata_settings = $settings[ 'multiple_form_metadata'  ];

		foreach ( $record->get( 'meta' ) as $id => $field ) {
			if ( in_array( $id, $form_metadata_settings ) ) {
				$email_meta .= $this->field_formatted( $field ) . $line_break;
			}
		}

		if ( ! empty( $email_meta ) ) {
			$fields['multiple_email_content'] .= $line_break . '---' . $line_break . $line_break . $email_meta;
		}

		$headers = sprintf( 'From: %s <%s>' . "\r\n", $fields['multiple_email_from_name'], $fields['multiple_email_from'] );
		$headers .= sprintf( 'Reply-To: %s' . "\r\n", $fields['multiple_email_reply_to']);

		if ( $send_html ) {
			$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
		}

		/**
		 * Email headers.
		 *
		 * Filters the additional headers sent when the form send an email.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $headers Additional headers.
		 */
		$headers = apply_filters( 'elementor_pro/forms/wp_mail_headers', $headers );

		/**
		 * Email content.
		 *
		 * Filters the content of the email sent by the form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $email_content Email content.
		 */
		$fields['multiple_email_content'] = apply_filters( 'elementor_pro/forms/wp_mail_message', $fields['multiple_email_content'] );

		$email_sent = wp_mail( $fields['multiple_email_to'], $fields['multiple_email_subject'], $fields['multiple_email_content'], $headers);

		/**
		 * Elementor form mail sent.
		 *
		 * Fires when an email was sent successfully.
		 *
		 * @since 1.0.0
		 *
		 * @param array       $settings Form settings.
		 * @param Form_Record $record   An instance of the form record.
		 */
		do_action( 'elementor_pro/forms/mail_sent', $settings, $record );

		if ( ! $email_sent ) {
            
			$message = \ElementorPro\Modules\Forms\Classes\Ajax_Handler::get_default_message( \ElementorPro\Modules\Forms\Classes\Ajax_Handler::SERVER_ERROR, $settings );

			$ajax_handler->add_error_message( $message );

			throw new \Exception( $message );
		}

	}

	/**
	 * On export.
	 *
	 * Clears Multiple email form settings/fields when exporting.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $element
	 */
	public function on_export( $element ) {

		unset(
			$element['form_field_name'],
			$element['multiple_email_bind'],
            $element['multiple_email_from'],
			$element['multiple_email_from_name'],
			$element['multiple_email_subject'],
			$element['multiple_email_reply_to']
		);

		return $element;

	}

    /**
	 *
	 * @return string
	 */
	private function replace_content_shortcodes( $email_content, $record, $line_break ) {
		$email_content = do_shortcode( $email_content );
		$all_fields_shortcode = '[all-fields]';

		if ( false !== strpos( $email_content, $all_fields_shortcode ) ) {
			$text = '';
			foreach ( $record->get( 'fields' ) as $field ) {
				$formatted = $this->field_formatted( $field );
				if ( ( 'textarea' === $field['type'] ) && ( '<br>' === $line_break ) ) {
					$formatted = str_replace( [ "\r\n", "\n", "\r" ], '<br />', $formatted );
				}
				$text .= $formatted . $line_break;
			}

			$email_content = str_replace( $all_fields_shortcode, $text, $email_content );

		}

		return $email_content;
	}

    private function get_target_email($raw_fields, $form_field_name, $multiple_email_bind)
    {
        $email = "noreply@example.com";
        if(!array_key_exists($form_field_name,$raw_fields))
            return $email;
        $rows = preg_split("/(\r\n|\n|\r)/", $multiple_email_bind);
        if(!is_array($rows))
            return $email;
        foreach($rows as $row) {
            $pieces = preg_split("/\|/",$row);
            if(!is_array($pieces))
                return $email;
            if($pieces[0] == $raw_fields[$form_field_name]["value"])
                return $pieces[1];
        }
        return $email;
    }

	private function field_formatted( $field ) {
		$formatted = '';
		if ( ! empty( $field['title'] ) ) {
			$formatted = sprintf( '%s: %s', $field['title'], $field['value'] );
		} elseif ( ! empty( $field['value'] ) ) {
			$formatted = sprintf( '%s', $field['value'] );
		}

		return $formatted;
	}

}
