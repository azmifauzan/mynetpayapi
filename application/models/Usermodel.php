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
			'password' => md5($ps),
			'pin' => md5($pin),
			'tgl_daftar' => date('Y-m-d H:i:s'),			
		);
		return $this->db->insert('user',$data);
	}

	public function addSession($hp,$se,$ip)
	{
		$data = array(
			'hp' => $hp,
			'access' => $se,
			'generate_at' => date('Y-m-d H:i:s'),
			'destroy_at' => date('0000-00-00 00:00:00'),
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

	public function getSaldo($hp)
	{
		$this->db->where('hp',$hp);
		$q = $this->db->get('user');
		if($q->num_rows == 1)
		    return $q->row()->saldo;
		else
		    return null;
	}

	public function checkEmail($hp,$em)
	{
		$this->db->where('hp',$hp);
		$this->db->where('email',$em);
		if($this->db->get('user')->num_rows == 1)
		    return true;
		else
		    return false;
	}

	public function destroySession($hp,$ss)
	{
		$this->db->set('destroy_at',date('Y-m-d H:i:s'));
		$this->db->where('hp',$hp);
		$this->db->where('session',$ss);
		return $this->db->update('session');
	}

	public function getUserInfo($hp)
	{
		$this->db->select('hp,email,tgl_daftar,saldo');
		$this->db->where('hp',$hp);
		return $this->db->get('user')->result();
	}
}