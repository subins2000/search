<?php 
/**
 * Describes the current status of an crawler-instance.
 *
 * @package phpcrawl
 * @internal
 */
class PHPCrawlerStatus
{
  /**
   * Number of total bytes the crawler-instance received so far
   */
  public $bytes_received = 0;
  
  /**
   * Number of links the crawler-instance followed so far
   */
  public $links_followed = 0;
  
  /**
   * Number of documents the crawler-instance received so far
   */
  public $documents_received = 0;
  
  /**
   * Abort reason for aborting the crawling-process.
   *
   * @var int One of the PHPCrawlerAbortReasons-contants or NULL if the process shouldn't
   *          get aborted yet.
   */
  public $abort_reason = null;
  
  public $first_content_url = null;
  
  /**
   * Total time the crawler processes spend to connecting to server(s)
   */
  public $sum_server_connect_time = 0;
  
  /**
   * Total number of established server connects
   */
  public $sum_server_connects = 0;
  
  /**
   * Total time the crawler processes waited for server responses
   */
  public $sum_server_response_time = 0;
  
  /**
   * Total number of server responses
   */
  public $sum_server_responses = 0;
  
  /**
   * Total time the crawler processes spend with receiving data from server(s)
   */
  public $sum_data_transfer_time = 0;
  
  /**
   * Number of unbuffered bytes read (benchmark)
   */
  public $unbuffered_bytes_read = 0;
  
  public $last_request_time = null;
}
?>