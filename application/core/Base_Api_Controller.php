<?php

use PHPMailer\PHPMailer\PHPMailer;

include_once ('My_Notification.php');

class Base_Api_Controller extends API_Controller {

	public function __construct()
	{
		parent::__construct();

        $this->load->model('Notification_model', 'notifications');
        $this->load->model('User_unread_pn_count_model', 'user_unread_pn_counts');
        $this->load->model('User_push_token_model', 'user_push_tokens');
	}

    public function increase_and_get_user_unread_notification_count ($user_id) {
        $unread_count = 1;
        $user_unread_notification_count = $this->user_unread_pn_counts->get_first_one_where('user_id', $user_id);
        if($user_unread_notification_count) {
            $this->user_unread_pn_counts->increment_field($user_unread_notification_count->id, 'unread_count');
            $unread_count = $user_unread_notification_count->unread_count + 1;

        } else {
            $new_unread_count = array(
                'user_id' => $user_id,
                'unread_count' => 1,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_unread_pn_counts->insert($new_unread_count);
        }

        return $unread_count;
    }

    public function reset_user_unread_notification_count ($user_id) {
        $user_unread_notification_count = $this->user_unread_pn_counts->get_first_one_where('user_id', $user_id);
        if($user_unread_notification_count) {
            $this->user_unread_pn_counts->update_field($user_unread_notification_count->id, 'unread_count', 0);

        } else {
            $new_unread_count = array(
                'user_id' => $user_id,
                'unread_count' => 0,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_unread_pn_counts->insert($new_unread_count);
        }
    }

    public function send_push_notification_by_user_ids($user_ids, $notification_data) { // user_ids:array value
        $user_tokens = $this->user_push_tokens->get_users_tokens($user_ids);
        $result = false;
        if(count($user_tokens)>0) {
            $device_ids = array();
            foreach ($user_tokens as $user_token) {
                if($user_token->status == 1) $device_ids[] = $user_token->one_signal_id;
            }
            if (count($device_ids) > 0) {
                $result = $this->send_push_notification_by_devices($device_ids, $notification_data, 1);
            }
        }
        return $result;
    }

    public function send_push_notification_by_user($user_id, $notification_data) {
        $user_tokens = $this->user_push_tokens->get_where('user_id', $user_id);
        if(count($user_tokens)>0) {
            $device_ids = array();
            foreach ($user_tokens as $user_token) {
                if($user_token->status == 1) $device_ids[] = $user_token->one_signal_id;
            }
            if(count($device_ids)>0) {
                $badge_count = $this->increase_and_get_user_unread_notification_count($user_id);
                //$thread_notification = new My_Notification(3, $device_ids, $notification_data, $badge_count);
                //$thread_notification->start();
                return $this->send_push_notification_by_devices($device_ids, $notification_data, $badge_count);

            } else {
                return false;
            }

        } else {
            return false;
        }
    }


    public function send_email_by_phpmailer($email, $username, $subject, $msg, $from_email=EMAIL_FROM_ADDRESS, $from_name=EMAIL_FROM_NAME) {
        $mail = new PHPMailer;

        $mail->isSMTP();                    // Set mailer to use SMTP
        $mail->Host = SMTP_HOST;            // Specify main and backup SMTP servers
        $mail->Port = SMTP_PORT;            // TCP port to connect to
        $mail->SMTPSecure = SMTP_SECURE_MODE;          // Enable TLS encryption, `ssl` also accepted
        $mail->SMTPAuth = true;             // Enable SMTP authentication
        $mail->Username = SMTP_USERNAME;    // SMTP username
        $mail->Password = SMTP_PASSWORD;    // SMTP password

        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $msg;

        return $mail->send();
    }

	public function upload_image($key, $path, $filename_prefix = "image_") {
        if (is_uploaded_file($_FILES[$key]['tmp_name'])) {
            $milliseconds = round(microtime(true) * 1000);
            $fileName = $filename_prefix . $milliseconds . '.png';
            $file_path = $path . $fileName;

            $tmpFile = $_FILES[$key]['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                return $fileName;

            } else {
                $this->response(array("status" => 0, "error" => "Write failed"));
            }
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed."));
        }
    }

    public function upload_image_with_sizes($image) {
        if(!isset($_FILES[$image])) {
            return "";
        }

        if (is_uploaded_file($_FILES[$image]["tmp_name"])) {
            $fileName = $this->_random_filename() . "." . pathinfo($_FILES[$image]["name"], PATHINFO_EXTENSION);
            $filePath = UPLOAD_IMAGE_PATH . $fileName;

            if (move_uploaded_file($_FILES[$image]["tmp_name"], $filePath)) {
                $filePathTV = UPLOAD_IMAGE_PATH . "tv/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(1400, 1400)->save($filePathTV);

                $filePathPC = UPLOAD_IMAGE_PATH . "pc/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(1400, 1400)->save($filePathPC);

                $filePathTablet = UPLOAD_IMAGE_PATH . "tablet/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(1200, 1200)->save($filePathTablet);

                $filePathPhone = UPLOAD_IMAGE_PATH . "phone/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(800, 800)->save($filePathPhone);

                $filePathThumbnail = UPLOAD_IMAGE_PATH . "watch/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(400, 400)->save($filePathThumbnail);

                $filePathThumbnail = UPLOAD_IMAGE_PATH . "icon/" . $fileName;
                \Gregwar\Image\Image::open($filePath)->cropResize(100, 100)->save($filePathThumbnail);

                return $filePath;
            }
            else {
                return "";
            }
        }

        return "";
    }

    public function upload_video($video) {
        if(!isset($_FILES[$video])) {
            return "";
        }

        if (is_uploaded_file($_FILES[$video]["tmp_name"])) {
            $fileName = $this->_random_filename() . "." . pathinfo($_FILES[$video]["name"], PATHINFO_EXTENSION);
            $filePath = UPLOAD_VIDEO_PATH . $fileName;

            if (move_uploaded_file($_FILES[$video]["tmp_name"], $filePath)) {
                return $filePath;
            }
            else {
                return "";
            }
        }

        return "";
    }

    private function _random_filename()
    {
        $seedstr = explode(" ", microtime(), 5);
        $seed    = $seedstr[0] * 10000;
        srand($seed);
        $random  = rand(1000,10000);

        return date("YmdHis", time()) . $random;
    }

    function _getRandomHexString($length) {
        $characters = '0123456789abcdef';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function sendMessageToUser($message, $user_token) {
        if(strlen($user_token) > 0) {
            $fields = array(
                'to' => $user_token,
                'data' => array(
                    'message' => $message
                )
            );
            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FCM_MESSAGING_API);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(FCM_CONTENT_TYPE,
                FCM_SERVER_KEY));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);

        } else {
            $response = json_encode(array(
                "success"  => 0,
                "failure"  => 1
            ));
        }

        return $response;
    }

