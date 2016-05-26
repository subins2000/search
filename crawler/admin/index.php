<?php $index="";include("login.php");?>
<?php 
if(isset($_POST['username']) && isset($_POST['password'])){
 $ushahs="b0aed579336ab36608c0550f3711aff41b3de63e7af6c1607012e0ff84b0c216bc0bfe4ef1c0d64ed6982e54fbb30a58f59785558149bb6bbe9f35a9b2538ae3";
 $hashed="688148fd94e2b1308ceca027f91b667458a30a8ad4964e188cd0f16fbc82227a8d9e7277dcb5799a38e1e182ccfa8d77ba3c7db05c675767b79e06190d4ce4c2";
 if(hash("sha512", $_POST['password'])==$hashed && hash("sha512", $_POST['username'])==$ushahs){
  $_SESSION['itsok']=1;
  header("Location: home.php");
 }else{
  echo "Not Ok";
 }
}
?>
<form action="" method="POST">
 <input type="text" name="username"/>
 <input type="password" name="password"/>
 <button>Login</button>
</form>