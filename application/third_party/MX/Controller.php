<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/** load the CI class for Modular Extensions **/
require dirname(__FILE__).'/Base.php';

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library replaces the CodeIgniter Controller class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Controller.php
 *
 * @copyright	Copyright (c) 2015 Wiredesignz
 * @version 	5.5
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Controller 
{
	public $autoload = array();
	
	public function __construct() 
	{
		$class = str_replace(CI::$APP->config->item('controller_suffix'), '', get_class($this));
		log_message('debug', $class." MX_Controller Initialized");
		Modules::$registry[strtolower($class)] = $this;	
		
		/* copy a loader instance and initialize */
		$this->load = clone load_class('Loader');
		$this->load->initialize($this);	
		
		/* autoload module items */
		$this->load->_autoloader($this->autoload);
	}
	
	public function __get($class) 
	{
		return CI::$APP->$class;
	}

    //////////////////////////////////////////////////////////////
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function send_push_notification($fields) {
        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ONE_SIGNAL_API);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(API_CONTENT_TYPE, ONE_SIGNAL_AUTHORIZATION));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function get_field($notification_data) {
        $fields = array(
            'app_id' => ONE_SIGNAL_APP_ID,
            'headings' => $notification_data['title'],
            'contents' => $notification_data['content'],
            'data' => $notification_data['data']
        );

        return $fields;
    }

    public function notification_data($data, $content = "Test", $title = "Your Motor Parts") {
        $result = array(
            "title" => array( "en" => $title ),
            "content" => array( "en" => $content ),
            "data" => $data
        );

        return $result;
    }

    public function send_push_notification_all($notification_data) {
        $fields = $this->get_field($notification_data);
        $fields['included_segments'] = array('All');

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_filters($filters, $notification_data) {
        $fields = $this->get_field($notification_data);
        $fields['filters'] = $filters;

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_devices($user_one_signal_ids, $notification_data, $badge_count = 0) {
        if($badge_count==0) {
            $notification_data['data']['unread_count'] = 1;
        } else {
            $notification_data['data']['unread_count'] = $badge_count;
        }

        $fields = $this->get_field($notification_data);
        $fields['include_player_ids'] = $user_one_signal_ids;
        if($badge_count==0) {
            $fields['ios_badgeType'] = 'Increase';
            $fields['ios_badgeCount'] = 1;

        } else {
            $fields['ios_badgeType'] = 'SetTo';
            $fields['ios_badgeCount'] = $badge_count;
        }

        return $this->send_push_notification($fields);
    }

    public function send_push_notification_by_device($user_one_signal_id, $notification_data, $badge_count) {
        $one_signal_ids[] = $user_one_signal_id;
        return $this->send_push_notification_by_devices($one_signal_ids, $notification_data, $badge_count);
    }


    public function create_notification1()
    {
        // (optional) only top-level admin user groups can create Admin User
        //$this->verify_auth(array('webmaster'));
        $form = $this->form_builder->create_form();
        if ($form->validate()) {
            // passed validation
            $message = $this->input->post('message');
            $now = date("Y-m-d H:i:s");

            $data = array(
                'title' => "Your Motor Parts",
                'content' => $message,
                'sent_date' => $now
            );

            $users = $this->users->get_all();
            $user_tokens = [];
            foreach ($users as $user) {
                $user_token = $this->userFcmTokens->get_first_one_where('user_id', $user->id);
                if($user_token) {
                    $user_tokens[] = $user_token->token;
                }
            }

            $response = $this->sendMessageForTopic($message, "news");
            $resultObject = json_decode($response);
            print $response;
            if(isset($resultObject->error) && $resultObject->error) {
                $this->system_message->set_error($resultObject->error);

            } else {
                $messages = "Sent Successfully";
                $this->system_message->set_success($messages);

                $new_id = $this->notifications->insert($data);

                foreach ($users as $user) {
                    $insertUserNotificationData = array(
                        "user_id" => $user->id,
                        "notification_id" => $new_id,
                        "created_at" => $now
                    );
                    $this->userNotifications->insert($insertUserNotificationData);
                }
            }

            /*$response = $this->sendMessageToUsers($message, $user_tokens);
            $resultObject = json_decode($response);
            print $response;

            if ($resultObject->success) {
                $messages = "Sent Successfully";
                $this->system_message->set_success($messages);

                $new_id = $this->notifications->insert($data);

                foreach ($users as $user) {
                    $insertUserNotificationData = array(
                        "user_id" => $user->id,
                        "notification_id" => $new_id,
                        "created_at" => $now
                    );
                    $this->userNotifications->insert($insertUserNotificationData);
                }
            } else {
                $errors = "Failed";
                $this->system_message->set_error($errors);
            }*/

            refresh();
        }

        $this->mPageTitle = 'Create Notification';
        $this->mViewData['form'] = $form;
        $this->render('notification/notification_create');
    }

    function sendMessageToUsers($message, $user_tokens = []) {
        if(sizeof($user_tokens) > 0) {
            print json_encode($user_tokens);
            $fields = array(
                'to' => json_encode($user_tokens),
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


}