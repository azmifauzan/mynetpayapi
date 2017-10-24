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

	public function topupsaldo_post()
	{
		$hp = $this->post('hp');
		$jm = $this->post('jumlah');
		$nmbank = $this->post('namabankpengirim');
		$nmpmlk = $this->post('namapemilikrekening');
		$ss = $this->post('session');
		if($this->_session_exist($ss))
		{
			$noiv = $this->_createNoInvoice();
			$jmtr = 0;		
			do{
				$jmtr = $this->_createUniqueTransfer($jm);
			}while($this->trm->isUniqueTransferExist($jmtr));

			if($this->trm->addTopup($noiv,$hp,$jm,$jmtr,$nmbank,$nmpmlk))
			{
				$this->response([
	                'status' => TRUE,
	                'kode' => 21001,
	                'message' => 'Berhasil menyimpan topup',
	                'no_invoice' => $noiv,
	                'jumlah' => $jm,
	                'kode_unik_transfer' => $jmtr
	            ], REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 21002,
	                'message' => 'Gagal menyimpan topup'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
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
			// if($this->trm->addBuktiKonfirmasi($hp,$iv,$bk,$bank))
			// {
			// 	$this->trm->updateKonfirmasiTopup($hp,$iv);
			// 	$this->response([
	  		//               'status' => TRUE,
	  		//               'kode' => 21003,
	  		//               'message' => 'Berhasil menyimpan konfirmasi topup',
	  		//               'no_invoice' => $iv
	  		//           ], REST_Controller::HTTP_OK);
			// }
			// else
			// {
			// 	$this->response([
	  		//               'status' => FALSE,
	  		//               'kode' => 21004,
	  		//               'message' => 'Gagal menyimpan konfirmasi topup'
	  		//           ], REST_Controller::HTTP_OK);
			// }

			if($this->trm->transaksiKonfirmasiTopup($hp,$iv,$bk,$bank) === FALSE)
			{
				$this->response([
	              	'status' => FALSE,
	              	'kode' => 21004,
	              	'message' => 'Gagal menyimpan konfirmasi topup'
	          	], REST_Controller::HTTP_OK);
			}
			else
			{
				$this->response([
	              	'status' => TRUE,
	              	'kode' => 21003,
	              	'message' => 'Berhasil menyimpan konfirmasi topup',
	              	'no_invoice' => $iv
	          	], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function unconfirmtopup_get()
	{
		$hp = $this->get('hp');
		$ss = $this->get('session');
		if($this->_session_exist($ss))
		{
			$this->response([
                'status' => TRUE,
                'kode' => 21011,
                'message' => 'Topup yang belum dikonfirmasi',
                'hp' => $hp,
                'list' => $this->trm->getUnconfirmTopup($hp)
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);	
		}
	}

	public function cekpenjual_post()
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
	                'namapenjual' => $this->usm->getNamaFromHp($hp),
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
            	], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function listprodukmitra_get()
	{
		$ss = $this->get('session');
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
            ], REST_Controller::HTTP_OK);
		}
	}

	public function listprodukmitrakategori_get()
	{
		$ss = $this->get('session');
		$kat = $this->get('kategori');
		if($this->_session_exist($ss))
		{
			$this->response([
                'status' => TRUE,
                'kode' => 25001,
                'kategori' => $kat,
                'message' => 'List Produk Mitra',
                'list' => $this->trm->getListMitraKategori($kat)
            ], REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
		}
	}

	public function pembelianmitra_post()
	{
		$hp = $this->post('hp');
		$idp = $this->post('idprodukmitra');
		$ss = $this->post('session');
		$pin = $this->post('pin');
		$param = $this->post('param');
		$produk = $this->trm->getDetailProduk($idp);		
		if($this->_session_exist($ss))
		{
			if($this->trm->checkPin($hp,$pin))
			{
				if($this->trm->checkEnoughSaldoBayar($hp,$produk->harga))
				{
					// if($this->trm->addTransaksiPembayaran($hp,$produk->no_hp_mitra,$produk->harga,$param) && $this->trm->kurangiSaldo($hp,$produk->harga) && $this->trm->tambahSaldo($produk->no_hp_mitra,$produk->harga))
					// {
					// 	$this->response([
					//         'status' => TRUE,
					//         'kode' => 25002,
			  		//		   'hppembeli' => $hp,
			  		//	       'hppenjual' => $produk->no_hp_mitra,
			  		//         'jumlahpembayaran' => $produk->harga,
			  		//         'saldopembeli' => $this->usm->getSaldo($hp),
			  		//         'message' => 'Pembelian produk berhasil',
			  		//   ], REST_Controller::HTTP_OK);
					// }
					// else
					// {
					// 	$this->response([
			  		//         'status' => FALSE,
			  		//         'kode' => 25003,
			  		//         'message' => 'Gagal untuk memproses pembelian'
			  		//   ], REST_Controller::HTTP_OK);
					// }

					//$this->db->trans_start();
					
					//$this->trm->addTransaksiPembayaran($hp,$produk->no_hp_mitra,$produk->harga,$param);
					//$this->trm->kurangiSaldo($hp,$produk->harga);
					//$this->trm->tambahSaldo($produk->no_hp_mitra,$produk->harga);
					//$this->db->trans_complete();

					if ($this->trm->transaksiProdukMitra($hp,$produk,$param) === FALSE)
					{
						$this->response([
			  		        'status' => FALSE,
			  		        'kode' => 25003,
			  		        'message' => 'Gagal untuk memproses pembelian'
			  		  	], REST_Controller::HTTP_OK);
					}
					else
					{
						$this->response([
					        'status' => TRUE,
					        'kode' => 25002,
			  				'hppembeli' => $hp,
			  			    'hppenjual' => $produk->no_hp_mitra,
			  		        'jumlahpembayaran' => $produk->harga,
			  		        'saldopembeli' => $this->usm->getSaldo($hp),
			  		        'message' => 'Pembelian produk berhasil',
			  		  	], REST_Controller::HTTP_OK);
					}
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 23004,
		                'message' => 'Saldo tidak cukup'
		            ], REST_Controller::HTTP_OK);		
				}
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 23003,
	                'message' => 'PIN tidak sesuai'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
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
			if($this->trm->checkPin($hp,$pin))
			{
				if($this->trm->checkEnoughSaldoBayar($hp,$jp))
				{
					//if($this->trm->addTransaksiPembayaran($hp,$hpp,$jp,$ket) && $this->trm->kurangiSaldo($hp,$jp) && $this->trm->tambahSaldo($hpp,$jp))
					// {
					// 	$this->response([
			  		//               'status' => TRUE,
			  		//               'kode' => 23001,
			  		//               'hppembeli' => $hp,
			  		//               'hppenjual' => $hpp,
			  		//               'jumlahpembayaran' => $jp,
			  		//               'saldopembeli' => $this->usm->getSaldo($hp),
			  		//               'message' => 'pembayaran berhasil',
			  		//           ], REST_Controller::HTTP_OK);
					// }
					// else
					// {
					// 	$this->response([
			  		//               'status' => FALSE,
			  		//               'kode' => 23002,
			  		//               'message' => 'Gagal untuk memproses pembayaran'
			  		//           ], REST_Controller::HTTP_OK);
					// }

					if($this->trm->transaksiPembayaran($hp,$hpp,$jp,$ket) === FALSE)
					{
						$this->response([
	  		              	'status' => FALSE,
	  		              	'kode' => 23002,
	  		              	'message' => 'Gagal untuk memproses pembayaran'
	  		          	], REST_Controller::HTTP_OK);
					}
					else
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
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 23004,
		                'message' => 'Saldo tidak cukup'
		            ], REST_Controller::HTTP_OK);		
				}
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 23003,
	                'message' => 'PIN tidak sesuai'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
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
            ], REST_Controller::HTTP_OK);
		}
	}

	public function withdraw_post()
	{
		$ss = $this->post('session');
		$hp = $this->post('hp');
		$pin = $this->post('pin');
		$jumlah = $this->post('jumlah');
		if($this->_session_exist($ss))
		{
			if($this->usm->bankDataExist($hp))
			{
				if($this->usm->enoughSaldoWithdraw($hp,$jumlah))
				{
					if($this->trm->checkPin($hp,$pin))
					{
						// if($this->trm->addWithdraw($hp,$jumlah) && $this->trm->kurangiSaldo($hp,$jumlah))
						// {
						// 	$this->response([
				  		//               'status' => TRUE,
				  		//               'kode' => 24001,
				  		//               'message' => 'Withdraw berhasil ditambahkan'
				  		//           ], REST_Controller::HTTP_OK);
						// }	
						// else
						// {
						// 	$this->response([
				  		//               'status' => FALSE,
				  		//               'kode' => 24002,
				  		//               'message' => 'Gagal menambahkan data withdraw!'
				  		//           ], REST_Controller::HTTP_OK);
						// }

						if ($this->trm->transaksiWithdraw($hp,$jumlah) === FALSE) 
						{
							$this->response([
		  		              	'status' => FALSE,
		  		              	'kode' => 24002,
		  		              	'message' => 'Gagal menambahkan data withdraw!'
		  		          	], REST_Controller::HTTP_OK);
						}
						else
						{
							$this->response([
		  		              	'status' => TRUE,
		  		              	'kode' => 24001,
		  		              	'message' => 'Withdraw berhasil ditambahkan'
		  		          	], REST_Controller::HTTP_OK);
						}
					}
					else
					{
						$this->response([
			                'status' => FALSE,
			                'kode' => 23003,
			                'message' => 'PIN tidak sesuai'
			            ], REST_Controller::HTTP_OK);
					}
				}
				else
				{
					$this->response([
		                'status' => FALSE,
		                'kode' => 24004,
		                'message' => 'Jumlah saldo yang akan ditarik tidak mencukupi!'
		            ], REST_Controller::HTTP_OK);
				}
			}
			else
			{
				$this->response([
	                'status' => FALSE,
	                'kode' => 24003,
	                'message' => 'Data Bank masih kosong, Silahkan update profil!'
	            ], REST_Controller::HTTP_OK);
			}
		}
		else
		{
			$this->response([
                'status' => FALSE,
                'kode' => 10003,
                'message' => 'Invalid Session'
            ], REST_Controller::HTTP_OK);
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
		$no = str_pad($lid, 6, "0", STR_PAD_LEFT);
		return "MNPT".$no;
	}

	private function _createUniqueTransfer($jm)
	{
		$uq = rand(100,999);
		return substr($jm, 0, -3) . $uq;
	}
}