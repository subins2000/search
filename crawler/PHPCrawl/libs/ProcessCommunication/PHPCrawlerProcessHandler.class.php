<?php 
/**
 * Class containing methods for process handling and communication
 *
 * @package phpcrawl
 * @internal
 */
class PHPCrawlerProcessHandler
{
  protected $crawler_uniqid;
  
  protected $working_directory;
  
  /**
   * Initiates a new PHPCrawlerProcessHandler-object.
   *
   * @param string $crawler_uniqid     UID of the crawler
   * @param string $working_directory  Working-dir of the crawler
   */ 
  public function __construct($crawler_uniqid, $working_directory)
  {
    $this->crawler_uniqid = $crawler_uniqid;
    $this->working_directory = $working_directory;
    
    $this->crawlerStatus = new PHPCrawlerStatus();
  }
  
  /**
   * Registers the PID of a child-process
   *
   * @param int The PID
   */
  public function registerChildPID($pid)
  {
    $sem_key = sem_get($this->crawler_uniqid);
    sem_acquire($sem_key);
    
    file_put_contents($this->working_directory."pids", $pid."\n", FILE_APPEND);
    
    sem_release($sem_key);
  }
  
  /**
   * Returns alls PIDs of all running child-processes
   *
   * @param int $process_count If set, this function tries to get the child-PIDs until the gievn number of PIDs
   *                           was determinated.
   * @return array Numeric array conatining the PIDs
   */
  public function getChildPIDs($process_count = null)
  { 
    $child_pids = array();
    $try = true;
    
    while ($try == true)
    {
      if (file_exists($this->working_directory."pids"))
      {
        $ct = file_get_contents($this->working_directory."pids");
        $child_pids = preg_split("#\n#", $ct, -1, PREG_SPLIT_NO_EMPTY);
        
        if ($process_count == null) $try = false;
        if (count($child_pids) == $process_count) $try = false;
      }
      
      usleep(200000);
    }
    
    return $child_pids;
    
  }
  
  /**
   * Kills all running child-processes
   */
  public function killChildProcesses()
  {
    $child_pids = $this->getChildPIDs();
    for ($x=0; $x<count($child_pids); $x++)
    {
      posix_kill($child_pids[$x], SIGKILL);
    }
  }
  
  /**
   * Checks wehther any child-processes a (still) running.
   *
   * @return bool
   */
  public function childProcessAlive()
  {
    $pids = $this->getChildPIDs();
    $cnt = count($pids);
    
    for ($x=0; $x<$cnt; $x++)
    {
      if (posix_getsid($pids[$x]) != false)
      {
        return true;
      }
    }
    
    return false;
  }
}
?>