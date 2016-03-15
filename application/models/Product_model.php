<?php 

class Product_model extends MY_Model {

    protected $order_by = array('created_at', 'DESC');
    protected $where = array('product_status' => '1');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Sub_category_model', 'sub_categories');
        $this->load->model('Model_model', 'models');
        $this->load->model('Product_image_model', 'product_images');
    }

    protected function callback_after_get($result)
    {
        if ( !empty($result) ) {
            $result->owner = $this->users->get($result->owner_id);
            $sub_category = $this->sub_categories->get($result->sub_category_id);
            $model = $this->models->get($result->model_id);
            $result->category_id = $sub_category->category_id;
            $result->make_id = $model->make_id;

            $photos = [];
            $product_photos = $this->product_images->get_where("product_id", $result->id);
            foreach ($product_photos as $product_photo) {
                $photos[] = $product_photo->filename;
            }
            $result->photos = $photos;
            $result->product_images = $product_photos;
            $result->remaining_time_label = $this->getRemainTimeLabel($result);
        }

        return $result;
    }

    public function getListingTimeInHours($product) {
        if($product->owner_premium_type_when_create==3) {
            return 365*24;
        } else if($product->owner_premium_type_when_create==2) {
            return 182*24;
        } else if($product->owner_premium_type_when_create==1) {
            return 91*24;
        } else {
            return 30*24;
        }
    }

    public function getRemainingTimeInHours($product) {
        $now = time(); // or your date as well
        $your_date = strtotime($product->created_at);
        $time_diff = $now - $your_date;
        $passedHours = round($time_diff / 3600);
        return $this->getListingTimeInHours($product) - $passedHours;
    }

    public function getRemainTimeLabel($product) {
        $remainHours = $this->getRemainingTimeInHours($product);
        $remainDays = intval($remainHours / 24);
        $remainHours = $remainHours - $remainDays * 24;
        return $remainDays>0?''.$remainDays.'d'.($remainHours>0?' '.$remainHours.'h':''):($remainHours>0?''.$remainHours.'h':'0h');
    }

    public function get_products_with_ids($product_ids) {
        if(!$product_ids || $product_ids == '') return [];

        $query = "SELECT * FROM products where id in ($product_ids)";

        $result = $this->db->query($query)->result();
        $data = [];
        foreach ($result as $item) {
            $data[] = $this->callback_after_get($item);
        }

        return $data;
    }

}