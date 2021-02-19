<?php

class Chat_sticker_category_model extends MY_Model {
    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->mainPic = base_url(). UPLOAD_CHAT_STICKER_CATEGORY. $result->main_pic;
        }
        return $result;
    }
}