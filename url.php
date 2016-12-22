<?php 
$url=isset($_GET['u']) ? urldecode($_GET['u']):"";
if(filter_var($url, FILTER_VALIDATE_URL) === FALSE || $url==""){
 header("Location: http://".$_SERVER['HTTP_HOST'], 302);
 exit;
}else{
?>
 <html>
  <head>
   <noscript><META http-equiv="refresh" content="0;URL=<?php echo$url;?>"></noscript>
   <script>window.location.replace("<?php echo$url;?>");</script>
  </head>
  <body></body>
 </html>
<?php 
}
?>
