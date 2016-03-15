<?php 

class User_fcm_token_model extends MY_Model {
    protected function callback_after_get($result)
    {
        return $result;
    }
}