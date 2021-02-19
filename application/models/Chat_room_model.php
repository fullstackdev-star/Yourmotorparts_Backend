<?php 

class Chat_room_model extends MY_Model {
    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            if($result->avatar_url && $result->avatar_url != "" && strpos($result->avatar_url, 'http') === false) {
                $result->avatar_url = base_url() . UPLOAD_CHAT_ROOM_PHOTO . $result->avatar_url;
            }
            //$result->creator = $this->users->get($result->creator_id);
            /*if($result->contact_id && $result->contact_id>0) {
                $this->load->model('User_contact_model', 'user_contacts');
                $result->contact = $this->user_contacts->get($result->contact_id);
            }*/
        }

        return $result;
    }
}