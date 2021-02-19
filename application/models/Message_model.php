<?php 

class Message_model extends MY_Model {

    protected $order_by = array('created_at', 'ASC');
    protected $where = array('message_status' => '1');

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            //$result->created_at = $this->time_elapsed_string($result->created_at);
            $result->sender = $this->users->get($result->sender_id);
            $result->receiver = $this->users->get($result->receiver_id);
            if($result->parent_id>0) {
                $result->parent_message = $this->get($result->parent_id);
            }
            $result->created_at_seconds = strtotime($result->created_at);
        }

        return $result;
    }

    public function get_user_messages($user_id) {
        $sql = "SELECT * FROM `messages` WHERE message_status = 1 AND ((sender_id = '$user_id' AND deleted_from_sender = 0) 
                OR (receiver_id = '$user_id' AND deleted_from_receiver = 0)) ORDER BY created_at ASC";
        $messages = $this->db->query($sql)->result();
        $data = [];
        foreach ($messages as $message) {
            if($message->sender_id == $user_id && $message->deleted_from_sender == 0) {
                $data[] = $this->callback_after_get($message);
            } else if($message->receiver_id == $user_id && $message->deleted_from_receiver == 0) {
                $data[] = $this->callback_after_get($message);
            }
        }
        return $data;
    }

}