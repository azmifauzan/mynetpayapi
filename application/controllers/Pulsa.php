<?php

require APPPATH . '/libraries/REST_Controller.php';

class Pulsa extends REST_Controller
{
	private $url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
	private $username = "085220150587";
	private $apikey = "56259e059b2eb6ad";

	public function balance_get()
	{
		$sign = MD5($this->username.$this->apikey."bl");
		$req = '<?xml version="1.0" ?><mp><commands>balance</commands><username>'.$this->username.'</username><sign>'.$sign.'</sign></mp>';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req );
		$result = curl_exec($ch);
		$balance = 0;
		
		if($result === false)
		{
			$balance = curl_error($ch);			
		}
		else
		{
			$data = simplexml_load_string($result);
			$balance =  htmlentities($data->balance); 
		}

		curl_close($ch);
		$this->response([
            'status' => TRUE,
            'message' => $balance
        ], REST_Controller::HTTP_OK);
	}

	public function pricelist_get()
	{
		$sign = MD5($this->username.$this->apikey."pl");
		$req = '<?xml version="1.0" ?><mp><commands>pricelist</commands><username>'.$this->username.'</username><sign>'.$sign.'</sign></mp>';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    	curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req );
		$result = curl_exec($ch);
		if($result === false)
		{
			$data = curl_error($ch);			
		}
		else
		{
			$temp = simplexml_load_string($result);
			foreach($temp->pulsa as $pl)
			{
				if($pl->pulsa_type == "pulsa" && $pl->status == "active")
				{
					$data[] = $pl;
				}
			}
		}
		curl_close($ch);

		$this->response([
            'status' => TRUE,
            'message' => $data
        ], REST_Controller::HTTP_OK);
	}
}