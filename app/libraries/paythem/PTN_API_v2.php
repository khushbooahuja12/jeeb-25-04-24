<?php
/**
 * This class is used for direct integration with the PayThem.Net Electronic Voucher Distribution API subsystem.
 *
 * This is the base class. Any calls to this must follow the API protocal inherently, without deviation.
 *
 * @author Richard S. de Breyn / support@paythem.atlassian.net
 * @version 2.2.0
 * @copyright PayThem.Net WLL, 2014-2021
 *
 * @requires Requires openSSL if available. If not available, it fails back to the AESCTR library.
 *
 * @update 2020-05-07 Converted to 2.0.4 API protocol.
 * @update 2020-05-07 Changed constructor $environment variable to be required.
 * @update 2020-05-07 Refractored code & micro-optimized strings " to ' and removed unnecessary {} combinations.
 * @update 2020-05-07 Changed copyright.
 * @update 2020-05-07 Added HASH_STUB randomized variable to call. Resolve HMAC duplication for concurrent calls.
 * @update 2020-05-07 Removed removeWhiteSpaces
 * @update 2020-05-07 Changed certain functions access rights to private
 * @update 2020-05-07 Added phpDoc comments to constructor
 * @update 2021-04-15 -> 2021-04-30
 *   - Bump protocol version to 2.2.0.
 *   - Officially:
 *     - added OpenSSL as encryption option. AES encryption to be moved to "Deprecated" state in later releases.
 *     - added the HASH_STUB to avoid HASH duplication when repeating calls without re-initializing object.
 *     - Server-side new calls (review official documentation version 2.2.0:
 *       - get_ProductAvailability: retrieve the availability for a single product.
 *       - get_AllProductAvailability: retrieve the availability for all products on profile.
 *       - get_ErrorDescription: retrieve description of error ID.
 *   - Removing requirement to pass the APPID for constructor and set to default 2848.
 *   - callAPI
 *     - Throws Exception if IV is passed, but openSSL module not installed as PHP module.
 *     - Throws Exception if IV is passed, but is not exactly 16 long.
 *   - Several code refactoring, changes and optimizations.
 *   - Added encryption method variables.
 *   - Minimum PHP version bumped to 7.0.
 *   - "Prettified" debug output.
 *   - Add public class variables for cURL error and HTTP/S response code.
 *   - Moved access to server URI to private variable.
 *   - Only close cURL on destruct, allowing for multiple calls via single instantiated instance of object.
 *   - Added destructor for GC.
 *   - Fixed ENCRYPT_RESPONSE feature over openSSL.
 *   - Completed PHPDoc comments.
 *   - Parameters and return variable type definitions
 */
namespace App\Libraries\Paythem;
use Exception;

class PTN_API_v2 {
	/**
	 * @var int the returned API call ID as returned by server.
	 */
	public $environment 			                = 'production';

	/**
	 * @var int the returned API call ID, as returned by server, representing 
	 * the current call's log entry on the server.
	 */
	public $serverTransactionID					    = 0;
	
	/**
	 * @var string the returned API result code. 00000 if success, otherwise, 
	 * a relevant error ID. Description can be retrieved by calling get_ErrorDescription
	 */
	public $resultCode								= '';
	
	/**
	 * @var int the HTTP response code.
	 */
	public $httpResponse;
	
	/**
	 * @var string the cURL code, if an error occurs.
	 */
	public $httpCurlError;
	
	/**
	 * @var string the initialization vector for openSSL encryption.
	 */
	public $IV										= '';
	
	/**
	 * @var string the translated error code to human legible text as returned from server.
	 */
	public $errorDescription;
	
	/**
	 * @var mixed|NULL the result as decoded and, optionally, decrypted.
	 */
	public $result;
	
	/**
	 * @var mixed|null the raw response, as received from server, decoded into array
	 */
	public $response;

	/**
	 * @var string Current API Version
	 */
	private $CAPIV                                  = '2.2.0';
	
	/**
	 * @var false|resource The cURL object, instantiated once in constructor, for multiple calls.
	 */
	private $cURLing;
	
	/**
	 * @var array the inner variables for setting storage.
	 */
	private $innerVars          				    = [];
	
	/**
	 * @var string encoding used for HMAC hash creation.
	 */
	private $hashMacENC                             = 'sha256';
	
	/**
	 * @var string The URI / Universal Resource Identifier of the server.
	 */
	private $serverURI;
	
	/**
	 * Array containing the encrypted content to be JSON encoded and then posted to server.
	 * @var array
	 */
	private $encryptedContent                       = [];

