<?php 

class Product_image_model extends MY_Model {

	protected $order_by = array('pos', 'ASC');
	protected $upload_fields = array('filename' => UPLOAD_PRODUCTS);
	
	// Append tags
	protected function callback_after_get($result)
	{
		$result = parent::callback_after_get($result);

		return $result;
	}

}