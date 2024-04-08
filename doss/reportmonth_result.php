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

$month = date('m');
$year = date('Y');
$numday=cal_days_in_month(CAL_GREGORIAN, $_POST['txtmonth'], $_POST['txtyear']);
//กำหนดวันเริ่มต้น
$start = $_POST['txtyear']."-".$_POST['txtmonth']."-01";
//กำหนดวันสิ้นสุด
$end = $_POST['txtyear']."-".$_POST['txtmonth']."-".$numday;

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
<title>ผลการออกรายงาน : ยอดหนี้-ยอดชำระประจำเดือน</title>
<link rel="icon" type="image" href="img/logo_page.png">
<link href="css/report_result_design.css" rel="stylesheet" type="text/css"/>

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

<?php
$mm = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
$i = $_POST['txtmonth']-1;

mysql_select_db($doss_connection, $doss);
$query_Rec_report="SELECT receipt.receiptDate, receipt.debtorID, debtor.FirstName, debtor.LastName, receipt.receiptOwe AS Owe, SUM(paydebt.amount) AS totalamount
FROM receipt,debtor,paydebt
WHERE (receipt.debtorID = debtor.debtorID AND receipt.receiptID = paydebt.receiptID) 
AND receipt.receiptDate BETWEEN '$start' AND '$end'
GROUP BY receipt.receiptDate, receipt.debtorID, debtor.FirstName, debtor.LastName";

$Rec_report = mysql_query($query_Rec_report, $doss) or die(mysql_error());
$row_Rec_report = mysql_fetch_assoc($Rec_report);
$totalRows_Rec_report = mysql_num_rows($Rec_report); 

if ($totalRows_Rec_report == 0) {
  echo '<script>alert("ไม่พบข้อมูลในระยะเวลาที่ต้องการ กรุณาค้นหาอีกครั้ง");';
  echo 'window.location.href = "reportmonth.php";</script>';
}

?>
<p></p>
<p></p>
<center>
<img id="pdf-only" src="img/logo.png" style="width: 120px; height: 33px;">
<p></p>
<h2>ร้าน Smart Shop<br>รายงานสรุปยอดหนี้ - ยอดชำระ เดือน <?php echo $mm[$i]; ?> ปี ค.ศ. <?php echo $_POST['txtyear']; ?></h1>
<p></p>
  <table class="table_detail2">
  <tr class="tr_head2">
        <td width="250">วันที่มาค้างชำระ</td>
        <td width="70">รหัสลูกหนี้</td>
        <td width="250">ชื่อ - นามสกุล</td>
        <td width="100">ยอดหนี้:บาท</td>
        <td width="100">ยอดชำระ:บาท</td>
    </tr>
          <?php do { ?>
            <tr >
              <td>
                <?php $dd=$row_Rec_report['receiptDate'];
                  echo date("d/m/Y",strtotime("$dd"));
                ?>
              </div></td>
              <td><?php $debtorID=$row_Rec_report['debtorID'];
                  echo $debtorID;
                ?>
              </div></td>
              <td><?php $FirstName=$row_Rec_report['FirstName'] . ' ' . $LastName=$row_Rec_report['LastName'];
                  echo $FirstName;
                ?>
              </div></td>
              <td><?php echo number_format($row_Rec_report['Owe'],2); ?></div></td>
              <td><?php echo number_format($row_Rec_report['totalamount'],2); ?></div></td>
            </tr> 
            <?php
		  $gnum = $gnum+$row_Rec_report['Owe'];
		  $gtotal = $gtotal+$row_Rec_report['totalamount'];
		} while ($row_Rec_report = mysql_fetch_assoc($Rec_report)); ?>
          <tr height="30"></tr>
          <tr class="tr_head3">
            <td>ยอดหนี้สุทธิ(บาท)</td>
            <td></td>
            <td></td>
            <td><b><?php echo number_format($gnum,2); ?></b></td>
            <td></td>
          </tr>
          <tr class="tr_head4">
            <td>ยอดการชำระหนี้สุทธิ(บาท)</td>
            <td></td>
            <td></td>
            <td></td>
            <td><b><?php echo number_format($gtotal,2); ?></div></b></td>
          </tr>
        </table>
    </div></td>
  </tr>
  <p></p>
  <tr>
    <td>วันที่พิมพ์ <?php echo date("d/m/y/ H:i:s",strtotime("now"))?>&nbsp;
    <br><input name="print" type="submit" id="print" value="ดาวน์โหลด" class="button-9" onclick="window.print()" /></td>
  </tr>
</table>
</body>
</html>
<?php
mysql_free_result($Rec_report);
?>
