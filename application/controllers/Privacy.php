<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home page
 */
class Privacy extends MY_Controller {

	public function index()
	{
        $this->load->model('constant_model', 'constants');
        $this->mViewData['privacy'] = $this->constants->get_first_one_where('key', 'privacy')->value;
		$this->render('privacy', 'full_width');
	}
}
