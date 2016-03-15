<?php

class Chat_sticker_model extends MY_Model {
    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->fullPic = base_url(). UPLOAD_CHAT_STICKERS_FULL_SIZE. $result->full_pic;
            $result->smallPic = base_url(). UPLOAD_CHAT_STICKERS_SMALL_SIZE. $result->small_pic;
        }
        return $result;
    }
}