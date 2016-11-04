<?php
session_start();

$GLOBALS['q']=isset($_GET['q']) ? htmlspecialchars(urldecode(urlencode($_GET['q']))):"";
$GLOBALS['displayQ']=$GLOBALS['q'];
$GLOBALS['q']=strtolower($GLOBALS['q']);
$GLOBALS['p']=isset($_GET['p']) && is_numeric($_GET['p']) ? $_GET['p']:1;
$GLOBALS['dbh']=$dbh;

function htmlFilt($s){
 $s=str_replace("<", "&lt;", $s);
 $s=str_replace(">", "&gt;", $s);
 return $s;
}
function head($title="", $IncOtherCss=array()){
 $title=$title=="" ? "Web Search" : $title." - Web Search";
 /* Display The <title> tag */
 echo "<title>$title</title>";
 /* The Stylesheets */
 $cssFiles = array_merge(
  array(
   "all",
   "http://fonts.googleapis.com/css?family=Ubuntu"
  ),
  $IncOtherCss
 );
 foreach($cssFiles as $css){
  $url=preg_match("/http/", $css) ? $css : HOST."/cdn/css/$css.css";
  echo "<link href='".$url."' async='async' rel='stylesheet' />";
 }
 echo "<meta name='description' content=\"Search the world's information, webpages, problems and more. Find exactly what you're looking for easily without any ads and other distractions\"/>";
}
function headerElem(){ // header() is already a function in PHP
 $header = "<div class='header'><a class='logo' href='".HOST."'><strong>Web Search</strong></a><form method='GET' action='".HOST."/search.php' class='searchForm'><input id='query' type='text' placeholder='Your Query' autocomplete='off' name='q' value=\"".$GLOBALS['displayQ']."\"/><button><svg viewBox='0 0 100 100' class='shape-search'><use xlink:href='#shape-search'></use></svg></button></form></div>";
 echo $header;
}
function footer(){
 $footer = "<div class='footer'><a href='".HOST."/about'>About</a><a href='".HOST."/about/stats.php'>Stats</a><a href='".HOST."/about/bot.php'>Dingo</a><div style='float:right;'>&copy; Copyright <a target='_blank' href='http://subinsb.com'>Subin</a>".date("Y")."</div></div>";
 $footer.='
 <svg style="display:none;">
  <defs>
   <path id="shape-search" d="m 85.160239,99.375807 c -0.828634,-0.2952 -6.785463,-5.7653 -13.237403,-12.1558 l -11.730795,-11.6193 -6.6207,2.1766 C 33.39036,84.411907 12.627177,75.515007 3.6984912,56.407007 -5.6131124,36.479667 3.2485677,12.852077 23.649685,3.2119175 29.682607,0.36117746 31.404851,0.01130746 39.459783,5.746345e-5 50.03976,-0.01474254 56.477126,1.9699875 63.781566,7.4987375 77.935087,18.211537 83.541599,36.335507 77.964788,53.348307 l -2.173424,6.6304 11.744957,11.7927 c 9.455968,9.4945 11.857728,12.4888 12.323668,15.3642 1.319521,8.1432 -6.925821,15.008903 -14.69975,12.2402 z m -33.083916,-33.2366 c 5.656943,-2.5459 11.702601,-8.5732 14.216739,-14.1737 8.683318,-19.34281 -5.230473,-40.9032 -26.331076,-40.80178 -26.510022,0.12741 -38.6174499,32.4025 -18.836563,50.21308 2.774148,2.4979 7.069057,5.1647 9.656546,5.9963 5.992636,1.9257 15.497206,1.375 21.294354,-1.2339 z"></path>
  </defs>
 </svg>';
 include("track.php");
 echo $footer;
}

/* Results */
function getResults(){
 $q=$GLOBALS['q'];
 $p=$GLOBALS['p'];
 $start=($p-1)*10;
 if($q!=null){
  $starttime = microtime(true);
  $sql=$GLOBALS['dbh']->prepare("SELECT `title`, `url`, `description` FROM search WHERE `title` LIKE :q OR `url` LIKE :q OR `description` LIKE :q ORDER BY id");
  $sql->bindValue(":q", "%$q%");
  $sql->execute();
  $trs=$sql->fetchAll(PDO::FETCH_ASSOC);
  $endtime = microtime(true);
  if($sql->rowCount()==0 || $start>$sql->rowCount()){
   return 0;
  }else{
   $duration = $endtime - $starttime;
   $res=array();
   $res['count']=$sql->rowCount();
   $res['time']=round($duration, 4);
   $limitedResults=array_slice($trs, $start, 10);
   foreach($limitedResults as $r){
    $res["results"][]=array($r['title'], $r['url'], $r['description']);
   }
   return $res;
  }
 }
}
?>
