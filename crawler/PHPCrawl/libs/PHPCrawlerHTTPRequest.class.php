<?php
/**
 * Class for performing HTTP-requests.
 *
 * @package phpcrawl
 * @internal
 */
class PHPCrawlerHTTPRequest
{
  /**
   * The user-agent-string
   */
  public $userAgentString = "PHPCrawl";
  
  /**
   * The HTTP protocol version to use.
   */
  public $http_protocol_version = 2;
  
  /**
   * Timeout-value for socket-connection
   */
  public $socketConnectTimeout = 10;
  
  /**
   * Socket-read-timeout
   */
  public $socketReadTimeout = 5;
  
  /**
   * Limit for content-size to receive
   *
   * @var int The kimit n bytes
   */
  protected $content_size_limit = 0;
  
  /**
   * Global counter for traffic this instance of the HTTPRequest-class caused.
   *
   * @var int Traffic in bytes
   */
  protected $global_traffic_count = 0;
  
  /**
   * Numer of bytes received from the header
   *
   * @var float Number of bytes
   */
  protected $header_bytes_received = null;
  
  /**
   * Number of bytes received from the content
   *
   * @var float Number of bytes
   */
  protected $content_bytes_received = null;
  
  /**
   * The time it took to tranfer the data of this document
   *
   * @var float Time in seconds and milliseconds
   */
  protected $data_transfer_time = null;
  
  /**
   * The time it took to connect to the server
   *
   * @var float Time in seconds and milliseconds or NULL if connection could not be established
   */
  protected $server_connect_time = null;
  
  /**
   * The server resonse time
   *
   * @var float time in seconds and milliseconds or NULL if the server didn't respond
   */
  protected $server_response_time = null;
  
  /**
   * Contains all rules defining the content-types that should be received
   *
   * @var array Numeric array conatining the regex-rules
   */
  protected $receive_content_types = array();
  
  /**
   * Contains all rules defining the content-types of pages/files that should be streamed directly to
   * a temporary file (instead of to memory)
   *
   * @var array Numeric array conatining the regex-rules
   */
  protected $receive_to_file_content_types = array();
  
  /**
   * Contains all rules defining the content-types defining which documents shoud get checked for links.
   *
   * @var array Numeric array conatining the regex-rules
   */
  protected $linksearch_content_types = array("#text/html# i");
  
  /**
   * The TMP-File to use when a page/file should be streamed to file.
   *
   * @var string
   */
  protected $tmpFile = "phpcrawl.tmp";
  
  /**
   * The URL for the request as PHPCrawlerURLDescriptor-object
   *
   * @var PHPCrawlerURLDescriptor
   */
  protected $UrlDescriptor;
  
  /**
   * The parts of the URL for the request as returned by PHPCrawlerUtils::splitURL()
   *
   * @var array
   */
  protected $url_parts = array();
  
  /**
   * DNS-cache
   *
   * @var PHPCrawlerDNSCache
   */
  public $DNSCache;
  
  /**
   * Link-finder object
   *
   * @var PHPCrawlerLinkFinder
   */
  protected $LinkFinder;
  
  /**
   * The last response-header this request-instance received.
   */
  protected $lastResponseHeader;
  
  /**
   * Array containing cookies to send with the request
   *
   * @array
   */
  protected $cookie_array = array();
  
  /**
   * Array containing POST-data to send with the request
   *
   * @var array
   */
  protected $post_data = array();
  
  /**
   * The proxy to use
   *
   * @var array Array containing the keys "proxy_host", "proxy_port", "proxy_username", "proxy_password".
   */
  protected $proxy;
  
  /**
   * The socket used for HTTP-requests
   */
  protected $socket;
  
  /**
   * The bytes contained in the socket-buffer directly after the server responded
   */
  protected $socket_prefill_size;
  
  /**
   * Enalbe/disable request for gzip encoded content.
   */
  protected $request_gzip_content = false;
  
  protected $header_check_callback_function = null;
  
  public function __construct()
  {
    // Init LinkFinder
    if (!class_exists("PHPCrawlerLinkFinder")) {
            include_once(dirname(__FILE__) . "/PHPCrawlerLinkFinder.class.php");
        }
        $this->LinkFinder = new PHPCrawlerLinkFinder();
    
    // Init DNS-cache
    if (!class_exists("PHPCrawlerDNSCache")) {
            include_once(dirname(__FILE__) . "/PHPCrawlerDNSCache.class.php");
        }
        $this->DNSCache = new PHPCrawlerDNSCache();
    
    // Cookie-Descriptor
    if (!class_exists("PHPCrawlerCookieDescriptor")) {
            include_once(dirname(__FILE__) . "/PHPCrawlerCookieDescriptor.class.php");
        }

        // ResponseHeader-class
    if (!class_exists("PHPCrawlerResponseHeader")) {
            include_once(dirname(__FILE__) . "/PHPCrawlerResponseHeader.class.php");
        }

        // PHPCrawlerHTTPProtocols-class
    if (!class_exists("PHPCrawlerHTTPProtocols")) {
            include_once(dirname(__FILE__) . "/Enums/PHPCrawlerHTTPProtocols.class.php");
        }
    }
  
