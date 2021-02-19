<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_builder');
        $this->load->model('Chat_message_model', 'chat_messages');
        $this->load->model('Chat_file_model', 'files');
	}

	// Frontend User CRUD
	public function index()
	{
		$crud = $this->generate_crud('chat_messages');

        $crud->set_relation('user_id', 'users', '({id}) {username}');
        $crud->display_as('user_id', 'User');
        $crud->set_relation('room_id', 'chat_rooms', '({id}) {name}');
        $crud->display_as('room_id', 'Chat Room');

        $crud->callback_column('message', array($this, 'callback_message'));
        //$crud->callback_column('type', array($this, 'callback_type'));
        $crud->callback_column('created_at', array($this, 'callback_created_at'));

        $crud->field_type('type', 'dropdown',
            array('1' => 'Text', '2' => 'File', '3' => 'Location', '4' => 'Contact', '5' => 'Sticker', '1000' => 'User Join', '1001' => 'User Left'));

        $crud->unset_add();
        $crud->unset_edit();

        $crud->callback_after_insert(array($this, 'callback_message_after_insert'));
		$this->mPageTitle = 'Chat Messages';
		$this->render_crud();
	}

    function callback_message_after_insert($post_array, $primary_key)
    {
        $this->chat_messages->update_field($primary_key, 'created_at', round(microtime(true) * 1000));
        return true;
    }

    public function callback_message($value, $row) {
        if($row->type == 5) {
            return "<img style='width:50px; height:50px; object-fit:cover' src='" . $value . "'/>";

        } else if($row->type == 2) {
            $file = $this->files->get($value);
            if($file && $file->thumb_id>0) {
                if(strpos($file->mime_type, 'video')!==false) {
                    $thumb = $this->files->get($file->thumb_id);
                    return "<a href='".$file->downloadUrl."'><div id='video_".$file->id."' class='loadVideo' style='width: 170px; height: 90px;background-image: url($thumb->downloadUrl); background-size:cover; background-position: center; background-repeat: no-repeat; position:relative'>" .
                    "<img style='width:30px; height:30px; position: absolute; top: 50%; left: 50%; -ms-transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);".
                    "' src='" . base_url() . "/assets/images/icon_play.png'/></div></a>";
                }
                else {
                    $thumb = $this->files->get($file->thumb_id);
                    return "<img style='width:120px; height:80px; object-fit:cover' src='" . $thumb->downloadUrl . "'/>";
                }
            }
            else if($file && strpos($file->mime_type, 'video')!==false) {
                return "<a href='".$file->downloadUrl."'><div id='video_".$file->id."' class='loadVideo' style='width: 170px; height: 90px;background-color:#000000;background-size:cover; background-position: center; background-repeat: no-repeat; position:relative'>" .
                "<img style='width:30px; height:30px; position: absolute; top: 50%; left: 50%; -ms-transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);".
                "' src='" . base_url() . "/assets/images/icon_play.png'/></div></a>";
            }
            else if($file && strpos($file->mime_type, 'audio')!==false) {
                return "<audio controls><source src='$file->downloadUrl'></audio>";
            }
            else if($file) {
                return '<a href="'.$file->downloadUrl.'">'.$file->name.'</a>';
            }
        }
        return $value;
    }

    public function callback_type($value, $row) {
        if($value == 1) {
            return "Text";
        } else if($value == 2) {
            return "File";
        } else if($value == 3) {
            return "Location";
        } else if($value == 4) {
            return "Contact";
        } else if($value == 5) {
            return "Sticker";
        } else if($value == 1000) {
            return "User Join";
        } else if($value == 1001) {
            return "User Left";
        }
        return $value;
    }

    public function callback_created_at($value, $row) {
        $d = new DateTime( date('Y-m-d H:i:s.', $value/1000) );
        return $d->format("Y-m-d H:i:s");
    }

    public function chat_groups() {
        $crud = $this->generate_crud('chat_rooms');
        $crud->columns('name', 'avatar_url', 'creator_id', 'type', 'contact_id', 'created_at', 'updated_at', 'room_status', 'users');
        $state = $crud->getState();
        $crud->set_relation('creator_id', 'users', '({id}) {username}');
        $crud->set_relation_n_n('users', 'chat_room_users', 'users', 'room_id', 'user_id', 'username');
        if ($state == 'list' || $state == 'success') {
            $crud->callback_column('avatar_url', array($this, 'callback_profile_photo'));
        } else {
            $crud->set_field_upload('avatar_url', UPLOAD_CHAT_ROOM_PHOTO);
        }
        $type = array('Private', 'Group');
        $crud->field_type('type', 'true_false', $type);
        $this->mPageTitle = 'Chat Group List';
        $this->render_crud();
    }

    public function callback_profile_photo($value, $row) {
        if(strlen($value)==0) {
            return "";
        }
        if (strpos($value, 'http') !== false) {
            return "<img style='width:50px; height:50px object-fit:cover' class='img-circle' src='".$value."'></>";

        } else {
            $photo = base_url() . UPLOAD_CHAT_ROOM_PHOTO . $value;
            return "<a href='". $photo ."' class='image-thumbnail'><img style='width:50px; height:50px; object-fit:cover' class='img-circle' src='".$photo."'></></a>";
        }
    }

	public function sticker_category() {
        $crud = $this->generate_crud('chat_sticker_categories');
        $crud->set_field_upload('main_pic', UPLOAD_CHAT_STICKER_CATEGORY);
        $this->mPageTitle = 'Sticker Categories';
        $this->render_crud();
    }

    public function stickers() {
        $crud = $this->generate_crud('chat_stickers');
        $crud->set_relation('sticker_category_id', 'chat_sticker_categories', '({id}) {title}');
        $crud->set_field_upload('full_pic', UPLOAD_CHAT_STICKERS_FULL_SIZE);
        $crud->set_field_upload('small_pic', UPLOAD_CHAT_STICKERS_SMALL_SIZE);
        $this->mPageTitle = 'Stickers';
        $this->render_crud();
    }

}
