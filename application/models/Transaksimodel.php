<?php 

class Transaksimodel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	public function getLastInvoiceTopupId()
	{
		$this->db->order_by('id',desc);
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

	public function addTopup($noiv,$hp,$jmtr)
	{
		$data = array(
			'hp' => $hp,
			'invoice' => $noiv,
			'jumlah' => $jmtr,
			'waktu' => date('Y-m-d H:i:s')
		);
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
}