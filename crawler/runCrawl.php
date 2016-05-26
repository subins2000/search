<?php 
$dir=realpath(dirname(__FILE__));
$s="$dir/crawlStatus.txt";
$c=file_get_contents($s);
if($c==0){
 function execInbg($cmd) { 
    if (substr(php_uname(), 0, 7) == "Windows"){ 
        pclose(popen("start /B ". $cmd, "r"));  
    } 
    else { 
        exec($cmd . " > /dev/null &");   
    } 
 }
 execInbg("php -q $dir/bgCrawl.php");
 file_put_contents($s, 1);
 echo "Started Running";
}else{
 echo "Currently Running";
}
?>