<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->load->model('Product_model', 'products');
        $this->load->model('Product_image_model', 'product_images');
	}

	// Frontend User CRUD
	public function index($category_id = 0, $make_id = 0)
	{
		$crud = $this->generate_crud('products');
		$crud->columns('id', 'name', 'sub_category_id', 'content', 'type', 'grade', 'year', 'model_id', 'mileage', 'stock', 'owner_id',
            'price', 'images', 'views_count', 'favorites_count', 'created_at', 'updated_at', 'seller_request', 'required_at',
            'ended_at', 'product_status');
		$this->unset_crud_fields('views_count', 'favorites_count');

        $crud->display_as('sub_category_id', 'Sub Category');
        $crud->display_as('views_count', '<i class="fa fa-eye" aria-hidden="true"></i> Views');
        $crud->display_as('favorites_count', '<i class="fa fa-heart" aria-hidden="true" style="color: red"></i> Favorites');

        $crud->callback_column('views_count', array($this, 'callback_views_count'));
        $crud->callback_column('favorites_count', array($this, 'callback_favorites_count'));

        $crud->display_as('owner_id', 'Seller');
        $crud->set_relation('sub_category_id', 'sub_categories', 'title');
        $crud->display_as('model_id', 'Model');
        $crud->set_relation('model_id', 'models', 'title');

        //$crud->display_as('make_id', 'Make');
        //$crud->display_as('model_id', 'Model');
        $crud->set_relation('owner_id', 'users', 'username', array('user_type' => 1));

        for ($i=1980; $i<=date("Y"); $i++) {
            $years[] = $i;
        }
        $crud->field_type('year', 'enum', $years);

        $crud->callback_column('images', array($this, 'callback_product_images'));

        $crud->set_subject('Part');
        $this->mPageTitle = 'Parts';

        $this->load->model('Category_model', 'categories');
        $this->load->model('Sub_category_model', 'sub_categories');
        $categories = $this->categories->get_all();
        $selected_category_id = 1;
        if($category_id && is_numeric($category_id) && $category_id>0) {
            $selected_category_id = $category_id;
        } else {
            if(count($categories)>0) {
                $selected_category_id = $categories[0]->id;
            }
        }
        if($selected_category_id>0) {
            $crud->set_relation('sub_category_id', 'sub_categories', 'title', array('category_id' => $selected_category_id));
            /*$sub_categories = $this->sub_categories->get_where('category_id', $selected_category_id);
            $sub_category_titles = [];
            foreach ($sub_categories as $sub_category) {
                $sub_category_titles[] = $sub_category->title;
            }
            $crud->field_type('sub_category_id', 'enum', $sub_category_titles);*/

            $this->mViewData['product_category'] = true;
            $this->mViewData['selected_category_id'] = $selected_category_id;
            $this->mViewData['categories'] = $categories;
            $crud->where('category_id', $selected_category_id);
        }

        $this->load->model('Make_model', 'makes');
        $this->load->model('Model_model', 'models');
        $makes = $this->makes->get_all();
        $selected_make_id = 1;
        if($make_id && is_numeric($make_id) && $make_id>0) {
            $selected_make_id = $make_id;
        } else {
            if(count($makes)>0) {
                $selected_make_id = $makes[0]->id;
            }
        }
        if($selected_make_id>0) {
            $crud->set_relation('model_id', 'models', 'title', array('make_id' => $selected_make_id));

            $this->mViewData['product_make'] = true;
            $this->mViewData['selected_make_id'] = $selected_make_id;
            $this->mViewData['makes'] = $makes;
            $crud->where('make_id', $selected_make_id);
        }

        if ($this->ion_auth->in_group(array('webmaster'))) {

        } else if ($this->ion_auth->in_group(array('admin'))) {
            $crud->where('owner_id', $this->mUser->id);
            $crud->unset_fields('owner_id');
            $crud->callback_after_insert(array($this, 'callback_product_after_insert'));
        }

		$this->render_crud();
	}

    public function user_info($user_id)
    {
        $crud = $this->generate_crud('products');
        $crud->columns('id', 'name', 'sub_category_id', 'content', 'type', 'grade', 'year', 'model_id', 'mileage', 'stock', 'owner_id',
            'price', 'images', 'views_count', 'favorites_count', 'created_at', 'updated_at', 'seller_request', 'required_at',
            'ended_at', 'product_status');
        $this->unset_crud_fields('views_count', 'favorites_count');

        $crud->display_as('sub_category_id', 'Sub Category');
        $crud->display_as('views_count', '<i class="fa fa-eye" aria-hidden="true"></i> Views');
        $crud->display_as('favorites_count', '<i class="fa fa-heart" aria-hidden="true" style="color: red"></i> Favorites');

        $crud->callback_column('views_count', array($this, 'callback_views_count'));
        $crud->callback_column('favorites_count', array($this, 'callback_favorites_count'));

        $crud->display_as('owner_id', 'Seller');
        $crud->set_relation('sub_category_id', 'sub_categories', 'title');
        $crud->display_as('model_id', 'Model');
        $crud->set_relation('model_id', 'models', 'title');

        //$crud->display_as('make_id', 'Make');
        //$crud->display_as('model_id', 'Model');
        $crud->set_relation('owner_id', 'users', 'username', array('user_type' => 1));

        for ($i=1980; $i<=date("Y"); $i++) {
            $years[] = $i;
        }
        $crud->field_type('year', 'enum', $years);

        $crud->callback_column('images', array($this, 'callback_product_images'));

        $crud->unset_add();
        $crud->unset_edit();

        $crud->set_subject('Part');
        $this->mPageTitle = 'User Info';

        $crud->where('owner_id', $user_id);

        $user = $this->users->get($user_id);

        // for addresses
        $data = $user->addresses;
        if(count($data)>0) {
            $this->load->library('googlemaps');
            $center_lat = $data[0]->lat;
            $center_lng = $data[0]->lng;
            $config['center'] = "$center_lat, $center_lng";
            $config['zoom'] = 'auto';
            $config['map_height'] = '500px';
            $this->googlemaps->initialize($config);

            foreach($data as $d) {
                if($d->lat==0 && $d->lng==0) continue;
                $user = $this->users->get($d->user_id);

                $marker = array();
                $marker['position'] = $d->lat . ',' . $d->lng;
                $marker['infowindow_content'] = "<a href='".base_url()."admin/user/index/read/".$d->user_id."' class='image-thumbnail'><img style='width:50px; height:50px; object-fit:cover' class='img-circle' src='".$user->photo."'/>"."&nbsp&nbsp&nbsp ".$user->username."(".$d->user_id.")</a>"."<p><br/>".$d->address;
                $this->googlemaps->add_marker($marker);
            }
            $this->mViewData['map'] = $this->googlemaps->create_map();
        }
        //

        $this->mViewData['user_info'] = $user;
        $this->render_crud();
    }

    public function pending_list() {
        $crud = $this->generate_crud('products');
        $crud->columns('id', 'name', 'sub_category_id', 'content', 'type', 'grade', 'year', 'model_id', 'mileage', 'stock', 'owner_id',
            'price', 'images', 'views_count', 'favorites_count', 'created_at', 'updated_at', 'product_status', 'seller_request', 'required_at',
            'ended_at');
        $this->unset_crud_fields('views_count', 'favorites_count');

        $crud->display_as('sub_category_id', 'Sub Category');
        $crud->display_as('views_count', '<i class="fa fa-eye" aria-hidden="true"></i> Views');
        $crud->display_as('favorites_count', '<i class="fa fa-heart" aria-hidden="true" style="color: red"></i> Favorites');

        $crud->callback_column('views_count', array($this, 'callback_views_count'));
        $crud->callback_column('favorites_count', array($this, 'callback_favorites_count'));

        $crud->display_as('owner_id', 'Seller');
        $crud->set_relation('sub_category_id', 'sub_categories', 'title');
        $crud->display_as('model_id', 'Model');
        $crud->set_relation('model_id', 'models', 'title');

        //$crud->display_as('make_id', 'Make');
        //$crud->display_as('model_id', 'Model');
        $crud->set_relation('owner_id', 'users', 'username', array('user_type' => 1));

        for ($i=1980; $i<=date("Y"); $i++) {
            $years[] = $i;
        }
        $crud->field_type('year', 'enum', $years);

        $crud->callback_column('images', array($this, 'callback_product_images'));

        $crud->where('seller_request_status', 'pending');

        $crud->set_subject('Part');
        $this->mPageTitle = 'Pending Parts';

        $this->render_crud();
    }

    public function callback_product_images($value, $row)
    {
        $product_images = $this->product_images->get_where('product_id', $row->id);
        $items = '';
        foreach ($product_images as $product_image) {
            $items .= '<li><img style="width: 80px; height:50px" src="' . $product_image->filename . '"></li>';
        }
        return '<a href="' . base_url() . 'admin/product/images/' . $row->id . '"><ul class="h-images" style="width: 200px;">' . $items . '</ul></a>';
    }

    public function callback_views_count($value, $row)
    {
        $this->load->model('User_view_model', 'views');
        $user_views = $this->views->get_where('product_id', $row->id);
        return "".count($user_views);
    }

    public function callback_favorites_count($value, $row)
    {
        $this->load->model('User_favorite_model', 'favorites');
        $search = array(
            'product_id' => $row->id,
            'favor_status' => 1
        );
        $user_favorites = $this->favorites->get_where($search);
        return "".count($user_favorites);
    }

    public function callback_product_after_insert($post_array, $primary_key) {
        return $this->products->update_field($primary_key, 'owner_id', $this->mUser->id);
    }

    public function images($product_id = '')
    {
        if ($product_id == "upload_file" || $product_id == "delete_file") {
            $crud = $this->generate_image_crud('product_images', 'filename', UPLOAD_PRODUCTS, 'product_id');
            $crud->callback_after_insert(array($this, 'callback_images_after_insert'));
            $this->mPageTitle = 'Product Images';
            $this->mViewData['product_image_category'] = true;
            $this->mViewData['selected_product_id'] = $product_id;
            $this->mViewData['products'] = $this->products->get_all();
            $this->render_crud();

        } else {
            if ($this->ion_auth->in_group(array('webmaster', 'admin'))) {
                if (!empty($product_id) && $this->products->get($product_id)) {
                    $product = $this->products->get($product_id);
                    $crud = $this->generate_image_crud('product_images', 'filename', UPLOAD_PRODUCTS, 'product_id');
                    $crud->callback_after_insert(array($this, 'callback_images_after_insert'));
                    $this->mPageTitle = 'Product - ' . $product->name . ' Images';
                    $this->mViewData['product_image_category'] = true;
                    $this->mViewData['selected_product_id'] = $product_id;
                    $this->mViewData['products'] = $this->products->get_all();
                    $this->render_crud();

                } else {
                    $products = $this->products->get_all();
                    if (count($products) > 0) {
                        $first_product = $products[0];
                        redirect('admin/product/images/' . $first_product->id);
                    }
                }

            } else {
                if (!empty($product_id) && $this->products->get($product_id)) {
                    $product = $this->products->get($product_id);
                    if ($product->venue_id == $this->mUser->venue_id) {
                        $crud = $this->generate_image_crud('product_images', 'filename', UPLOAD_PRODUCTS, 'product_id');
                        $crud->callback_after_insert(array($this, 'callback_images_after_insert'));
                        $this->mPageTitle = 'Product - ' . $product->name . ' Images';
                        $this->mViewData['product_image_category'] = true;
                        $this->mViewData['selected_product_id'] = $product_id;
                        $this->mViewData['products'] = $this->products->get_where('venue_id', $this->mUser->venue_id);
                        $this->render_crud();

                    } else {
                        $products = $this->products->limit(1)->get_where('venue_id', $this->mUser->venue_id);
                        if (count($products) > 0) {
                            $first_product = $products[0];
                            redirect('admin/product/images/' . $first_product->id);
                        }
                    }
                } else {
                    $products = $this->products->limit(1)->get_where('venue_id', $this->mUser->venue_id);
                    if (count($products) > 0) {
                        $first_product = $products[0];
                        redirect('admin/product/images/' . $first_product->id);
                    }
                }
            }
        }
    }

    public function callback_images_after_insert($data, $id)
    {
        //$this->event_images->update_field($id, 'event_title', $this->events->get($data['event_id'])->title);
        //$this->system_message->set_success("error");
    }

    public function callback_images_after_delete($id)
    {

    }


    public function category() {
        $crud = $this->generate_crud('categories');
        $crud->columns('title', 'image_url');
        $this->unset_crud_fields('id');

        $state = $crud->getState();
        if ($state == 'list' || $state == 'success' || $state == 'read') { // || $state == 'ajax_list_info' || $state == 'ajax_list'
            $crud->callback_column('image_url', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('image_url', UPLOAD_PROFILE_PHOTO);
        }

        $this->mPageTitle = 'Category';
        $this->render_crud();
    }

    public function callback_profile_photo($value, $row) {
        if(strlen($value)==0) {
            return "";
        }
        if (strpos($value, 'http') !== false) {
            return "<img style='width:50px; height:50px object-fit:cover' class='img-circle' src='".$value."'></>";

        } else {
            $photo = base_url() . UPLOAD_PROFILE_PHOTO . $value;
            return "<a href='". $photo ."' class='image-thumbnail'><img style='width:50px; height:50px; object-fit:cover' class='img-circle' src='".$photo."'></></a>";
        }
    }

    public function sub_category() {
        $crud = $this->generate_crud('sub_categories');
        $crud->columns('category_id', 'title', 'image_url');
        $this->unset_crud_fields('id');

        $crud->set_relation('category_id', 'categories', 'title');

        $state = $crud->getState();
        if ($state == 'list' || $state == 'success' || $state == 'read') { // || $state == 'ajax_list_info' || $state == 'ajax_list'
            $crud->callback_column('image_url', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('image_url', UPLOAD_PROFILE_PHOTO);
        }

        $this->mPageTitle = 'Sub Category';
        $this->render_crud();
    }

    public function make_list() {
        $crud = $this->generate_crud('makes');
        $crud->columns('title', 'image_url');
        $this->unset_crud_fields('id');

        $state = $crud->getState();
        if ($state == 'list' || $state == 'success' || $state == 'read') { // || $state == 'ajax_list_info' || $state == 'ajax_list'
            $crud->callback_column('image_url', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('image_url', UPLOAD_PROFILE_PHOTO);
        }

        $this->mPageTitle = 'Makes';
        $this->render_crud();
    }

    public function model_list() {
        $crud = $this->generate_crud('models');
        $crud->columns('make_id', 'title', 'image_url');
        $this->unset_crud_fields('id');

        $crud->display_as('make_id', 'Make');
        $crud->set_relation('make_id', 'makes', 'title');

        $state = $crud->getState();
        if ($state == 'list' || $state == 'success' || $state == 'read') { // || $state == 'ajax_list_info' || $state == 'ajax_list'
            $crud->callback_column('image_url', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('image_url', UPLOAD_PROFILE_PHOTO);
        }

        $this->mPageTitle = 'Model';
        $this->render_crud();
    }

    public function payment() {
        $crud = $this->generate_crud('payments');
        $crud->columns('id', 'user_id', 'products', 'paid_date', 'payment_token', 'payment_name', 'payment_description', 'signature', 'payment_method',
            'gross_amount', 'status');
        $crud->order_by('id', 'desc');
        $crud->callback_column('products', array($this, 'callback_wrap_text'));

        $this->mPageTitle = 'Payments';
        $this->render_crud();
    }

}
