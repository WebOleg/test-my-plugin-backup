<?php

/* Payment connection class     */

/**
 * Woocommerce BNA Gateway
 *
 * @author 		BNA
 * @category 	'BNA Payment Connection' Class
 * @version     1.0
 */

require_once dirname(__FILE__). "/bna_class_encryption.php";

/**
 * Error constants
 *
 * @var string
 */
define ('BNA_EXCHANGE_ERROR_VALIDATE_ARGS', -1);
define ('BNA_EXCHANGE_ERROR_REQUEST_ANSWER', -10);
define ('BNA_EXCHANGE_ERROR_REQUEST_PARAMS', -11);
define ('BNA_EXCHANGE_ERROR_REQUEST_TOKEN', -20);

if (!class_exists('BNAAccountManager')) {
  class BNAExchanger 
  {
		/**
		 * Params
		 *
		 * @var string
		 */
    private $token = null;
    private $paylinks_args = null;
    private $cookies = array();

    public $errmsg = '';
    public $errno = 0;
    

    public function __construct($args = null) 
    {
      if ( !$this->validate_payment_args ($args) ) {
        $this->error_log(BNA_EXCHANGE_ERROR_VALIDATE_ARGS);
        return null;
      }

      $this->paylinks_args = $args;
      //$credentials = base64_encode (json_encode( 
        //(object) ['login' => $this->paylinks_args['login'], 'transactionPassword' => $this->paylinks_args['transactionPassword']] 
      //));

      //$headers = array(
        //'Content-Type: application/json',
        //'Access-Control-Allow-Origin: ' . $this->paylinks_args['serverUrl'],
        //'Origin: '.	(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]",
        //'Authorization: Basic '.$credentials
      //);

      //$result = $this->connect( 'https://authgatewaystaging.paylinks.ca/v1/auth/token', [], $headers, true );
      //$result = json_decode ( decryptString($result, $this->paylinks_args['secretKey']), true );
      
      //if ( !isset($result['token']) ) {
        //$this->error_log(BNA_EXCHANGE_ERROR_REQUEST_TOKEN);
        //return null;
      //}

      //$this->token = $result['token'];

      return 0;
    }

		/**
		 * Checking of payment settings.
		 *
		 * @param array $args
		 * @return bool
		 */
    private function validate_payment_args($args)
    {
      $fields = ['serverUrl', 'protocol',  'secretKey', 'login'];//'transactionPassword',
      $args_keys = array_keys ($args);

      foreach ($fields as $field) {
        if ( !in_array($field, $args_keys) || empty($args[$field]) ) return false;
      }

      return true;
    }

		/**
		 * Request forming function.
		 * @param string $url_method
		 * @param array $postparams
		 * @param string $custom_request
		 * @return json
		 */
    public function query ($url_method, $postparams = null, $custom_request = 'POST')
    {
		//$accessKey = 'Ot7itRJklD7Sb2Yn';
		//$secretKey = 'HOMyb$C}KTq;5;izD:QxKdi}w3UWuJr0';
		$credentials = base64_encode( $this->paylinks_args['login'] . ':' . $this->paylinks_args['secretKey'] );
		
      $headers = array(
          'Content-Type: application/json',
          'Authorization: Basic '.$credentials,
          //'Access-Control-Allow-Origin: ' . $this->paylinks_args['serverUrl'],
          //'Origin: '.	(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"
      );

      if ( count($this->cookies) > 0 ) {
        //array_push($headers, 'Cookie: '.$this->cookies[0][1]);
      }

      //$postparams = encryptString( json_encode( $postparams ), $this->paylinks_args['secretKey'] );
      //$result = $this->connect($url_method, (object) [ 'data' => $postparams ], $headers, false, $custom_request);
      $result = $this->connect($url_method, $postparams, $headers, false, $custom_request);

      //return json_decode(decryptString($result, $this->paylinks_args['secretKey']), true);
      return $result;
    }

		/**
		 * Saving the main cookie.
		 * @param object $ch
		 * @param string $headerLine
		 * @return int
		 */
    private function getCookieCallback ($ch, $headerLine) {
        if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1)
            $this->cookies[] = $cookie;
        return strlen($headerLine);   
    }

		/**
		 * Create connect
		 * @param string $url
		 * @param array $params_post
		 * @param array $headers
		 * @param bool $get_cookie
		 * @param string $custom_request
		 * @return json string
		 */
    public function connect ($url, $params_post = null, $headers = null, $get_cookie = null, $custom_request = 'POST')
    {
      $ch  = curl_init();

      if ( !empty($params_post) ) {       
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($params_post));
      } 

      if ( is_array($params_post) || !empty($params_post) || $custom_request === 'GET' || $custom_request === 'DELETE') {       
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_request);
      } 

      if ( !empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      }

      if ( !empty($get_cookie)) {
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, 'getCookieCallback'));
      }
      curl_setopt($ch, CURLOPT_URL,  $url);
      curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_ENCODING, '');
      curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
      curl_setopt($ch, CURLOPT_TIMEOUT, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

      $response = curl_exec($ch);
      $this->errno = curl_errno($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
      
      return $response;
    }


    public function error_log ($error_code)
    {
      $message = '';
      $this->errno = $error_code;

      switch ($error_code) {
        case BNA_EXCHANGE_ERROR_VALIDATE_ARGS:
          $message = "Input args is not correct";
          break;
        case BNA_EXCHANGE_ERROR_REQUEST_ANSWER:
          $message = "Can't connect to payment system.";
          break;
          break;
        case BNA_EXCHANGE_ERROR_REQUEST_TOKEN:
          $message = "The token was not transferred.";
          break;
        case BNA_EXCHANGE_ERROR_REQUEST_PARAMS:
          $message = "Incorrect parameters in the request.";
          break;
      }

      error_log($message);
    }
  }
}