  /**
   * Sets the URL for the request.
   *
   * @param PHPCrawlerURLDescriptor $UrlDescriptor An PHPCrawlerURLDescriptor-object containing the URL to request
   */
  public function setUrl(PHPCrawlerURLDescriptor $UrlDescriptor)
  {
    $this->UrlDescriptor = $UrlDescriptor;
    
    // Split the URL into its parts
    $this->url_parts = PHPCrawlerUtils::splitURL($UrlDescriptor->url_rebuild);
  }
  
  /**
   * Adds a cookie to send with the request.
   *
   * @param string $name Cookie-name
   * @param string $value Cookie-value
   */
  public function addCookie($name, $value)
  {
    $this->cookie_array[$name] = $value;
  }
  
  /**
   * Adds a cookie to send with the request.
   *
   * @param PHPCrawlerCookieDescriptor $Cookie
   */
  public function addCookieDescriptor(PHPCrawlerCookieDescriptor $Cookie)
  {
    $this->addCookie($Cookie->name, $Cookie->value);
  }
  
  /**
   * Adds a bunch of cookies to send with the request
   *
   * @param array $cookies Numeric array containins cookies as PHPCrawlerCookieDescriptor-objects
   */
  public function addCookieDescriptors($cookies)
  {
    $cnt = count($cookies);
    for ($x=0; $x<$cnt; $x++)
    {
      $this->addCookieDescriptor($cookies[$x]);
    }
  }
  
  /**
   * Removes all cookies to send with the request.
   */
  public function clearCookies()
  {
    $this->cookie_array = array();
  }
  
  /**
   * Sets the html-tags from which to extract/find links from.
   *
   * @param array $tag_array Numeric array containing the tags, i.g. array("href", "src", "url", ...)
   * @return bool
   */
  public function setLinkExtractionTags($tag_array)
  {
      if (!is_array($tag_array)) {
            return false;
        }

        $this->LinkFinder->extract_tags = $tag_array;
    return true;
  }
  
  /**
   * Specifies whether redirect-links set in http-headers should get searched for.
   *
   * @return bool
   */
  public function setFindRedirectURLs($mode)
  {
      if (!is_bool($mode)) {
            return false;
        }

        $this->LinkFinder->find_redirect_urls = $mode;
    
    return true;
  }
  
  /**
   * Adds post-data to send with the request.
   */
  public function addPostData($key, $value)
  {
    $this->post_data[$key] = $value;
  }
  
  /**
   * Removes all post-data to send with the request.
   */
  public function clearPostData()
  {
    $this->post_data = array();
  }
  
  public function setProxy($proxy_host, $proxy_port, $proxy_username = null, $proxy_password = null)
  {
    $this->proxy = array();
    $this->proxy["proxy_host"] = $proxy_host;
    $this->proxy["proxy_port"] = $proxy_port;
    $this->proxy["proxy_username"] = $proxy_username;
    $this->proxy["proxy_password"] = $proxy_password;
  }
  
  /**
   * Sets basic-authentication login-data for protected URLs.
   */
  public function setBasicAuthentication($username, $password)
  {
    $this->url_parts["auth_username"] = $username;
    $this->url_parts["auth_password"] = $password;
  }
  
  /**
   * Enables/disables aggresive linksearch
   *
   * @param bool $mode
   * @return bool
   */
  public function enableAggressiveLinkSearch($mode)
  {
      if (!is_bool($mode)) {
            return false;
        }

        $this->LinkFinder->aggressive_search = $mode;
    return true;
  }
  
  public function setHeaderCheckCallbackFunction(&$obj, $method_name)
  {
    $this->header_check_callback_function = array($obj, $method_name);
  }
  
