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
		$this->db->where('apikey',$key);
		$jum = $this->db->get('mynetpaykey')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;
	}
}