<?php
require APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller
{
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('keymodel','kym');
		$this->load->model('otpmodel','otm');
		$this->load->model('usermodel','usm');
	}

	public function kirimotp_get()
	{
		$hp = $this->get('hp');
		$key = $this->get('key');
		if($this->_key_exist($key))
		{
			$this->_generateOTP($hp);
			$this->response([
                'status' => TRUE,
                'message' => 'Kirim OTP berhasil'
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function validateotp_get()
	{
		$hp = $this->get('hp');
		$otp = $this->get('otp');
		$key = $this->get('key');
		if($this->_key_exist($key))
		{
			if($this->otm->isOtpExist($hp,$otp)){
				$this->response([
	                'status' => TRUE,
	                'kode' => 11001,
	                'message' => 'OTP Valid'
	            ], REST_Controller::HTTP_OK);
			}
			else{
				$this->response([
	                'status' => TRUE,
	                'kode' => 11002,
	                'message' => 'OTP not Valid'
	            ], REST_Controller::HTTP_OK);	
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10002,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function daftar_post()
	{
		$hp = $this->post('hp');
		$key = $this->post('key');
		$otp = $this->post('otp');
		$em = $this->post('email');
		$nm = $this->post('nama');
		$ps = $this->post('password');
		$pin = $this->post('pin');
		if($this->_key_exist($key))
		{
			if($this->otm->isOtpExist($hp,$otp))
			{
				if($this->usm->addUser($hp,$em,$nm,$ps,$pin))
				{
					$this->response([
		                'status' => TRUE,
		                'kode' => 12001,
		                'message' => 'Berhasil menambahkan user'
		            ], REST_Controller::HTTP_OK);
				}
				else
				{
					$this->response([
		                'status' => TRUE,
		                'kode' => 12002,
		                'message' => 'Gagal menambahkan user ke database'
		            ], REST_Controller::HTTP_OK);		
				}
			}
			else
			{
				$this->response([
	                'status' => TRUE,
	                'kode' => 11002,
	                'message' => 'OTP not Valid'
	            ], REST_Controller::HTTP_OK);	
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10002,
                'message' => 'Invalid API key'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	private function _generateOTP($hp)
	{
		$otp = rand(100000,999999);
		$this->otm->disableOTP($hp);
		$this->otm->saveOTP($hp,$otp);
		$this->otm->smsOTP($hp,$otp);
	}

	private function _key_exist($key)
	{
		if($this->kym->checkKey($key))
		{
			return true;
		}
		else{
			return false;
		}
	}
}