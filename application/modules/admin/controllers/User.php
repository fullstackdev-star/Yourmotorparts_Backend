<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->load->model('User_address_model', 'user_addresses');
	}

	// Frontend User CRUD
	public function index()
    {
        $this->generate_users_crud();

		$this->mPageTitle = 'Users';
		$this->render_crud();
	}

    // Frontend Seller CRUD
    public function sellers()
    {
        $crud = $this->generate_users_crud();
        $crud->where('user_type', '1');

        $this->mPageTitle = 'Sellers';
        $this->render_crud();
    }

    // Frontend Customer CRUD
    public function customers()
    {
        $crud = $this->generate_users_crud();
        $crud->where('user_type', '0');

        $this->mPageTitle = 'Customers';
        $this->render_crud();
    }

    public function generate_users_crud() {
        $crud = $this->generate_crud('users');
        $crud->columns('id', 'username', 'email', 'phone', 'title', 'first_name', 'last_name', 'photo', 'addresses', 'premium_type', 'active');
        $this->unset_crud_fields('groups', 'ip_address', 'last_login', 'company', 'user_type', 'fb_id', 'birthday', 'gender');

        $state = $crud->getState();
        if ($state == "" || $state == 'list' || $state == 'success' || $state == 'ajax_list_info' || $state == 'ajax_list') { // || $state == 'read'
            $crud->callback_column('photo', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('photo', UPLOAD_PROFILE_PHOTO);
        }
        $crud->display_as('fb_id', "Facebook Id");

        //$crud->where('active', '1');
        $crud->callback_column('addresses', array($this, 'callback_addresses'));

        $gender = array('male', 'female');
        $crud->field_type('gender', 'true_false', $gender);

        $crud->field_type('premium_type', 'dropdown',
            array('0' => 'Free Account', '1' => '3 Months Subscription','2' => '6 Months Subscription' , '3' => 'A Year Subscription'));

        // only webmaster and admin can change member groups
        if ($crud->getState() == 'list' || $this->ion_auth->in_group(array('webmaster', 'admin'))) {
            $crud->set_relation_n_n('groups', 'users_groups', 'groups', 'user_id', 'group_id', 'name');
        }

        //$crud->callback_column('gender', array($this, 'callback_gender'));
        //$crud->callback_column('fb_id', array($this, 'callback_fb_login'));

        $state = $crud->getState();
        if ($state === 'add') {
        }

        if ($state === 'edit') {
        }
        // only webmaster and admin can reset user password
        if ($this->ion_auth->in_group(array('webmaster'))) {
            $crud->add_action('Reset Password', '', 'admin/user/reset_password', 'fa fa-repeat');
        }

        $crud->add_action('View User', '', 'admin/product/user_info', 'fa fa-search');

        // disable direct create / delete Frontend User
        if ($crud->getState() == 'list' || $this->ion_auth->in_group(array('admin'))) {
            $crud->unset_add();
            $crud->unset_delete();
            $crud->unset_edit();
            $crud->unset_read();
            //$crud->callback_after_update();
            $crud->callback_delete(array($this, 'delete_user'));
        }

        return $crud;
    }

    public function callback_addresses($value, $row)
    {
        $user_addresses = $this->user_addresses->get_where('user_id', $row->id);
        $items = '';
        foreach ($user_addresses as $user_address) {
            $point = '<img style=\'width:20px; height:20px\' src=\''.base_url().'/assets/images/point.png\'/>';
            $items .= '<li>'.$point.wordwrap($user_address->address, 30, "<br>", true).'</li>';
        }
        return '<a href="' . base_url() . 'admin/user/location/' . $row->id . '"><ul>' . $items . '</ul></a>';
    }

    public function delete_user($primary_key) {
        $user = $this->users->get($primary_key);
        $this->load->model('Users_group_model', 'users_groups');
        $this->load->model('Admin_user_model', 'admin_users');
        $user_groups = $this->users_groups->get_where("user_id", $primary_key);
        foreach ($user_groups as $group) {
            if($group->group_id==2) {
                $admin_users = $this->admin_users->get_where("username", $user->username);
                if(count($admin_users)>0) {
                    $this->admin_users->delete($admin_users[0]->id);
                }
            }
        }

        return $this->users->delete($primary_key); //$this->users->update_field($primary_key, 'active', 0);
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

    public function callback_gender($value, $row)
    {
        $gender = "male";
        if($value==0){
            $gender = "female";
        }
        return $gender;
    }

    public function callback_fb_login($value, $row)
    {
        $fb_login = "";
        if($value==0){
            $fb_login = "";

        } else {
            $fb_login = "Facebook Login";
        }
        return $fb_login;
    }

	// Create Frontend User
	public function create()
	{
        $this->load->library('form_builder');
        $form = $this->form_builder->create_form(NULL, true);

		if ($form->validate())
		{
            // passed validation
            $filename = '';
            if(is_uploaded_file($_FILES['user_image']['tmp_name'])) {
                $path = UPLOAD_PROFILE_PHOTO;

                $milliseconds = round(microtime(true) * 1000);
                $filename = "profile_" . $milliseconds . '.png';
                $file_path = $path . $filename;

                $tmpFile = $_FILES['user_image']['tmp_name'];
                if(move_uploaded_file($tmpFile, $file_path)) {
                    //$this->system_message->set_success("Successfully uploaded");
                } else {
                    $filename = '';
                    //$this->system_message->set_error("Failed move");
                }
            }

			$username = $this->input->post('username');
			$email = $this->input->post('email');
            $password = $this->input->post('password');
			$identity = empty($username) ? $email : $username;
			$additional_data = array(
				'first_name'	=> $this->input->post('first_name'),
				'last_name'		=> $this->input->post('last_name'),
                'phone'         => $this->input->post('phone'),
                'gender'        => $this->input->post('gender'),
                'photo'         => $filename
			);
			$groups =  $this->input->post('groups');

            foreach ($groups as $group) {
                if($group==2) {
                    $this->load->model('Admin_user_model', 'admin_users');
                    $admin_user_id = $this->ion_auth->register($identity, $password, $email, $additional_data, $groups);
                }
            }

			// [IMPORTANT] override database tables to update Frontend Users instead of Admin Users
			$this->ion_auth_model->tables = array(
				'users'				=> 'users',
				'groups'			=> 'groups',
				'users_groups'		=> 'users_groups',
				'login_attempts'	=> 'login_attempts',
			);

			// proceed to create user
			$user_id = $this->ion_auth->register($identity, $password, $email, $additional_data, $groups);
			if ($user_id)
            {
                // success
				$messages = $this->ion_auth->messages();
				$this->system_message->set_success("Account Successfully Created");

                foreach ($groups as $group) {
                    if ($group == 2) {
                        $this->users->update_field($user_id, 'user_type', 1);
                    }
                }

                $address_info = $this->input->post('address_info'); //address
                $address_json = json_decode($address_info);
                foreach ($address_json as $arj) {
                    $address_region = $arj->address;
                    $lat = $arj->lat;
                    $lng = $arj->lng;

                    $ad_id = $this->user_addresses->insert(array(
                        'user_id' => $user_id,
                        'address' => $address_region,
                        'lat' => $lat,
                        'lng' => $lng
                    ));

                    $ad_ids[] = $ad_id;
                }

				// directly activate user
				$this->ion_auth->activate($user_id);
			}
			else
			{
				// failed
				$errors = $this->ion_auth->errors();
				$this->system_message->set_error($errors);
			}
			refresh();
		}

		// get list of Frontend user groups
		$this->load->model('group_model', 'groups');
		$this->mViewData['groups'] = $this->groups->get_all();
		$this->mPageTitle = 'Create User';

		$this->mViewData['form'] = $form;
		$this->render('user/create');
	}

    public function load_upload_library($path=null) {
        $config['upload_path']          = $path?$path:'./assets/uploads/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 100000;
        $config['max_width']            = 10240;
        $config['max_height']           = 7680;
        $config['encrypt_name']         = true;

        $this->load->library('upload', $config);
    }

    public function upload_file()
    {
        $this->mViewData['error'] = ' ';
        $this->render('upload_form');
        //$this->load->view('upload_form', array('error' => ' ' ));
    }

    public function do_upload()
    {
        $config['upload_path']          = './assets/uploads/';
        $config['allowed_types']        = 'gif|jpg|png|txt';
        $config['max_size']             = 100000;
        $config['max_width']            = 1024;
        $config['max_height']           = 1024;
        $config['encrypt_name']         = true;

        $this->load->library('upload', $config);
        //$this->upload->initialize($config);

        if ( ! $this->upload->do_upload('userfile'))
        {
            var_dump($this->upload->data());
            $error = array('error' => $this->upload->display_errors());
            //$this->load->view('upload_form', $error);
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            //$this->load->view('upload_success', $data);
        }

        $this->mViewData['error'] = ' ';
        $this->render('upload_form');
    }

    // Create Frontend User
    public function upload_image()
    {
        $this->load->library('form_builder');
        $form = $this->form_builder->create_form(NULL, true);

        if ($form->validate()) {
            // passed validation
            if(is_uploaded_file($_FILES['upload_image']['tmp_name'])) {
                $path = UPLOAD_PROFILE_PHOTO;

                $milliseconds = round(microtime(true) * 1000);
                $fileName = "profile_" . $milliseconds . '.png';
                $file_path = $path . $fileName;

                $tmpFile = $_FILES['upload_image']['tmp_name'];
                if(move_uploaded_file($tmpFile, $file_path)) {
                    $this->system_message->set_success("Successfully uploaded");
                } else {
                    $this->system_message->set_error("Failed move");
                }
            } else {
                $this->system_message->set_error("No image");
            }
            //refresh();
        }

        $this->mPageTitle = 'Upload Image';
        $this->mViewData['form'] = $form;
        $this->render('user/upload_image');
    }

	// User Groups CRUD
	public function group()
	{
		$crud = $this->generate_crud('groups');
		$this->mPageTitle = 'User Groups';
		$this->render_crud();
	}

	// Frontend User Reset Password
	public function reset_password($user_id)
	{
		// only top-level users can reset user passwords
		$this->verify_auth(array('webmaster', 'admin'));

        $this->load->library('form_builder');
        $form = $this->form_builder->create_form();
		if ($form->validate())
		{
			// pass validation
			$data = array('password' => $this->input->post('new_password'));

            $user = $this->users->get($user_id);
            $this->load->model('Users_group_model', 'users_groups');
            $this->load->model('Admin_user_model', 'admin_users');
            $user_groups = $this->users_groups->get_where("user_id", $user_id);
            foreach ($user_groups as $group) {
                if($group->group_id==2) {
                    $admin_users = $this->admin_users->get_where("username", $user->username);
                    if(count($admin_users)>0) {
                        $this->ion_auth->update($admin_users[0]->id, $data); //for seller password change
                    }
                }
            }

			// [IMPORTANT] override database tables to update Frontend Users instead of Admin Users
			$this->ion_auth_model->tables = array(
				'users'				=> 'users',
				'groups'			=> 'groups',
				'users_groups'		=> 'users_groups',
				'login_attempts'	=> 'login_attempts',
			);

			// proceed to change user password
			if ($this->ion_auth->update($user_id, $data))
			{
				$messages = $this->ion_auth->messages();
				$this->system_message->set_success("The Password Successfully Updated");
			}
			else
			{
				$errors = $this->ion_auth->errors();
				$this->system_message->set_error($errors);
			}
			refresh();
		}

		$this->load->model('user_model', 'users');
		$target = $this->users->get($user_id);
		$this->mViewData['target'] = $target;

		$this->mViewData['form'] = $form;
		$this->mPageTitle = 'Reset User Password';
		$this->render('user/reset_password');
	}

    public function map()
    {
        $this->load->library('googlemaps');
        $config['center'] = '37.4419, -122.1419';
        $config['zoom'] = 'auto';
        $config['map_height'] = '500px';
        $this->googlemaps->initialize($config);

        $data = $this->user_addresses->get_all();

        foreach($data as $d) {
            if($d->lat==0 && $d->lng==0) continue;
            $user = $this->users->get($d->user_id);

            $marker = array();
            $marker['position'] = $d->lat . ',' . $d->lng;
            $marker['infowindow_content'] = "<a href='".base_url()."admin/user/index/read/".$d->user_id."' class='image-thumbnail'><img style='width:50px; height:50px; object-fit:cover' class='img-circle' src='".$user->photo."'/>"."&nbsp&nbsp&nbsp ".$user->username."(".$d->user_id.")</a>"."<p><br/>".$d->address;
            $this->googlemaps->add_marker($marker);
        }

        /*$marker['draggable'] = TRUE;
        $marker['animation'] = 'DROP';
        $marker['onclick'] = 'alert("You just clicked me!!")';
        $marker['position'] = 'Crescent Park, Palo Alto';
        $marker['icon'] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=A|9999FF|000000';*/

        $this->mTitle = 'User Locations';
        $this->mViewData['map'] = $this->googlemaps->create_map();
        $this->render('map/map');
    }

    public function location($user_id){
        $this->load->library('googlemaps');
        $config['zoom'] = 'auto';
        $this->googlemaps->initialize($config);

        $data = $this->user_addresses->get_where('user_id', $user_id);
        foreach($data as $d) {
            $marker = array();
            $marker['position'] = $d->lat . ', ' . $d->lng;
            $marker['infowindow_content'] = $d->address;
            //$marker['icon'] = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=A|9999FF|000000';
            $this->googlemaps->add_marker($marker);
        }

        $user = $this->users->get($user_id);
        $this->mTitle = 'User (' . $user->username.') Location';
        $this->mViewData['map'] = $this->googlemaps->create_map();
        $this->render('map/map');
    }

    public function payments() {
        $crud = $this->generate_crud('payments');
        $crud->set_relation('user_id', 'users', '{username} ({email})');
        $crud->display_as('user_id', 'User');
        $crud->required_fields('user_id', 'order_note');

        $crud->set_lang_string('insert_error', 'User already exist.');
        $crud->set_lang_string('update_error', 'User already exist.');
        $crud->callback_before_insert(array($this, 'callback_before_insert_payment'));
        $crud->callback_before_update(array($this, 'callback_before_update_payment'));
        $this->mPageTitle = 'Payments';
        $this->render_crud();
    }

    public function callback_before_insert_payment($post_array) {
        $this->load->model('Payment_model', 'payments');
        $exist_payments = $this->payments->get_where('user_id', $post_array['user_id']);
        if(count($exist_payments)>0) {
            $this->form_validation->set_message('check_salt',"Salt value must be less then FIVE");
            return false;
        }

        return $post_array;
    }

    public function callback_before_update_payment($post_array, $primary_key) {
        $this->load->model('Payment_model', 'payments');
        $origin_payment = $this->payments->get($primary_key);
        $user_id = $origin_payment->user_id;

        if($user_id != $post_array['user_id']) {
            $exist_payments = $this->payments->get_where('user_id', $post_array['user_id']);
            if(count($exist_payments)>0) {
                return false;
            }
        }

        return $post_array;
    }

    public function countries() {
        $crud = $this->generate_crud('countries');
        $crud->columns('name', 'code', 'phone_code');
        $this->unset_crud_fields('id');

        $this->mPageTitle = 'Countries';
        $this->render_crud();
    }

    public function currencies() {
        $crud = $this->generate_crud('currencies');
        $crud->columns('code', 'symbol', 'name', 'symbol_native', 'exchange_rate');
        $this->unset_crud_fields('id', 'last_update', 'pos');

        $this->mPageTitle = 'Currencies';
        $this->render_crud();
    }

}
