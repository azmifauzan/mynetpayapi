<?php
/**
* 
*/
class Otpmodel extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function saveOTP($hp,$otp)
	{
		$data = array(
			'hp' => $hp,
			'kodeotp' => $otp,
			'created_at' => date('Y-m-d H:i:s'),
			'used' => 0
		);

		return $this->db->insert('otp',$data);
	}

	public function disableOTP($hp)
	{
		$this->db->where('hp',$hp);
		$this->db->set('used',1);
		return $this->db->update('otp');
	}

	public function smsOTP($hp,$otp)
	{
		$dbsms = $this->load->database('sms',true);
		$data = array(
			'hp' => $hp,
			'waktu' => date('Y-m-d H:i:s'),
			'sms' => 'Kode OTP MYNETPAY anda adalah : '.$otp.'. Kode ini bersifat RAHASIA, jangan pernah memeberikan kepada siapapun.',
			'sent' => 0,
		);
		return $dbsms->insert('outbox',$data);
	}

	public function isOtpExist($hp,$otp)
	{
		$this->db->where('hp',$hp);
		$this->db->where('kodeotp',$otp);
		$this->db->where('used',0);
		$jum = $this->db->get('otp')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;
	}
}