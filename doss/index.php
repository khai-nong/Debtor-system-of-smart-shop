<?php require_once('Connections/doss.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['txtusername2'])) {
  $loginUsername=$_POST['txtusername2'];
  $password=$_POST['txtpw2'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "home.php";
  $MM_redirectLoginFailed = "index.php";
  $MM_redirecttoReferrer = false;
  mysql_select_db($doss_connection, $doss);
  
  $LoginRS__query=sprintf("SELECT username, password FROM `user` WHERE username=%s AND password=%s",
    GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $doss) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
     $loginStrGroup = "";
    
	if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login</title>
<link rel="icon" type="image" href="img/Login_Tab_Icon.png">
<link href="css/login_design.css" rel="stylesheet" type="text/css" />
</head>

<body>
<center><div class="topic">เข้าสู่ระบบ</div>
<form id="form1" name="form1" method="POST" action="<?php echo $loginFormAction; ?>">
<table>
    <tr>
        <td rowspan="7" align="center"><img src="img/home.png" class="home"/></td>
    </tr>
    <tr>
        <td height="90" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td id="name" height="10">USERNAME&nbsp;&nbsp; : &nbsp;&nbsp;</td>
        <td><input type="text" name="txtusername2" id="txtusername2" /></td>
    </tr>
    <tr>
        <td height="10" colspan="2">&nbsp;</td>
    </tr
    <tr>
         <td id="name" height="10">PASSWORD&nbsp;&nbsp; : &nbsp;&nbsp;</td>
        <td><input type="password" name="txtpw2" id="txtpw" maxlength="8"/></td>
    </tr>
    <tr>
        <td colspan="2" align="center"><input type="submit" name="button3" id="button3" value=" เข้าสู่ระบบ " class="button-9"/>
    </tr>
  </table>
</center>
  <p>&nbsp;</p>
</form>
</body>
</html>