  /**
   * Sends the HTTP-request and receives the page/file.
   *
   * @return A PHPCrawlerDocumentInfo-object containing all information about the received page/file
   */
  public function sendRequest()
  {
    // Prepare LinkFinder
    $this->LinkFinder->resetLinkCache();
    $this->LinkFinder->setSourceUrl($this->UrlDescriptor);
    
    // Initiate the Response-object and pass base-infos
    $PageInfo = new PHPCrawlerDocumentInfo();
    $PageInfo->url = $this->UrlDescriptor->url_rebuild;
    $PageInfo->protocol = $this->url_parts["protocol"];
    $PageInfo->host = $this->url_parts["host"];
    $PageInfo->path = $this->url_parts["path"];
    $PageInfo->file = $this->url_parts["file"];
    $PageInfo->query = $this->url_parts["query"];
    $PageInfo->port = $this->url_parts["port"];
    
    // Create header to send
    $request_header_lines = $this->buildRequestHeader();
    $header_string = trim(implode("", $request_header_lines));
    $PageInfo->header_send = $header_string;
    
    // Open socket
    $this->openSocket($PageInfo->error_code, $PageInfo->error_string);
    $PageInfo->server_connect_time = $this->server_connect_time;
    
    // If error occured
    if ($PageInfo->error_code != null)
    {
      // If proxy-error -> throw exception
      if ($PageInfo->error_code == PHPCrawlerRequestErrors::ERROR_PROXY_UNREACHABLE)
      {
        throw new Exception("Unable to connect to proxy '".$this->proxy["proxy_host"]."' on port '".$this->proxy["proxy_port"]."'");
      }
      
      $PageInfo->error_occured = true;
      return $PageInfo; 
    }
    
    // Send request
    $this->sendRequestHeader($request_header_lines);
    
    // Read response-header
    $response_header = $this->readResponseHeader($PageInfo->error_code, $PageInfo->error_string);
    $PageInfo->server_response_time = $this->server_response_time;
    
    // If error occured
    if ($PageInfo->error_code != null)
    {
      $PageInfo->error_occured = true;
      return $PageInfo; 
    }
    
    // Set header-infos
    $this->lastResponseHeader = new PHPCrawlerResponseHeader($response_header, $this->UrlDescriptor->url_rebuild);
    $PageInfo->responseHeader = $this->lastResponseHeader;
    $PageInfo->header = $this->lastResponseHeader->header_raw;
    $PageInfo->http_status_code = $this->lastResponseHeader->http_status_code;
    $PageInfo->content_type = $this->lastResponseHeader->content_type;
    $PageInfo->cookies = $this->lastResponseHeader->cookies;
    
    // Referer-Infos
    if ($this->UrlDescriptor->refering_url != null)
    {
      $PageInfo->referer_url = $this->UrlDescriptor->refering_url;
      $PageInfo->refering_linkcode = $this->UrlDescriptor->linkcode;
      $PageInfo->refering_link_raw = $this->UrlDescriptor->link_raw;
      $PageInfo->refering_linktext = $this->UrlDescriptor->linktext;
    }
      
    // Call header-check-callback
    $ret = 0;
    if ($this->header_check_callback_function != null) {
            $ret = call_user_func($this->header_check_callback_function, $this->lastResponseHeader);
        }

        // Check if content should be received
    $receive = $this->decideRecevieContent($this->lastResponseHeader);
    
    if ($ret < 0 || $receive == false)
    {
      fclose($this->socket);
      $PageInfo->received = false;
      $PageInfo->links_found_url_descriptors = $this->LinkFinder->getAllURLs(); // Maybe found a link/redirect in the header
      $PageInfo->meta_attributes = $this->LinkFinder->getAllMetaAttributes();
      return $PageInfo;
    }
    else
    {
      $PageInfo->received = true;
    }
    
    // Check if content should be streamd to file
    $stream_to_file = $this->decideStreamToFile($response_header);
                    
    // Read content
    $response_content = $this->readResponseContent($stream_to_file, $PageInfo->error_code, $PageInfo->error_string, $PageInfo->received_completely);
     
    // If error occured
    if ($PageInfo->error_code != null)
    {
      $PageInfo->error_occured = true;
    }
    
    fclose($this->socket);
    
    // Complete ResponseObject
    $PageInfo->content = $response_content;
    $PageInfo->source = &$PageInfo->content;
    $PageInfo->received_completly = $PageInfo->received_completely;
    
    if ($stream_to_file == true) {
            $PageInfo->received_to_file = true;
            $PageInfo->content_tmp_file = $this->tmpFile;
        } else {
            $PageInfo->received_to_memory = true;
        }

        $PageInfo->links_found_url_descriptors = $this->LinkFinder->getAllURLs();
    $PageInfo->meta_attributes = $this->LinkFinder->getAllMetaAttributes();
    
    // Info about received bytes
    $PageInfo->bytes_received = $this->content_bytes_received;
    $PageInfo->header_bytes_received = $this->header_bytes_received;
    
    $dtr_values = $this->calulateDataTransferRateValues();
    if ($dtr_values != null)
    {
      $PageInfo->data_transfer_rate = $dtr_values["data_transfer_rate"];
      $PageInfo->unbuffered_bytes_read = $dtr_values["unbuffered_bytes_read"];
      $PageInfo->data_transfer_time = $dtr_values["data_transfer_time"];
    }
    
    $PageInfo->setLinksFoundArray();
    
    $this->LinkFinder->resetLinkCache();
    
    return $PageInfo;
  }
  
