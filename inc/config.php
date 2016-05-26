<?php 
/* Configuration */
ini_set("display_errors", "on"); // Do you want to see the errors ?
define("HOST", "http://local.dev/search/local"); // No '/' at the end
if(preg_match("/\.dev/", HOST)){
 $host = "localhost"; // Hostname
 $port = "3306"; // MySQL Port; Default : 3306
 $user = "root"; // Username Here
 $pass = "backstreetboys"; // Password Here
 $db   = "test"; // Database Name
 $dbh  = new PDO('mysql:dbname='.$db.';host='.$host.';port='.$port, $user, $pass);
}else{
 $host=getenv('OPENSHIFT_MYSQL_DB_HOST');
 $port=getenv('OPENSHIFT_MYSQL_DB_PORT');
 $user=getenv('OPENSHIFT_MYSQL_DB_USERNAME');
 $pass=getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
 $db=getenv('OPENSHIFT_GEAR_NAME');
 $dbh  = new PDO('mysql:dbname='.$db.';host='.$host.';port='.$port, $user, $pass);
}
/* End Configuration */

/* Replace the lines 5 to 19 with the following and change the credentials as you like :

 $host = "localhost"; // Hostname
 $port = "3306"; // MySQL Port; Default : 3306
 $user = "root"; // Username Here
 $pass = "backstreetboys"; // Password Here
 $db   = "search"; // Database Name
 $dbh  = new PDO('mysql:dbname='.$db.';host='.$host.';port='.$port, $user, $pass);

 */
?>
