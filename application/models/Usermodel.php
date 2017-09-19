<?php

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
			'pin' => $pin,
			'tgl_daftar' => date('Y-m-d H:i:s'),			
		);
		return $this->db->insert('user',$data);
	}

	public function addSession($hp,$se,$ip)
	{
		$data = array(
			'hp' => $hp,
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
			return false;
	}

	// public function isUsernameExist($us)
	// {
	// 	$this->db->where('username',$us);
	// 	$jum = $this->db->get('user')->num_rows();
	// 	if($jum == 1)
	// 		return true;
	// 	else
	// 		return false
	// }

	public function getMutasi($hp,$ba,$jd)
	{
		$this->db->select('trx.waktu_transaksi,jenis_trx.jenis,trx.debetkredit,trx.jumlah,trx.keterangan');
		$this->db->where('hp',$hp);
		$this->db->limit($jd,$ba);
		$this->db->order_by('waktu_transaksi');
		$this->db->join('jenis_trx','trx.jenis_transaksi = jenis_trx.id');
		return $this->db->get('trx')->result();
	}
}