<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * product Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class Chat extends Base_Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Chat_message_model', 'messages');
        $this->load->model('Chat_message_seen_model', 'message_seens');
        $this->load->model('Chat_room_model', 'rooms');
        $this->load->model('Chat_room_user_model', 'room_users');
        $this->load->model('Chat_file_model', 'files');
        $this->load->model('Chat_sticker_category_model', 'sticker_categories');
        $this->load->model('Chat_sticker_model', 'stickers');
    }

    public function rooms_last_message_post() {
        $user_id = $this->post('user_id');
        $str_ids = $this->post('room_ids');
        $room_ids = explode(",", $str_ids);
        $data = [];
        foreach ($room_ids as $room_id) {
            $last_message = $this->messages->get_first_one_where('room_id', $room_id);

            $search_key = array(
                'seen_status' => 0,
                'user_id'   => $user_id
            );
            $unread_messages = [];
            $message_seens = $this->message_seens->get_where($search_key);
            foreach ($message_seens as $message_seen) {
                $message = $this->messages->get($message_seen->message_id);
                if($message && $message->room_id == $room_id) {
                    $unread_messages[] = $message;
                }
            }
            $data[] = array (
                'last_message' => $last_message,
                'unread_count' => count($unread_messages),
                'unread_messages' => $unread_messages
            );
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data"  => $data
        );
        $this->response($result);
    }

    public function rooms_list_get($user_id) {
        $search_key = array('user_id' => $user_id);
        $room_users = $this->room_users->get_where($search_key);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data"  => $room_users
        );
        $this->response($result);
    }

    public function rooms_list_post() {
        $offset = $this->post("offset");
        $limit = $this->post("limit");
        $user_id = $this->post('user_id');

        $room_users = $this->room_users->paginate_with_offset($offset, array('user_id' => $user_id), $limit);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data"  => $room_users
        );
        $this->response($result);
    }

    public function room_get($room_id) {
        $room = $this->rooms->get($room_id);
        if($room) {
            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $room
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "no room"
            );
        }
        $this->response($result);
    }

    public function rooms_by_ids_post() {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $id) {
            $data[] = $this->rooms->get($id);
        }

        if(count($data)>0) {
            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $data
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "no data"
            );
        }
        $this->response($result);
    }

    public function get_contact_id($user_id, $opponent_id) {
        $this->load->model('User_contact_model', 'user_contacts');
        $search_key1 = array(
            'user_id' => $user_id,
            'opponent_id' => $opponent_id
        );
        $search_key2 = array(
            'user_id' => $opponent_id,
            'opponent_id' => $user_id
        );
        $user_contacts1 = $this->user_contacts->set_where($search_key1)->get_all();
        $user_contacts2 = $this->user_contacts->set_where($search_key2)->get_all();
        if(count($user_contacts1)>0) {
            return $user_contacts1[0]->id;

        } else if(count($user_contacts2)>0) {
            return $user_contacts2[0]->id;

        } else {
            return 0;
        }
    }

    public function create_dialog_post() {
        $user_id = $this->post("user_id");
        $str_opponent_ids = $this->post("opponent_ids");
        $opponent_ids = explode(",", $str_opponent_ids);

        if(count($opponent_ids)==1) {
            $opponent_id = $opponent_ids[0];
            $opponent = $this->users->get($opponent_id);
            $contact_id = $this->get_contact_id($user_id, $opponent_id);
            $room = $this->rooms->get_first_one_where('contact_id', $contact_id);
            if($room) {
                // add opponent's room user
                $search_key = array(
                    "room_id"   => $room->id,
                    "user_id"   => $opponent_id
                );
                $room_users = $this->room_users->set_where($search_key)->get_all();
                if(count($room_users)<1) {
                    $new_room_user = array(
                        'room_user_id' => $room->id . "_" . $opponent_id,
                        'room_id' => $room->id,
                        'user_id' => $opponent_id,
                        'created_at' => round(microtime(true) * 1000),
                    );
                    $this->room_users->insert($new_room_user);
                }

                $search_key = array(
                    "room_id"   => $room->id,
                    "user_id"   => $user_id
                );
                $room_users = $this->room_users->set_where($search_key)->get_all();
                if(count($room_users)>0) {
                    $new_room_user_id = $room_users[0]->id;
                } else {
                    // add user's room user
                    $new_room_user = array(
                        'room_user_id' => $room->id . "_" . $user_id,
                        'room_id' => $room->id,
                        'user_id' => $user_id,
                        'created_at' => round(microtime(true) * 1000),
                    );
                    $new_room_user_id = $this->room_users->insert($new_room_user);
                }
                $new_room_user = $this->room_users->get($new_room_user_id);

            } else {
                $new_room = array(
                    'name' => $opponent->full_name,
                    'avatar_url' => $opponent->photo,
                    'contact_id' => $contact_id,
                    'creator_id' => $user_id,
                    'type'  => 0, // private dialog
                    'created_at' => round(microtime(true) * 1000),
                    'updated_at' => round(microtime(true) * 1000)
                );
                $new_room_id = $this->rooms->insert($new_room);
                //$room = $this->rooms->get($new_room_id);

                // add opponent's room user
                $new_room_user = array(
                    'room_user_id'  => $new_room_id."_".$opponent_id,
                    'room_id'  => $new_room_id,
                    'user_id'  => $opponent_id,
                    'created_at'  => round(microtime(true) * 1000),
                );
                $this->room_users->insert($new_room_user);

                // add user's room user
                $new_room_user = array(
                    'room_user_id'  => $new_room_id."_".$user_id,
                    'room_id'  => $new_room_id,
                    'user_id'  => $user_id,
                    'created_at'  => round(microtime(true) * 1000),
                );
                $new_room_user_id = $this->room_users->insert($new_room_user);
                $new_room_user = $this->room_users->get($new_room_user_id);
            }
        } else if(count($opponent_ids)>1) {
            $room_name = "";
            foreach ($opponent_ids as $opponent_id) {
                $opponent = $this->users->get($opponent_id);
                if(strlen($room_name)==0) {
                    $room_name = $opponent->full_name;
                } else {
                    $room_name .= ", ".$opponent->full_name;
                }
            }
            $new_room = array(
                'name' => $room_name,
                'avatar_url' => 'icon_group.png',
                'creator_id' => $user_id,
                'type'  => 1, // private dialog
                'created_at' => round(microtime(true) * 1000),
                'updated_at' => round(microtime(true) * 1000)
            );
            $new_room_id = $this->rooms->insert($new_room);
            //$room = $this->rooms->get($new_room_id);

            foreach ($opponent_ids as $opponent_id) {
                $opponent = $this->users->get($opponent_id);
                $new_room_user = array(
                    'room_user_id'  => $new_room_id."_".$opponent->id,
                    'room_id'  => $new_room_id,
                    'user_id'  => $opponent->id,
                    'created_at'  => round(microtime(true) * 1000)
                );
                $this->room_users->insert($new_room_user);
            }
            $new_room_user = array(
                'room_user_id'  => $new_room_id."_".$user_id,
                'room_id'  => $new_room_id,
                'user_id'  => $user_id,
                'created_at'  => round(microtime(true) * 1000),
            );
            $new_room_user_id = $this->room_users->insert($new_room_user);
            $new_room_user = $this->room_users->get($new_room_user_id);

        } else {
            $result = array(
                "status" => 0,
                "message" => "no contact id"
            );
            $this->response($result);
            return;
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data"  => $new_room_user
        );
        $this->response($result);
    }

    public function create_room_post() {
        $new_room = array(
            'name' => $this->post('name'),
            'avatar_url' => $this->post('avatarURL'),
            'creator_id' => $this->post('userID'),
            'created_at' => round(microtime(true) * 1000) //$this->post('created')
        );

        $new_id = $this->rooms->insert($new_room);
        $data = $this->rooms->get($new_id);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );
        $this->response($result);
    }

    public function update_room_post() {
        $room_id = $this->post('roomID');
        $avatar_url = $this->post('avatarURL');
        $room_name = $this->post('name');
        if(isset($avatar_url) && $avatar_url) {
            $room_data = array(
                'name' => $room_name,
                'avatar_url' => $avatar_url
            );
        } else {
            $room_data = array(
                'name' => $room_name
            );
        }

        $id = $this->rooms->update($room_id, $room_data);
        $data = $this->rooms->get($id);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );
        $this->response($result);
    }

    public function create_room_user_post() {
        $new_room_user = array(
            'room_user_id'  => $this->post('roomUserId'),
            'room_id'  => $this->post('roomID'),
            'user_id'  => $this->post('userID'),
            'created_at'  => round(microtime(true) * 1000) //$this->post('created'),
        );
        $search_key = array(
            'room_id'  => $this->post('roomID'),
            'user_id'  => $this->post('userID')
        );
        $room_users = $this->room_users->set_where($search_key)->get_all();
        if(count($room_users)>0) {
            $data = $room_users[0];

        } else {
            $new_id = $this->room_users->insert($new_room_user);
            $data = $this->room_users->get($new_id);
        }
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );
        $this->response($result);
    }

    public function update_room_avatar_post() {
        $room_id = $this->post('room_id');
        $room_name = $this->post('room_name');
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $path = UPLOAD_CHAT_ROOM_PHOTO;
            $milliseconds = round(microtime(true) * 1000);
            $fileName = "profile_" . $milliseconds . '.png';
            $file_path = $path . $fileName;

            $tmpFile = $_FILES['image']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                $update_data = array(
                    "name" => $room_name,
                    "avatar_url" => $fileName
                );

                $this->rooms->update($room_id, $update_data);
                $room = $this->rooms->get($room_id);
                $response = array(
                    "status" => 1,
                    "data" => $room
                );
                $this->response($response);

            } else {
                $this->response(array("status" => 0, "error" => "Image Upload failed"));
            }
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed."));
        }
    }

    public function room_user_get($id) {
        $room_user = $this->room_users->get($id);
        if($room_user) {
            $search_key = array(
                'seen_status' => 0,
                'user_id'   => $room_user->user_id
            );
            $message_seens = $this->message_seens->set_where($search_key)->get_all();
            foreach ($message_seens as $message_seen) {
                $message = $this->messages->get($message_seen->message_id);
                if($message && $message->room_id == $room_user->room_id) {
                    $this->message_seens->update_field($message_seen->id, 'seen_status', 1);
                }
            }

            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $room_user
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "No data"
            );
        }
        $this->response($result);
    }

    public function room_user_by_room_user_id_get($room_user_id) {
        $room_users = $this->room_users->get_where('room_user_id', $room_user_id);
        if(count($room_users)>0) {
            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $room_users[0]
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "No data"
            );
        }
        $this->response($result);
    }

    public function room_user_by_room_id_and_user_id_post() {
        $search_key = array(
            'room_id'   => $this->post('roomID'),
            'user_id'   => $this->post('userID')
        );
        $room_users = $this->room_users->set_where($search_key)->get_all();
        if(count($room_users)>0) {
            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $room_users[0]
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "No data"
            );
        }
        $this->response($result);
    }

    public function room_users_by_room_id_get($room_id) {
        $room_users = $this->room_users->get_where('room_id', $room_id);
        if(count($room_users)>0) {
            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $room_users
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "No data"
            );
        }
        $this->response($result);
    }

    public function room_users_by_user_id_get($user_id) {
        $room_users = $this->room_users->get_where('user_id', $user_id);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $room_users
        );
        $this->response($result);
    }

    public function room_users_by_room_ids_post() {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $room_id) {
            $data = array_merge($data, $this->room_users->get_where('room_id', $room_id));
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );
        $this->response($result);
    }

    public function room_users_by_user_ids_post()
    {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $user_id) {
            $data = array_merge($data, $this->room_users->get_where('user_id', $user_id));
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );
        $this->response($result);
    }

    public function message_get($id) {
        $message = $this->messages->get($id);
        if($message) {
            $result = array(
                "status" => 1,
                "message" => "success",
                "data" => $message
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "no exist"
            );
        }
        $this->response($result);
    }

    public function save_new_message_post() {
        $type = $this->post('type');
        $user_id = $this->post('userID');
        $room_id = $this->post('roomID');
        $local_id = $this->post('localID');
        $message = $this->post('message');
        $new_message = array (
            'user_id'   => $user_id,
            'room_id'   => $room_id,
            'local_id'  => $local_id,
            'message'   => $message,
            'type'   => $type,
            'created_at'   => round(microtime(true) * 1000)//$this->post('created')
        );

        if($type == 2) {
            $file_id = $this->post('fileID');
            if ($file_id) {
                $new_message['message'] = $file_id;
            }
        }
        $new_id = $this->messages->insert($new_message);

        $new_seen = array(
            'message_id'    => $new_id,
            'user_id'    => $user_id,
            'created_at' => round(microtime(true) * 1000)
        );
        $this->message_seens->insert($new_seen);

        $new_message = $this->messages->get($new_id);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $new_message
        );
        $this->response($result);
    }

    public function delete_message_post() {
        $message_id = $this->post('id');
        $update_data = array (
            'message_status'   => 0,
            'deleted_at'   => round(microtime(true) * 1000)//$this->post('created')
        );
        $updated = $this->messages->update($message_id, $update_data);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $updated
        );
        $this->response($result);
    }

    public function send_push_to_offline_users_post() {
        $message_id = $this->post('messageId');
        $userIds = $this->post('roomUserIDs');
        $message = $this->messages->get($message_id);
        if(!$message) {
            $result = array(
                "status" => 0,
                "message" => "failed",
                "data" => "empty message."
            );
            $this->response($result);
        }
        $offline_user_ids = [];
        $sender = $this->users->get($message->user_id);
        if($userIds) {
            $user_ids = explode(",", $userIds);
            $room_users = $this->room_users->get_where('room_id', $message->room_id);
            if(count($room_users)>count($user_ids)) {
                foreach ($room_users as $room_user) {
                    $is_offline = true;
                    foreach ($user_ids as $id) {
                        if($room_user->user_id == $id) {
                            $is_offline = false;
                        }
                    }
                    if($is_offline) {
                        $offline_user_ids[] = $room_user->user_id;
                    }
                }
                //print json_encode($offline_user_ids);
                foreach ($offline_user_ids as $offline_user_id) {
                    $new_notification = array(
                        'sender_id' => $message->user_id,
                        'receiver_id' => $offline_user_id,
                        'type'  => '2',
                        'title' => 'New message from '. $sender->full_name,
                        'content' => $message->message,
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    $new_notification_id = $this->notifications->insert($new_notification);
                    $new_notification = $this->notifications->get($new_notification_id);
                    unset($message->user);
                    unset($message->room);
                    unset($message->seenBy);
                    $data = array(
                        'notification' => $new_notification,
                        'message' => $message
                    );

                    $new_seen = array(
                        'message_id'    => $message_id,
                        'user_id'    => $offline_user_id,
                        'created_at' => round(microtime(true) * 1000),
                        'seen_status' => 0
                    );
                    $this->message_seens->insert($new_seen);

                    //print json_encode($data);
                    $push_message = $message->message;
                    if($message->type == 2) {
                        if($message->file && is_object($message->file) && $message->file->file && strpos($message->file->file->mime_type, 'image') !== false) {
                            $push_message = $sender->full_name." has sent you a image";
                        } else if($message->file && is_object($message->file) && $message->file->file && strpos($message->file->file->mime_type, 'video') !== false) {
                            $push_message = $sender->full_name." has sent you a video";
                        } else if($message->file && is_object($message->file) && $message->file->file && strpos($message->file->file->mime_type, 'audio') !== false) {
                            $push_message = $sender->full_name." has sent you a audio";
                        } else {
                            $push_message = $sender->full_name." has sent you a file";
                        }
                    } else if($message->type == 3) {
                        $push_message = $sender->full_name." has sent you a location info";
                    } else if($message->type == 4) {
                        $push_message = $sender->full_name." has sent you a contact info";
                    } else if($message->type == 5) {
                        $push_message = $sender->full_name." has sent you a sticker";
                    } else if($message->type == 1000) {
                        $push_message = $sender->full_name." has joined";
                    } else if($message->type == 1001) {
                        $push_message = $sender->full_name." has left";
                    }
                    $notification_data = $this->notification_data($data, $push_message, "New message from ". $sender->full_name);
                    $this->send_push_notification_by_user($offline_user_id, $notification_data);
                }
            }
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => json_encode($message)." sent to ". json_encode($offline_user_ids) ." push successfully."
        );
        $this->response($result);
    }

    public function set_all_messages_as_seen_post() {
        $room_id = $this->post('room_id');
        $user_id = $this->post('user_id');
        $search_key = array(
            'seen_status' => 0,
            'user_id'   => $user_id
        );
        $message_seens = $this->message_seens->set_where($search_key)->get_all();
        foreach ($message_seens as $message_seen) {
            $message = $this->messages->get($message_seen->message_id);
            if($message && $message->room_id == $room_id) {
                $this->message_seens->update_field($message_seen->id, 'seen_status', 1);
            }
        }
        $result = array(
            "status" => 1,
            "message" => "success"
        );
        $this->response($result);
    }

    public function add_seen_message_by_post() {
        $message_id = $this->post('messageID');
        $new_seen = array(
            'message_id'    => $message_id,
            'user_id'    => $this->post('userID'),
        );
        $message_seens = $this->message_seens->set_where($new_seen)->get_all();
        if(count($message_seens)>0) {
            $this->message_seens->update_field($message_seens[0]->id, 'seen_status', 1);
            $result = array(
                "status" => 1,
                "message" => "Already added",
                "data" => $this->messages->get($message_id)
            );
        } else {
            $this->message_seens->insert(array_merge($new_seen, array('created_at' => round(microtime(true) * 1000))));
            $result = array(
                "status" => 1,
                "message" => "Succeed",
                "data" => $this->messages->get($message_id)
            );
        }
        $this->response($result);
    }

    public function messages_post() {
        $room_id = $this->post('room_id');
        $offset = $this->post('offset');
        $limit = $this->post('limit');

        $where = array(
            'room_id'   => $room_id,
            'message_status' => 1
        );
        $data = $this->messages
            ->paginate_with_offset($offset, $where, $limit);

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );

        $this->response($result);
    }

    public function message_latest_get($room_id, $last_message_id) {
        if($room_id>0) {
            $messages = $this->messages->get_room_latest_messages_with_latest_id($room_id, $last_message_id);
        } else {
            $messages = [];
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => array('messages' => $messages)
        );

        $this->response($result);
    }

    public function message_list_options() {}

    public function message_list_get($room_id, $last_message_id) {
        if($room_id>0) {
            $messages = $this->messages->get_room_messages_with_latest_id_and_limit($room_id, $last_message_id);
        } else {
            $messages = [];
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => array('messages' => $messages)
        );

        $this->response($result);
    }

    public function message_list_post() {
        $room_id = $this->post('roomID');
        $last_message_id = $this->post('lastMessageID');

        if($room_id>0) {
            $messages = $this->messages->get_room_messages_with_latest_id_and_limit($room_id, $last_message_id);
        } else {
            $messages = [];
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => array('messages' => $messages)
        );

        $this->response($result);
    }

    public function upload_file_options() {}

    public function upload_file_post()
    {
        $mime_type = $this->post('mime_type');
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $path = UPLOAD_CHAT_FILES;
            $milliseconds = round(microtime(true) * 1000);

            $name = $_FILES["file"]["name"];
            $ext = pathinfo($name, PATHINFO_EXTENSION);

            $fileName = "file_" . $milliseconds . "." . $ext;
            $file_path = $path . $fileName;

            $tmpFile = $_FILES['file']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                //$finfo = finfo_open(FILEINFO_MIME_TYPE);
                //$mime = finfo_file($finfo, $tmpFile);
                $file_type = $mime_type?$mime_type:$this->mime_content_type($file_path);
                $new_file = array(
                    'name'  => $fileName,
                    'mime_type' => $file_type,
                    'size'  => $_FILES['file']['size'],
                    'created_at' => round(microtime(true) * 1000)
                );
                $new_id = $this->files->insert($new_file);
                $file = $this->files->get($new_id);

                $thumb = null;
                if (strpos($file_type, 'jpeg') !== false || strpos($file_type, 'gif') !== false || strpos($file_type, 'png') !== false) {
                    $thumbName = "thumb_" . $milliseconds . "." . $ext;
                    $thumb_path = UPLOAD_CHAT_THUMBS . $thumbName;
                    $this->resize_crop_image(180, 180, $file_path, $thumb_path);

                    $new_file['name'] = $thumbName;
                    $new_file['is_thumb'] = 1;
                    $new_thumb_id = $this->files->insert($new_file);
                    $thumb = $this->files->get($new_thumb_id);

                    $this->files->update_field($new_id, 'thumb_id', $new_thumb_id);
                    $file->thumb_id = $new_thumb_id;

                } else if(strpos($file_type, 'video') !== false) {
                    $ffmpeg = FFMpeg\FFMpeg::create([
                        'ffmpeg.binaries'  => 'C:/ffmpeg/bin/ffmpeg.exe', // the path to the FFMpeg binary
                        'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe', // the path to the FFProbe binary
                        'timeout'          => 3600, // the timeout for the underlying process
                        'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
                    ]);
                    /*$ffmpeg = FFMpeg\FFMpeg::create([
                        'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                        'ffprobe.binaries' => '/usr/bin/ffprobe',
                        'timeout'          => 3600, // the timeout for the underlying process
                        'ffmpeg.threads'   => 1,   // the number of threads that FFMpeg should use
                    ]);*/
                    //$ffmpeg = FFMpeg\FFMpeg::create();
                    $video = $ffmpeg->open($file_path);
                    $thumbName = "thumb_" . $milliseconds . ".jpg";
                    $thumb_path = UPLOAD_CHAT_THUMBS . $thumbName;
                    $video
                        ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))
                        ->save($thumb_path);

                    $new_file['name'] = $thumbName;
                    $new_file['is_thumb'] = 1;
                    $new_file['mime_type'] = "image/jpeg";
                    $new_thumb_id = $this->files->insert($new_file);
                    $thumb = $this->files->get($new_thumb_id);

                    $this->files->update_field($new_id, 'thumb_id', $new_thumb_id);

                    $file->thumb_id = $new_thumb_id;
                }

                if($thumb) {
                    $data = array('file' => $file, 'thumb' => $thumb);

                } else {
                    $data = array('file' => $file);
                }
                $result = array (
                    "status" => 1,
                    "message" => "Uploaded successfully",
                    "data" => $data
                );
            } else {
                $result = array("status" => 0, "message" => "Copy failed");
            }
        } else {
            $result = array("status" => 0, "message" => "Upload failed");
        }

        $this->response($result);
    }

    protected function mime_content_type($file)
    {
        $type = null;

        // First try with fileinfo functions
        if (function_exists('finfo_open')) {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );
            $type = finfo_file( $finfo, $file );
            finfo_close( $finfo );

        } elseif (function_exists('mime_content_type')) {
            $type = mime_content_type($file);
        }

        // Fallback to the default application/octet-stream
        if (! $type) {
            $type = 'application/octet-stream';
        }

        return $type;
    }

    function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];
        $mime = $imgsize['mime'];

        switch($mime){
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 7;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 80;
                break;

            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if($width_new > $width){
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        }else{
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if($dst_img)imagedestroy($dst_img);
        if($src_img)imagedestroy($src_img);
    }

    public function download_file_get($id) {
        $file = $this->files->get($id);
        redirect($file->downloadUrl);
    }

    public function stickers_get() {
        $sticker_categories = $this->sticker_categories->get_all();
        $data = [];
        foreach ($sticker_categories as $sticker_category) {
            $stickers = $this->stickers->get_where('sticker_category_id', $sticker_category->id);
            $data[] = array(
                'id' => $sticker_category->id,
                'mainPic'   => $sticker_category->mainPic,
                'list'      => $stickers
            );
        }
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => array('stickers'  => $data)
        );
        $this->response($result);
    }

}
