<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends Base_Api_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_notification_model', 'user_notifications');
    }

    public function finding_parts_messages_get($user_id) {
        $messages = $this->user_notifications->get_user_notifications($user_id);
        $result = array(
            "status" => 1,
            "data" => $messages
        );
        $this->response($result);
    }

    public function delete_notification_post() {
        $user_id = $this->post('user_id');
        $notification_id = $this->post('notification_id');

        $search = array(
            'user_id' => $user_id,
            'notification_id' => $notification_id
        );
        $user_notification = $this->user_notifications->get_first_one_where($search);
        if($user_notification) {
            $data = $this->user_notifications->update($user_notification->id, array('is_deleted' => true));

        } else {
            $new_user_notification = array(
                'user_id' => $user_id,
                'notification_id' => $notification_id,
                'created_at' => time(),
                'is_deleted' => true
            );
            $data = $this->user_notifications->insert($new_user_notification);
        }

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function update_read_status_post() {
        $user_id = $this->post('user_id');
        $notification_id = $this->post('notification_id');
        $is_read = $this->post('is_read');

        $search = array(
            'user_id' => $user_id,
            'notification_id' => $notification_id
        );
        $user_notification = $this->user_notifications->get_first_one_where($search);
        if($user_notification) {
            $data = $this->user_notifications->update($user_notification->id, array('is_read' => true));

        } else {
            $new_user_notification = array(
                'user_id' => $user_id,
                'notification_id' => $notification_id,
                'created_at' => time(),
                'is_read' => $is_read==1
            );
            $data = $this->user_notifications->insert($new_user_notification);
        }

        $result = array(
            "status" => 1,
            "data" => $is_read==1?"1":"0"
        );
        $this->response($result);
    }

}