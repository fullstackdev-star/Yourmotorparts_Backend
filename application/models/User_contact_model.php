<?php 

class User_contact_model extends MY_Model {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'users');
    }

    protected $order_by = array('created_at', 'DESC');
    protected $where = array('user_contact_status' => 1);

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->user = $this->users->get($result->user_id);
            $result->opponent = $this->users->get($result->opponent_id);
        }
        return $result;
    }

    public function get_contacts($user_id) {
        /*$sql = "SELECT * FROM `user_contacts` WHERE user_status = 1 AND opponent_status = 1 AND user_id = '$user_id' OR ".
                        "user_status = 1 AND opponent_status = 1 AND opponent_id = '$user_id' OR ".
                        "user_status = 0 AND opponent_status = 1 AND user_id = '$user_id' OR ".
                        "user_status = 1 AND opponent_status = 0 AND opponent_id = '$user_id'";*/

        $sql = "SELECT * FROM `user_contacts` WHERE user_contact_status = 1 AND (user_id = '$user_id' OR opponent_id = '$user_id')";
        return $this->db->query($sql)->result();
    }

}