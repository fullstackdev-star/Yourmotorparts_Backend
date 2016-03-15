<?php 

class Model_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Make_model', 'makes');
    }

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            if(strpos($result->image_url, "http://") === false){
                $result->image_url = base_url().UPLOAD_PROFILE_PHOTO.$result->image_url;
                $result->make = $this->makes->get($result->make_id);
            }
        }

        return $result;
    }

}