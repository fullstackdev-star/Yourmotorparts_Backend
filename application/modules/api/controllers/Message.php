<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message extends Base_Api_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Message_model', 'messages');
        $this->load->model('Notification_model', 'notifications');
    }

    public function message_get($id) {
        $result = array(
            "status" => 1,
            "data" => $this->messages->get($id)
        );
        $this->response($result);
    }

    public function user_messages_get($user_id) {
        $result = array(
            "status" => 1,
            "data" => $this->messages->get_user_messages($user_id)
        );
        $this->response($result);
    }

    public function delete_message_post() {
        $user_id = $this->post('user_id');
        $message_id = $this->post('message_id');

        $message = $this->messages->get($message_id);
        if($message->sender_id == $user_id) {
            $update_data = array('deleted_from_sender' => 1);
        } else {
            $update_data = array('deleted_from_receiver' => 1);
        }
        $result = array(
            "status" => 1,
            "data" => $this->messages->update($message_id, $update_data)
        );
        $this->response($result);
    }

    public function delete_dialog_post() {
        $user_id = $this->post('user_id');
        $message_id = $this->post('message_id');
        $message = $this->messages->get($message_id);

        if (!$message) {
            $result = array(
                "status" => 0,
                "data" => "Invalid message"
            );
            $this->response($result);
        }

        if (!isset($message->prodcut_id) || !$message->prodcut_id) {
            $update_data = array('deleted_from_sender' => 1);
            $this->messages->update($message_id, $update_data);

            $result = array(
                "status" => 1,
                "data" => "success"
            );
            $this->response($result);
        }

        $user_messages = $this->messages->get_where(array(
            "sender_id" => $user_id,
            "receiver_id" => $message->receiver_id,
            "product_id" => $message->prodcut_id,
            "deleted_from_sender" => 0,
            "message_status" =>1,
        ));
        foreach ($user_messages as $user_message) {
            $update_data = array('deleted_from_sender' => 1);
            $this->messages->update($user_message->id, $update_data);
        }

        $user_oppo_messages = $this->messages->get_where(array(
            "receiver_id" => $user_id,
            "sender_id" => $message->receiver_id,
            "product_id" => $message->prodcut_id,
            "deleted_from_receiver" => 0,
            "message_status" =>1,
        ));
        foreach ($user_oppo_messages as $user_oppo_message) {
            $update_data = array('deleted_from_receiver' => 1);
            $this->messages->update($user_oppo_message->id, $update_data);
        }

        $result = array(
            "status" => 1,
            "data" => "success"
        );
        $this->response($result);
    }

    public function new_message_post() {
        $sender_id = $this->post('sender_id');
        $receiver_id = $this->post('receiver_id');
        $parent_id = $this->post('parent_id');
        $message = $this->post('message');
        $product_id = $this->post('product_id');
        $notification_id = $this->post('notification_id');
        $product_name = $this->post('product_name');

        $new_message = array(
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'parent_id' => $parent_id,
            'message' => $message,
            'created_at' => date("Y-m-d H:i:s"),
            'product_id' => $product_id,
            'notification_id' => $notification_id,
            'product_name' => $product_name
        );
        $new_message_id = $this->messages->insert($new_message);
        $message = $this->messages->get($new_message_id);
        $this->send_message_push($message);

        $result = array(
            "status" => 1,
            "data" => $this->messages->get($new_message_id)
        );
        $this->response($result);
    }

    public function send_message_push($message) {
        $sender = $message->sender;
        $new_notification = array(
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'type'  => '2',
            'title' => 'New message from '. $sender->full_name,
            'content' => $message->message,
            'created_at' => date("Y-m-d H:i:s")
        );
        //$new_notification_id = $this->notifications->insert($new_notification);
        //$new_notification = $this->notifications->get($new_notification_id);
        unset($message->sender);
        unset($message->receiver);
        unset($message->parent_message);
        $data = array(
            'notification_type' => USER_MESSAGE,
            'notification' => $new_notification,
            'message' => $message
        );

        $push_message = $message->message;
        $notification_data = $this->notification_data($data, $push_message, "New message from ". $sender->full_name);
        $this->send_push_notification_by_user($message->receiver_id, $notification_data);
    }

    public function update_read_status_post() {
        $user_id = $this->post('user_id');
        $message_id = $this->post('message_id');
        $is_read = $this->post('is_read');

        $message = $this->messages->get($message_id);
        if($message->sender_id == $user_id) {
            $opponent_id = $message->receiver_id;
        } else {
            $opponent_id = $message->sender_id;
        }

        if($message->notification_id == 0) {
            $where = array(
                "receiver_id" => $user_id,
                "sender_id" => $opponent_id,
                "product_id" => $message->product_id
            );
        } else {
            $where = array(
                "receiver_id" => $user_id,
                "sender_id" => $opponent_id,
                "notification_id" => $message->notification_id
            );
        }
        $messages = $this->messages->get_where($where);
        $updated_messages = [];
        if($is_read == 1) {
            foreach ($messages as $message) {
                if($message->is_read == 0) {
                    $update_data = array('is_read' => $is_read);
                    $this->messages->update($message->id, $update_data);
                    $updated_messages[] = $this->messages->get($message->id);
                }
            }
        } else {
            if(count($messages)>0) {
                $message = $messages[0];
                $update_data = array('is_read' => $is_read);
                $this->messages->update($message->id, $update_data);
                $updated_messages[] = $this->messages->get($message->id);
            }
        }

        $result = array(
            "status" => 1,
            "data" => $is_read,
            "messages" => $updated_messages
        );
        $this->response($result);
    }

    public function notifications_get() {
        $messages = $this->messages->get_all();
        foreach ($messages as $message) {
            $this->messages->update_field($message->id, 'created_on', strtotime($message->created_at));
        }
        $result = array(
            "status" => 1,
            "data" => $this->messages->get_all()
        );
        $this->response($result);
    }

    public function users_requests_get() {
        $search_key = $this->get('search_key');
        $offset = $this->get('offset');
        $limit = $this->get('limit');

        $where = array(
            'notification_status' => 1,
            );
        $other_where = "(content LIKE '%$search_key%') and sender_id > 1";
        $sort_by = array('created_at', 'DESC');
        $data = $this->notifications->paginate_search($offset, $limit, $where, $other_where, $sort_by);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

}