	/**
	 * Constructor.
	 *
	 * @param String $environment The server environment that this transaction will be executed against.
	 * @param int $appID The API Application to communicate with.
	 * @param string $iv The IV to be used for openSSL encryption. Needs to be exactly 16 alphanumerical characters.
	 *                   If no IV is passed, internal AESCTR encrytion will be used.
	 */
	public function __construct(string $environment, int $appID=2824, string $iv=''){
		$this->environment                          = $environment;
		$this->serverURI            				= "https://vvs{$this->environment}.paythem.net/API/{$appID}/";
		$this->innerVars['DEBUG_OUTPUT']			= $this->innerVars['FAULTY_PROXY']
													= $this->innerVars['SERVER_DEBUG']
													= false;
		$this->innerVars['PARAMETERS']				= [];
		$this->IV                                   = $iv;
		$this->cURLing								= curl_init();
		curl_setopt($this->cURLing, CURLOPT_URL				, $this->serverURI);
		curl_setopt($this->cURLing, CURLOPT_POST    			, 1);
		curl_setopt($this->cURLing, CURLOPT_RETURNTRANSFER	, true);
		curl_setopt($this->cURLing, CURLOPT_SSL_VERIFYPEER	, false);
	}

	/**
	 * Destructor
	 */
	public function __destruct(){
		try{
			curl_close($this->cURLing);
		}catch(Throwable $e){}
		unset(
			$this->cURLing, $this->innerVars, $this->serverTransactionID,
			$this->resultCode, $this->httpResponse, $this->httpCurlError,
			$this->IV, $this->result
		);
	}
	
	/**
	 * Magic method for returning from innerVars.
	 *
	 * @param string $prop
	 * @return mixed|null
	 */
	public function __get(string $prop) {
		return $this->innerVars[$prop] ? $this->innerVars[$prop] : NULL;
	}
	
	/**
	 * Magic method to set a value in innerVars.
	 *
	 * @param string $prop
	 * @param mixed $val
	 */
	public function __set(string $prop, $val) {
		$this->innerVars[$prop]                     = $val;
	}
	
	/**
	 * Perform the call to the server.
	 *
	 * @param bool $debug
	 * @return array|mixed
	 * @throws Exception
	 */
	public function callAPI(bool $debug=false){
		// $this->dPrt('Posting to (empty for production)'     , $this->environment, false, false);
		// $this->dPrt('Debug post (returns running commentary)', $debug, false, false);
		
		// Set global debug flag.
		$this->innerVars['DEBUG_OUTPUT']			= $debug;
		
		// Fill in incomplete variables
		$this->setVariableDefaults('ENCRYPT_RESPONSE', false);
		$this->setVariableDefaults('SOURCE_IP'		, $this->getServerIP());
		
		// Check that all required variables are filled.
		$this->checkVariable('PUBLIC_KEY'			, 'API_C_00004', 'Public key cannot be empty.');
		$this->checkVariable('PRIVATE_KEY'			, 'API_C_00005', 'Private key cannot be empty.');
		$this->checkVariable('USERNAME'				, 'API_C_00006', 'Username cannot be empty.');
		$this->checkVariable('PASSWORD'				, 'API_C_00007', 'Password cannot be emtpy.');
		$this->checkVariable('FUNCTION'				, 'API_C_00008', 'No function specified.');
		
		// Build the content (POST) variable
		$content                                    = json_encode(
			array_merge(
				$this->innerVars,
				[
					'API_VERSION'					=> $this->CAPIV,
					'SERVER_URI'					=> $this->serverURI,
					'SERVER_TIMESTAMP'				=> date('Y-m-d H:i:s'),
					'SERVER_TIMEZONE'				=> date_default_timezone_get(),
					'HASH_STUB'						=> rand(1111111111, 9999999999),
					'PRIVATE_KEY'                   => 'REMOVED'
				]
			)
		);
		// $this->dPrt(PHP_EOL.'JSON string to post', $content, true, false);
		
		// Generate the HMAC hash
		$hash										= hash_hmac($this->hashMacENC, $content, $this->innerVars['PRIVATE_KEY']);
		// $this->dPrt(PHP_EOL.'HMAC Hash of JSON string', $hash, false, false);
		
		// Create the headers for the POST
		$headers									= array(
			'X-Public-Key: '						.$this->innerVars['PUBLIC_KEY'],
			'X-Hash: '								.$hash,
			'X-Sourceip: '							.$this->innerVars['SOURCE_IP']
		);
		if($this->innerVars['FAULTY_PROXY'])
			$headers['X-Forwarded-For-Override: ']	= $this->getServerIP();
		// $this->dPrt(PHP_EOL.'HTTP Headers', $headers, true, false);
		
		// Encrypt the POST content and set the PUBLIC KEY
		$this->encryptedContent['PUBLIC_KEY']       = $this->innerVars['PUBLIC_KEY'];
		$this->encryptedContent['CONTENT']          = $this->doEncrypt($content);
		// $this->dPrt(PHP_EOL.'Encrypted POST content', $this->encryptedContent, true, false);
		
		// Do cURL call
		curl_setopt($this->cURLing, CURLOPT_POSTFIELDS		, $this->encryptedContent);
		curl_setopt($this->cURLing, CURLOPT_HTTPHEADER		, $headers);
		$this->httpResponse							= curl_exec($this->cURLing);
		$this->httpCurlError                        = curl_error($this->cURLing);
		if($this->httpCurlError){
			// $this->dPrt('Error',$this->httpCurlError, false);
			return [
				'RESULT'                            => -1,
				'CONTENT'                           => $this->httpCurlError
			];
		}
		
		// Process and decrypt the result
		$this->response								= json_decode($this->httpResponse, true);
		// $this->dPrt('API response: Original'    , $this->httpResponse, true, false);
		
		$this->result							    =
			$this->innerVars['ENCRYPT_RESPONSE']
			? $this->doDecrypt($this->response['CONTENT'])
			: $this->response['CONTENT'];
		if($this->result != 'ERROR')
			$this->result                           = json_decode($this->result, true);
		// $this->dPrt('API response: Decoded'     , $this->result);
		$this->resultCode							= $this->response['RESULT'];
		// $this->dPrt('Result code'               , $this->resultCode, false, false);
		$this->serverTransactionID					= $this->response['SERVER_TRANSACTION_ID'];
		// $this->dPrt('Server transaction ID'     , $this->serverTransactionID, false, false);
		$this->errorDescription                     =
			!empty($this->response['ERROR_DESCRIPTION'])
			? $this->response['ERROR_DESCRIPTION']
			: '';
		// $this->dPrt('Error description'         , $this->errorDescription, false);
		return $this->result;
	}
	
