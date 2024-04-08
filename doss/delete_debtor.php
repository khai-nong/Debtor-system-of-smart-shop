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
//สร้าง recordset ของ detail_orders
$colname_Rec_debtorID = "-1";
if (isset($_GET['did'])) {
  $colname_Rec_debtorID = $_GET['did'];
}

mysql_select_db($doss_connection, $doss);
$query_Rec_debtorID = sprintf("SELECT debtorID FROM debtor WHERE debtorID = %s", GetSQLValueString($colname_Rec_detail_orders, "int"));
$Rec_debtorID = mysql_query($query_Rec_debtorID, $doss) or die(mysql_error());
$row_Rec_debtorID = mysql_fetch_assoc($Rec_debtorID);
$totalRows_Rec_debtorID = mysql_num_rows($Rec_debtorID);
//****

//ตรวจสอบการอ้างอิงในตาราง detail_orders ถ้ามีการอ้างอิงอยู่จะไม่ลบข้อมูลจากตาราง product
if ($totalRows_Rec_debtorID == 0) {
  if ((isset($_GET['did'])) && ($_GET['did'] != "")) {
      // Check if there are related records in the "receipt" table
      $checkReceiptSQL = sprintf("SELECT COUNT(*) FROM receipt WHERE debtorID=%s",
          GetSQLValueString($_GET['did'], "int"));
      
      mysql_select_db($doss_connection, $doss);
      $checkResult = mysql_query($checkReceiptSQL, $doss) or die(mysql_error());

      $row = mysql_fetch_row($checkResult);
      $relatedReceipts = $row[0];

      if ($relatedReceipts > 0) {
          echo "<center><h3>ไม่สามารถลบข้อมูลได้ เนื่องจากมีการอ้างอิงอยู่ในตาราง receipt</h3>";
          echo "<a href='javascript:history.back(-1)'>กลับหน้าเดิม</a></center>";
      } else {
          // No related records found, proceed with deletion
          $deleteSQL = sprintf("DELETE FROM debtor WHERE debtorID=%s",
              GetSQLValueString($_GET['did'], "int"));

          $Result1 = mysql_query($deleteSQL, $doss) or die(mysql_error());

          if (isset($_SERVER['QUERY_STRING'])) {
              $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
              $deleteGoTo .= $_SERVER['QUERY_STRING'];
              echo '<script>';
              echo 'alert("ลบข้อมูลสำเร็จ กรุณากดปุ่มเพื่อดำเนินการต่อ");';
              echo 'window.history.back();;';
              echo '</script>';
          }
      }
  }
}

if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ไม่สามารถลบข้อมูลได้</title>
<script>
alert("ไม่สามารถลบข้อมูลได้เนื่องจากมีการอ้างอิงอยู่");
window.location.href = "edit_menu_alldebtor.php";
</script>
</head>
<body>
</body>
</html>
