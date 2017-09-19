<?php

class Logmodel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function logAccess($api,$ip,$key,$var)
	{
		$data = array(
			'api' => $api,
			'ip_address' => $ip,
			'apikey' => $key,
			'variable' => $var,
			'waktu_akses' => date('Y-m-d H:i:s'),
		);

		return $this->db->insert('log',$data);
	}
}