	/**
	 * Print to the CLI interface, if required and enabled.
	 *
	 * @param string $title
	 * @param mixed $var
	 * @param bool $newLine
	 * @param bool $xtraLine
	 */
	private function dPrt(string $title, $var, bool $newLine=true, bool $xtraLine=true){
		if(!$this->innerVars['DEBUG_OUTPUT'])
			return;
		elseif($newLine)
			return PHP_EOL.$title.': '.PHP_EOL.str_repeat('-', strlen($title)+1).PHP_EOL;
		else
			return PHP_EOL.$title.': ';
		if(is_array($var))
			var_export($var);
		else{
			if(is_a($var, 'stdClass'))
				var_dump($var);
			else
				return($var);
		}
		if($xtraLine)
			return PHP_EOL;
	}
	
	/**
	 * Encrypt, using supplied credentials and settings, the passed content.
	 *
	 * @param string $content
	 * @return string
	 * @throws Exception
	 */
	private function doEncrypt(string $content) :string {
		// Check the IV and openSSL setup/config.
		if(
			$this->IV != '' &&
			strlen($this->IV) != 16
		)
			throw new Exception(PHP_EOL.'IV must be exactly 16 (randomly generated) alpha-numeric.'.PHP_EOL);
		elseif(
			strlen($this->IV) == 16 &&
			!extension_loaded('openssl')
		)
			throw new Exception(PHP_EOL.'OPENSSL extension NOT LOADED as PHP module.'.PHP_EOL);
		if($this->IV != ''){
			// $this->dPrt('Using encryption method', 'AES-CBC-256-OPENSSL with IV '.$this->IV, false);
			$this->encryptedContent['ENC_METHOD']   = 'AES-CBC-256-OPENSSL';
			if($this->IV != '')
				$this->encryptedContent['ZAPI']     = $this->IV;
			return base64_encode(openssl_encrypt($content,'aes-256-cbc', $this->innerVars['PRIVATE_KEY'],OPENSSL_RAW_DATA, $this->IV));
		}else{
			// $this->dPrt('Using encryption method', 'AES-CTR-256-INTERNAL', false);
			require_once 'class.Aes.php';
			require_once 'class.AesCtr.php';
			$this->encryptedContent['ENC_METHOD']   = 'AES-CTR-256-INTERNAL';
			return base64_encode(AesCtr::encrypt($content, $this->innerVars['PRIVATE_KEY'], 256));
		}
	}
	
	/**
	 * Decrypt, using supplied credentials and settings, passed content.
	 *
	 * @param string $content
	 * @return string
	 */
	private function doDecrypt(string $content) :string {
		return
			$this->IV == ''
			? AesCtr::decrypt(base64_decode($content), $this->innerVars['PRIVATE_KEY'], 256)
			: openssl_decrypt(base64_decode($content), 'aes-256-cbc', $this->innerVars['PRIVATE_KEY'], OPENSSL_RAW_DATA, $this->IV);
	}

	/**
	 * Get the configured IP address of the server or override
	 *
	 * @param string $overrideIP The IP to use instead of the retrieved IP.
	 * @return string
	 */
	function getServerIP(string $overrideIP='') :string {
		return
			$overrideIP == ''
			? gethostbyname(gethostname())
			: $overrideIP;
	}

	/**
	 * Check if an variable has been registered in innerVars, stop processing if not.
	 *
	 * @param string $var
	 * @param string $errCode
	 * @param string $errMsg
	 *
	 * @throws Exception
	 */
	private function checkVariable(string $var, string $errCode='UNSPECIFIED', string $errMsg='UNSPECIFIED'){
		// $this->dPrt('Checking', $var, false, false);
		if(!isset($this->innerVars[$var]))
			throw new Exception(PHP_EOL."ERROR {$errCode}: {$errMsg}".PHP_EOL);
	}

	/**
	 * Set default values for variables.
	 *
	 * @param string $var
	 * @param mixed $default
	 */
	private function setVariableDefaults(string $var, $default){
		// $this->dPrt('Setting', $var, false, false);
		if(!isset($this->innerVars[$var]))
			$this->innerVars[$var]					= $default;
	}
}