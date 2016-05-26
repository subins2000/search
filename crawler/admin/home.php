<?php $home="";include("login.php");?>
<form method="POST">
 <input type="text" name="url[]" placeholder="URL1" size="50"/><br/>
 <input type="text" name="url[]" placeholder="URL2" size="50"/><br/>
 <input type="text" name="url[]" placeholder="URL3" size="50"/><br/>
 <input type="text" name="url[]" placeholder="URL4" size="50"/><br/>
 <button>Crawl</button>
</form>
<?php 
if(isset($_POST['url']) && array_search("", $_POST['url'])===false){
 $crawlToken=418941;
 $url4Array=$_POST['url'];
 print_r($url4Array);
 include("../crawl.php");
}
?>
