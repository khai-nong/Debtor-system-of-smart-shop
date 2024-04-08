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

$currentPage = $_SERVER["PHP_SELF"];

$colname_Rec_customer = "-1";
if (isset($_GET['cid'])) {
  $colname_Rec_customer = $_GET['cid'];
}
mysql_select_db($doss_connection, $doss);
$query_Rec_customer = sprintf("SELECT debtorID, FirstName, LastName
FROM debtor
WHERE debtorID = %s
GROUP BY debtorID" , GetSQLValueString($colname_Rec_customer, "int"));
$Rec_customer = mysql_query($query_Rec_customer, $doss) or die(mysql_error());
$row_Rec_customer = mysql_fetch_assoc($Rec_customer);
$totalRows_Rec_customer = mysql_num_rows($Rec_customer);

$maxRows_Rec_orders = 20;
$pageNum_Rec_orders = 0;
if (isset($_GET['pageNum_Rec_orders'])) {
  $pageNum_Rec_orders = $_GET['pageNum_Rec_orders'];
}
$startRow_Rec_orders = $pageNum_Rec_orders * $maxRows_Rec_orders;

$colname_Rec_orders = "-1";
if (isset($_GET['cid'])) {
  $colname_Rec_orders = $_GET['cid'];
}
mysql_select_db($doss_connection, $doss);
$query_Rec_orders = sprintf("SELECT * FROM paydebt,debtor,receipt 
WHERE (paydebt.receiptID = receipt.receiptID AND receipt.debtorID = debtor.debtorID) AND debtor.debtorID = %s 
ORDER BY paydebt.payDate DESC", GetSQLValueString($colname_Rec_orders, "int"));
$query_limit_Rec_orders = sprintf("%s LIMIT %d, %d", $query_Rec_orders, $startRow_Rec_orders, $maxRows_Rec_orders);
$Rec_orders = mysql_query($query_limit_Rec_orders, $doss) or die(mysql_error());
$row_Rec_orders = mysql_fetch_assoc($Rec_orders);


$query1 = sprintf("SELECT paydebt.payDate, debtor.debtorID, debtor.FirstName, debtor.LastName,SUM(paydebt.amount) AS 'total_pay' 
FROM paydebt, debtor, receipt 
WHERE paydebt.receiptID = receipt.receiptID AND receipt.debtorID = debtor.debtorID AND debtor.debtorID = %s
ORDER BY paydebt.payDate DESC", 
GetSQLValueString($colname_Rec_customer, "text"));

$query_receipt = sprintf("SELECT SUM(receipt.receiptPay) AS 'total_receiptpay',SUM(receipt.receiptOwe) AS 'total_owe',SUM(receipt.receiptLeft) AS'total_left',COUNT(receipt.receiptID) AS 'count_receipt'
FROM receipt 
WHERE receipt.debtorID = %s
GROUP BY receipt.debtorID", 
GetSQLValueString($colname_Rec_customer, "text"));

mysql_select_db($doss_connection, $doss);
$Rec_debtor1 = mysql_query($query1, $doss) or die(mysql_error());
$row_Rec_debtor1 = mysql_fetch_assoc($Rec_debtor1);
$Rec_receipt = mysql_query($query_receipt, $doss) or die(mysql_error());
$row_Rec_receipt = mysql_fetch_assoc($Rec_receipt);


if (isset($_GET['totalRows_Rec_orders'])) {
  $totalRows_Rec_orders = $_GET['totalRows_Rec_orders'];
} else {
  $all_Rec_orders = mysql_query($query_Rec_orders);
  $totalRows_Rec_orders = mysql_num_rows($all_Rec_orders);
}
$totalPages_Rec_orders = ceil($totalRows_Rec_orders/$maxRows_Rec_orders)-1;


$queryString_Rec_orders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_Rec_orders") == false && 
        stristr($param, "totalRows_Rec_orders") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_Rec_orders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_Rec_orders = sprintf("&totalRows_Rec_orders=%d%s", $totalRows_Rec_orders, $queryString_Rec_orders);

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
<title>ประวัติการชำระ</title>
<link rel="icon" type="image" href="img/logo_page.png">
<link href="css/navisbar.css" rel="stylesheet" type="text/css" />
</head>
<style>
body{
    font-family: 'LINE Seed Sans TH';
    margin: 0 auto;
    background: #E8E8E8; 
}
h1{
    color: #000;
    text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    font-size: 73px;
    font-family: 'LINE Seed Sans TH';
    font-weight: 310;
    line-height: normal;
    text-align: center;
}

.table_detail{ /**/
    width: 80%;
    height: 100px;
    text-align:left;
    border-radius: 10px;
    border-collapse: collapse;
    background: rgba(56, 182, 255, 0.65);
    
}

.tr_head2{ /**/
    color: #000000;
    text-align: center;
    border-radius: 10px;
    height: 40px;
    font-size: 18px;
    border-collapse: collapse;
    border-color: rgb(23, 23, 23);
}

.tr_center{ /**/
    color: #000000;
    text-align: center;
    border-radius: 10px;
    font-size: 17px;
    border-radius: 6px;
    background: #ffffff;
    box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
}

.text{
    color: #000;
    text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    font-size: 22px;
    font-family: 'LINE Seed Sans TH';
    line-height: normal;
    text-align: center;
}

</style>

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
<br>
<center>
<h1 class="topic">ประวัติการชำระหนี้</h1>
<table>
<tr class="text">
    <td width="100">รหัสลูกหนี้</td>
    <td style="color: #007CEF;" width="70" align="center"><?php echo $row_Rec_customer['debtorID'];?></td>
    <td width="70">ชื่อ </td>
    <td style="color: #007CEF;" width="150">&nbsp;<?php echo $row_Rec_customer['FirstName'];?>&nbsp;</td>
    <td width="70">นามสกุล </td>
    <td style="color: #007CEF;" width="150">&nbsp;<?php echo $row_Rec_customer['LastName'];?>&nbsp;</td>
  </tr>
</table>
<br>
<table class="text">
  <tr class="head" align="left">
    <td align="left">จำนวนการค้างชำระทั้งหมด</td>
    <td align="center" style="color: #007CEF;" width="120"><?php echo $row_Rec_receipt['count_receipt']; ?></td>
    <td width="60">&nbsp;&nbsp;ครั้ง</td>
  </tr>
  <tr class="head">
    <td align="left" width="250">ยอดหนี้รวมทั้งหมด</td>
    <td align="center" width="120" style="color: #007CEF;"><?php echo $row_Rec_receipt['total_owe']; ?></td>
    <td width="60">บาท</td>
  </tr>
  <tr class="head">
    <td align="left">ยอดชำระรวมทั้งหมด</td>
    <td align="center" style="color: #007CEF;" width="120"><?php echo $row_Rec_debtor1['total_pay']; ?></td>
    <td width="60">บาท</td>
  <tr class="head" align="left">
    <td align="left">ยอดมัดจำรวมทั้งหมด</td>
    <td align="center" style="color: #007CEF;" width="120"><?php echo ($row_Rec_receipt['total_receiptpay'] == 0) ? '0.00' : $row_Rec_receipt['total_receiptpay']; ?></td>
    <td width="60">&nbsp;&nbsp;บาท</td>
  <tr class="head" align="left">
    <td align="left">ยอดคงเหลือรวมทั้งหมด</td>
    <td align="center" style="color: #007CEF;" width="120"><?php echo $row_Rec_receipt['total_left']; ?></td>
    <td width="60">&nbsp;&nbsp;บาท</td>
  </tr>
</table>
<p>&nbsp;</p>
<table class="table_detail">
  <tr class="tr_head2">
        <td width="270" align="center">วัน/เดือน/ปีที่มาชำระ</td>
        <td width="170" align="center">รหัสการค้างชำระ</td>
        <td width="170" align="center">รหัสการชำระ</td>
        <td width="110">ครั้งที่ชำระ</td>
        <td width="200" align="center">ยอดหนี้ทั้งหมด<br>(บาท)</td>
        <td width="200" align="center">ยอดการชำระ<br>(บาท)</td>
        <td width="200" align="center">ยอดคงเหลือทั้งหมด<br>(บาท)</td>
        <td width="250" align="center">หมายเหตุ</td>
  </tr>
        <?php do { ?>
          <tr class="tr_center">
            <td><?php $dd=$row_Rec_orders['payDate']; echo date("d-m-Y  H:i",strtotime("$dd")); ?></td>
            <td><?php echo $row_Rec_orders['receiptID']; ?></td>
            <td><?php echo $row_Rec_orders['payID']; ?></td>
            <td><?php echo $row_Rec_orders['ordinalPay']; ?></td>
            <td><?php echo $row_Rec_orders['debt']; ?></td>
            <td><?php echo $row_Rec_orders['amount']; ?></td>
            <td><?php echo $row_Rec_orders['unpaiddebt']; ?></td>
            <td><?php echo $row_Rec_orders['note']; ?></td>
          </tr>
          <?php $startRow_Rec_orders++;} while ($row_Rec_orders = mysql_fetch_assoc($Rec_orders)); ?>
        <tr>
          <td colspan="8" align="center" style="background-color: #2B90ED; border-radius: 10px;" >
          <span class="text" style="color: #FFFFFF;">รวมทั้งสิ้น <?php echo $totalRows_Rec_orders; ?> รายการ</span></td>

        </tr>
      </table></td>
  </tr>
</table>
<p>&nbsp;</p>

</center>
</body>
</html>
<?php
mysql_free_result($Rec_customer);
mysql_free_result($Rec_orders);
mysql_free_result($Rec_receipt);
?>
