<?php

use PHPMailer\PHPMailer\PHPMailer;

defined('BASEPATH') OR exit('No direct script access allowed.');

/**
 * Enhanced Email library by CI Bootstrap 3
 */
class My_email extends CI_Email {

	protected $CI;

	public function __construct(array $config = array())
	{
		parent::__construct($config);
		$this->CI =& get_instance();
		$this->CI->config->load('ci_bootstrap');
	}
	
	// Send email templates using default CodeIgniter setting (e.g. SMTP)
	// Template folder: /application/views/email/
	public function send_email_template($to_email, $to_name, $subject, $view, $view_data = NULL)
	{
		// load values from config
		$config = $this->CI->config->item('ci_bootstrap')['email'];
		$from_email = $config['from_email'];
		$from_name = $config['from_name'];
		$subject = $config['subject_prefix'].$subject;

		// basic parameters
		parent::from($from_email, $from_name);
		parent::to($to_email, $to_name);
		parent::subject($subject);

		// append view data
		$info = new stdClass();
		$info->from_email = $from_email;
		$info->from_name = $from_name;
		$info->to_email = $to_email;
		$info->to_name = $to_name;
		$info->subject_prefix = $config['subject_prefix'];
		$info->subject = $subject;
		$view_data['email_info'] = $info;

		// get HTML content from view
		$msg = $this->CI->load->view($view, $view_data, TRUE);
		parent::message($msg);

		// confirm to send
		if (ENVIRONMENT=='development')
		{
			// return result with debug messages
			$result = parent::send(FALSE);
			return array(
				'result'	=> $result,
				'debug'		=> parent::print_debugger()
			);
		}
		else
		{
			// return only result (TRUE / FALSE)
			$result = parent::send();
			return array('result' => $result);
		}
	}

    // Send email template using PhpMailer
    // Template folder: /application/views/email/
    public function send_by_phpmailer($to_email, $to_name, $subject, $view, $view_data = NULL)
    {
        $from_email = EMAIL_FROM_ADDRESS;
        $from_name = EMAIL_FROM_NAME;

        // append view data
        $info = new stdClass();
        $info->from_email = $from_email;
        $info->from_name = $from_name;
        $info->to_email = $to_email;
        $info->to_name = $to_name;
        $info->subject_prefix = '';
        $info->subject = $subject;
        $view_data['email_info'] = $info;

        // get HTML content from view
        $msg = $this->CI->load->view($view, $view_data, TRUE);

        $mail = new PHPMailer;

        $mail->isSMTP();                    // Set mailer to use SMTP
        $mail->Host = SMTP_HOST;            // Specify main and backup SMTP servers
        $mail->Port = SMTP_PORT;            // TCP port to connect to
        $mail->SMTPSecure = SMTP_SECURE_MODE;          // Enable TLS encryption, `ssl` also accepted
        $mail->SMTPAuth = true;             // Enable SMTP authentication
        $mail->Username = SMTP_USERNAME;    // SMTP username
        $mail->Password = SMTP_PASSWORD;    // SMTP password

        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $msg;

        return $mail->send();
    }

	// Send email template using Mailgun HTTP API
	// Template folder: /application/views/email/
	public function send_by_mailgun($to_email, $to_name, $subject, $view, $view_data = NULL)
	{
		// load values from config
		$config = $this->CI->config->item('ci_bootstrap')['email'];
		$mailgun_config = $config['mailgun_api'];

		// config not set correctly
		if ( empty($mailgun_config['domain']) || empty($mailgun_config['private_api_key']) )
		{
			return array(
				'result'	=> FALSE,
				'error'		=> 'Mailgun API config not set'
			);
		}

		// other values from config
		$from_email = $config['from_email'];
		$from_name = $config['from_name'];
		$subject = $config['subject_prefix'].$subject;

		// create Mailgun object
		$api_key = $mailgun_config['private_api_key'];
		$domain = $mailgun_config['domain'];
		$mg = new Mailgun\Mailgun($api_key);

		// append view data
		$info = new stdClass();
		$info->from_email = $from_email;
		$info->from_name = $from_name;
		$info->to_email = $to_email;
		$info->to_name = $to_name;
		$info->subject_prefix = $config['subject_prefix'];
		$info->subject = $subject;
		$view_data['email_info'] = $info;

		// get HTML content from view
		$html = $this->CI->load->view('email/'.$view, $view_data, TRUE);

		// Mailgun MessageBuilder
		// Reference: https://github.com/mailgun/mailgun-php/blob/master/src/Mailgun/Messages/README.md
		$mb = $mg->MessageBuilder();
		$mb->setFromAddress($from_email, array('full_name' => $from_name));
		$mb->addToRecipient($to_email, array('full_name' => $to_name));
		$mb->setSubject($subject);
		$mb->setHtmlBody($html);

		// confirm to send
		$response = $mg->post("{$domain}/messages", $mb->getMessage());
		return array('result' => ($response->http_response_code==200));
	}
}