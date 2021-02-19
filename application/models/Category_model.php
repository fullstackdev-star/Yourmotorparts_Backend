<?php 

class Category_model extends MY_Model {

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            if(strpos($result->image_url, "http://") === false){
                $result->image_url = base_url().UPLOAD_PROFILE_PHOTO.$result->image_url;
            }
        }

        return $result;
    }

}