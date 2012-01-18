<?php
/**
 * FlashFoto PHP API SDK
 * For FlashFoto APIv2
 */

class FlashFoto extends Object {

	protected $partner_username = null;
	protected $partner_apikey = null;
	protected $base_url = null;

	/**
	 * Create a new FlashFoto object with API credentials and base API endpoint
	 * @param string $partner_username Partner username from API credentials
	 * @param string $partner_apikey Partner API key from API credentials
	 * @param string $base_url Base API endpoint URL
	 */
	public function __construct($partner_username, $partner_apikey, $base_url='http://flashfotoapi.com/api/') {
		$this->partner_username = $partner_username;
		$this->partner_apikey = $partner_apikey;
		$this->base_url = $base_url;
	}

	/**
	 * Makes a request to the FlashFoto API
	 * @param string $url Request URL
	 * @param string $method HTTP request method
	 * @param array $post_data Array of POST data
	 * @param bool $decode False to turn off json decoding
	 * @return string|array JSON decoded array or string if $decode is false
	 * @throws Exception
	 */
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

	/**
	 * Adds params to query string of url
	 * @param string $url
	 * @param array $params
	 * @return string
	 */
	protected function getUrlWithParamString($url, $params=null){
		if($params === null) {
			return $url;
		}
		return $url . '?' . http_build_query($params);
	}

	/**
	 * Adds an image
	 * @param string $image_data String of raw image data
	 * @param array $params version<br/>privacy<br/>group<br/>format<br/>location<br/>
	 * @return array JSON response array
	 */
	function add($image_data=null, $params=null) {
		$url = $this->getUrlWithParamString("add", $params);
		$result = $this->__make_request($url, "POST", $image_data);
		return $result;
	}

	/**
	 * Creates a copy of an image
	 * @param int $image_id
	 * @param array $params version<br/>x<br/>y<br/>width<br/>height<br/>dest_id<br/>dest_version<br/>dest_group<br/>dest_privacy
	 * @return array JSON response array
	 */
	function copy($image_id, $params=null) {
		$url = $this->getUrlWithParamString("copy/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * Retrieves an image
	 * @param int $image_id
	 * @param array $params image_id<br/>width<br/>height<br/>resize
	 * @return string Binary image data
	 */
	function get($image_id, $params=null) {
		$url = $this->getUrlWithParamString("get/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * Removes an image
	 * @param int $image_id
	 * @param array $params version
	 * @return array JSON response array
	 */
	function delete($image_id, $params=null) {
		$url = $this->getUrlWithParamString("delete/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * Finds Images that belong to you given the filtering parameters you provide.
	 * @param array $params group
	 * @return array JSON response array
	 */
	function find($params=null) {
		$url = $this->getUrlWithParamString("find", $params);
		return $this->__make_request($url);
	}

	/**
	 * Retrieves the information that we are storing about a particular image.
	 * @param int $image_id
	 * @param array $params image_id
	 * @return array JSON response array
	 */
	function info($image_id, $params=null) {
		$url = $this->getUrlWithParamString("info/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method processes the specified image, and retrieves the facial location data about an image.<br/>
	 * If you want to retrieve the location data of an image you have already processed, you can call findfaces_status
	 * @param int $image_id
	 * @return array JSON response array
	 */
	function findfaces($image_id) {
		return $this->__make_request('findfaces/' . $image_id);
	}

	/**
	 * This method retrieves the facial location data about an image that you have already processed.<br/>
	 * If you want to retrieve the location data of an image you have not already processed, you can call findfaces
	 * @param int $image_id
	 * @return array JSON response array
	 */
	function findfaces_status($image_id) {
		return $this->__make_request('findfaces_status/' . $image_id);
	}

	/**
	 * This method processes the specified image, and detects the face and hair lines of the primary face in the image.
	 * @param int $image_id
	 * @param array $params
	 * @return array JSON response array
	 */
	function segment($image_id, $params=null) {
		$url = $this->getUrlWithParamString("segment/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method returns the results of the segment method. If the Segmentation has failed, or is pending/processing, the response will represent that.
	 * @param int $image_id
	 * @param array $params
	 * @return array JSON response array
	 */
	function segment_status($image_id, $params=null) {
		$url = $this->getUrlWithParamString("segment_status/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method processes the specified image, and detects the face, hair and body area of the primary person in the image.
	 * @param int $image_id
	 * @param array $params
	 * @return array JSON response array
	 */
	function mugshot($image_id, $params=null) {
		$url = $this->getUrlWithParamString("mugshot/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method returns the results of the mugshot method. If the Mugshot has failed, or is pending/processing, the response will represent that.
	 * @param int $image_id
	 * @param array $params
	 * @return array JSON response array
	 */
	function mugshot_status($image_id, $params=null) {
		$url = $this->getUrlWithParamString("mugshot_status/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method removes the background of an image.
	 * @param int $image_id
	 * @param array $params findholes<br/>hole_similarity_threshold<br/>adapt_hist_eq_clip_limit
	 * @return array JSON response array
	 */
	function remove_uniform_background($image_id, $params=null) {
		$url = $this->getUrlWithParamString("remove_uniform_background/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method allows for the crop of an image given a specified aspect ratio.
	 * @param int $image_id
	 * @param array $params ratioHeight<br/>ratioWidth
	 * @return array JSON response array
	 */
	function crop($image_id, $params=null) {
		$url = $this->getUrlWithParamString("crop/".$image_id, $params);
		return $this->__make_request($url);
	}

	/**
	 * This method allows for the merging of multiple images together at specified coordinates.
	 * @param array $merge_data image_id<br/>version<br/>x<br/>y<br/>scale<br/>angle<br/>flip
	 * @param array $params privacy<br/>group
	 * @return array JSON response array
	 */
	function merge($merge_data, $params=null) {
		$url = $this->getUrlWithParamString("merge");
		return $this->__make_request($url, $method='POST', json_encode($merge_data));
	}


}
