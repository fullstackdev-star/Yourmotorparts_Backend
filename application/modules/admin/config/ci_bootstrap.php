<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| CI Bootstrap 3 Configuration
| -------------------------------------------------------------------------
| This file lets you define default values to be passed into views 
| when calling MY_Controller's render() function. 
| 
| See example and detailed explanation from:
| 	/application/config/ci_bootstrap_example.php
*/

$config['ci_bootstrap'] = array(

	// Site name
	'site_name' => 'Admin Panel',

	// Default page title prefix
	'page_title_prefix' => '',

	// Default page title
	'page_title' => '',

	// Default meta data
	'meta_data'	=> array(
		'author'		=> '',
		'description'	=> '',
		'keywords'		=> ''
	),
	
	// Default scripts to embed at page head or end
	'scripts' => array(
		'head'	=> array(
			'assets/dist/admin/adminlte.min.js',
			'assets/dist/admin/lib.min.js',
			'assets/dist/admin/app.min.js'
		),
		'foot'	=> array(
		),
	),

	// Default stylesheets to embed at page head
	'stylesheets' => array(
		'screen' => array(
			'assets/dist/admin/adminlte.min.css',
			'assets/dist/admin/lib.min.css',
			'assets/dist/admin/app.min.css'
		)
	),

	// Default CSS class for <body> tag
	'body_class' => '',
	
	// Multilingual settings
	'languages' => array(
	),

	// Menu items
	'menu' => array(
		'home' => array(
			'name'		=> 'Home',
			'url'		=> '',
			'icon'		=> 'fa fa-home',
		),
		'user' => array(
			'name'		=> 'Users',
			'url'		=> 'user',
                'icon'		=> 'fa fa-users',
			'children'  => array(
				'All'			=> 'user',
                'Sellers'       => 'user/sellers',
                'Customers'     => 'user/customers',
                'Map' 		    => 'user/map',
				'Create'		=> 'user/create',
				'User Groups'	=> 'user/group',
                'Countries'	    => 'user/countries',
                'Currencies'    => 'user/currencies'
                //'Test Upload Image' => 'user/upload_image'
			)
		),
        'product' => array(
            'name'		=> 'Parts',
            'url'		=> 'product',
            'icon'		=> 'fa fa-car',
            'children'  => array(
                'List'			=> 'product',
                'Pending List'	=> 'product/pending_list',
                'Add'           => 'product/index/add',
                'Make List'  	=> 'product/make_list',
                'Model List'  	=> 'product/model_list',
                'Category'  	=> 'product/category',
                'Sub Category'  => 'product/sub_category'
            )
        ),
       /* 'chat' => array(
            'name'		=> 'Chat',
            'url'		=> 'chat',
            'icon'		=> 'fa fa-comments-o',
            'children'  => array(
                'Message List'	=> 'chat',
                //'Group List'	=> 'chat/chat_groups',
                //'Sticker Category'		=> 'chat/sticker_category',
                //'Stickers'	    => 'chat/stickers',
            )
        ),*/
        'message' => array(
            'name'		=> 'Messages',
            'url'		=> 'message',
            'icon'		=> 'fa fa-envelope',
            'children'  => array(
                'User Messages'			=> 'message',
                'Support Messages'      => 'message/contact_messages'
            )
        ),
        'notification' => array(
            'name'		=> 'Send Notification',
            'url'		=> 'notification',
            'icon'		=> 'fa fa-send',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                'Sent Notifications'		=> 'notification',
                'Create Notification'		=> 'notification/create_notification'
            )
        ),
        /*'panel' => array(
			'name'		=> 'Admin Panel',
			'url'		=> 'panel',
			'icon'		=> 'fa fa-cog',
			'children'  => array(
				'Admin Users'			=> 'panel/admin_user',
				'Create Admin User'		=> 'panel/admin_user_create',
				'Admin User Groups'		=> 'panel/admin_user_group',
			)
		),*/
		'util' => array(
			'name'		=> 'Utilities',
			'url'		=> 'util',
			'icon'		=> 'fa fa-cogs',
			'children'  => array(
                'Settings'	        	=> 'util/settings',
                'Terms of Service'      => 'util/terms',
                'Privacy Policy'        => 'util/privacy',
				//'Database Versions'		=> 'util/list_db',
			)
		),
		'logout' => array(
			'name'		=> 'Sign Out',
			'url'		=> 'panel/logout',
			'icon'		=> 'fa fa-sign-out',
		)
	),

	// Login page
	'login_url' => 'admin/login',

	// Restricted pages
	'page_auth' => array(
		'user/create'				=> array('webmaster'),
		'user/group'				=> array('webmaster'),
        'user/sellers'              => array('webmaster'),
        'product/make_list'         => array('webmaster'),
        'product/model_list'        => array('webmaster'),
        'product/category'          => array('webmaster'),
        'product/sub_category'      => array('webmaster'),
		'panel'						=> array('webmaster'),
		'panel/admin_user'			=> array('webmaster'),
		'panel/admin_user_create'	=> array('webmaster'),
		'panel/admin_user_group'	=> array('webmaster'),
		'util'						=> array('webmaster'),
		'util/list_db'				=> array('webmaster'),
		'util/backup_db'			=> array('webmaster'),
		'util/restore_db'			=> array('webmaster'),
		'util/remove_db'			=> array('webmaster'),
	),

	// AdminLTE settings
	'adminlte' => array(
		'body_class' => array(
			'webmaster'	=> 'skin-red',
			'admin'		=> 'skin-purple',
			'manager'	=> 'skin-black',
			'staff'		=> 'skin-blue',
		)
	),

	// Useful links to display at bottom of sidemenu
	'useful_links' => array(
		/*array(
			'auth'		=> array('webmaster', 'admin', 'manager', 'staff'),
			'name'		=> 'Frontend Website',
			'url'		=> '',
			'target'	=> '_blank',
			'color'		=> 'text-aqua'
		),
		array(
			'auth'		=> array('webmaster', 'admin'),
			'name'		=> 'API Site',
			'url'		=> 'api',
			'target'	=> '_blank',
			'color'		=> 'text-orange'
		),
		array(
			'auth'		=> array('webmaster', 'admin', 'manager', 'staff'),
			'name'		=> 'Github Repo',
			'url'		=> CI_BOOTSTRAP_REPO,
			'target'	=> '_blank',
			'color'		=> 'text-green'
		),*/
	),

	// Debug tools
	'debug' => array(
		'view_data'	=> FALSE,
		'profiler'	=> FALSE
	),
);

/*
| -------------------------------------------------------------------------
| Override values from /application/config/config.php
| -------------------------------------------------------------------------
*/
$config['sess_cookie_name'] = 'ci_session_admin';