  /**
   * Calculates data tranfer rate values
   *
   * @return int The rate in bytes/second
   */
  protected function calulateDataTransferRateValues()
  {
    $vals = array();
    
    // Workd like this:
    // After the server resonded, the socket-buffer is already filled with bytes,
    // that means they were received within the server-response-time.
    
    // To calulate the real data transfer rate, these bytes have to be substractred from the received
    // bytes beofre calulating the rate.
    if ($this->data_transfer_time > 0 && $this->content_bytes_received > 4 * $this->socket_prefill_size)
    {
      $vals["unbuffered_bytes_read"] = $this->content_bytes_received + $this->header_bytes_received - $this->socket_prefill_size;
      $vals["data_transfer_rate"] = $vals["unbuffered_bytes_read"] / $this->data_transfer_time;
      $vals["data_transfer_time"] = $this->data_transfer_time;
    }
    else
    {
      $vals = null;
    }
    
    return $vals;
  }
  
  /**
   * Opens the socket to the host.
   *
   * @param  int    &$error_code          Error-code by referenct if an error occured.
   * @param  string &$error_string        Error-string by reference
   *
   * @return bool   TRUE if socket could be opened, otherwise FALSE.
   */
  protected function openSocket(&$error_code, &$error_string)
  { 
    PHPCrawlerBenchmark::reset("connecting_server");
    PHPCrawlerBenchmark::start("connecting_server");

    // SSL or not?
    if ($this->url_parts["protocol"] === "https://") {
            $protocol_prefix = "ssl://";
        } else {
            $protocol_prefix = "";
        }

        // If SSL-request, but openssl is not installed
    if ($protocol_prefix == "ssl://" && !extension_loaded("openssl"))
    {
      $error_code = PHPCrawlerRequestErrors::ERROR_SSL_NOT_SUPPORTED;
      $error_string = "Error connecting to ".$this->url_parts["protocol"].$this->url_parts["host"].": SSL/HTTPS-requests not supported, extension openssl not installed.";
    }
    
    // Get IP for hostname
    $ip_address = $this->DNSCache->getIP($this->url_parts["host"]);
    
    // Open socket
    if ($this->proxy != null)
    {
      $this->socket = @stream_socket_client($this->proxy["proxy_host"].":".$this->proxy["proxy_port"], $error_code, $error_str,
                                           $this->socketConnectTimeout, STREAM_CLIENT_CONNECT);
    }
    else
    {
      // If ssl -> perform Server name indication
      if ($this->url_parts["protocol"] == "https://")
      {
      // $context = stream_context_create(array('ssl' => array('SNI_server_name' => $this->url_parts["host"])));
      $context = stream_context_create([
        'http' => ['method' => 'GET'],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
      ]);

                $this->socket = stream_socket_client('ssl://'.$ip_address. ":" . $this->url_parts["port"],$error_code,$error_str,$this->socketConnectTimeout, STREAM_CLIENT_CONNECT,$context);
                if ($this->socket === false) {
                    throw new UnexpectedValueException("Failed to connect: $error_str");
                }
      }
      else
      {
        $this->socket = stream_socket_client($protocol_prefix.$ip_address.":".$this->url_parts["port"], $error_code, $error_str,
                                              $this->socketConnectTimeout, STREAM_CLIENT_CONNECT); // NO $context here, memory-leak-bug in php v. 5.3.x!!
      }
    }
    
    $this->server_connect_time = PHPCrawlerBenchmark::stop("connecting_server");
        
    // If socket not opened -> throw error
    if ($this->socket == false)
    { 
      $this->server_connect_time = null;
      
      // If proxy not reachable
      if ($this->proxy != null)
      {
        $error_code = PHPCrawlerRequestErrors::ERROR_PROXY_UNREACHABLE;
        $error_string = "Error connecting to proxy ".$this->proxy["proxy_host"].": Host unreachable (".$error_str.").";
        return false;
      }
      else
      {
        $error_code = PHPCrawlerRequestErrors::ERROR_HOST_UNREACHABLE;
        $error_string = "Error connecting to ".$this->url_parts["protocol"].$this->url_parts["host"].": Host unreachable (".$error_str.").";
        return false;
      }
    }
    else
    {
      return true;
    }
  }
  
  /**
   * Send the request-header.
   */
  protected function sendRequestHeader($request_header_lines)
  {
    // Header senden
    $cnt = count($request_header_lines);
    for ($x=0; $x<$cnt; $x++)
    {
      fputs($this->socket, $request_header_lines[$x]);
    }
  }
  
