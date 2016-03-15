<?php 

class Chat_message_seen_model extends MY_Model {

    protected $order_by = array('created_at', 'DESC');

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {

        }

        return $result;
    }

}