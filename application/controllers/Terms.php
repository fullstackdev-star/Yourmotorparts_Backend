<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home page
 */
class Terms extends MY_Controller {

	public function index()
	{
        $this->load->model('constant_model', 'constants');
        $this->mViewData['terms_of_service'] = $this->constants->get_first_one_where('key', 'terms')->value;
		$this->render('terms_of_service', 'full_width');
	}

}
