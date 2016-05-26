<?php 
/**
 * Contains summarizing information about a crawling-process after the process is finished.
 *
 * @package phpcrawl
 */
class PHPCrawlerProcessReport
{
  /**
   * The total number of links/URLs the crawler found and followed.
   *
   * @var int
   * @section 1 General
   */
  public $links_followed = 0;
  
  /**
   * The total number of documents the crawler received.
   *
   * @var int
   * @section 1 General
   */
  public $files_received = 0;
  
  /**
   * The total number of bytes the crawler received alltogether.
   *
   * @var int
   * @section 1 General
   */
  public $bytes_received = 0;
  
  /**
   * The total time the crawling-process was running in seconds.
   *
   * @var float Proess-runtime in seconds.
   * @section 10 Benchmarks
   */
  public $process_runtime = 0;
  
  /**
   * The total data-throughput of the crawler
   *
   * @var float The rate in bytes/second
   * @section 10 Benchmarks
   */
  public $data_throughput = 0;
  
  /**
   * Will be TRUE if the crawling-process stopped becaus the traffic-limit was reached.
   *
   * @var bool
   * @section 1 General
   */
  public $traffic_limit_reached = false;
  
  /**
   * Will be TRUE if the page/file-limit was reached.
   *
   * @var bool
   * @section 1 General
   */
  public $file_limit_reached = false;
  
  /**
   * Will be TRUE if the crawling-process stopped because the overridable function handleDocumentInfo() returned a negative value.
   *
   * @var bool
   * @section 1 General
   */
  public $user_abort = false;
  
  /**
   * The peak memory-usage the crawling-process caused.
   *
   * @var int Memory-usage in bytes. May be NULL if PHP-version is lower than 5.2.0. 
   * @section 1 General
   */
  public $memory_peak_usage;
  
  /**
   * Reason for the abortion of the crawling-process
   *
   * @var int One of the {@link PHPCrawlerAbortReasons}-constants
   * @section 1 General
   */
  public $abort_reason;
  
  /**
   * The average server connect-time.
   *
   * @var int The time in seconds and milliseconds or NULL if not a single connection cluld be established.
   * @section 10 Benchmarks
   */
  public $avg_server_connect_time;
  
  /**
   * The average server response time.
   *
   * @var int The time in seconds and milliseconds or NULL if didn't responde a single time
   * @section 10 Benchmarks
   */
  public $avg_server_response_time;
  
  /**
   * The average data-transfer-rate per process
   *
   * @var float The rate in bytes per second.
   * @section 10 Benchmarks
   */
  public $avg_proc_data_transfer_rate;
  
  /**
   * Returns an array with all properties of this class.
   *
   * @return array
   * @internal
   */
  public function toArray()
  {
    return get_object_vars($this);
  }
}
?>