    function sendMessageForTopic($message, $topic = ""){
        if($topic != "") {
            $fields = array(
                'to' => "/topics/".$topic,
                'data' => array(
                    'message' => $message
                )
            );
            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FCM_MESSAGING_API);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(FCM_CONTENT_TYPE,
                FCM_SERVER_KEY));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);

        } else {
            $response = json_encode(array(
                "error"  => "Topic is not set",
            ));
        }

        return $response;
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getUser() {
        $token = null;
        foreach (getallheaders() as $name => $value) {
            if($name == 'token') {
                $token = $value;
            }
        }
        if($token) {
            $user = $this->users->get_first_one_where('token', $token);
            return $user;
        }

        return null;
    }

    public function send_email($from_email, $to_email, $subject, $message) {
        $from = $from_email;
        $from_name = "Your Motor Parts";
        $to = $to_email;

        $headers = 'From: '.$from_name.'<'.$from.'>' . "\r\n" .
            'Content-type: text/html; charset=utf8' . "\r\n".
            'X-Mailer: PHP/' . phpversion();

        $data = array();
        if(mail($to, $subject, $message, $headers)) {
            $data['status'] = 'success';

        } else {
            $data['status'] = 'fail';
        }
        return $data;
    }

    public function send_email_test_get() {
        $result = $this->send_email_by_phpmailer("jinqianaaa@gmail.com", "Jin", "test", "test");
	    $this->response($result);
    }

    public function php_version_get() {
	    $this->response(phpversion());
    }

}
