<?php
require APPPATH . '/libraries/REST_Controller.php';

class Transaksi extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('transaksimodel','trm');
	}

	public function topup_post()
	{
		$hp = $this->post('hp');
		$jm = $this->post('jumlah');
		$noiv = $this->_createNoInvoice();
		$jmtr = 0;		
		do{
			$jmtr = $this->_createUniqueTransfer($jm);
		}while($this->trm->isUniqueTransferExist($jmtr));

		if($this->trm->addTopup($noiv,$hp,$jmtr))
		{
			$this->response([
                'status' => TRUE,
                'kode' => 21001,
                'message' => 'Berhasil menyimpan topup',
                'no_invoice' => $noiv,
                'jumlah_transfer' => $jmtr,
                'rekening_transfer' => $this->trm->getRekeningTransfer()
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 21002,
                'message' => 'Gagal menyimpan topup'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function konfirmasitopup_post()
	{
		$hp = $this->post('hp');
		$iv = $this->post('noinvoice');
		$bk = $this->post('buktikonfirmasi');
		$bank = $this->post('bank');

		if($this->trm->addBuktiKonfirmasi($hp,$iv,$bk,$bank))
		{
			$this->trm->updateKonfirmasiTopup($hp,$iv);
			$this->response([
                'status' => TRUE,
                'kode' => 21003,
                'message' => 'Berhasil menyimpan konfirmasi topup',
                'no_invoice' => $iv
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 21004,
                'message' => 'Gagal menyimpan konfirmasi topup'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	private function _createNoInvoice()
	{
		$lid = $this->trm->getLastInvoiceTopupId() + 1;
		$no = str_pad($lid, 6, "0");
		return "MNPT".$no;
	}

	private function _createUniqueTransfer($jm)
	{
		$uq = rand(100,999);
		return substr($jm, 0, -3) . $uq;
	}
}