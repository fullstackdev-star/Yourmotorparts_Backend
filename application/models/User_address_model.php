<?php 

class User_address_model extends MY_Model {

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            //$result->user = $this->users->get($result->user_id);
        }
        return $result;
    }

}