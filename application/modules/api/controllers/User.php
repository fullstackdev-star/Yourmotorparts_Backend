<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * product Controller with Swagger annotations
 * Reference: https://github.com/zircote/swagger-php/
 */
class User extends Base_Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Manager_model', 'managers');
        $this->load->model('Users_group_model', 'user_groups');
        $this->load->model('Group_model', 'groups');
        $this->load->model('User_contact_model', 'user_contacts');
        $this->load->model('Chat_room_user_model', 'room_users');
        $this->load->model('User_address_model', 'user_addresses');
        $this->load->model('Users_review_model', 'users_reviews');
        $this->load->model('Country_model', 'countries');
        $this->load->model('Currency_model', 'currencies');
    }

    public function fill_users_full_name_get()
    {
        $users = $this->users->get_all();
        foreach ($users as $user) {
            $this->users->update_field($user->id, 'full_name', $user->first_name . " " . $user->last_name);
        }
        $result = array(
            "status" => 1,
            "data" => $users[0]
        );
        $this->response($result);
    }

    public function get_user_profile_post()
    {
        $user_id = $this->post('user_id');
        $users = $this->users->get($user_id);
        if (count($users) > 0) {
            $result = array(
                "status" => 1,
                "data" => $users[0]
            );
        } else {
            $result = array(
                "status" => 0,
                "error" => "Not exist user"
            );
        }
        $this->response($result);
    }

    public function id_get($id)
    {
        $data = $this->users->get($id);

        $result = array(
            "status" => 1,
            "data" => $data,
            "settings" => $this->settings()
        );
        $this->response($result);
    }

    public function settings_get() {
        $result = array(
            "status" => 1,
            "data" => $this->settings()
        );
        $this->response($result);
    }

    public function id_post($id)
    {
        $data = $this->users->get($id);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function contact_get($contact_id) {
        $data = $this->contacts->get($contact_id);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function contact_users_get($user_id)
    {
        $data = [];
        $user_contacts = $this->user_contacts->get_contacts($user_id);
        foreach ($user_contacts as $user_contact) {
            if ($user_contact->user_id == $user_id) {
                $opponent = $this->users->get($user_contact->opponent_id);
                $opponent->user_contact_id = $user_contact->id;
                $opponent->my_status = $user_contact->user_status;
                $opponent->opponent_status = $user_contact->opponent_status;
                $data[] = $opponent;

            } else {
                $opponent = $this->users->get($user_contact->user_id);
                $opponent->user_contact_id = $user_contact->id;
                $opponent->my_status = $user_contact->opponent_status;
                $opponent->opponent_status = $user_contact->user_status;
                $data[] = $opponent;
            }
        }

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function contacts_get($user_id)
    {
        /*$where = array(
        );
        $other_where = "(user_id = '$user_id' AND user_status = 1 OR opponent_id = '$user_id' AND opponent_status = 1 OR user_id = '$user_id' AND user_status = 0 OR opponent_id = '$user_id' AND opponent_status = 0)";
        $data = $this->user_contacts
            ->paginate_search(0, 1000, $where, $other_where);*/

        $data = [];
        $user_contacts = $this->user_contacts->get_contacts($user_id);
        foreach ($user_contacts as $user_contact) {
            $user_contact->user = $this->users->get($user_contact->user_id);
            $user_contact->opponent = $this->users->get($user_contact->opponent_id);
            /*if ($user_contact->user_id == $user_id) {
                $opponent = $this->users->get($user_contact->opponent_id);
                $user_contact->opponent = $opponent;

            } else {
                $opponent = $this->users->get($user_contact->user_id);
                $user_contact->opponent = $opponent;
            }
            */
            $data[] = $user_contact;
        }

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function contacts_post()
    {
        $user_id = $this->post('user_id');
        $offset = $this->post('offset');
        $limit = $this->post('limit');

        $where = array();
        $other_where = "(user_id = '$user_id' AND user_status = 1 OR opponent_id = '$user_id' AND opponent_status = 1 OR user_id = '$user_id' AND user_status = 0 OR opponent_id = '$user_id' AND opponent_status = 0)";
        $data = $this->user_contacts
            ->paginate_search($offset, $limit, $where, $other_where);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function request_add_friend_post()
    {
        $user_id = $this->post('user_id');
        $opponent_id = $this->post('opponent_id');

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
        $user_old_status = 0;
        if (count($user_contacts1) > 0) {
            $user_contact = $user_contacts1[0];
            $contact_id = $user_contact->id;
            $user_old_status = $user_contact->user_status;
            $update_data = array(
                'user_status' => 1,
                'opponent_status' => 0,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_contacts->update($contact_id, $update_data);

        } else if (count($user_contacts2) > 0) {
            $user_contact = $user_contacts2[0];
            $contact_id = $user_contact->id;
            $user_old_status = $user_contact->opponent_status;
            $update_data = array(
                'opponent_status' => 1,
                'user_status' => 0,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_contacts->update($contact_id, $update_data);

        } else {
            $new_contact = array_merge($search_key1, array('created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")));
            $contact_id = $this->user_contacts->insert($new_contact);
        }

        $sent_notification = false;
        //if($user_old_status==0) {
        $new_notification = array(
            'sender_id' => $user_id,
            'receiver_id' => $opponent_id,
            'type' => 1,
            'title' => 'Contact Request',
            'content' => '1000',
            'created_at' => date("Y-m-d H:i:s")
        );
        $new_notification_id = $this->notifications->insert($new_notification);
        $new_notification = $this->notifications->get($new_notification_id);
        $data = array(
            'notification' => $new_notification
        );
        $sender = $this->users->get($user_id);
        $notification_data = $this->notification_data($data, $sender->full_name . " has sent you a contact request.", "Contact Request");
        $response = $this->send_push_notification_by_user($opponent_id, $notification_data);
        if ($response) {
            $resultObject = json_decode($response);
            if (isset($resultObject->recipients) && isset($resultObject->id)) {
                $this->notifications->update_field($new_notification_id, 'is_sent', 1);
                $sent_notification = true;
            }
        }
        //}

        $user_contact = $this->user_contacts->set_where(array())->get($contact_id);
        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $user_contact
        );
        $this->response($result);
    }

    public function request_accept_friend_post()
    {
        $contact_id = $this->post('contact_id');
        $user_id = $this->post('user_id');
        $opponent_id = $this->post('opponent_id');

        $user_old_status = 0;
        $user_contact = $this->user_contacts->get($contact_id);
        if ($user_contact->user_id == $user_id) {
            $user_old_status = $user_contact->user_status;
            $update_data = array(
                'user_status' => 1,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_contacts->update($user_contact->id, $update_data);

        } else if ($user_contact->opponent_id == $user_id) {
            $user_old_status = $user_contact->opponent_status;
            $update_data = array(
                'opponent_status' => 1,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_contacts->update($user_contact->id, $update_data);
        }
        $user_contact = $this->user_contacts->get($contact_id);

        $sent_notification = false;
        if ($user_old_status == 0) {
            $new_notification = array(
                'sender_id' => $user_id,
                'receiver_id' => $opponent_id,
                'type' => 1,
                'title' => 'Contact Request',
                'content' => '1001',
                'created_at' => date("Y-m-d H:i:s")
            );
            $new_notification_id = $this->notifications->insert($new_notification);
            $data = array(
                'notification_id' => $new_notification_id
            );
            $sender = $this->users->get($user_id);
            $notification_data = $this->notification_data($data, $sender->full_name . " has accept your contact request.", "Contact Request");
            $response = $this->send_push_notification_by_user($opponent_id, $notification_data);
            if ($response) {
                $resultObject = json_decode($response);
                if (isset($resultObject->recipients) && isset($resultObject->id)) {
                    $this->notifications->update_field($new_notification_id, 'is_sent', 1);
                    $sent_notification = true;
                }
            }
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $user_contact
        );
        $this->response($result);
    }

    public function request_reject_friend_post()
    {
        $contact_id = $this->post('contact_id');
        $user_id = $this->post('user_id');
        $opponent_id = $this->post('opponent_id');

        $user_old_status = 0;
        $user_contact = $this->user_contacts->get($contact_id);
        if ($user_contact->user_id == $user_id) {
            $user_old_status = $user_contact->opponent_status;
            $update_data = array(
                'user_status' => 3,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_contacts->update($user_contact->id, $update_data);

        } else if ($user_contact->user_id == $opponent_id) {
            $user_old_status = $user_contact->opponent_status;
            $update_data = array(
                'opponent_status' => 3,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_contacts->update($user_contact->id, $update_data);
        }
        $user_contact = $this->user_contacts->get($contact_id);

        $sent_notification = false;
        if ($user_old_status == 0) {
            $new_notification = array(
                'sender_id' => $user_id,
                'receiver_id' => $opponent_id,
                'type' => 1,
                'title' => 'Contact Request',
                'content' => '1002',
                'created_at' => date("Y-m-d H:i:s")
            );
            $new_notification_id = $this->notifications->insert($new_notification);
            $data = array(
                'notification_id' => $new_notification_id
            );
            $sender = $this->users->get($user_id);
            $notification_data = $this->notification_data($data, $sender->full_name . " has reject your contact request.", "Contact Request");
            $response = $this->send_push_notification_by_user($opponent_id, $notification_data);
            if ($response) {
                $resultObject = json_decode($response);
                if (isset($resultObject->recipients) && isset($resultObject->id)) {
                    $this->notifications->update_field($new_notification_id, 'is_sent', 1);
                    $sent_notification = true;
                }
            }
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $user_contact
        );
        $this->response($result);
    }

    public function delete_contact_post()
    {
        $user_id = $this->post('user_id');
        $user_contact_id = $this->post('user_contact_id');
        $user_contact = $this->user_contacts->get($user_contact_id);
        if ($user_contact->user_id == $user_id) {
            $delete_contact = array(
                'user_status' => 3
            );
            $this->user_contacts->update($user_contact_id, $delete_contact);

            // delete dialogs
            $opponent_id = $user_contact->opponent_id;
            $user_rooms = $this->room_users->get_where('user_id', $user_id);
            foreach ($user_rooms as $user_room) {
                $search_key = array(
                    'room_id' => $user_room->room_id,
                    'user_id' => $opponent_id
                );
                $opponent_rooms = $this->room_users->set_where($search_key)->get_all();
                if (count($opponent_rooms) > 0) {
                    $this->room_users->delete($user_room->id);
                }
            }

        } else if ($user_contact->opponent_id == $user_id) {
            $delete_contact = array(
                'opponent_status' => 3
            );
            $this->user_contacts->update($user_contact_id, $delete_contact);

            // delete dialogs
            $opponent_id = $user_contact->user_id;
            $user_rooms = $this->room_users->get_where('user_id', $user_id);
            foreach ($user_rooms as $user_room) {
                $search_key = array(
                    'room_id' => $user_room->room_id,
                    'user_id' => $opponent_id
                );
                $opponent_rooms = $this->room_users->set_where($search_key)->get_all();
                if (count($opponent_rooms) > 0) {
                    $this->room_users->delete($user_room->id);
                }
            }
        } else {
            $result = array(
                "status" => 0,
                "error" => "failed"
            );
            $this->response($result);
        }

        $sent_notification = false;
        //if($user_old_status==0) {
        $new_notification = array(
            'sender_id' => $user_id,
            'receiver_id' => $opponent_id,
            'type' => 1,
            'title' => 'Contact Request',
            'content' => '1003',
            'created_at' => date("Y-m-d H:i:s")
        );
        $new_notification_id = $this->notifications->insert($new_notification);
        $new_notification = $this->notifications->get($new_notification_id);
        $data = array(
            'notification' => $new_notification
        );
        $sender = $this->users->get($user_id);
        $notification_data = $this->notification_data($data, $sender->full_name . " has deleted you from his contacts.", "Contact Request");
        $response = $this->send_push_notification_by_user($opponent_id, $notification_data);
        if ($response) {
            $resultObject = json_decode($response);
            if (isset($resultObject->recipients) && isset($resultObject->id)) {
                $this->notifications->update_field($new_notification_id, 'is_sent', 1);
                $sent_notification = true;
            }
        }

        $result = array(
            "status" => 1,
            "message" => "success"
        );
        $this->response($result);
    }

    public function delete_dialog_post()
    {
        $user_id = $this->post('user_id');
        $dialog_id = $this->post('dialog_id');
        $this->room_users->delete($dialog_id);

        $result = array(
            "status" => 1,
            "message" => "success"
        );
        $this->response($result);
    }

    public function all_managers_post()
    {
        $managers = $this->managers->get_all();
        $data = "";
        foreach ($managers as $manager) {
            $data .= "," . $manager->user_id;
        }
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function user_put($id)
    {
        $data = elements(array('first_name', 'last_name'), $this->put());

        // proceed to update user
        $updated = $this->ion_auth->update($id, $data);

        // result
        ($updated) ? $this->success($this->ion_auth->messages()) : $this->error($this->ion_auth->errors());
    }

    public function reset_gender_post()
    {
        $user_id = $this->post('id');
        $gender = $this->post('gender');

        $data = array('gender' => $gender);

        // proceed to update user
        $updated = $this->ion_auth->update($user_id, $data);

        // result
        ($updated) ? $this->success($this->ion_auth->messages()) : $this->error($this->ion_auth->errors());
    }

    public function sign_up_post()
    {
        // required fields
        $password = $this->post('password');
        $email = $this->post('email');
        $username = $this->post('username');

        $token = $this->generateRandomString(50);
        // additional fields
        $additional_data = array(
            "username" => $username,
            "full_name" => $username,
            "token" => $token
        );

        // set user to "members" group
        $group = array('1');

        $email_users = $this->users->get_where('email', $email);
        $username_users = $this->users->get_where('username', $username);
        if (count($email_users) > 0) {
            $result = array(
                "status" => 0,
                "error" => "This email has been taken, please sign in"
            );
            $this->response($result);

        } else if (count($username_users) > 0) {
            $result = array(
                "status" => 0,
                "error" => "The username  has been taken, please choose another one"
            );
            $this->response($result);

        } else {
            // proceed to create user
            $user_id = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
            if ($user_id) {
                $createdUser = $this->users->get($user_id);
                $token = $this->generateRandomString(50);
                $this->users->update_field($createdUser->id, 'token', $token);
                $createdUser->token = $token;

                $result = array(
                    "status" => 1,
                    "data" => $createdUser
                );
                $this->response($result);

            } else {
                $this->error($this->ion_auth->errors());
            }
        }

    }

    public function update_first_last_name_post() {
        $user_id = $this->post('user_id');
        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');

        $update_data = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "full_name" => $first_name . " " . $last_name
        );

        $this->users->update($user_id, $update_data);
        $mUser = $this->users->get($user_id);

        $response = array(
            "status" => 1,
            "data" => $mUser
        );
        $this->response($response);
    }

    public function update_profile_post() {
        $user_id = $this->post('user_id');
        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');
        $number = $this->post('number');
        $gender = $this->post('gender');
        $title = $this->post('title');
        $country_id = $this->post('country_id');
        $currency_id = $this->post('currency_id');
        //$birthday = $this->post('birthday');
        //$postal_code = $this->post('postal_code');
        //$address = $this->post('address');

        $update_data = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "phone" => $number,
            "gender" => $gender,
            'title' => $title,
            //"birthday" => $birthday,
            //"postal_code" => $postal_code,
            //"address" => $address,
            "full_name" => $first_name . " " . $last_name,
            "currency_id" => $currency_id,
            "country_id" => $country_id
        );

        $this->users->update($user_id, $update_data);
        $mUser = $this->users->get($user_id);

        $response = array(
            "status" => 1,
            "data" => $mUser
        );
        $this->response($response);
    }

    public function update_profile_with_image_post()
    {
        $user_id = $this->post('user_id');
        $username = $this->post('username');
        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');
        $number = $this->post('number');
        $gender = $this->post('gender');
        $title = $this->post('title');
        $country_id = $this->post('country_id');
        $currency_id = $this->post('currency_id');
        //$birthday = $this->post('birthday');
        //$postal_code = $this->post('postal_code');
        //$address = $this->post('address');

        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $path = UPLOAD_PROFILE_PHOTO;

            $milliseconds = round(microtime(true) * 1000);
            $fileName = "profile_" . sprintf("%.0f", $milliseconds) . '.png';
            $file_path = $path . $fileName;

            $tmpFile = $_FILES['image']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                $update_data = array(
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "phone" => $number,
                    "gender" => $gender,
                    "photo" => $fileName,
                    'title' => $title,
                    "currency_id" => $currency_id,
                    "country_id" => $country_id,
                    //"birthday" => $birthday,
                    //"postal_code" => $postal_code,
                    //"address" => $address,
                    "full_name" => $first_name . " " . $last_name
                );

                $this->users->update($user_id, $update_data);
                $mUser = $this->users->get($user_id);

                $response = array(
                    "status" => 1,
                    "data" => $mUser
                );
                $this->response($response);

            } else {
                $this->response(array("status" => 0, "error" => "Image Upload failed"));
            }
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed."));
        }
    }

    public function upload_profile_image_post()
    {
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $path = UPLOAD_PROFILE_PHOTO;
            $userId = $this->post("user_id");

            $milliseconds = round(microtime(true) * 1000);
            $fileName = "profile_" . $milliseconds . '.png';
            $file_path = $path . $fileName;

            $oldFile = $this->users->get_photo($userId);
            if (!is_null($oldFile) && $oldFile!='') {
                $oldFilePath = UPLOAD_PROFILE_PHOTO . $oldFile;
                unlink($oldFilePath);
            }

            $this->users->update_field($userId, 'photo', $fileName);

            $tmpFile = $_FILES['image']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                $this->response(array("status" => 1, "message" => "Uploaded successfully", "image_url" => base_url().$file_path));
            } else {
                $this->response(array("status" => 0, "error" => "Upload failed"));
            }
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed."));
        }
    }

    public function remove_profile_image_post()
    {
        $userId = $this->post("user_id");

        $photo = $this->users->get_photo($userId);
        if (!is_null($photo) && $photo!='') {
            $photoPath = UPLOAD_PROFILE_PHOTO . $photo;
            unlink($photoPath);
        }
        $updated = $this->users->update_field($userId, 'photo', '');

        if ($updated) {
            $this->response(array("status" => 1, "message" => "Removed successfully"));
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed"));
        }
    }

    public function upload_profile_image1_post()
    {
        if (is_uploaded_file($_FILES['profile_name']['tmp_name'])) {
            $path = UPLOAD_USER_PROFILE_POST;
            $userId = basename($_FILES['profile_name']['name']);

            $milliseconds = round(microtime(true) * 1000);
            $fileName = "profile_" . $milliseconds . '.jpg';
            $file_path = $path . $fileName;

            $oldFile = $this->users->get_field($userId, 'profile_image');
            if (!is_null($oldFile)) {
                //$oldFile = $path . $oldFile;
                unlink($oldFile);
            }

            $this->users->update_field($userId, 'profile_image', $fileName);

            $tmpFile = $_FILES['profile_name']['tmp_name'];
            if (move_uploaded_file($tmpFile, $file_path)) {
                $this->response(array("status" => 1, "message" => "Uploaded successfully", "profile_url" => $file_path));
            } else {
                $this->response(array("status" => 0, "error" => "Upload failed"));
            }
        } else {
            $this->response(array("status" => 0, "error" => "Upload failed"));
        }
    }

    public function activate_post()
    {
        $user_id = $this->post('id');
        $code = $this->post('code');
        $activation = $this->ion_auth->activate($user_id, $code);

        // result
        ($activation) ? $this->success($this->ion_auth->messages()) : $this->error($this->ion_auth->errors());
    }

    public function login_post()
    {
        $email = $this->post('email');
        $password = $this->post('password');

        // proceed to login user
        $logged_in = $this->ion_auth->login($email, $password, FALSE);
        if ($logged_in) {
            // get User object and remove unnecessary fields
            $user = $this->ion_auth->user()->row();

            $token = $this->generateRandomString(50);
            $this->users->update_field($user->id, 'token', $token);

            $user = $this->users->get($user->id);

            $this->load->model('Product_model', 'products');
            $created_product_count = $this->products->get_where("owner_id", $user->id);
            // return result
            $result = array(
                "status" => 1,
                "data" => $user,
                "created_product_count" => count($created_product_count)
            );
            $this->response($result);

        } else {
            /*$result = array(
                "status" => 0,
                "message" => "Email or password is invalid",
                "data" => ""
            );
            $this->response($result);*/
            $this->error($this->ion_auth->errors());
        }
    }

    public function login_fb_post()
    {
        $fb_id = $this->post('fb_id');
        $username = $this->post('username');
        $email = $this->post('email');
        $first_name = $this->post('first_name');
        $last_name = $this->post('last_name');
        $gender = $this->post('gender');
        $password = $fb_id;

        $search_key = array(
            "fb_id" => $fb_id
        );
        $exist_users = $this->users->set_where($search_key)->get_all();
        if (count($exist_users) > 0) {
            $logged_in = $this->ion_auth->login($email, $password, FALSE);
            if ($logged_in) {
                // get User object and remove unnecessary fields
                $user = $this->ion_auth->user()->row();
                $token = $this->generateRandomString(50);
                $this->users->update_field($user->id, 'token', $token);
                
                $user = $this->users->get($user->id);

                $this->load->model('Product_model', 'products');
                $created_product_count = $this->products->get_where("owner_id", $user->id);
                // return result
                $result = array(
                    "status" => 1,
                    "data" => $user,
                    "is_sign_up" => false,
                    "created_product_count" => $created_product_count
                );
                $this->response($result);
            } else {
                //$this->error($this->ion_auth->errors());
                $result = array(
                    "status" => 0,
                    "message" => "Email or password is invalid",
                    "data" => ""
                );
                $this->response($result);
            }
        } else {
            // additional fields
            $photo = "http://graph.facebook.com/" . $fb_id . "/picture?type=square";
            $additional_data = array(
                "fb_id" => $fb_id,
                "username" => $username,
                "gender" => $gender,
                "photo" => $photo,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "full_name" => $first_name . " " . $last_name
            );

            // set user to "members" group
            $group = array('1');

            // proceed to create user
            $user_id = $this->ion_auth->register($username, $password, $email, $additional_data, $group);

            // result
            if ($user_id) {
                $createdUser = $this->users->get($user_id);
                $token = $this->generateRandomString(50);
                $this->users->update_field($createdUser->id, 'token', $token);
                $createdUser->token = $token;
                
                $result = array(
                    "status" => 1,
                    "is_sign_up" => true,
                    "data" => $createdUser
                );
                $this->response($result);

            } else {
                $result = array(
                    "status" => 0,
                    "message" => "Can not create an account, Please contact to support team",
                    "data" => ""
                );
                $this->response($result);
                //$this->error($this->ion_auth->errors());
            }
        }

    }

    public function update_addresses_post() {
        $user_id = $this->post('user_id');
        $addresses = $this->post('addresses');
        foreach ($addresses as $address) {
            $address_id = $address['id'];
            $new_user_address = array(
                'user_id' => $user_id,
                'address' => $address['address'],
                'lat'  => $address['lat'],
                'lng'  => $address['lng']
            );
            if($address_id && $address_id != '') {
                $user_address = $this->user_addresses->get($address_id);
                if($user_address) {
                    $this->user_addresses->update($user_address->id, $new_user_address);
                } else {
                    $this->user_addresses->insert($new_user_address);
                }
            } else {
                $this->user_addresses->insert($new_user_address);
            }
        }
        $user = $this->users->get($user_id);
         $result = array(
             "status" => 1,
             "data" => $user
         );
         $this->response($result);
    }

    public function register_push_token_post()
    {
        $user_id = $this->post('user_id');
        $one_signal_id = $this->post('one_signal_id');
        $token = $this->post('token');
        $device_id = $this->post('device_id');
        $device_type = $this->post('device_type');

        if($user_id != '') {
            $this->reset_user_unread_notification_count($user_id);
        }

        $search_key = array(
            'device_id' => $device_id,
            'device_type' => $device_type
        );

        $user_token = $this->user_push_tokens->get_first_one_where($search_key);
        if ($user_token) {
            $update_data = array(
                'user_id' => $user_id,
                'token' => $token,
                'one_signal_id' => $one_signal_id,
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_push_tokens->update($user_token->id, $update_data);
            $res = $user_token->status;

        } else {
            $new_token = array(
                'user_id' => $user_id,
                'token' => $token,
                'one_signal_id' => $one_signal_id,
                'topic' => 'news',
                'device_id' => $device_id,
                'device_type' => $device_type,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            );
            $this->user_push_tokens->insert($new_token);
            $res = 1;
        }

        $result = array(
            "status" => 1,
            "data" => $res
        );
        $this->response($result);
    }

    public function notification_status_post() {
        $device_id = $this->post('device_id');
        $device_type = $this->post('device_type');
        $status = $this->post('status');

        $search_key = array(
            'device_id' => $device_id,
            'device_type' => $device_type
        );

        $user_token = $this->user_push_tokens->get_first_one_where($search_key);
        if ($user_token) {
            $this->user_push_tokens->update_field($user_token->id, "status", $status);
            $result = array(
                "status" => 1,
                "data" => $status
            );
        } else {
            $result = array(
                "status" => 0,
                "error" => "Device is not registered."
            );
        }

        $this->response($result);
    }

    public function search_users_with_sub_username()
    {
        $offset = $this->post("offset");
        $limit = $this->post("limit");
        $search_key = $this->post("search_key");

        $other_where = "(username LIKE '%$search_key%')";
        $data = $this->users->paginate_search($offset, $limit, array(), $other_where);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function search_users_with_sub_full_name()
    {
        $offset = $this->post("offset");
        $limit = $this->post("limit");
        $search_key = $this->post("search_key");

        $other_where = "(full_name LIKE '%$search_key%')";
        $data = $this->users->paginate_search($offset, $limit, array(), $other_where);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function search_users_with_sub_email()
    {
        $offset = $this->post("offset");
        $limit = $this->post("limit");
        $search_key = $this->post("search_key");

        $other_where = "(email LIKE '%$search_key%')";
        $data = $this->users->paginate_search($offset, $limit, array(), $other_where);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function search_users_post()
    {
        $offset = $this->post("offset");
        $limit = $this->post("limit");
        $search_key = $this->post("search_key");
        $user_type = $this->post("user_type");
        //$user_status = $this->post("user_status");

        $where = array();
        if (!empty($user_type)) {
            $where["user_type"] = $user_type;
        }

        /*if ($user_status == "0" || $user_status == "1") {
            $where["active"] = $user_status;
        }*/

        $other_where = "";
        if (!empty($search_key)) {
            $other_where = "(username LIKE '%$search_key%' OR full_name LIKE '%$search_key%')";
        }

        $data = $this->users->paginate_search($offset, $limit, $where, $other_where);

        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function reset_unread_push_count_post()
    {
        $user_id = $this->post('user_id');
        $this->reset_user_unread_notification_count($user_id);
        $result = array(
            "status" => 1,
            "message" => "success",
        );
        $this->response($result);
    }

    public function send_push_test_post()
    {
        $user_id = $this->post('user_id');
        $message_id = $this->post('message_id');
        $notification_id = $this->post('notification_id');
        $this->load->model('Chat_message_model', 'messages');
        $message = $this->messages->get($message_id);
        unset($message->user);
        unset($message->room);
        unset($message->seenBy);
        $notification = $this->notifications->get($notification_id);
        $data = array(
            'notification' => $notification,
            'message' => $message
        );
        $sender = $this->users->get($user_id);
        $notification_data = $this->notification_data($data, $sender->full_name . " has sent you a notification.", "Test");
        $response = $this->send_push_notification_by_user($user_id, $notification_data);
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function update_user_type_post()
    {
        $user_id = $this->post('user_id');
        $user_type = $this->post('user_type');

        $update_data = array(
            'user_type' => $user_type
        );

        $this->users->update($user_id, $update_data);
        $result = array(
            "status" => 1
        );
        $this->response($result);
    }

    public function forgot_password_post()
    {
        // proceed to forgot password
        $email = $this->post('email');
        $forgotten = $this->ion_auth->forgotten_password($email);

        if ($forgotten) {
            // TODO: send email to user
            $code = $forgotten['forgotten_password_code'];

            $this->load->library('My_email');
            $subject = $this->lang->line('email_forgotten_password_subject');
            $email_view = $this->config->item('email_templates', 'ion_auth') . $this->config->item('email_forgot_password', 'ion_auth');

            $this->my_email->send_by_phpmailer($email, "Your Motor Parts", $subject, $email_view, $forgotten);

            $result = array(
                "status" => 1
            );
            $this->response($result);

        } else {
            //$this->error($this->ion_auth->errors());
            $result = array(
                "status" => 0
            );
            $this->response($result);
        }
    }

    /*$from_email = "jin_q@outlook.com";
            $from_name = "Jin";
            $to      = $email;
            $subject = "Rest Password";
            $message = "Reset your password";
            $html_message = "<html><body><div><p>$message</p></div></body></html>";

            $headers = 'From: '.$from_name.'<'.$from_email.'>' . "\r\n" .
                'Content-type: text/html; charset=utf8' . "\r\n".
                'X-Mailer: PHP/' . phpversion();

            $data = array();
            if(mail($to, $subject, $html_message, $headers)){
                $data['status'] = 'success';
                $data['code'] = $code;
                $this->response($data);

            } else {
                $data['status'] = 'fail';
                $data['code'] = $code;
                $this->response($data);
            }*/

    public function forgotten_password_complete_post()
    {
        // proceed to reset password
        $code = $this->post('code');
        $password = $this->post('password');
        $password_confirm = $this->post('password_confirm');

        // verify passwords are the same (TODO: better validation)
        if ($password === $password_confirm) {
            // verify reset code
            $reset = $this->ion_auth->forgotten_password_complete($code);

            if ($reset) {
                // proceed to change user password
                $updated = $this->ion_auth->reset_password($reset['identity'], $password);
                ($updated) ? $this->success($this->ion_auth->messages()) : $this->error($this->ion_auth->errors());
            } else {
                $this->error($this->ion_auth->errors());
            }
        } else {
            $this->error('Password not identical');
        }
    }

    public function reset_password_post()
    {
        // proceed to reset password
        $email = $this->post('email');
        $password = $this->post('password');
        $password_confirm = $this->post('password_confirm');

        $forgotten = $this->ion_auth->forgotten_password($email);
        if ($forgotten) {
            // TODO: send email to user
            $code = $forgotten['forgotten_password_code'];

            // verify passwords are the same (TODO: better validation)
            if ($password === $password_confirm) {
                // verify reset code
                $reset = $this->ion_auth->forgotten_password_complete($code);

                if ($reset) {
                    // proceed to change user password
                    $updated = $this->ion_auth->reset_password($reset['identity'], $password);
                    ($updated) ? $this->success($this->ion_auth->messages()) : $this->error($this->ion_auth->errors());
                } else {
                    $this->error($this->ion_auth->errors());
                }
            } else {
                $this->error('Password not identical');
            }

            //$this->success($this->ion_auth->messages());
        } else {
            $this->error($this->ion_auth->errors());
        }
    }

    public function is_available_users_to_active_account_post($user_id)
    {
        $result = array(
            "status" => 1
        );
        $this->response($result);
    }

    public function is_user_need_to_force_update_app_post()
    {
        $app_version = $this->post('app_version');
        $result = array(
            "status" => 1
        );

        if (version_compare($app_version, '2.2.56') >= 0) {
            $result['data'] = 0;

        } else {
            $result['data'] = 1;
        }

        $this->response($result);
    }

    public function membership_items_get()
    {
        $this->load->model('Membership_item_model', 'membership_items');
        $data = $this->membership_items->get_all();
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    // for chat part
    public function node_login_options() {}

    public function node_login_post()
    {
        $user_id = $this->post('user_id');
        $room_id = $this->post('room_id');
        $user = $this->users->get($user_id);
        if (!$user) {
            $result = array(
                "status" => 0,
                "data" => "Not exist user"
            );
            $this->response($result);
            return;
        }

        $room = $this->rooms->get($room_id);
        if (!$room) {
            $result = array(
                "status" => 0,
                "data" => "Not exist room"
            );
            $this->response($result);
            return;
            /*$new_room = array(
                'name'  => 'room_of_'.$user->name,
                'creator_id' => $user->id,
                'created_at' => round(microtime(true) * 1000)
            );
            $new_id = $this->rooms->insert($new_room);
            $room = $this->rooms->get($new_id);*/
        }

        $search_key = array(
            'user_id' => $user->id,
            'room_id' => $room->id
        );
        $room_users = $this->room_users->set_where($search_key)->get_all();
        if (count($room_users) == 0) {
            $this->room_users->insert(array_merge($search_key, array('created_at' => round(microtime(true) * 1000))));
        }

        if ($user) {
            $token = $this->generateRandomString(50);
            $this->users->update_field($user->id, 'token', $token);
            $user->roomName = $room->name;
            $user->token = $token;
            $result = array(
                "status" => 1,
                "data" => array(
                    'user' => $user,
                    'token' => $token
                )
            );
        } else {
            $result = array(
                "status" => 0,
                "data" => "Not exist user"
            );
        }
        $this->response($result);
    }

    public function users_by_ids_post() {
        $str_ids = $this->post('ids');
        $ids = explode(",", $str_ids);

        $data = [];
        foreach ($ids as $id) {
            $data[] = $this->users->get($id);
        }

        $result = array(
            "status" => 1,
            "message" => "success",
            "data" => $data
        );

        $this->response($result);
    }

    public function list_get($room_id)
    {
        $room_users = $this->room_users->get_where('room_id', $room_id);
        $data = [];
        foreach ($room_users as $room_user) {
            $user = $room_user->user;
            if (!$user) continue;
            $user->roomID = $room_id;
            $data[] = $user;
        }

        if (count($data) > 0) {
            $result = array(
                "status" => 1,
                "data" => $data
            );
        } else {
            $result = array(
                "status" => 0,
                "message" => "No users"
            );
        }
        $this->response($result);
    }

    public function timezone_get()
    {
        //$timezone1 = date_default_timezone_get();
        //$date1 = date('Y-m-d H:i:s T', time());
        //date_default_timezone_set('UTC');
        $date2 = date('Y-m-d H:i:s T', time());
        $result = array(
            "status" => 0,
            //"timezone1" => $timezone1,
            //"date1" => $date1,
            "timezone2" => date_default_timezone_get(),
            "date2" => $date2,
        );

        $this->response($result);
    }

    public function contact_us_post() {
        $username = $this->post('username');
        $email = $this->post('email');
        $subject = $this->post('subject');
        $message = $this->post('message');
        $type = $this->post('type');

        $new_message = array(
            'username'  => $username,
            'email'     => $email,
            'subject'   => $subject?$subject:"",
            'message'   => $message,
            'type'      => $type,
            'created_at' => date("Y-m-d H:i:s")
        );
        $this->load->model('Report_message_model', 'report_messages');
        $this->report_messages->insert($new_message);

        if($subject) {
            $this->load->model('Constant_model', 'constants');
            $contact_email = $this->constants->get_first_one_where('key', 'contact_email')->value;
            $data = $this->send_email_by_phpmailer($contact_email, EMAIL_FROM_NAME, $subject, $message, $email, $username);
        }

        $result = array(
            "status" => 1,
            "result" => $data   
        );
        $this->response($result);
    }

    public function block_user_post($user_id) {
        $user = $this->users->get($user_id);
        if($user->active == 1) {
            $status = $this->users->update_field($user_id, 'active', '0');
        } else {
            $status = $this->users->update_field($user_id, 'active', '1');
        }
        $result = array(
            "status" => 1,
            "result" => $status
        );
        $this->response($result);
    }

    public function iap_status_post() {
        $user_id = $this->post('user_id');
        $premium_type = $this->post('premium_type');
        $status = $this->users->update_field($user_id, 'premium_type', $premium_type);
        $result = array(
            "status" => 1,
            "result" => $status
        );
        $this->response($result);
    }

    public function user_customers_reviews_get($user_id) {
        $result = array(
            "status" => 1,
            "data" => $this->users_reviews->get_where('user_id', $user_id)
        );

        $this->response($result);
    }

    public function customer_review_post() {
        $user_id = $this->post('user_id');
        $customer_id = $this->post('customer_id');
        $rating = $this->post('rating');
        $comment = $this->post('comment');

        $update_date = array(
            "user_id" => $user_id,
            "customer_id" => $customer_id,
            "rating" => $rating,
            "comment" => $comment,
            "updated_at" => date("Y-m-d H:i:s")
        );

        $search = array(
            "user_id" => $user_id,
            "customer_id" => $customer_id
        );
        $exist_data = $this->users_reviews->get_first_one_where($search);
        if($exist_data) {
            $this->users_reviews->update($exist_data->id, $update_date);
            $data = array_merge(array('id' => $exist_data->id, 'created_at' => $exist_data->created_at, 'customer'=>$exist_data->customer), $update_date);

        } else {
            $update_date["created_at"] = date("Y-m-d H:i:s");
            $new_id = $this->users_reviews->insert($update_date);
            $data = array_merge(array('id' => $new_id, 'customer'=>$this->users->get($customer_id)), $update_date);
        }

        $result = array(
            "status" => 1,
            "data" => $data
        );

        $this->response($result);
    }

    public function delete_customer_review_post() {
        $user_id = $this->post('user_id');
        $customer_id = $this->post('customer_id');

        $search = array(
            "user_id" => $user_id,
            "customer_id" => $customer_id
        );
        $exist_data = $this->users_reviews->get_first_one_where($search);
        $result = false;
        if($exist_data) {
            $result = $this->users_reviews->delete($exist_data->id);
        }

        $result = array(
            "status" => 1,
            "data" => $result
        );

        $this->response($result);
    }

    public function getAllPushTokens_get() {
        $data = $this->user_push_tokens->get_all();
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function countries_get() {
        $data = $this->countries->get_all();
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function currencies_get() {
        $data = $this->currencies->get_all();
        $result = array(
            "status" => 1,
            "data" => $data
        );
        $this->response($result);
    }

    public function settings() {
        $data = array();

        $this->load->model('Constant_model', 'constants');
        $constants = $this->constants->get_all();
        foreach ($constants as $constant) {
            if($constant->key === 'privacy') continue;
            if($constant->key === 'terms') continue;
            if($constant->key == 'intro_video') {
                $intro_video = $constant->value;
                if($intro_video!=null && $intro_video!='') {
                    $intro_video = base_url().UPLOAD_INTRO_VIDEO.$intro_video;
                }
                $data = array_merge($data, array($constant->key => $intro_video));
            } else {
                $data = array_merge($data, array($constant->key => $constant->value));
            }
        }

        return $data;
    }

}
