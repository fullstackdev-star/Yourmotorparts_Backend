<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_builder');
        $this->load->model('product_model', 'products');
        $this->load->model('Reservation_model', 'reservations');
        $this->load->model('Payment_method_model', 'paymentMethods');
        $this->load->model('Constant_model', 'constants');

    }

	public function index()
	{
        redirect('admin/setting/settings');
	}

    public function settings(){
        $form = $this->form_builder->create_form();

        $constant_product_limit_count = $this->constants->get_where('title', 'product_limit_count')[0];
        $constant_advanced_hours = $this->constants->get_where('title', 'advanced_hours')[0];

        $product_limit_count = $constant_product_limit_count->value;
        $advanced_hours = $constant_advanced_hours->value;  

        if ($form->validate())
        {
            // passed validation
            $post_product_limit_count = $this->input->post('product_limit_count');
            $post_advanced_hours = $this->input->post('advanced_hours');

            // proceed to create user
            $result = $this->constants->update_field($constant_product_limit_count->id, 'value', $post_product_limit_count);
            $result = $this->constants->update_field($constant_advanced_hours->id, 'value', $post_advanced_hours);
            if ($result)
            {
                // success
                $this->system_message->set_success("successfully set");
            }
            else
            {
                $this->system_message->set_error("failed");
            }
            refresh();
        }

        $this->mPageTitle = 'Default Settings';
        $this->mViewData['form'] = $form;
        $this->mViewData['default'] = array(
            'product_limit_count' => $product_limit_count,
            'advanced_hours' => $advanced_hours
        );
        $this->render('util/settings');
    }

}
