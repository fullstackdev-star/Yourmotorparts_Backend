<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Util extends Admin_Controller {

	private $mLatestSqlFile;
	private $mBackupSqlFiles;

	public function __construct()
	{
		parent::__construct();

		// list out .sql files from /sql/backup/ folder
		$sql_path = FCPATH.'sql';
		$files = preg_grep("/.(.sql)$/", scandir($sql_path.'/backup', SCANDIR_SORT_DESCENDING));
		$this->mBackupSqlFiles = $files;
		$this->mLatestSqlFile = $sql_path.'/latest.sql';

		$this->mPageTitle = 'Utilities';
		$this->mViewData['backup_sql_files'] = $this->mBackupSqlFiles;
		$this->mViewData['latest_sql_file'] = $this->mLatestSqlFile;
	}

	// List out saved versions of database
	public function list_db()
	{
		$this->render('util/list_db');
	}

	// Backup current database version
	public function backup_db()
	{
		$this->load->dbutil();
		$this->load->helper('file');

		// Options: http://www.codeigniter.com/user_guide/database/utilities.html?highlight=csv#setting-backup-preferences
		$prefs = array('format' => 'txt');
		$backup = $this->dbutil->backup($prefs);
		$file_path_1 = FCPATH.'sql/backup/'.date('Y-m-d_H-i-s').'.sql';
		$result_1 = write_file($file_path_1, $backup);
		
		// overwrite latest.sql
		$save_latest = $this->input->get('save_latest');
		if ( !empty($save_latest) )
		{
			$file_path_2 = FCPATH.'sql/latest.sql';
			$result_2 = write_file($file_path_2, $backup);	
		}

		redirect($this->mModule.'/util/list_db');
	}

	// Restore specific version of database
	public function restore_db($file)
	{
		$path = '';
		if ($file=='latest')
			$path = FCPATH.'sql/latest.sql';
		else if ( in_array($file, $this->mBackupSqlFiles) )
			$path = FCPATH.'sql/backup/'.$file;

		// proceed to execute SQL queries
		if ( !empty($path) && file_exists($path) )
		{
			//$sql = file_get_contents($path);
			//$this->db->query($sql);
			$username = $this->db->username;
			$password = $this->db->password;
			$database = $this->db->database;
			exec("mysql -u $username -p$password --database $database < $path");
		}

		redirect($this->mModule.'/util/list_db');
	}

	// Remove specific database version
	public function remove_db($file)
	{
		if ( in_array($file, $this->mBackupSqlFiles) )
		{
			$path = FCPATH.'sql/backup/'.$file;

			$this->load->helper('file');
			unlink($path);
			$result = delete_files($path);
		}

		redirect($this->mModule.'/util/list_db');
	}

    public function terms() {
        $this->load->library('form_builder');
        $form = $this->form_builder->create_form();

        $this->load->model('Constant_model', 'constants');
        $constant_terms = $this->constants->get_first_one_where('key', 'terms');
        $terms = $constant_terms?$constant_terms->value:"";

        if ($form->validate())
        {
            // passed validation
            $post_terms = $this->input->post('terms');

            // proceed to create user
            $result = $this->constants->update_field($constant_terms->id, 'value', $post_terms);
            if ($result)
            {
                // success
                $this->system_message->set_success("successfully updated");
            }
            else
            {
                $this->system_message->set_error("failed");
            }
            refresh();
        }

        $this->mPageTitle = 'Terms of Service';
        $this->mViewData['form'] = $form;
        $this->mViewData['constant'] = array(
            'terms' => $terms
        );
        $this->render('util/terms');
    }

    public function privacy() {
        $this->load->library('form_builder');

        $form = $this->form_builder->create_form();

        $this->load->model('Constant_model', 'constants');
        $constant_privacy = $this->constants->get_first_one_where('key', 'privacy');

        $privacy = $constant_privacy?$constant_privacy->value:"";

        if ($form->validate())
        {
            // passed validation
            $post_instruction = $this->input->post('privacy');

            // proceed to create user
            $result = $this->constants->update_field($constant_privacy->id, 'value', $post_instruction);
            if ($result)
            {
                // success
                $this->system_message->set_success("successfully updated");
            }
            else
            {
                $this->system_message->set_error("failed");
            }
            refresh();
        }

        $this->mPageTitle = 'Privacy Policy';
        $this->mViewData['form'] = $form;
        $this->mViewData['constant'] = array(
            'privacy' => $privacy
        );
        $this->render('util/privacy');
    }

    public function settings(){
        $this->load->library('form_builder');
        $this->load->model('Constant_model', 'constants');

        $form = $this->form_builder->create_form();

        $constant_contact_phone = $this->constants->get_first_one_where('key', 'contact_phone');
        $constant_contact_email = $this->constants->get_first_one_where('key', 'contact_email');
        $constant_pending_time = $this->constants->get_first_one_where('key', 'pending_time');

        $contact_phone = $constant_contact_phone->value;
        $contact_email = $constant_contact_email->value;
        $pending_time = $constant_pending_time->value;

        if ($form->validate())
        {
            // passed validation
            $post_contact_phone = $this->input->post('contact_phone');
            $post_contact_email = $this->input->post('contact_email');
            $post_pending_time = $this->input->post('pending_time');

            // proceed to create user
            $result = $this->constants->update_field($constant_contact_phone->id, 'value', $post_contact_phone);
            $result = $this->constants->update_field($constant_contact_email->id, 'value', $post_contact_email);
            $result = $this->constants->update_field($constant_pending_time->id, 'value', $post_pending_time);
            if ($result)
            {
                // success
                $this->system_message->set_success("successfully seted");
            }
            else
            {
                $this->system_message->set_error("failed");
            }
            refresh();
        }

        $this->mPageTitle = 'Default Settings';
        $this->mViewData['form'] = $form;
        $this->mViewData['constant'] = array(
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'pending_time' => $pending_time
        );
        $this->render('util/settings');
    }

}
