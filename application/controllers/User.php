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
		$us = $this->post('username');
		if($this->_key_exist($key))
		{
			if($this->otm->isOtpExist($hp,$otp))
			{
				if($this->usm->addUser($hp,$em,$nm,$ps,$pin,$us))
				{
					$ip = $this->_get_ip_address;
					$session = $this->_generateSession($us,$ip);
					$this->response([
		                'status' => TRUE,
		                'kode' => 12001,
		                'message' => 'Berhasil menambahkan user',
		                'session' => $session,
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

	private function _generateSession($us,$ip)
	{
		return sha1($us.$ip.date('Y-m-d H:i:s'));
	}

	private function _get_ip_address() {
	    // check for shared internet/ISP IP
	    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
	        return $_SERVER['HTTP_CLIENT_IP'];
	    }

	    // check for IPs passing through proxies
	    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        // check if multiple ips exist in var
	        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
	            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            foreach ($iplist as $ip) {
	                if ($this->_validate_ip($ip))
	                    return $ip;
	            }
	        } else {
	            if ($this->_validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
	                return $_SERVER['HTTP_X_FORWARDED_FOR'];
	        }
	    }
	    if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->_validate_ip($_SERVER['HTTP_X_FORWARDED']))
	        return $_SERVER['HTTP_X_FORWARDED'];
	    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->_validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
	        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->_validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
	        return $_SERVER['HTTP_FORWARDED_FOR'];
	    if (!empty($_SERVER['HTTP_FORWARDED']) && $this->_validate_ip($_SERVER['HTTP_FORWARDED']))
	        return $_SERVER['HTTP_FORWARDED'];

	    // return unreliable ip since all else failed
	    return $_SERVER['REMOTE_ADDR'];
	}

	private function _validate_ip($ip) {
	    if (strtolower($ip) === 'unknown')
	        return false;

	    // generate ipv4 network address
	    $ip = ip2long($ip);

	    // if the ip is set and not equivalent to 255.255.255.255
	    if ($ip !== false && $ip !== -1) {
	        // make sure to get unsigned long representation of ip
	        // due to discrepancies between 32 and 64 bit OSes and
	        // signed numbers (ints default to signed in PHP)
	        $ip = sprintf('%u', $ip);
	        // do private network range checking
	        if ($ip >= 0 && $ip <= 50331647) return false;
	        if ($ip >= 167772160 && $ip <= 184549375) return false;
	        if ($ip >= 2130706432 && $ip <= 2147483647) return false;
	        if ($ip >= 2851995648 && $ip <= 2852061183) return false;
	        if ($ip >= 2886729728 && $ip <= 2887778303) return false;
	        if ($ip >= 3221225984 && $ip <= 3221226239) return false;
	        if ($ip >= 3232235520 && $ip <= 3232301055) return false;
	        if ($ip >= 4294967040) return false;
	    }
	    return true;
	}
}