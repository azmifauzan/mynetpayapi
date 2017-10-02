<?php
/**
* 
*/
class Keymodel extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function checkKey($key)
	{
		$this->db->where('apikey',MD5($key.'mynetpay2017'));
		$jum = $this->db->get('mynetpaykey')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;
	}

	public function checkSession($ss)
	{
		$this->db->where('access',$ss);
		$q = $this->db->get('session');
		$jum = $q->num_rows();
		if($jum == 1 && $q->row()->destroy_at == '0000-00-00 00:00:00')
			return true;
		else
			return false;
	}
}