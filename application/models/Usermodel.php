<?php

class Usermodel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function addUser($hp,$em,$nm,$ps,$pin,$us)
	{
		$data = array(
			'hp' => $hp,
			'email' => $em,
			'nama' => $nm,
			'password' => $ps,
			'pin' => $pin,
			'username' => $us,
			'tgl_daftar' => date('Y-m-d H:i:s'),			
		);
		return $this->db->insert('user',$data);
	}

	public function addSession($us,$se,$ip)
	{
		$data = array(
			'username' => $us,
			'session' => $se,
			'created_at' => date('Y-m-d H:i:s'),
			'last_access' => date('Y-m-d H:i:s'),
			'ipaddress' => $ip,
		);
		return $this->db->insert('sessions',$data);
	}

	public function isHpExist($hp)
	{
		$this->db->where('hp',$hp);
		$jum = $this->db->get('user')->num_rows();
		if($jum == 1)
			return true;
		else
			return false
	}

	public function isUsernameExist($us)
	{
		$this->db->where('username',$us);
		$jum = $this->db->get('user')->num_rows();
		if($jum == 1)
			return true;
		else
			return false
	}
}