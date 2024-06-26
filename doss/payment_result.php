<?php require_once('Connections/doss.php'); ?>
<?php date_default_timezone_set('Asia/Bangkok');?>
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

$maxRows_Rec_debtor = 1;
$pageNum_Rec_debtor = 0;
if (isset($_GET['pageNum_Rec_debtor'])) {
  $pageNum_Rec_debtor = $_GET['pageNum_Rec_debtor'];
}
$startRow_Rec_debtor = $pageNum_Rec_debtor * $maxRows_Rec_debtor;

$colname_Rec_debtor = "-1";
if (isset($_POST['textsearch'])) {
  $colname_Rec_debtor = $_POST['textsearch'];
}

$query1 = sprintf("SELECT paydebt.payDate,SUM(paydebt.amount) AS 'total_pay' 
FROM paydebt, debtor, receipt 
WHERE paydebt.receiptID = receipt.receiptID AND receipt.debtorID = debtor.debtorID AND receipt.debtorID =%s 
GROUP BY receipt.debtorID", 
GetSQLValueString($colname_Rec_debtor,"text"));

$query_receipt = sprintf("SELECT debtor.debtorID, debtor.FirstName, debtor.LastName,SUM(receipt.receiptOwe) AS 'total_owe',SUM(receipt.receiptLeft) AS 'total_left'
FROM receipt,debtor
WHERE receipt.debtorID = debtor.debtorID AND receipt.debtorID =%s
GROUP BY receipt.debtorID", 
GetSQLValueString($colname_Rec_debtor, "text"));

mysql_select_db($doss_connection, $doss);
$Rec_debtor1 = mysql_query($query1, $doss) or die(mysql_error());
$row_Rec_debtor1 = mysql_fetch_assoc($Rec_debtor1);

$Rec_receipt = mysql_query($query_receipt, $doss) or die(mysql_error());
$row_Rec_receipt = mysql_fetch_assoc($Rec_receipt);

if (isset($_GET['totalRows_Rec_debtor'])) {
  $totalRows_Rec_debtor = $_GET['totalRows_Rec_debtor'];
} else {
  $all_Rec_debtor = mysql_query($query1);
  $totalRows_Rec_debtor = mysql_num_rows($all_Rec_debtor);
}
$totalPages_Rec_debtor = ceil($totalRows_Rec_debtor/$maxRows_Rec_debtor)-1;

if (mysql_num_rows($Rec_debtor1) == 0 OR mysql_num_rows($Rec_receipt) == 0) {
  echo '<script>window.location.href = "search_payment.php";';
  echo 'alert("ไม่พบข้อมูลลูกหนี้ที่ต้องการ \nกรุณาเพิ่มการชำระหนี้หรือตรวจสอบรหัสลูกหนี้อีกครั้ง");</script>';
}
$Rec_receipt = mysql_query($query_receipt, $doss) or die(mysql_error());

//initialize the session
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
<title>ค้นหาจากรหัส : ประวัติการชำระหนี้</title>
<link rel="icon" type="image" href="img/logo_page.png">
<link href="css/navisbar.css" rel="stylesheet" type="text/css" />
<link href="css/payment_result.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<nav>
  <div class="wrapper">
    <div class="logo"><a href="home.php"><img src="img/logo.png" style="width: 120px; height: 33px; align-items: center;"></a></div>
    <input type="radio" name="slider" id="menu-btn">
    <input type="radio" name="slider" id="close-btn">
    <ul class="nav-links">
      <label for="close-btn" class="btn close-btn"><i class="fas fa-times"></i></label>
      <li><a href="home.php">Home</a></li>

      <li>
        <a class="desktop-item">ลูกหนี้</a>
        <input type="checkbox" id="showDrop">
        <label for="showDrop" class="mobile-item">
        <ul class="drop-menu">
          <li><a href="search_debtor_menu.php">ค้นหาข้อมูลลูกหนี้</a></li>
          <li><a href="add_debtor.php">เพิ่มข้อมูลลูกหนี้</a></li>
        </ul>
      </label>
      </li>
      <li>
        <a class="desktop-item">ค้างชำระ</a>
        <input type="checkbox" id="showDrop">
        <label for="showDrop" class="mobile-item"></label>
        <ul class="drop-menu">
          <li><a href="search_receipt.php">ค้นหาการค้างชำระ</a></li>
          <li><a href="add_receipt.php">เพิ่มการค้างชำระ</a></li>
        </ul>
      </li>

      <li>
        <a class="desktop-item">ชำระหนี้</a>
        <input type="checkbox" id="showDrop">
        <label for="showDrop" class="mobile-item"></label>
        <ul class="drop-menu">
          <li><a href="search_payment.php">ค้นหาประวัติการชำระหนี้</a></li>
          <li><a href="search_paydebt.php">เพิ่มข้อมูลการชำระหนี้</a></li>
        </ul>
      </li>

      <li>
        <a class="desktop-item">ออกรายงาน</a>
        <input type="checkbox" id="showDrop">
        <label for="showDrop" class="mobile-item"></label>
        <ul class="drop-menu">
          <li><a href="reportmonth.php">ออกรายงานประจำเดือน</a></li>
          <li><a href="reportyear.php">ออกรายงานประจำปี</a></li>
          <li><a href="reportcustom.php">ออกรายงานกำหนดเอง</a></li>
        </ul>
      </li>
      <li><a href="about_us.php">About Us</a></li>
      <li><a href="<?php echo $logoutAction ?>">ออกจากระบบ</a></li>
    </ul>
  </div>
</nav>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<center>
<h1>ผลการค้นหาประวัติการชำระหนี้<br>จากรหัสลูกหนี้</td></h1>
<font class="text">พบจำนวน&nbsp;<b><?php echo $totalRows_Rec_debtor ?> &nbsp;</b>รายการ</font>
<p>&nbsp;</p>
<table class="table_detail">
        <tr class="tr_head2">
          <td width="240">รหัสลูกหนี้</td>
        <td width="170" align="center">ชื่อ</td>
        <td width="170" align="center">นามสกุล</td>
        <td width="240" align="center">ยอดหนี้ทั้งหมด<br>(บาท)</td>
        <td width="240" align="center">ยอดการชำระ<br>(บาท)</td>
        <td width="240" align="center">ยอดคงเหลือทั้งหมด<br>(บาท)</td>
          <td width="172" align="center">รายละเอียด</td>
        </tr>
          <tr class="tr_center">
            <td><?php echo $row_Rec_receipt['debtorID']; ?></td>
            <td><?php echo $row_Rec_receipt['FirstName']; ?></td>
            <td><?php echo $row_Rec_receipt['LastName']; ?></td>
            <td><?php echo $row_Rec_receipt['total_owe']; ?></td>
            <td><?php echo $row_Rec_debtor1['total_pay']; ?></td>
            <td><?php echo $row_Rec_receipt['total_left']; ?></td>
            <td><a href="payment_history.php?cid=<?php echo $row_Rec_receipt['debtorID']; ?>"><img src="img/detail.png"></a></td>
          </tr>
          <?php $startRow_Rec_debtor++;?>
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
</body>
</html>
<?php
mysql_free_result($Rec_debtor1);
mysql_free_result($Rec_receipt);
?>
