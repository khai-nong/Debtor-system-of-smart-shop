<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
// Database connection parameters
$db_host = 'localhost';
$db_user = 'root';
$db_password = 'your_password';
$doss_connection = 'doss';

// Create a MySQL connection
$doss = mysql_connect($db_host, $db_user, $db_password);

if (!$doss) {
    die('Could not connect: ' . mysql_error());
}

// เลือกฐาน
// mysql_select_db($doss_connection, $doss);
// $Result1 = mysql_query($insertSQL, $doss) or die(mysql_error());