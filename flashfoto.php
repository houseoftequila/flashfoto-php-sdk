<?php
/**
 * FlashFoto PHP API
 */

class FlashFoto extends Object {

	protected $partner_username = null;
	protected $partner_apikey = null;
	protected $base_url = null;

	public function __construct($partner_username, $partner_apikey, $base_url='http://flashfotoapi.com/api/') {
		$this->partner_username = $partner_username;
		$this->partner_apikey = $partner_apikey;
		$this->base_url = $base_url;
	}

	protected function __make_request($url, $method = 'GET', $post_data = null, $decode = true) {
		$url = $this->base_url . $url;
		if(strstr($url, '?')){
			$url .= "&";
		} else {
			$url .= "?";
		}
		$url .= "partner_username=".$this->partner_username."&partner_apikey=".$this->partner_apikey;

		$ch = curl_init();
		// Make request
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($post_data) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		}
		if($method == "GET") {
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			curl_setopt($ch, CURLOPT_POST, false);
		}
		if($method == "POST") {
			curl_setopt($ch, CURLOPT_HTTPGET, false);
			curl_setopt($ch, CURLOPT_POST, true);
		}


		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$http_status = isset($info['http_code']) ? $info['http_code'] : null;
		curl_close($ch);
		// Check to see if the result if bad
		if($http_status != 200){
			$result = json_decode($result, true);
			throw new Exception($result['message'], $result['code']);
		}

		if($decode) {
			$decoded = json_decode($result, true);
			return $decoded;
		} else {
			return $result;
		}
	}
	protected function getUrlWithParamString($url, $params=null){
		if(!$params)
			return "";
		$param_list = array();
		foreach($params as $k=>$v){
			$param_list = $k.=urlencode($v);
		}
		$param_string = join("&", $param_list);
		if($param_string){
			$url .= "?".$param_string;
		}
		return $url;
	}

	/*
	 * APIv2 Compliant
	 */
	function add($image_data=null, $params=null) {
		$url = $this->getParamString("add", $params);
		$result = $this->__make_request($url, "POST", $image_data);
		return $result;
	}

	function copy($image_id, $params=null) {
		$url = $this->getParamString("copy/".$image_id, $params);
		return $this->__make_request($url);
	}

	function get($image_id, $params=null) {
		$url = $this->getParamString("get/".$image_id, $params);
		return $this->__make_request($url);
	}
	function delete($image_id, $params=null) {
		$url = $this->getParamString("delete/".$image_id, $params);
		return $this->__make_request($url);
	}

	function find($params=null) {
		$url = $this->getParamString("find", $params);
		return $this->__make_request($url);
	}

	function info($image_id, $params=null) {
		$url = $this->getParamString("info/".$image_id, $params);
		return $this->__make_request($url);
	}

	function findfaces($image_id, $params=null) {
		$url = $this->getParamString("findfaces/".$image_id, $params);
		return $this->__make_request($url);
	}

	function findfaces_status($image_id, $params=null) {
		$url = $this->getParamString("findfaces_status/".$image_id, $params);
		return $this->__make_request($url);
	}

	function segment($image_id, $params=null) {
		$url = $this->getParamString("segment/".$image_id, $params);
		return $this->__make_request($url);
	}

	function segment_status($image_id, $params=null) {
		$url = $this->getParamString("segment_status/".$image_id, $params);
		return $this->__make_request($url);
	}
	function mugshot($image_id, $params=null) {
		$url = $this->getParamString("mugshot/".$image_id, $params);
		return $this->__make_request($url);
	}
	function mugshot_status($image_id, $params=null) {
		$url = $this->getParamString("mugshot_status/".$image_id, $params);
		return $this->__make_request($url);
	}
	function remove_uniform_background($image_id, $params=null) {
		$url = $this->getParamString("remove_uniform_background/".$image_id, $params);
		return $this->__make_request($url);
	}
	function crop($image_id, $params=null) {
		$url = $this->getParamString("crop/".$image_id, $params);
		return $this->__make_request($url);
	}
	function merge($merge_data, $params=null) {
		$url = $this->getParamString("merge");
		return $this->__make_request($url, $method='POST', json_encode($merge_data));
	}


}
