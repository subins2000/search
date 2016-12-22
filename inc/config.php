<?php 
/**
 * Configuration
 */

ini_set("display_errors", "on");

/**
 * Site URL
 * No '/' at the end
 */
define("HOST", "//search.sim");

$host = getenv('OPENSHIFT_MYSQL_DB_HOST');
$port = getenv('OPENSHIFT_MYSQL_DB_PORT');
$user = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
$pass = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
$db = getenv('OPENSHIFT_GEAR_NAME');
$dbh = new PDO('mysql:dbname='.$db.';host='.$host.';port='.$port, $user, $pass);
?>
