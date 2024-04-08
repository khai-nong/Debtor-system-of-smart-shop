<?php date_default_timezone_set("Asia/bangkok");?>
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

if (isset($_GET['receipt'])) {
  $colname_Rec_receipt = $_GET['receipt'];
}


mysql_select_db($doss_connection, $doss);
$query_Rec_receipt = sprintf("SELECT paydebt.payDate,paydebt.ordinalPay, paydebt.amount, paydebt.unpaiddebt, receipt.mustpay, paydebt.unpaiddebt
    FROM paydebt, receipt
    WHERE receipt.receiptID = paydebt.receiptID AND receipt.receiptID = %s
    ORDER BY paydebt.ordinalPay DESC", GetSQLValueString($colname_Rec_receipt, "text"));

$Rec_receipt = mysql_query($query_Rec_receipt, $doss) or die(mysql_error());
$row_Rec_receipt = mysql_fetch_assoc($Rec_receipt);
$totalRows_Rec_receipt = mysql_num_rows($Rec_receipt);

$query_Rec_debtor = sprintf("SELECT debtor.debtorID, debtor.FirstName,debtor.LastName, debtor.Addr, debtor.tell, receipt.receiptOwe,receipt.receiptID, collection.perroundID,collection.perroundName
    FROM debtor, receipt, `collection`
    WHERE debtor.debtorID = receipt.debtorID AND collection.perroundID = receipt.perroundID
    AND receipt.receiptID = %s", GetSQLValueString($colname_Rec_receipt, "text"));

$Rec_debtor = mysql_query($query_Rec_debtor, $doss) or die(mysql_error());
$row_Rec_debtor = mysql_fetch_assoc($Rec_debtor);
$totalRows_Rec_debtor = mysql_num_rows($Rec_debtor);

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
<title>ผลการออกรายงาน : ใบเสร็จการชำระ</title>
<link rel="icon" type="image" href="img/logo_page.png">
<link href="css/report_result_design.css" rel="stylesheet" type="text/css" />
<style>
  nav{
  z-index: 99;
  width: 100%;
  height: 65px;
  background: #FFF;
  box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
  margin: 0;
  padding: 0;
  font-family: 'LINE Seed Sans TH';
}

nav .wrapper{
  max-width: 1350px; /*ปรับความยาว*/
  padding: 0px 5px;
  height: 65px;
  line-height: 70px;
  margin: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

nav li:hover {
  background: rgba(152, 152, 152, 0.60);
  transition: all 0.2s ease;
}

.wrapper .logo a{
  color: #f2f2f2;
  font-size: 60px;
  font-weight: 600;
  text-decoration: none;
}

.wrapper .nav-links{
  display: inline-flex;
}
.nav-links li{
  list-style: none;
}
.nav-links li a{
  color: #000000;
  text-decoration: none;
  font-size: 18px;
  font-weight: 500;
  padding: 21px 20px;
  border-radius: 3px;
  
}
.nav-links li a:hover{
  background: rgba(152, 152, 152, 0.60);
    width: 185px;
}

.nav-links .drop-menu{
  position: absolute;
  background: #FEFEFE;
  line-height: 45px;
  opacity: 0;
  margin: 0;
  padding: 0;
  box-shadow: 0 6px 10px rgba(0,0,0,0.15);
}
.nav-links li:hover .drop-menu{
  transition: all 0.3s ease;
  opacity: 1;
  width: 200px;
}
.drop-menu li a{
  width: 100%;
  display: block;
  padding: 0 0 0 15px;
  font-weight: 400;
  border-radius: 0px;
}
.wrapper .btn{
  color: #fff;
  font-size: 20px;
  cursor: pointer;
  display: none;
}
.wrapper .btn.close-btn{
  position: absolute;
  right: 30px;
  top: 10px;
}

nav input{
  display: none;
}

img#pdf-only {
    display: none;
}
@media print {
    nav {
        display: none;
    }
    .button-9 {
        display: none;
    }
    .table_detail2{
      width: 800px;
    }
    img#pdf-only {
      display: block;
    }  
}


</style>
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

<center>
<p></p>
<p></p>
<img id="pdf-only" src="img/logo.png" style="width: 120px; height: 33px;">
<p></p>
<h1>ใบเสร็จการชำระหนี้</h1>
<font face="TH Sarabun New">ออก ณ วันที่ <?php echo date("d/m/y/ H:i:s",strtotime("now"))?></font>
<table class="table_record">
  <tr>
    <td colspan="4">รหัสการค้างชำระ : <?php echo $row_Rec_debtor['receiptID']; ?></td>
  </tr>
  <tr>
    <td colspan="4">รหัสลูกหนี้ : <?php echo $row_Rec_debtor['debtorID']; ?></td>
  </tr>
  <tr>
    <td width="60"></td>
    <td>ชื่อลูกหนี้ : <?php echo $row_Rec_debtor['FirstName'] . ' '. $row_Rec_debtor['LastName'];?></td>
  <tr>
    <td></td>
    <td>ที่อยู่ : <?php echo $row_Rec_debtor['Addr']; ?></td>
</tr>
<tr>
  <td></td>
  <td>เบอร์โทร : <?php echo $row_Rec_debtor['tell']; ?></td>
</tr>
  <tr>
    <td colspan="4">รหัสการชำระ : <?php echo $row_Rec_debtor['perroundID'] . ' - ' . $row_Rec_debtor['perroundName']; ?></td>
  </tr>
  <tr height="30"></tr>
  <tr>
    <td colspan="4">ยอดหนี้ทั้งหมด : <?php echo $row_Rec_debtor['receiptOwe']; ?> บาท</td>
  </tr>
</table>

<table border="2" class="table_detail">
        <tr class="tr_head">
            <td width="250">วันเดือนปีที่ชำระ</td></td>
            <td width="70">ครั้งที่</td></td>
            <td width="150">จำนวนเงินที่จ่าย<br>(บาท)</td>
            <td width="150">ยอดเงินคงเหลือ<br>(บาท)</td>
        </tr>
        <?php
          if (mysql_num_rows($Rec_receipt) == 0) {
              echo '<tr align="center"><td colspan="4">ไม่มีข้อมูลการชำระ</td></tr>';
          } else {
              do {
                  ?>
                  <tr class="tr_center">
                      <td><?php echo $row_Rec_receipt['payDate']; ?></td>
                      <td><?php echo $row_Rec_receipt['ordinalPay']; ?></td>
                      <td><?php echo $row_Rec_receipt['amount']; ?></td>
                      <td><?php echo $row_Rec_receipt['unpaiddebt']; ?></td>
                  </tr>
                  <?php
              } while ($row_Rec_receipt = mysql_fetch_assoc($Rec_receipt));
          }
          ?>
      </table>
    </div></td>
</tr>
</table>
<p></p>
<input name="print" type="submit" id="print" value="ดาวน์โหลด" class="button-9" onclick="window.print()" />
</center>
</font>
</body>
</html>
<?php
mysql_free_result($Rec_receipt);
?>
