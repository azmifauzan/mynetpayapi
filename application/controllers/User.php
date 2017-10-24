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
	
	public function cekuserdaftar_get()
	{
	    $hp = $this->get('hp');
	    if($this->usm->isHpExist($hp))
	    {
	        $this->response([
                'status' => FALSE,
                'kode' => 12011,
                'message' => 'No HP telah terdaftar'
            ], REST_Controller::HTTP_OK);
	    }
	    else
	    {
	        $this->response([
                'status' => TRUE,
                'kode' => 120012,
                'message' => 'No HP belum terdaftar'
            ], REST_Controller::HTTP_OK); 
	    }
	}

	public function kirimotp_get()
	{
		$hp = $this->get('hp');
		//$nama = $this->usm->getNamaFromHp($hp);
		$otp = $this->_generateOTP($hp);
		$sms = $this->_kirimSms($hp,$otp);
		$jsms = json_decode($sms);
		
		if(isset($jsms->messages[0]->messageId))
		{
			$idsms = $jsms->messages[0]->messageId;
			$this->usm->simpanSmsGateway($sms,$idsms,$hp,$otp);	
	
			$this->response([
	            'status' => TRUE,
	            'kode' => 11003,
	            'message' => 'Kirim OTP berhasil',
	            'otp' => $otp,
	            'smsgateway' => $jsms
	        ], REST_Controller::HTTP_OK);	
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 11009,
                'message' => 'Gagal mengirim sms'
            ], REST_Controller::HTTP_OK);	
		}
	}

	public function validateotp_get()
	{
		$hp = $this->get('hp');
		$otp = $this->get('otp');
		
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

	public function daftar_post()
	{
		$hp = $this->post('hp');
		//$key = $this->post('key');
		$otp = $this->post('otp');
		$em = $this->post('email');
		$nm = $this->post('nama');
		$ps = $this->post('password');
		$pin = $this->post('pin');
		
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
			else
			{
				$valid = true;
			}

			if($valid)
			{
				if($this->usm->addUser($hp,$em,$nm,$ps,$pin))
				{
					$ip = $this->_get_ip_address();
					$session = $this->_generateSession($hp,$ip);
					$this->usm->addSession($hp,$session,$ip);
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

	public function login_post()
	{
		//$key = $this->post('key');
		$hp = $this->post('hp');
		$ps = $this->post('password');
		$ip = $this->_get_ip_address();
		
		if($this->usm->cekLogin($hp,$ps))
		{
			$session = $this->_generateSession($hp,$ip);
			if($this->usm->addSession($hp,$session,$ip))
			{
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
	                'kode' => 13003,
	                'message' => 'Login tidak berhasil, gagal generate session!'
	            ], REST_Controller::HTTP_OK);		
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 13002,
                'message' => 'Username / Password tidak dikenali!'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function cetakmutasi_get()
	{
		//$key = $this->get('key');
		$ss = $this->get('session');
		$hp = $this->get('hp');
		$ba = $this->get('batasatas');
		$jd = $this->get('jumlahdata');
		
		if($this->_session_exist($ss))
		{
			$mutasi = $this->usm->getMutasi($hp,$ba,$jd);
			$this->response([
                'status' => TRUE,
                'kode' => 14002,
                'message' => 'Cetak Mutasi',
                'hp' => $hp,
                'mutasi' => $mutasi
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function historytransaksi_get()
	{
		$ss = $this->get('session');
		$hp = $this->get('hp');
		$tgla = $this->get('tanggalawal');
		$tglb = $this->get('tanggalakhir');
		if($this->_session_exist($ss))
		{
			$histori = $this->usm->getHistory($hp,$tgla,$tglb);
			$this->response([
                'status' => TRUE,
                'kode' => 14003,
                'message' => 'History Transaksi',
                'hp' => $hp,
                'mutasi' => $histori
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function ceksaldo_get()
	{
		//$key = $this->get('key');
		$hp = $this->get('hp');
		$ss = $this->get('session');
		
		if($this->_session_exist($ss))
		{
			$saldo = $this->usm->getSaldo($hp);
			if($saldo != null)
			{
    			$this->response([
                    'status' => TRUE,
                    'kode' => 14001,
                    'message' => 'Cek Saldo',
                    'hp' => $hp,                
                    'saldo' => $saldo
                ], REST_Controller::HTTP_OK);
			}
			else
			{
			    $this->response([
	                'status' => FALSE,
	                'kode' => 14009,
	                'message' => 'Data tidak ditemukan'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function resetpassword_post()
	{
		$hp = $this->post('hp');
		$em = $this->post('email');
		if($this->usm->checkEmail($hp,$em))
		{
			if($this->_kirimEmailResetPassword())
			{
				$this->response([
		            'status' => TRUE,
		            'kode' => 15001,
		            'message' => 'Link reset password telah dikirimkan ke email',	            
		        ], REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 15002,
	                'message' => 'Gagal mengirimkan email reset password'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 15003,
                'message' => 'hp / email tidak terdaftar!'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function logout_get()
	{
		$hp = $this->get('hp');
		$ss = $this->get('session');
		if($this->usm->destroySession($hp,$ss))
		{
			$this->response([
	            'status' => TRUE,
	            'kode' => 13003,
	            'message' => 'Logout berhasil',	            
	        ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 13004,
                'message' => 'Gagal logout'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function info_get()
	{
		$hp = $this->get('hp');
		$ss = $this->get('session');
		if($this->_session_exist($ss))
		{
			$this->response([
	            'status' => TRUE,
	            'kode' => 16001,
	            'message' => 'User Info',
	            'hp' => $hp,
	            'info' => $this->usm->getUserInfo($hp)	            
	        ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function editprofil_post()
	{
		$hp = $this->post('hp');
		$ss = $this->post('session');
		$nama = $this->post('nama');
		$email = $this->post('email');
		$nmbank = $this->post('namabank');
		$nmrek = $this->post('namarekening');
		$norek = $this->post('norekening');

		if($this->_session_exist($ss))
		{
			if($this->usm->updateProfil($hp,$nama,$email,$nmbank,$nmrek,$norek))
			{
				$this->response([
		            'status' => TRUE,
		            'kode' => 16002,
		            'message' => 'Berhasil update profil',
		            'hp' => $hp	            
		        ], REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 16003,
	                'message' => 'Gagal update profil'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function gantipin_post()
	{
		$hp = $this->post('hp');
		$ss = $this->post('session');
		$pinlama = $this->post('pinlama');
		$pinbaru = $this->post('pinbaru');

		if($this->_session_exist($ss))
		{
			if($this->usm->chekPinLama($hp,$pinlama))
			{
				if($this->usm->gantiPin($hp,$pinlama,$pinbaru))
				{
					$this->response([
			            'status' => TRUE,
			            'kode' => 17001,
			            'message' => 'Berhasil ganti PIN',
			        ], REST_Controller::HTTP_OK);		
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 17002,
		                'message' => 'Gagal mengganti PIN'
		            ], REST_Controller::HTTP_OK);		
				}
			}	
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 17003,
	                'message' => 'PIN Lama tidak sesuai'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function gantipassword_post()
	{
		$hp = $this->post('hp');
		$ss = $this->post('session');
		$passlama = $this->post('passwordlama');
		$passbaru = $this->post('passwordbaru');

		if($this->_session_exist($ss))
		{
			if($this->usm->chekPasswordLama($hp,$passlama))
			{
				if($this->usm->gantiPassword($hp,$passlama,$passbaru))
				{
					$this->response([
			            'status' => TRUE,
			            'kode' => 18001,
			            'message' => 'Berhasil ganti Password',
			        ], REST_Controller::HTTP_OK);		
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 18002,
		                'message' => 'Gagal mengganti Password'
		            ], REST_Controller::HTTP_OK);		
				}
			}	
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 18003,
	                'message' => 'Password Lama tidak sesuai'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	private function _generateOTP($hp)
	{
		$otp = rand(100000,999999);
		$this->otm->disableOTP($hp);
		$this->otm->saveOTP($hp,$otp);
		//$this->otm->smsOTP($hp,$otp);
		return $otp;
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

	private function _session_exist($ss)
	{
		if($this->kym->checkSession($ss))
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

	private function _kirimEmailResetPassword()
	{
		return true;
	}
	
	private function _kirimSMS($to, $otp)
	{
		$text = "Halo, Terimakasih sudah melakukan registrasi MYNETPAY. Untuk melanjutkan, silahkan masukkan kode OTP berikut : $otp";
		$pecah              = explode(",",$to);
	    $jumlah             = count($pecah);
	    $from               = "SMSVIRO"; //Sender ID or SMS Masking Name, if leave blank, it will use default from telco
	    $username           = "andikabayu"; //your smsviro username
	    $password           = "suksesbersama85"; //your smsviro password
	    $postUrl            = "http://107.20.199.106/restapi/sms/1/text/advanced"; # DO NOT CHANGE THIS
	
	    //for($i=0; $i<$jumlah; $i++){
	        if(substr($to,0,2) == "62" || substr($to,0,3) == "+62"){
	            $pecah = $pecah;
	        }elseif(substr($to,0,1) == "0"){
	            $to[0] = "X";
	            $pecah = str_replace("X", "62", $to);
	        }else{
	            return array("messages" => "Invalid mobile number format");
	        }
	        $destination = array("to" => $pecah);
	        $data = array(
				"from" => $from,
				"destinations" => $destination,
				"text" => $text,
				"notify" => true,
				"notifyUrl" => "https://www.mynetpay.co.id/notify.php",
				"notifyContentType" => "application/json",
			);
			
			$postDataJson = json_encode(array("messages" => $data));
	        //$postData           = array("messages" => array($message));
	        //$postDataJson       = json_encode($postData);
	        $ch                 = curl_init();
	        $header             = array("Content-Type:application/json", "Accept:application/json");
			
	        curl_setopt($ch, CURLOPT_URL, $postUrl);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        $response = curl_exec($ch);
	        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	        $responseBody = json_decode($response);
	        curl_close($ch);
			return $response;
	    //}
	}

	private function _kirimSms_old($to,$otp)
	{
		$text = "Halo, Terimakasih sudah melakukan registrasi MYNETPAY. Untuk melanjutkan, silahkan masukkan kode OTP berikut : $otp";
		//$pecah              = explode(",",$to);
	    $from               = "SMSVIRO"; //Sender ID or SMS Masking Name, if leave blank, it will use default from telco
	    $username           = "andikabayu"; //your smsviro username
	    $password           = "suksesbersama85"; //your smsviro password
	    $postUrl            = "http://107.20.199.106/restapi/sms/1/text/advanced"; # DO NOT CHANGE THIS
		
	    if(substr($to,0,2) == "62" || substr($to,0,3) == "+62"){
            $to = $to;
        }elseif(substr($to,0,1) == "0"){
            $to[0] = "X";
            $to = str_replace("X", "62", $to);
        }else{
            return array("messages"=>"Invalid mobile number format");
        }

		$data = array(
			"from" => $from,
			"destinations" => array("to"=>$to),
			"text" => $text,
			"notify" => true,
			"notifyUrl" => "https://www.mynetpay.co.id/notify.php",
			"notifyContentType" => "application/json",
		);
		$postDataJson = json_encode(array("messages" => $data));
	    
        $ch                 = curl_init();
        $header             = array("json_decode(json)", "Accept:application/json");
		
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //$responseBody = json_decode($response);
        curl_close($ch);

		return $response;
	}
}