<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('auth_model','AuthModel');
		$this->load->library('session');
		$this->load->library('email');

	}
	public function login_loader(){
		$data['validation_error'] = "NULL";
		$data['Error'] = "NULL";
		//$this->load->view('login/login_register_main',array('data' => $data));
		$this->loadView('login/login_register',array('data' => $data),false);
	}
	public function login(){
		$data['validation_error'] = "NULL";
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$role_s = $this->input->post('role');
		if($role_s == "Admin"){
			$screen = 1;
		}
		elseif ($role_s == "User") {
			$screen = 2;
		}
		//echo $username;
		//echo $password;
		$db_users = $this->AuthModel->login();
		//echo count($db_users);
		if($username && $password){
			foreach ($db_users as $p){
				if($username == $p['email'] && $password == $p['password'] && 2 == $p['role_id'] && $screen == 2)
				{
					$diff = date("Y-m-d")-$p['dob'];
					$newdata = array(
								'user_id'   =>  $p['user_id'],
               					'username'  =>  $p['fname'].' '.$p['lname'],
               					'email'     =>  $p['email'],
               					'phone'     =>  $p['phone'],
               					'cnic'      =>  $p['cnic'],
               					'age'       =>  $diff, 
               					'gender'    =>  $p['gender'],
               					'is_valued' =>  $p['is_valued'],
               					'is_new'    =>  $p['is_new'],
               					'logged_in' =>  TRUE
           );
					$this->session->set_userdata($newdata);
					redirect('index.php/Welcome/index');
				}
				elseif($username == $p['email'] && $password == $p['password'] && 1 == $p['role_id'] && $screen == 1){
					$_SESSION['Role'] = 'Admin';
					redirect('index.php/role/lists');
				}
				elseif($username == $p['email'] && $password == $p['password'])
				{
					$data['Error'] = "Incorrect Role";
					$this->loadView('login/login_register', array('data'=> $data),false);
				}}
				$data['Error'] = "Incorrect Username or password";
				$this->loadView('login/login_register', array('data'=> $data),false);
			}
			else
			{
				$data['Error'] = "Incorrect Username or password";
				$this->loadView('login/login_register', array('Error'=> $data),false);
			}
	}

	public function test(){
		$this->load->view('test/test');
	}

	public function logout(){
		//$this->session->unset_userdata('logged_in');
		$this->session->sess_destroy();
		redirect('index.php/Welcome/index');
	}

	public function register()
	{
		$postedData = $this->input->post();
		$validation_result = $this->validate_form($postedData);
		if(count($validation_result) > 0){
			$data['validation_error'] = $validation_result;
			$data['Error'] = "NULL";
			//$this->load->view('layout/header');
			//$this->load->view('layout/nav');
			$this->loadView('login/login_register', array('data' => $data), false);
			//$this->load->view('layout/footer');
		}
		else{
			$this->AuthModel->AddNewUser($postedData);
			$to = $postedData['email'];
			$subject = "Account successfully created on Smart-Travel";
			$txt = "Greetings,
			Welcome to Smart-Travel! We are excited to have you as part of our membership.
			Membership is a lifelong journey and we look forward to helping you start yours.\n
			As a member of Smart-Travel, you will enjoy many unique benefits. Please see our
			upcoming news so we can explain these benefits and how you can get more.
			\n
			We look forward to seeing you there in near future!\n\n
			Please let us know if you have any questions about your membership.\n
			Best wishes,\n
			Team Smart-Travel\n\n Electronicaly generated mail.PLZ don't reply.";
			$headers = "From: support@smart-travel.com" . "\r\n" .
			"CC: k132387@nu.edu.pk";
			//mail($to,$subject,$txt,$headers);
			$this->email->from($headers, 'Nisar Hassan'); 
         	$this->email->to($postedData['email']);
         	$this->email->subject($subject); 
         	$this->email->message($txt);
         	if($this->email->send()){
         		redirect('index.php/Welcome/index');
         	}
         	else
         		echo "Hum to yaro beech bichary lut gaiy";

		}
	}

	private function validate_form($form_data)
	{
		$validation_error = array();
		$datadb = $this->AuthModel->get_email_cnic();
		if($form_data['password'] !== $form_data['r_password'])
		{
			array_push($validation_error, 'Passwod do not Match.');
		}
		foreach ($datadb as $key) {
			if($form_data['cnic'] == $key['cnic']){
				array_push($validation_error, 'CNIC Already Used.');
			}
		}
		foreach ($datadb as $key) {
			if($form_data['email'] == $key['email']){
				array_push($validation_error, 'Email Already Used.');
			}
		}
		return $validation_error;
	}
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */