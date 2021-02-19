<?php 

class Chat_room_user_model extends MY_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Chat_room_model', 'rooms');
        $this->load->model('Chat_message_model', 'messages');
    }

    public $belongs_to = array(
        'user' => array(
            'model'			=> 'Chat_room_model',
            'primary_key'	=> 'room_id'
        ),
        'trainer' => array(
            'model'			=> 'User_model',
            'primary_key'	=> 'user_id'
        )
    );

    protected $order_by = array('created_at', 'ASC');

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->room = $this->rooms->get($result->room_id);
            $result->user = $this->users->get($result->user_id);
            /*$room_messages = $this->messages->set_where(array('room_id' => $result->room_id))->get_all();
            if(count($room_messages)>0) {
                $result->last_message = $room_messages[0];
            }*/
            //$result->elapsed_time = $this->time_elapsed_string(date("Y-m-d H:i:s", $result->last_message->created_at);
        }

        return $result;
    }

}