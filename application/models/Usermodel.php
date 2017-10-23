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
		return $this->db->insert('session',$data);
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

	public function getMutasi($hp,$ba,$jd)
	{
		$this->db->select('trx.hp_penerima,trx.hp_pengirim,trx.waktu_transaksi,jenis_trx.jenis,trx.debetkredit,trx.jumlah,trx.keterangan');
		$this->db->where('hp_penerima',$hp);
		$this->db->or_where('hp_pengirim',$hp);
		$this->db->limit($jd,$ba);
		$this->db->order_by('waktu_transaksi');
		$this->db->join('jenis_trx','trx.jenis_transaksi = jenis_trx.id');
		$dt = $this->db->get('trx');
		foreach ($dt->result() as $mt) {
			$data[] = array(
				"waktu_transaksi" => $mt->waktu_transaksi,
				"jenis_transaksi" => $mt->jenis,
				"pengirim" => $this->_getDetailUserFromHp($mt->hp_pengirim),
				"penerima" => $this->_getDetailUserFromHp($mt->hp_penerima),
				"jumlah" => $mt->jumlah,
				"debetkredit" => $mt->debetkredit,
				"keterangan" => $mt->keterangan
			);
		}

		return $data;
	}

	public function getHistory($hp,$tgla,$tglb)
	{
		$tglaw = $tgla." 00:00:00";
		$tglak = $tglb." 23:59:59";
		$this->db->select('trx.hp_penerima,trx.hp_pengirim,trx.waktu_transaksi,jenis_trx.jenis,trx.debetkredit,trx.jumlah,trx.keterangan');
		$this->db->where("hp_pengirim = $hp or hp_penerima = $hp");
		$this->db->where("waktu_transaksi >= '$tglaw' and waktu_transaksi <= '$tglak'");		
		$this->db->order_by('waktu_transaksi');
		$this->db->join('jenis_trx','trx.jenis_transaksi = jenis_trx.id');
		$dt = $this->db->get('trx');
		foreach ($dt->result() as $mt) {
			$data[] = array(
				"waktu_transaksi" => $mt->waktu_transaksi,
				"jenis_transaksi" => $mt->jenis,
				"pengirim" => $this->_getDetailUserFromHp($mt->hp_pengirim),
				"penerima" => $this->_getDetailUserFromHp($mt->hp_penerima),
				"jumlah" => $mt->jumlah,
				"debetkredit" => $mt->debetkredit,
				"keterangan" => $mt->keterangan
			);
		}

		return $data;
	}

	private function _getDetailUserFromHp($hp)
	{
		$this->db->select('nama,email,hp');
		$this->db->where('hp',$hp);
		return $this->db->get('user')->row();
	}

	public function getSaldo($hp)
	{
		$this->db->where('hp',$hp);
		$q = $this->db->get('user');
		if($q->num_rows() == 1)
		    return $q->row()->saldo;
		else
		    return null;
	}

	public function checkEmail($hp,$em)
	{
		$this->db->where('hp',$hp);
		$this->db->where('email',$em);
		if($this->db->get('user')->num_rows() == 1)
		    return true;
		else
		    return false;
	}

	public function destroySession($hp,$ss)
	{
		$this->db->set('destroy_at',date('Y-m-d H:i:s'));
		$this->db->where('hp',$hp);
		$this->db->where('access',$ss);
		return $this->db->update('session');
	}

	public function getUserInfo($hp)
	{
		$this->db->select('hp,nama,email,tgl_daftar,saldo,nama_bank,nama_rekening,no_rekening');
		$this->db->where('hp',$hp);
		return $this->db->get('user')->result();
	}

	public function checkUserExist($hp)
	{
		$this->db->where('hp',$hp);
		$jum = $this->db->get('user')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;
	}

	public function cekLogin($hp,$ps)
	{
	    $this->db->where('hp',$hp);
	    $this->db->where('password',MD5($ps));
	    $jum = $this->db->get('user')->num_rows();
	    if($jum == 1)
	        return true;
	    else
	        return false;
	}

	public function updateProfil($hp,$nama,$email,$nmbank,$nmrek,$norek)
	{
		$this->db->where('hp',$hp);
		$this->db->set('nama',$nama);
		$this->db->set('email',$email);
		$this->db->set('nama_bank',$nmbank);
		$this->db->set('nama_rekening',$nmrek);
		$this->db->set('no_rekening',$norek);
		return $this->db->update('user');
	}

	public function chekPinLama($hp,$pinlama)
	{
		$this->db->where('hp',$hp);
		$this->db->where('pin',MD5($pinlama));
		$jum = $this->db->get('user')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;
	}

	public function gantiPin($hp,$pinlama,$pinbaru)
	{
		$this->db->where('hp',$hp);
		$this->db->where('pin',MD5($pinlama));
		$this->db->set('pin',MD5($pinbaru));
		return $this->db->update('user');
	}

	public function chekPasswordLama($hp,$passlama)
	{
		$this->db->where('hp',$hp);
		$this->db->where('password',MD5($passlama));
		$jum = $this->db->get('user')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;	
	}

	public function gantiPassword($hp,$passlama,$passbaru)
	{
		$this->db->where('hp',$hp);
		$this->db->where('password',MD5($passlama));
		$this->db->set('password',MD5($passbaru));
		return $this->db->update('user');	
	}

	public function getNamaFromHp($hp)
	{
		$this->db->where('hp',$hp);
		$q = $this->db->get('user');
		if($q->num_rows() == 1)
			return $q->row()->nama;
		else
			return "null";
	}

	public function simpanSmsGateway($sms,$idsms,$hp,$otp)
	{
		$this->db->where('hp',$hp);
		$this->db->where('kodeotp',$otp);
		$this->db->set('result_smsgateway',$sms);
		$this->db->set('sms_id',$idsms);
		return $this->db->update('otp');
	}

	public function bankDataExist($hp)
	{
		$this->db->where('hp',$hp);
		$q = $this->db->get('user');
		if($q->num_rows() == 1)
		{
			$us = $q->row();
			if($us->nama_bank == "" || $us->nama_rekening == "" || $us->no_rekening == "")
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	public function enoughSaldoWithdraw($hp,$jumlah)
	{
		$this->db->where('hp',$hp);
		$q = $this->db->get('user');
		if($q->num_rows() == 1)
		{
			$us = $q->row();
			if($us->saldo >= $jumlah)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
}