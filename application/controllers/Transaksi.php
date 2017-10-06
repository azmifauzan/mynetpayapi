<?php
require APPPATH . '/libraries/REST_Controller.php';

class Transaksi extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('transaksimodel','trm');
		$this->load->model('keymodel','kym');
		$this->load->model('usermodel','usm');
	}

	public function topup_post()
	{
		$hp = $this->post('hp');
		$jm = $this->post('jumlah');
		$ss = $this->post('session');
		if($this->_session_exist($ss))
		{
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
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function konfirmasitopup_post()
	{
		$hp = $this->post('hp');
		$iv = $this->post('noinvoice');
		$bk = $this->post('buktikonfirmasi');
		$bank = $this->post('bank');
		$ss = $this->post('session');

		if($this->_session_exist($ss))
		{
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
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function cekdatapenjual_post()
	{
		$hp = $this->post('hppenjual');
		$ss = $this->post('session');

		if($this->_session_exist($ss))
		{
			if($this->usm->checkUserExist($hp))
			{
				$this->response([
	                'status' => TRUE,
	                'kode' => 22001,
	                'hppenjual' => $hp,
	                'message' => 'Penjual terdaftar',
	            ], REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 22002,
	                'hppenjual' => $hp,
	                'message' => 'Penjual tidak terdaftar'
            	], REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function listprodukmitra_get()
	{
		$ss = $this->post('session');
		if($this->_session_exist($ss))
		{
			$this->response([
                'status' => TRUE,
                'kode' => 25001,
                'message' => 'List Produk Mitra',
                'list' => $this->trm->getListMitra()
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function pembelianmitra_post()
	{
		$hp = $this->post('hp');
		$idp = $this->post('idprodukmitra');
		$ss = $this->post('session');
		$pin = $this->post('pin');
		$produk = $this->trm->getDetailProduk($idp);		
		if($this->_session_exist($ss))
		{
			if($this->trm->chekcPin($hp,$pin))
			{
				if($this->trm->checkEnoughSaldoBayar($hp,$produk->harga))
				{
					if($this->trm->addTransaksiPembayaran($hp,$produk->no_hp_mitra,$produk->harga,"Pembelian ".$produk->nama_produk) && $this->trm->kurangiSaldo($hp,$produk->harga) && $this->trm->tambahSaldo($produk->no_hp_mitra,$produk->harga))
					{
						$this->response([
			                'status' => TRUE,
			                'kode' => 25002,
			                'hppembeli' => $hp,
			                'hppenjual' => $produk->no_hp_mitra,
			                'jumlahpembayaran' => $produk->harga,
			                'saldopembeli' => $this->usm->getSaldo($hp),
			                'message' => 'pembelian produk berhasil',
			            ], REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response([
			                'status' => FALSE,
			                'kode' => 25003,
			                'message' => 'Gagal untuk memproses pembelian'
			            ], REST_Controller::HTTP_BAD_REQUEST);
					}
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 23004,
		                'message' => 'Saldo tidak cukup'
		            ], REST_Controller::HTTP_BAD_REQUEST);		
				}
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 23003,
	                'message' => 'PIN tidak sesuai'
	            ], REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function pembayaran_post()
	{
		$hp = $this->post('hppembeli');
		$hpp = $this->post('hppenjual');
		$ss = $this->post('session');
		$pin = $this->post('pin');
		$jp = $this->post('jumlahpembayaran');
		$ket = $this->post('keterangan');
		if($this->_session_exist($ss))
		{
			if($this->trm->chekcPin($hp,$pin))
			{
				if($this->trm->checkEnoughSaldoBayar($hp,$jp))
				{
					if($this->trm->addTransaksiPembayaran($hp,$hpp,$jp,$ket) && $this->trm->kurangiSaldo($hp,$jp) && $this->trm->tambahSaldo($hpp,$jp))
					{
						$this->response([
			                'status' => TRUE,
			                'kode' => 23001,
			                'hppembeli' => $hp,
			                'hppenjual' => $hpp,
			                'jumlahpembayaran' => $jp,
			                'saldopembeli' => $this->usm->getSaldo($hp),
			                'message' => 'pembayaran berhasil',
			            ], REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response([
			                'status' => FALSE,
			                'kode' => 23002,
			                'message' => 'Gagal untuk memproses pembayaran'
			            ], REST_Controller::HTTP_BAD_REQUEST);
					}
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 23004,
		                'message' => 'Saldo tidak cukup'
		            ], REST_Controller::HTTP_BAD_REQUEST);		
				}
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 23003,
	                'message' => 'PIN tidak sesuai'
	            ], REST_Controller::HTTP_BAD_REQUEST);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	public function infobank_get()
	{
		$ss = $this->get('session');
		if($this->_session_exist($ss))
		{
			$this->response([
	                'status' => TRUE,
	                'kode' => 21011,
	                'message' => 'Info Bank Topup',
	                'rekening' => $this->trm->getRekeningTransfer()
	            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	private function _session_exist($ss)
	{
		if($this->kym->checkSession($ss))
		{
			return true;
		}
		else{
			return false;
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