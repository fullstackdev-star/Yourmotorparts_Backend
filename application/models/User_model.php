<?php 

class User_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_address_model', 'user_addresses');
    }

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->name = $result->username;
            $result->addresses = $this->user_addresses->get_where('user_id', $result->id);
            if($result->photo && $result->photo != "") {
                $result->photo = base_url() . UPLOAD_PROFILE_PHOTO . $result->photo;
            }
        }

        unset($result->ip_address);
        unset($result->password);
        unset($result->salt);
        unset($result->activation_code);
        unset($result->forgotten_password_code);
        unset($result->forgotten_password_time);
        unset($result->remember_code);
        unset($result->last_login);

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