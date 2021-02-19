<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Admin_Controller {

	public function index()
	{
		$this->load->model('user_model', 'users');
		$this->mViewData['count'] = array(
			'users' => $this->users->count_all(),
            'sellers' => count($this->users->get_where(array('user_type' => 1))),
            'buyers' => count($this->users->get_where(array('user_type' => 0))),
		);
		$this->render('home');
	}

}
