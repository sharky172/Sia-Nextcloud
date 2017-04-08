<?php
/**
 * @copyright Copyright (c) 2017, Nebulous
 *
 * @author Johnathan Howell <me@johnathanhowell.com>
 *
 * @license MIT
 * */

namespace Sia;

class Client {
	private $apiaddr;
	private $apiauth;

	private function apiCurl($route, $method) {
		$url=$this->apiaddr . $route;
		$passwd=$this->apiauth;

        $ch=curl_init("$url");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_USERAGENT,'Sia-Agent');

		if (!empty($passwd) && $passwd)
			curl_setopt($ch, CURLOPT_USERPWD, ":$passwd");

		if ($method == 'POST')
			curl_setopt($ch, CURLOPT_POST, 1);

		$res=curl_exec($ch);
		$status_code=curl_getinfo ($ch, CURLINFO_HTTP_CODE );
		if ( $status_code < 200 || $status_code > 299 || curl_errno($ch)) {
			throw new \Exception("curl error: ".curl_errno($ch));
		}
		curl_close($ch);

		return $res;
       }

	private function apiGet($route) {
		$res=$this->apiCurl($route, 'GET');
		return json_decode($res);
	}

	private function apiPost($route) {
		$res=$this->apiCurl($route, 'POST');
		return json_decode($res);
	}

	public function __construct($apiaddr,$apiauth) {
		if (!is_string($apiaddr)) {
			throw new \InvalidArgumentException('api addr must be a string');
		}
		if (!empty($apiauth) && !is_string($apiauth)) {
			throw new \InvalidArgumentException('password wrong format');
		}
		$this->apiaddr = 'http://' . $apiaddr;
		$this->apiauth= $apiauth;
	}	

	// Daemon API
	// version returns a string representation of the current Sia daemon version.
	public function version() {
		return $this->apiGet('/daemon/version')->version;
	}

	// Wallet API
	// wallet returns the wallet object
	public function wallet() {
		return $this->apiGet('/wallet');
	}

	// Renter API
	// renterSettings returns the renter settings
	public function renterSettings() {
		return $this->apiGet('/renter');
	}

	// renterFiles returns the files in the renter
	public function renterFiles() {
		return $this->apiGet('/renter/files')->files;
	}

	// renterContracts returns the contracts being used by the renter
	public function renterContracts() {
		return $this->apiGet('/renter/contracts')->contracts;
	}

	// download downloads the file at $siapath to $dest.
	public function download($siapath, $dest) {
		$this->apiGet('/renter/downloadasync/' . $siapath . '?destination=' . $dest);
	}

	public function downloads() {
		return $this->apiGet('/renter/downloads')->downloads;
	}

	public function upload($siapath, $src) {
		$this->apiPost('/renter/upload/' . $siapath . '?source=' . $src);
	}

	public function delete($siapath) {
		$this->apiPost('/renter/delete/' . $siapath);
	}

	public function rename($siapath, $newsiapath) {
		$this->apiPost('/renter/rename/' . $siapath . '?newsiapath=' . $newsiapath);
	}
}

