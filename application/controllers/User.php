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
		$this->load->model('logmodel','lgm');
	}

	public function kirimotp_get()
	{
		$hp = $this->get('hp');
		$key = $this->get('key');
		$ip = $this->_get_ip_address();
		$var = 'hp:'.$hp;
		$this->lgm->logAccess('user/kirimotp',$ip,$key,$var);
		if($this->_key_exist($key))
		{
			$this->_generateOTP($hp);
			$this->response([
                'status' => TRUE,
                'kode' => 11003,
                'message' => 'Kirim OTP berhasil'
            ], REST_Controller::HTTP_OK);
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

	public function validateotp_get()
	{
		$hp = $this->get('hp');
		$otp = $this->get('otp');
		$key = $this->get('key');
		$ip = $this->_get_ip_address();
		$var = "hp:$hp;otp:$otp";
		$this->lgm->logAccess('user/validateotp',$ip,$key,$var);
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
	                'status' => FALSE,
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
		$ip = $this->_get_ip_address();
		$var = "hp:$hp;otp:$otp;email:$em;nama:$nm;password:$ps;pin:$pin";
		$this->lgm->logAccess('user/daftar',$ip,$key,$var);
		if($this->_key_exist($key))
		{
			if($this->otm->isOtpExist($hp,$otp))
			{
				$valid = false;
				//cek hp sudah terdaftar
				if($this->usm->isHpExist($hp))
				{
					$kode = 12003;
					$msg = 'No Handphone sudah terdaftar!';
				}
				//cek email valid
				else if(!$this->_valid_email($em))
				{
					$kode = 12004;
					$msg = 'Format email tidak valid!';
				}
				//cek pin 6 angka
				else if(!$this->_valid_pin($pin))
				{
					$kode = 12005;
					$msg = 'Format pin harus berupa 6 angka!';
				}
				else
				{
					$valid = true;
				}

				if($valid)
				{
					if($this->usm->addUser($hp,$em,$nm,$ps,$pin))
					{
						$ip = $this->_get_ip_address();
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
			                'status' => FALSE,
			                'kode' => 12002,
			                'message' => 'Gagal menambahkan user ke database'
			            ], REST_Controller::HTTP_OK);		
					}
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => $kode,
		                'message' => $msg
		            ], REST_Controller::HTTP_OK);
				}
			}
			else
			{
				$this->response([
	                'status' => FALSE,
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

	public function login_post()
	{
		$key = $this->post('key');
		$hp = $this->post('hp');
		$ps = $this->post('password');
		$ip = $this->_get_ip_address();
		$var = "hp:$hp;password:$ps";
		$this->lgm->logAccess('user/login',$ip,$key,$var);				

		if($this->_key_exist($key))
		{
			if($this->usm->cekLogin($hp,$ps))
			{
				$session = $this->_generateSession($hp,$ip);
				$this->response([
	                'status' => TRUE,
	                'kode' => 13001,
	                'message' => 'Login berhasil',
	                'session' => $session
	            ], REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 13002,
	                'message' => 'Username / Password tidak dikenali!'
	            ], REST_Controller::HTTP_BAD_REQUEST);
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

	public function cetakmutasi_get()
	{
		$key = $this->get('key');
		$hp = $this->get('hp');
		$ba = $this->get('batas_atas');
		$jd = $this->get('jumlah_data');
		if($this->_key_exist($key))
		{
			$mutasi = $this->usm->getMutasi($hp,$ba,$jd);
			$this->response([
                'status' => TRUE,
                'kode' => 14001,
                'message' => 'Cetak Mutasi',
                'hp' => $hp,
                'batas_atas' => $ba,
                'jumlah_data' => $jd,
                'mutasi' => $mutasi
            ], REST_Controller::HTTP_OK);
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

	private function _generateSession($hp,$ip)
	{
		return sha1($hp.$ip.date('Y-m-d H:i:s'));
	}

	private function _get_ip_address() {
	    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
	        return $_SERVER['HTTP_CLIENT_IP'];
	    }

	    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
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

	    return $_SERVER['REMOTE_ADDR'];
	}

	private function _validate_ip($ip) {
	    if (strtolower($ip) === 'unknown')
	        return false;

	    $ip = ip2long($ip);
	    if ($ip !== false && $ip !== -1) {
	        $ip = sprintf('%u', $ip);
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

	private function _valid_email($em)
	{
		if (filter_var($em, FILTER_VALIDATE_EMAIL))
			return true;
		else
			return false;
	}

	private function _valid_pin($pin)
	{
		$options = array(
		    'options' => array(
		        'min_range' => 6,
		        'max_range' => 6,
		    )
		);
		if (filter_var($pin, FILTER_VALIDATE_INT, $options))
			return true;
		else
			return false;
	}
}