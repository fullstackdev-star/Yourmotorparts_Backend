<?php

/**
 * Config file for form validation
 * Reference: http://www.codeigniter.com/user_guide/libraries/form_validation.html
 * (Under section "Creating Sets of Rules")
 */

$config = array(

	// Admin User Login
	'login/index' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
	),

	// Create User
	'user/create' => array(
		array(
			'field'		=> 'first_name',
			'label'		=> 'First Name',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'last_name',
			'label'		=> 'Last Name',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'is_unique[users.username]',				// use email as username if empty
		),
		array(
			'field'		=> 'email',
			'label'		=> 'Email',
			'rules'		=> 'required|valid_email|is_unique[users.email]',
		),
        array(
            'field'		=> 'phone',
            'label'		=> 'Phone',
            'rules'		=> 'required|is_unique[users.phone]',
        ),
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[password]',
		),
	),

	// Reset User Password
    'user/reset_password' => array(
        array(
            'field'		=> 'new_password',
            'label'		=> 'New Password',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'retype_password',
            'label'		=> 'Retype Password',
            'rules'		=> 'required|matches[new_password]',
        ),
    ),

    // Test Upload Image
    'user/upload_image' => array(
        array(
            'field'		=> 'first_name',
            'label'		=> 'First Name',
            'rules'		=> 'required',
        ),
    ),

	// Create Admin User
	'panel/admin_user_create' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'required|is_unique[users.username]',
		),
		array(
			'field'		=> 'first_name',
			'label'		=> 'First Name',
			'rules'		=> 'required',
		),
		/* Admin User can have no email
		array(
			'field'		=> 'email',
			'label'		=> 'Email',
			'rules'		=> 'valid_email|is_unique[users.email]',
		),*/
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[password]',
		),
	),

	// Reset Admin User Password
	'panel/admin_user_reset_password' => array(
		array(
			'field'		=> 'new_password',
			'label'		=> 'New Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[new_password]',
		),
	),

	// Admin User Update Info
	'panel/account_update_info' => array(
		array(
			'field'		=> 'username',
			'label'		=> 'Username',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'password',
			'label'		=> 'Password',
			'rules'		=> 'required',
		),
	),

	// Admin User Change Password
	'panel/account_change_password' => array(
		array(
			'field'		=> 'new_password',
			'label'		=> 'New Password',
			'rules'		=> 'required',
		),
		array(
			'field'		=> 'retype_password',
			'label'		=> 'Retype Password',
			'rules'		=> 'required|matches[new_password]',
		),
	),

    // Create notification
    'notification/create_notification' => array(
        array(
            'field'		=> 'message',
            'label'		=> 'Message',
            'rules'		=> 'required',
        ),
    ),

    // Setting constants
    'util/settings' => array(
        array(
            'field'		=> 'contact_phone',
            'label'		=> 'Contact Phone Number',
            'rules'		=> 'required',
        ),
        array(
            'field'		=> 'contact_email',
            'label'		=> 'Contact Email',
            'rules'		=> 'required|valid_email',
        ),
        array(
            'field'		=> 'pending_time',
            'label'		=> 'Default Pending Duration (days)',
            'rules'		=> 'required|integer',
        )
    ),

    // Setting constants
    'util/privacy' => array(
        array(
            'field'		=> 'privacy',
            'label'		=> 'Privacy Police',
            'rules'		=> 'required',
        )
    ),

    // Setting constants
    'util/terms' => array(
        array(
            'field'		=> 'terms',
            'label'		=> 'Terms of Service',
            'rules'		=> 'required',
        )
    ),

);