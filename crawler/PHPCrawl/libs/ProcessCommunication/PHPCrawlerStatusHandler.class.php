<?php 
/**
 * Class for reading and writing the PHPCrawlerStatus
 *
 * @package phpcrawl
 * @internal
 */
class PHPCrawlerStatusHandler
{
  protected $crawlerStatus;
  protected $crawler_uniqid;
  protected $working_directory;
  
  /**
   * Flag indicating whether the crawler status-information should be written to a file
   */
  public $write_status_to_file = false;
  
  /**
   * Flag indicating whether updates to the crawler status-information should get semaphore-locked
   */
  public $lock_status_updates = false;
  
  /**
   * Initiates a new PHPCrawlerStatusHandler
   * @param string $crawler_uniqid     UID of the crawler
   * @param string $working_directory  Working-directory of the crawler
   */
  public function __construct($crawler_uniqid, $working_directory)
  {
    $this->crawler_uniqid = $crawler_uniqid;
    $this->working_directory = $working_directory;
    
    $this->crawlerStatus = new PHPCrawlerStatus();
  }
  
  /**
   * Returns/reads the current crawler-status
   *
   * @return PHPCrawlerStatus The current crawlerstatus as a PHPCrawlerStatus-object
   */
  public function getCrawlerStatus()
  {
    // Get crawler-status from file
    if ($this->write_status_to_file == true)
    {
      $this->crawlerStatus = PHPCrawlerUtils::deserializeFromFile($this->working_directory."crawlerstatus.tmp");
      if ($this->crawlerStatus == null) {
                $this->crawlerStatus = new PHPCrawlerStatus();
            }
        }
    
    return $this->crawlerStatus;
  }
  
  /**
   * Sets/writes the current crawler-status
   *
   * @param PHPCrawlerStatus $crawler_status The status to set
   */
  public function setCrawlerStatus(PHPCrawlerStatus $crawler_status)
  {
    $this->crawlerStatus = $crawler_status;
    
    // Write crawler-status back to file
    if ($this->write_status_to_file == true)
    {
      PHPCrawlerUtils::serializeToFile($this->working_directory."crawlerstatus.tmp", $crawler_status);
    }
  }
  
  /**
   * Updates the status of the crawler
   *
   * @param PHPCrawlerDocumentInfo $PageInfo          The PHPCrawlerDocumentInfo-object of the last received document
   *                                                  or NULL if no document was received.
   * @param int                    $abort_reason      One of the PHPCrawlerAbortReasons::ABORTREASON-constants if the crawling-process
   *                                                  should get aborted, otherwise NULL
   * @param string                 $first_content_url The first URL some content was found in (or NULL if no content was found so far).
   */
  public function updateCrawlerStatus($PageInfo, $abort_reason = null, $first_content_url = null, $last_request_time = null)
  {
    PHPCrawlerBenchmark::start("updating_crawler_status");
    
    // Set semaphore/lock if
    if ($this->lock_status_updates == true)
    {
      $sem_key = sem_get($this->crawler_uniqid);
      sem_acquire($sem_key);
    }
    
    // Get current Status
    $crawler_status = $this->getCrawlerStatus();
    
    // Update status
    if ($PageInfo != null)
    {
      // Increase number of followed links
      $crawler_status->links_followed++;
      
      // Increase documents_received-counter
      if ($PageInfo->received == true) {
                $crawler_status->documents_received++;
            }

            // Increase bytes-counter
      $crawler_status->bytes_received += $PageInfo->bytes_received + $PageInfo->header_bytes_received;
      
      // Benchmarks
      if ($PageInfo->error_occured == false)
      {
        // server connect time
        $crawler_status->sum_server_connect_time += $PageInfo->server_connect_time;
        $crawler_status->sum_server_connects++;
        
        // server response time
        $crawler_status->sum_server_response_time += $PageInfo->server_response_time;
        $crawler_status->sum_server_responses++;
        
        // data transfer time
        $crawler_status->sum_data_transfer_time += $PageInfo->data_transfer_time;
        
        // unbuffered bytes read
        $crawler_status->unbuffered_bytes_read += $PageInfo->unbuffered_bytes_read;
      }
    }
    
    // Set abortreason
    if ($abort_reason !== null) {
            $crawler_status->abort_reason = $abort_reason;
        }

        // Set first_content_url
        if ($first_content_url !== null) {
            $crawler_status->first_content_url = $first_content_url;
        }

        // Set last request-time
        if ($last_request_time !== null) {
            $crawler_status->last_request_time = $last_request_time;
        }

        // Write crawler-status back
    $this->setCrawlerStatus($crawler_status);
    
    // Remove semaphore/lock
    if ($this->lock_status_updates == true)
    {
      sem_release($sem_key);
    }
    
    PHPCrawlerBenchmark::stop("updating_crawler_status");
  }
}
