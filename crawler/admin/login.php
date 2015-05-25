<?php 
session_start();
if(isset($_SESSION['itsok']) && !isset($home)){
 header("Location: home.php");
 exit;
}elseif(!isset($index) && !isset($_SESSION['itsok'])){
 header("Location: index.php");
 exit;
}
?>
