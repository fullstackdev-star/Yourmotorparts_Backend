<?php 

class Chat_message_model extends MY_Model {

    protected $order_by = array('created_at', 'DESC');
    protected $where = array('message_status' => '1');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Chat_room_model', 'rooms');
        $this->load->model('User_model', 'users');
        $this->load->model('Chat_file_model', 'files');
        $this->load->model('Chat_message_seen_model', 'message_seens');
    }

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->user = $this->users->get($result->user_id);
            //$result->room = $this->rooms->get($result->room_id);

            if($result->type == 2) {
                $file = $this->files->get($result->message);
                if($file) {
                    if ($file->thumb_id > 0) {
                        $file_data = array('file' => $file, 'thumb' => $this->files->get($file->thumb_id));
                    } else {
                        $file_data = array('file' => $file);
                    }
                    $result->file = $file_data;
                }
            }

            $seens_search_key = array(
                'message_id' => $result->id,
                'seen_status' => 1);
            $message_seens = $this->message_seens->set_where($seens_search_key)->get_all();
            $result->seenBy = $message_seens;
        }

        return $result;
    }

    public function get_room_messages_with_latest_id_and_limit($roomId, $latestId=0, $limit=50) {
        if($latestId>0) {
            $query = "SELECT * FROM (SELECT * FROM chat_messages ".
                "WHERE room_id = $roomId AND id < $latestId ".
                "ORDER BY id DESC LIMIT $limit) sub ORDER BY id ASC";
        } else {
            $query = "SELECT * FROM (SELECT * FROM chat_messages ".
                "WHERE room_id = $roomId ".
                "ORDER BY id DESC LIMIT $limit) sub ORDER BY id ASC";
        }

        $result = $this->db->query($query)->result();
        $data = [];
        foreach ($result as $item) {
            $data[] = $this->callback_after_get($item);
        }

        return $data;
    }

    public function get_room_latest_messages_with_latest_id($roomId, $latestId=0) {
        if($latestId>0) {
            $query = "SELECT * FROM chat_messages ".
                "WHERE room_id = $roomId AND id > $latestId ".
                "ORDER BY id ASC";
        } else {
            // getting latest 50 messages
            $query = "SELECT * FROM (SELECT * FROM chat_messages ".
                "WHERE room_id = $roomId ".
                "ORDER BY id DESC LIMIT 50) sub ORDER BY id ASC";
        }

        $result = $this->db->query($query)->result();
        $data = [];
        foreach ($result as $item) {
            $data[] = $this->callback_after_get($item);
        }

        return $data;
    }

}