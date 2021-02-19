<?php 

class Chat_file_model extends MY_Model {

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            if($result->is_thumb>0) {
                $result->downloadUrl = base_url() . UPLOAD_CHAT_THUMBS . $result->name;
            } else {
                $result->downloadUrl = base_url() . UPLOAD_CHAT_FILES . $result->name;
            }
        }
        return $result;
    }

}