  /**
   * Reads the response-header.
   *
   * @param  int    &$error_code           Error-code by reference if an error occured.
   * @param  string &$error_string         Error-string by reference
   *
   * @return string The response-header or NULL if an error occured
   */
  protected function readResponseHeader(&$error_code, &$error_string)
  { 
    PHPCrawlerBenchmark::reset("server_response_time");
    PHPCrawlerBenchmark::start("server_response_time");
    
    $status = socket_get_status($this->socket);
    $source_read = "";
    $header = "";
    $server_responded = false;
    
    while ($status["eof"] == false)
    {
      socket_set_timeout($this->socket, $this->socketReadTimeout);
      
      // Read line from socket
      $line_read = fgets($this->socket, 1024);
      
      // Server responded
      if ($server_responded == false)
      {
        $server_responded = true;
        $this->server_response_time = PHPCrawlerBenchmark::stop("server_response_time");
    
        // Determinate socket prefill size
        $status = socket_get_status($this->socket);
        $this->socket_prefill_size = $status["unread_bytes"];
        
        // Start data-transfer-time bechmark
        PHPCrawlerBenchmark::reset("data_transfer_time");
        PHPCrawlerBenchmark::start("data_transfer_time");
      }
      
      $source_read .= $line_read;
      
      $this->global_traffic_count += strlen($line_read);
      
      $status = socket_get_status($this->socket);
      
      // Socket timed out
      if ($status["timed_out"] == true)
      {
        $error_code = PHPCrawlerRequestErrors::ERROR_SOCKET_TIMEOUT;
        $error_string = "Socket-stream timed out (timeout set to ".$this->socketReadTimeout." sec).";
        return $header;
      }
      
      // No "HTTP" at beginnig of response
      if (strtolower(substr($source_read, 0, 4)) != "http")
      {
        $error_code = PHPCrawlerRequestErrors::ERROR_NO_HTTP_HEADER;
        $error_string = "HTTP-protocol error.";
        return $header;
      }
      
      // Header found and read (2 newlines) -> stop
      if (substr($source_read, -4, 4) == "\r\n\r\n" || substr($source_read, -2, 2) == "\n\n")
      {
        $header = substr($source_read, 0, strlen($source_read)-2);
        break;
      }
    }
    
    // Stop data-transfer-time bechmark
    PHPCrawlerBenchmark::stop("data_transfer_time");
    
    // Header was found
    if ($header != "")
    {
      // Search for links (redirects) in the header
      $this->LinkFinder->processHTTPHeader($header);
      $this->header_bytes_received = strlen($header);
      return $header;
    }
        
    // No header found
    if ($header == "")
    {
      $this->server_response_time = null;
      $error_code = PHPCrawlerRequestErrors::ERROR_NO_HTTP_HEADER;
      $error_string = "Host doesn't respond with a HTTP-header.";
      return null;
    }
  }
  
  /**
   * Reads the response-content.
   * 
   * @param bool    $stream_to_file If TRUE, the content will be streamed diretly to the temporary file and
   *                                this method will not return the content as a string.                            
   * @param int     &$error_code    Error-code by reference if an error occured.
   * @param &string &$error_string  Error-string by reference
   * @param &string &$document_received_completely Flag indicatign whether the content was received completely passed by reference
   *
   * @return string  The response-content/source. May be emtpy if an error ocdured or data was streamed to the tmp-file.
   */
  protected function readResponseContent(&$error_code, &$error_string, &$document_received_completely, $stream_to_file = false)
  { 
    $this->content_bytes_received = 0;
    
    // If content should be streamed to file
    if ($stream_to_file == true)
    {
      $fp = @fopen($this->tmpFile, "w");
      
      if ($fp == false)
      {
        $error_code = PHPCrawlerRequestErrors::ERROR_TMP_FILE_NOT_WRITEABLE;
        $error_string = "Couldn't open the temporary file ".$this->tmpFile." for writing.";
        return "";
      }
    }
    
    // Init
    $source_portion = "";
    $source_complete = "";
    $document_received_completely = true;
    $document_completed = false;
    $gzip_encoded_content = null;
    
    // Resume data-transfer-time benchmark
    PHPCrawlerBenchmark::start("data_transfer_time");
    
    while ($document_completed == false)
    {
      // Get chunk from content
      $content_chunk = $this->readResponseContentChunk($document_completed, $error_code, $error_string, $document_received_completely);
      $source_portion .= $content_chunk;
      
      // Check if content is gzip-encoded (check only first chunk)
      if ($gzip_encoded_content === null)
      {
          if (PHPCrawlerUtils::isGzipEncoded($content_chunk)) {
                    $gzip_encoded_content = true;
                } else {
                    $gzip_encoded_content = false;
                }
            }
      
      // Stream to file or store source in memory
      if ($stream_to_file == true)
      {
          fwrite($fp, $content_chunk);
      }
      else
      {
        $source_complete .= $content_chunk;
      }
      
      // Decode gzip-encoded content when done with document
      if ($document_completed == true && $gzip_encoded_content == true) {
                $source_complete = $source_portion = PHPCrawlerUtils::decodeGZipContent($source_complete);
            }

            // Find links in portion of the source
      if (($gzip_encoded_content == false && $stream_to_file == false && strlen($source_portion) >= 20000000) || $document_completed == true)
      {
        if (PHPCrawlerUtils::checkStringAgainstRegexArray($this->lastResponseHeader->content_type, $this->linksearch_content_types))
        {
          PHPCrawlerBenchmark::stop("data_transfer_time");
          $this->LinkFinder->findLinksInHTMLChunk($source_portion);
          $source_portion = substr($source_portion, -1500);
          PHPCrawlerBenchmark::start("data_transfer_time");
        }
      }
    }
    
    if ($stream_to_file === true) {
            fclose($fp);
        }

        // Stop data-transfer-time benchmark
    PHPCrawlerBenchmark::stop("data_transfer_time");
    $this->data_transfer_time = PHPCrawlerBenchmark::getElapsedTime("data_transfer_time");
    
    return $source_complete;
  }
  
