<?php
/**
 * FlashFoto PHP API SDK
 * For FlashFoto APIv2
 */

class FlashFoto {

	protected $partner_username = null;
	protected $partner_apikey = null;
	protected $base_url = null;

	/* @var string $last_response */
	protected $last_response = null;

	/* @var array $last_response_info */
	protected $last_response_info = null;

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
	 * @throws FlashFotoException
	 */
	protected function __make_request($url, $method = 'GET', $post_data = null, $decode = true) {
		//Reset last response data
		$this->last_response = $this->last_response_info = null;

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

		$this->last_response = $result = curl_exec($ch);
		$this->last_response_info = $info = curl_getinfo($ch);
		$http_status = isset($info['http_code']) ? $info['http_code'] : null;
		curl_close($ch);
		if($result === false){
			throw new FlashFotoException();
		}
		//Throw exception if result is bad
		if($http_status != 200) {
			$message = '';
			$code = 0;
			//Get message and code from API response
			$result = json_decode($result, true);
			if($result && isset($result['message']) && isset($result['code'])) {
				$message = $result['message'];
				$code = $result['code'];
			} else {
				throw new FlashFotoResponseDecodingException('Unable to decode API response', 0, null, $http_status);
			}
			//Throw proper exception type
			switch($http_status) {
				case 404:
					throw new FlashFotoNotFoundException($message, $code, null, $http_status);
				default:
					throw new FlashFotoException($message, $code, null, $http_status);
			}
		}

		if($decode) {
			$decoded = json_decode($result, true);
			if($decoded === null) {
				throw new FlashFotoResponseDecodingException('Unable to decode API response', 0, null, $http_status);
			} else {
				return $decoded;
			}
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
	 * @param string $image_data String of raw image data, null if using the location param
	 * @param array $params version<br/>privacy<br/>group<br/>format<br/>location<br/>
	 * @return array JSON response array
	 */
	function add($image_data=null, $params=null) {
		$url = $this->getUrlWithParamString("add", $params);
		if($image_data){
			$result = $this->__make_request($url, "POST", $image_data);
		} else {
			$result = $this->__make_request($url);
		}
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
	 * @param array $params width<br/>height<br/>resize<br/>version
	 * @return string Binary image data
	 */
	function get($image_id, $params=null) {
		$url = $this->getUrlWithParamString('get/' . $image_id, $params);
		return $this->__make_request($url, 'GET', null, false);
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
	 * @return array JSON response array
	 */
	function segment($image_id) {
		return $this->__make_request('segment/' . $image_id);
	}

	/**
	 * This method returns the results of the segment method. If the Segmentation has failed, or is pending/processing, the response will represent that.
	 * @param int $image_id
	 * @return array JSON response array
	 */
	function segment_status($image_id) {
		return $this->__make_request('segment_status/' . $image_id);
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
	 * @return string Binary image data
	 */
	function crop($image_id, $params=null) {
		$url = $this->getUrlWithParamString("crop/".$image_id, $params);
		return $this->__make_request($url, 'GET', null, false);
	}

	/**
	 * This method allows for one image to be inserted into the masked area of another.
	 * @param int $image_id
	 * @param array $params mask_id
	 * @return string Binary image data
	 */
	function compose($image_id, $params=null) {
		$url = $this->getUrlWithParamString("compose/".$image_id, $params);
		return $this->__make_request($url, 'GET', null, false);
	}

	/**
	 * This method allows for the merging of multiple images together at specified coordinates.
	 * @param array $merge_data image_id<br/>version<br/>x<br/>y<br/>scale<br/>angle<br/>flip
	 * @param array $params privacy<br/>group
	 * @return array JSON response array
	 */
	function merge($merge_data, $params=null) {
		$url = $this->getUrlWithParamString('merge', $params);
		return $this->__make_request($url, $method='POST', json_encode($merge_data));
	}

	/**
	 * Returns the raw last response from the FlashFoto API
	 * @return null|string
	 */
	public function getLastResponse() {
		return $this->last_response;
	}

	/**
	 * Returns the curl_getinfo data for the last response from the FlashFoto API
	 * @return array|null
	 */
	public function getLastResponseInfo() {
		return $this->last_response_info;
	}

}

//Base FlashFoto exception
class FlashFotoException extends Exception {
	/* @var int $http_status */
	protected $http_status;

	public function __construct($message='An Internal Error Occurred, please try again', $code=0, $previous=null, $http_status=500) {
		parent::__construct($message, $code, $previous);
		$this->http_status = $http_status;
	}

	public function getHttpStatus() {
		return $this->http_status;
	}
}
//If JSON decoding fails
class FlashFotoResponseDecodingException extends FlashFotoException {}
//404
class FlashFotoNotFoundException extends FlashFotoException {}