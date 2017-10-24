<?php 

class Transaksimodel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function getLastInvoiceTopupId()
	{
		$this->db->select('id');
		$this->db->order_by('id','desc');
		$this->db->limit(1);
		$q = $this->db->get('topup');
		if($q->num_rows() == 0)
			return 0;
		else 
			return $q->row()->id;
	}

	public function isUniqueTransferExist($jm)
	{
		//$this->db->where('hp',$hp);
		$this->db->select('id');
		$this->db->where('jumlah',$jm);
		$this->db->where('konfirmasi',0);
		$row = $this->db->get('topup')->num_rows();
		if($row == 1)
			return true;
		else
			return false;
	}

	public function getRekeningTransfer()
	{
		$this->db->select('bank,no_rekening,nama_rekening');
		return $this->db->get('rekening_topup')->result();
	}

	public function addTopup($noiv,$hp,$jum,$jmtr,$nmbank,$nmpmlk)
	{
		$data = array(
			'hp' => $hp,
			'invoice' => $noiv,
			'jumlah' => $jum,
			'jumlah_transfer' => $jmtr,
			'nama_bank' => $nmbank,
			'pemilik_rekening' =>$nmpmlk,
			'waktu' => date('Y-m-d H:i:s')
		);

		return $this->db->insert('topup',$data);
	}

	public function addBuktiKonfirmasi($hp,$iv,$bk,$bank)
	{
		$data = array(
			'hp' => $hp,
			'invoice' => $iv,
			'bukti' => $bk,
			'bank' => $bank,
			'waktu' => date('Y-m-d H:i:s'),
		);

		return $this->db->insert('konfirmasi_topup',$data);
	}

	public function updateKonfirmasiTopup($hp,$iv)
	{
		$this->db->where('hp',$hp);
		$this->db->where('invoice',$iv);
		$this->db->set('konfirmasi',1);
		return $this->db->update('topup');
	}

	public function checkEnoughSaldoBayar($hp,$jp)
	{
		$this->db->select('saldo');
		$this->db->where('hp',$hp);
		$q = $this->db->get('user');
		$saldo = ($q->num_rows() == 1) ? $q->row()->saldo : 0;
		if($saldo >= $jp)
			return true;
		else
			return false;
	}

	public function addTransaksiPembayaran($hp,$hpp,$jp,$ket)
	{
		$data = array(
			"hp_pengirim" => $hp,
			"hp_penerima" => $hpp,
			"waktu_transaksi" => date('Y-m-d H:i:s'),
			"debetkredit" => "kredit",
			"jumlah" => $jp,
			"jenis_transaksi" => 2,
			"keterangan" => $ket
		);

		return $this->db->insert('trx',$data);
	}

	public function kurangiSaldo($hp,$jp)
	{
		$this->db->where('hp',$hp);
		$this->db->set('saldo','saldo-'.$jp,false);
		return $this->db->update('user');
	}

	public function tambahSaldo($hp,$jp)
	{
		$this->db->where('hp',$hp);
		$this->db->set('saldo','saldo+'.$jp);
		return $this->db->update('user');	
	}

	public function checkPin($hp,$pin)
	{
		$this->db->select('id');
		$this->db->where('pin',MD5($pin));
		$this->db->where('hp',$hp);
		$jum = $this->db->get('user')->num_rows();
		if($jum == 1)
			return true;
		else
			return false;
	}

	public function getListMitra()
	{
		$this->db->select('id,nama_produk,kategori_produk,deskripsi_produk,gambar_produk,harga');
		$this->db->order_by('kategori_produk');
		$this->db->order_by('id','asc');
		return $this->db->get('produk_mitra')->result();
	}

	public function getListMitraKategori($kat)
	{
		$this->db->where('kategori_produk',$kat);
		$this->db->select('id,nama_produk,kategori_produk,deskripsi_produk,gambar_produk,harga');
		$this->db->order_by('id','asc');
		return $this->db->get('produk_mitra')->result();
	}

	public function getDetailProduk($idp)
	{
		$this->db->where('id',$idp);
		return $this->db->get('produk_mitra')->row();
	}

	public function getUnconfirmTopup($hp)
	{
		$this->db->select('invoice,waktu,jumlah,jumlah_transfer,nama_bank,pemilik_rekening');
		$this->db->where('hp',$hp);
		$this->db->where('konfirmasi',0);
		$this->db->order_by('invoice','asc');
		$q = $this->db->get('topup');
		if($q->num_rows() > 0)
		return $q->result();
		else
		return null;
	}

	public function addWithdraw($hp,$jumlah)
	{
		$data = array(
			'waktu' => date('Y-m-d H:i:s'),
			'hp' => $hp,
			'jumlah' => $jumlah
		);

		return $this->db->insert('withdraw',$data);
	}

	public function transaksiProdukMitra($hp,$produk,$param)
	{
		$this->db->trans_start();
		$this->db->query("INSERT INTO trx(hp_pengirim,hp_penerima,waktu_transaksi,debetkredit,jumlah,jenis_transaksi,keterangan) VALUES('".$hp."','".$produk->no_hp_mitra."','".date('Y-m-d H:i:s')."','kredit','".$produk->harga."','2','".$param."')");
		$this->db->query("UPDATE user SET saldo = saldo-".$produk->harga." WHERE hp='".$hp."'");
		$this->db->query("UPDATE user SET saldo = saldo+".$produk->harga." WHERE hp='".$produk->no_hp_mitra."'");
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function transaksiKonfirmasiTopup($hp,$iv,$bk,$bank)
	{
		$this->db->trans_start();
		$this->db->query("INSERT INTO konfirmasi_topup(hp,invoice,bukti,bank,waktu) VALUES('".$hp."','".$iv."','".$bk."','".$bank."','".date('Y-m-d H:i:s')."')");
		$this->db->query("UPDATE topup SET konfirmasi = 1 WHERE hp = '".$hp."' AND invoice = '".$iv."'");
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function transaksiPembayaran($hp,$hpp,$jp,$ket)
	{
		$this->db->trans_start();
		$this->db->query("INSERT INTO trx(hp_pengirim,hp_penerima,waktu_transaksi,debetkredit,jumlah,jenis_transaksi,keterangan) VALUES('".$hp."','".$hpp."','".date('Y-m-d H:i:s')."','kredit','".$jp."','2','".$ket."')");
		$this->db->query("UPDATE user SET saldo = saldo-".$jp." WHERE hp='".$hp."'");
		$this->db->query("UPDATE user SET saldo = saldo+".$jp." WHERE hp='".$hpp."'");
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function transaksiWithdraw($hp,$jumlah)
	{
		$this->db->trans_start();
		$this->db->query("INSERT INTO withdraw(waktu,hp,jumlah) VALUES('".date('Y-m-d H:i:s')."','".$hp."','".$jumlah."')");
		$this->db->query("UPDATE user SET saldo = saldo-".$jumlah." WHERE hp='".$hp."'");
		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}