  /**
   * Reads a chunk from the response-content
   *
   * @return string
   */
  protected function readResponseContentChunk(&$document_completed, &$error_code, &$error_string, &$document_received_completely)
  { 
    $source_chunk = "";
    $stop_receiving = false;
    $bytes_received = 0;
    $document_completed = false;
    
    // If chunked encoding and protocol to use is HTTP 1.1
    if ($this->http_protocol_version == PHPCrawlerHTTPProtocols::HTTP_1_1 && $this->lastResponseHeader->transfer_encoding == "chunked")
    {
      // Read size of next chunk
      $chunk_line = fgets($this->socket, 128);
      if (trim($chunk_line) === "") {
                $chunk_line = fgets($this->socket, 128);
            }
            $current_chunk_size = hexdec(trim($chunk_line));
    }
    else
    {
      $current_chunk_size = 20240;
    }
    
    if ($current_chunk_size === 0)
    {
      $stop_receiving = true;
      $document_completed = true;
    }
    
    while ($stop_receiving == false)
    {
      socket_set_timeout($this->socket, $this->socketReadTimeout);
      
      // Set byte-buffer to bytes in socket-buffer (Fix for SSL-hang-bug #56, thanks to MadEgg!)
      $status = socket_get_status($this->socket);
      if ($status["unread_bytes"] > 0) {
                $read_byte_buffer = $status["unread_bytes"];
            } else {
                $read_byte_buffer = 1024;
            }

            // If chunk will be complete next read -> resize read-buffer to size of remaining chunk
      if ($bytes_received + $read_byte_buffer >= $current_chunk_size && $current_chunk_size > 0)
      {
        $read_byte_buffer = $current_chunk_size - $bytes_received;
        $stop_receiving = true;
      }
      
      // Read line from socket
      $line_read = fread($this->socket, $read_byte_buffer); 
      
      $source_chunk .= $line_read;
      $line_length = strlen($line_read);
      $this->content_bytes_received += $line_length;
      $this->global_traffic_count += $line_length;
      $bytes_received += $line_length;
      
      // Check socket-status
      $status = socket_get_status($this->socket);
      
      // Check for EOF
      if ($status["eof"] == true)
      {
        $stop_receiving = true;
        $document_completed = true;
      }
      
      // Socket timed out
      if ($status["timed_out"] == true)
      {
        $stop_receiving = true;
        $document_completed = true;
        $error_code = PHPCrawlerRequestErrors::ERROR_SOCKET_TIMEOUT;
        $error_string = "Socket-stream timed out (timeout set to ".$this->socketReadTimeout." sec).";
        $document_received_completely = false;
        return $source_chunk;
      }

      // Check if content-length stated in the header is reached
      if ($this->lastResponseHeader->content_length == $this->content_bytes_received)
      {
        $stop_receiving = true;
        $document_completed = true;
      }
      
      // Check if contentsize-limit is reached
      if ($this->content_size_limit > 0 && $this->content_size_limit <= $this->content_bytes_received)
      {
        $document_received_completely = false;
        $stop_receiving = true;
        $document_completed = true;
      }
      
    }
    
    return $source_chunk;
  }
  
