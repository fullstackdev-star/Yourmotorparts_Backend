<?php 

class User_favorite_model extends MY_Model {

    protected $order_by = array('created_at', 'DESC');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_model', 'products');
    }

    protected function callback_after_get($result)
    {
        if (!empty($result)) {
            $result->product = $this->products->get($result->product_id);
        }

        return $result;
    }

}