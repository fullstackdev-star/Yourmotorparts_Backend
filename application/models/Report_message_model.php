<?php 

class Report_message_model extends MY_Model {

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->created_at = $this->time_elapsed_string($result->created_at);
        }

        return $result;
    }

}