  /**
   * Builds the request-header from the given settings.
   *
   * @return array  Numeric array containing the lines of the request-header
   */
  protected function buildRequestHeader()
  {
    // Create header
    $headerlines = array();
    
    // Methode(GET or POST)
    if (count($this->post_data) > 0) {
            $request_type = "POST";
        } else {
            $request_type = "GET";
        }

        // HTTP protocol
        if ($this->http_protocol_version == PHPCrawlerHTTPProtocols::HTTP_1_1) {
            $http_protocol_verison = "1.1";
        } else {
            $http_protocol_verison = "1.0";
        }

        if ($this->proxy != null)
    {
      // A Proxy needs the full qualified URL in the GET or POST headerline.
      $headerlines[] = $request_type." ".$this->UrlDescriptor->url_rebuild ." HTTP/1.0\r\n";
    }
    else
    {
      $query = $this->prepareHTTPRequestQuery($this->url_parts["path"].$this->url_parts["file"].$this->url_parts["query"]);
      $headerlines[] = $request_type." ".$query." HTTP/".$http_protocol_verison."\r\n";
    }
    
    $headerlines[] = "Host: ".$this->url_parts["host"]."\r\n";
    
    $headerlines[] = "User-Agent: ".str_replace("\n", "", $this->userAgentString)."\r\n";
    $headerlines[] = "Accept: */*\r\n";
    
    // Request GZIP-content
    if ($this->request_gzip_content == true)
    {
      $headerlines[] = "Accept-Encoding: gzip, deflate\r\n";
    }
    
    // Referer
    if ($this->UrlDescriptor->refering_url != null)
    {
      $headerlines[] = "Referer: ".$this->UrlDescriptor->refering_url."\r\n";
    }
    
    // Cookies
    $cookie_header = $this->buildCookieHeader();
    if ($cookie_header != null) {
            $headerlines[] = $this->buildCookieHeader();
        }

        // Authentication
    if ($this->url_parts["auth_username"] != "" && $this->url_parts["auth_password"] != "")
    {
      $auth_string = base64_encode($this->url_parts["auth_username"].":".$this->url_parts["auth_password"]);
      $headerlines[] = "Authorization: Basic ".$auth_string."\r\n";
    }
    
    // Proxy authentication
    if ($this->proxy != null && $this->proxy["proxy_username"] != null)
    {
      $auth_string = base64_encode($this->proxy["proxy_username"].":".$this->proxy["proxy_password"]);
      $headerlines[] = "Proxy-Authorization: Basic ".$auth_string."\r\n";
    }
    
    $headerlines[] = "Connection: close\r\n";
    
    // Wenn POST-Request
    if ($request_type == "POST")
    {
      // Post-Content bauen
      $post_content = $this->buildPostContent();
      
      $headerlines[] = "Content-Type: multipart/form-data; boundary=---------------------------10786153015124\r\n";
      $headerlines[] = "Content-Length: ".strlen($post_content)."\r\n\r\n";
      $headerlines[] = $post_content;
    }
    else
    {
      $headerlines[] = "\r\n";
    }

    return $headerlines;
  }
  
  /**
   * Prepares the given HTTP-query-string for the HTTP-request.
   *
   * HTTP-query-strings always should be utf8-encoded and urlencoded afterwards.
   * So "/path/file?test=tatütata" will be converted to "/path/file?test=tat%C3%BCtata":
   *
   * @param stirng The quetry-string (like "/path/file?test=tatütata")
   * @return string
   */
  protected function prepareHTTPRequestQuery($query)
  {
    // If string already is a valid URL -> do nothing
    if (PHPCrawlerUtils::isValidUrlString($query))
    {
      return $query;
    }
    
    // Decode query-string (for URLs that are partly urlencoded and partly not)
    $query = rawurldecode($query);
    
    // if query is already utf-8 encoded -> simply urlencode it,
    // otherwise encode it to utf8 first.
    if (PHPCrawlerUtils::isUTF8String($query) == true)
    {
      $query = rawurlencode($query);
    }
    else
    {
      $query = rawurlencode(utf8_encode($query));
    }
    
    // Replace url-specific signs back
    $query = str_replace("%2F", "/", $query);
    $query = str_replace("%3F", "?", $query);
    $query = str_replace("%3D", "=", $query);
    $query = str_replace("%26", "&", $query);
   
    return $query;
  }
  
  /**
   * Builds the post-content from the postdata-array for the header to send with the request (MIME-style)
   *
   * @return array  Numeric array containing the lines of the POST-part for the header
   */
  protected function buildPostContent()
  {
    $post_content = "";
    
    // Post-Data
    reset($this->post_data);
    while (list($key, $value) = each($this->post_data))
    {
      $post_content .= "-----------------------------10786153015124\r\n";
      $post_content .= "Content-Disposition: form-data; name=\"".$key."\"\r\n\r\n";
      $post_content .= $value."\r\n";
    }
    
    $post_content .= "-----------------------------10786153015124\r\n";
    
    return $post_content;
  }
  
  /**
   * Builds the cookie-header-part for the header to send.
   *
   * @return string  The cookie-header-part, i.e. "Cookie: test=bla; palimm=palaber"
   *                 Returns NULL if no cookies should be send with the header.
   */
  protected function buildCookieHeader()
  {
    $cookie_string = "";
    
    reset($this->cookie_array);
    while(list($key, $value) = each($this->cookie_array))
    {
      $cookie_string .= "; ".$key."=".$value."";
    }
    
    if ($cookie_string != "")
    {
      return "Cookie: ".substr($cookie_string, 2)."\r\n";
    }
    else
    {
      return null;
    }
  }
  
