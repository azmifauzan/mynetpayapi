<?php
/**
* 
*/
class Usermodel extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function addUser($hp,$em,$nm,$ps,$pin)
	{
		$data = array(
			'hp' => $hp,
			'email' => $em,
			'nama' => $nm,
			'password' => $ps,
			'pin' => $pin
		);
		return $this->db->insert('user',$data);
	}
}