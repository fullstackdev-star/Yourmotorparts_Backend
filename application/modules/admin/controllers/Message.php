<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->load->model('Report_message_model', 'report_messages');
	}

	// Frontend User CRUD
	public function index()
	{
        $crud = $this->generate_crud('messages');
        $crud->columns('sender_id', 'receiver_id', 'message', 'created_at', 'message_status');
        $this->unset_crud_fields('id', 'type', 'deleted_from_sender', 'deleted_from_receiver');

        $crud->set_relation('sender_id', 'users', 'username');
        $crud->set_relation('receiver_id', 'users', 'username');

        //$crud->unset_add();
        $crud->unset_read();

        $this->mPageTitle = 'Messages';
        $this->render_crud();
	}

    // Frontend User CRUD
    public function contact_messages()
    {
        $crud = $this->generate_crud('report_messages');
        $crud->columns('username', 'email', 'message', 'type', 'created_at');
        $this->unset_crud_fields('id', 'status');

        $crud->order_by('id', 'desc');

        $crud->unset_add();
        $crud->unset_edit();
        $crud->unset_read();

        $this->mPageTitle = 'Report Messages';
        $this->render_crud();

        $new_messages = $this->messages->get_where('status', '0');
        foreach ($new_messages as $new_message) {
            $this->messages->update_field($new_message->id, 'status', '1');
        }
    }



}