  /**
   * Checks whether the content of this page/file should be received (based on the content-type
   * and the applied rules)
   *
   * @param PHPCrawlerResponseHeader $responseHeader The response-header as an PHPCrawlerResponseHeader-object
   * @return bool TRUE if the content should be received
   */
  protected function decideRecevieContent(PHPCrawlerResponseHeader $responseHeader)
  {
    // Get Content-Type from header
    $content_type = $responseHeader->content_type;
    
    // No Content-Type given
    if ($content_type == null) {
            return false;
        }

        // Check against the given rules
    $receive = PHPCrawlerUtils::checkStringAgainstRegexArray($content_type, $this->receive_content_types);
    
    return $receive;
  }
  
  /**
   * Checks whether the content of this page/file should be streamed directly to file.
   *
   * @param string $response_header The response-header
   * @return bool TRUE if the content should be streamed to TMP-file
   */
  protected function decideStreamToFile($response_header)
  {
      if (count($this->receive_to_file_content_types) === 0) {
            return false;
        }

        // Get Content-Type from header
    $content_type = PHPCrawlerUtils::getHeaderValue($response_header, "content-type");
    
    // No Content-Type given
    if ($content_type === null) {
            return false;
        }

        // Check against the given rules
    $receive = PHPCrawlerUtils::checkStringAgainstRegexArray($content_type, $this->receive_to_file_content_types);
    
    return $receive;
  }
  
  /**
   * Adds a rule to the list of rules that decides which pages or files - regarding their content-type - should be received
   *
   * If the content-type of a requested document doesn't match with the given rules, the request will be aborted after the header
   * was received.
   *
   * @param string $regex The rule as a regular-expression
   * @return bool TRUE if the rule was added to the list.
   *              FALSE if the given regex is not valid.
   */
  public function addReceiveContentType($regex)
  {
    $check = PHPCrawlerUtils::checkRegexPattern($regex); // Check pattern
    
    if ($check == true)
    {
      $this->receive_content_types[] = trim(strtolower($regex));
    }
    return $check;
  }
  
  /**
   * Adds a rule to the list of rules that decides what types of content should be streamed diretly to the temporary file.
   *
   * If a content-type of a page or file matches with one of these rules, the content will be streamed directly into the temporary file
   * given in setTmpFile() without claiming local RAM.
   * 
   * @param string $regex The rule as a regular-expression
   * @return bool         TRUE if the rule was added to the list and the regex is valid.
   */
  public function addStreamToFileContentType($regex)
  {
    $check = PHPCrawlerUtils::checkRegexPattern($regex); // Check pattern
    
    if ($check == true)
    {
      $this->receive_to_file_content_types[] = trim($regex);
    }
    return $check;
  }
  
  /**
   * Sets the temporary file to use when content of found documents should be streamed directly into a temporary file.
   *
   * @param string $tmp_file The TMP-file to use.
   */
  public function setTmpFile($tmp_file)
  {
    //Check if writable
    $fp = @fopen($tmp_file, "w");
    
    if (!$fp)
    {
      return false;
    }
    else
    {
      fclose($fp);
      $this->tmpFile = $tmp_file;
      return true;
    }
  }
  
  /**
   * Sets the size-limit in bytes for content the request should receive.
   *
   * @param int $bytes
   * @return bool
   */
  public function setContentSizeLimit($bytes)
  {
      if (preg_match("#^[0-9]*$#", $bytes)) {
            $this->content_size_limit = $bytes;
            return true;
        } else {
            return false;
        }
    }
  
  /**
   * Returns the global traffic this instance of the HTTPRequest-class caused so far.
   *
   * @return int The traffic in bytes.
   */
  public function getGlobalTrafficCount()
  {
    return $this->global_traffic_count;
  }
  
  /**
   * Adds a rule to the list of rules that decide what kind of documents should get
   * checked for links in (regarding their content-type)
   *
   * @param string $regex Regular-expression defining the rule
   * @return bool         TRUE if the rule was successfully added
   */
  public function addLinkSearchContentType($regex)
  {
    $check = PHPCrawlerUtils::checkRegexPattern($regex); // Check pattern
    if ($check == true)
    {
      $this->linksearch_content_types[] = trim($regex);
    }
    return $check;
  }
  
  /**
   * Sets the http protocol version to use for requests
   *
   * @param int $http_protocol_version One of the PHPCrawlerHTTPProtocols-constants, or
   *                                   1 -> HTTP 1.0
   *                                   2 -> HTTP 1.1
   */
  public function setHTTPProtocolVersion($http_protocol_version)
  {
      if (preg_match("#[1-2]#", $http_protocol_version)) {
            $this->http_protocol_version = $http_protocol_version;
            return true;
        } else {
            return false;
        }
    }
  
  public function requestGzipContent($mode)
  {
    if (is_bool($mode))
    {
      $this->request_gzip_content = $mode;
    }
  }
}
