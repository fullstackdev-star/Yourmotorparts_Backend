<?php 

class Users_review_model extends MY_Model {

    protected $order_by = array('created_at', 'DESC');

    public function __construct()
    {
        parent::__construct();
    }

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->customer = $this->users->get($result->customer_id);
        }

        return $result;
    }

    public function get_photo($id)
    {
        $query = "SELECT photo FROM users WHERE id = $id LIMIT 1";
        $data = $this->db->query($query)->result();
        if($data) {
            return $data[0]->photo;
        }
        return null;
    }

}