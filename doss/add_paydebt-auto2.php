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

$debtorID = $_GET['debtorID'];
$receiptID = $_GET['receiptID'];

mysql_select_db($doss_connection, $doss);
$query_Rec_debtor = sprintf("SELECT debtorID, FirstName, LastName FROM debtor WHERE debtorID =%s", GetSQLValueString($debtorID, "text"));
$Rec_debtor = mysql_query($query_Rec_debtor, $doss) or die(mysql_error());
$row_Rec_debtor = mysql_fetch_assoc($Rec_debtor);

//ค้นหาการค้างชำระ
$selected_Rec_receipt = sprintf("SELECT receiptID, debtorID, receiptLeft,mustpay FROM receipt WHERE receiptID = %s", GetSQLValueString($receiptID, "double"));
$Rec_receipt = mysql_query($selected_Rec_receipt , $doss) or die(mysql_error());
$row_Rec_receipt = mysql_fetch_assoc($Rec_receipt);

// ตัวรันฟอร์ม
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  if (isset($_POST["debtorID"]) && isset($_POST["receiptID"]) && isset($_POST["amount"])) {
      $selectedDebtorID = $_POST["debtorID"];
      $selectedReceiptID = $_POST['receiptID'];
      $amount = floatval($_POST['amount']); // Convert to float (assuming amount is a decimal)
      
      $query_rec_receipt = sprintf("SELECT receiptOwe, receiptLeft,mustpay FROM receipt WHERE debtorID = %s AND receiptID = %s",GetSQLValueString($selectedDebtorID, "text"),GetSQLValueString($selectedReceiptID, "text"));
      $result = mysql_query($query_rec_receipt);
      if($result){
          $row_receipt = mysql_fetch_assoc($result);{
          $debt = $row_receipt['receiptOwe'];
          $undebt = $row_receipt['receiptLeft'] - floatval($_POST['amount']);

          if ($_POST['amount'] > $row_receipt['receiptLeft']) {
            echo '<script>';
            echo 'alert("ไม่สามารถเพิ่มข้อมูลได้ เนื่องจากจำนวนเงินที่ชำระมากกว่าจำนวนหนี้ที่เหลืออยู่");';
            echo 'window.location.href="search_paydebt.php";';
            echo '</script>';
            exit;
        }
          if ($_POST['amount'] < $row_receipt['mustpay']) {
            echo '<script>';
            echo 'alert("ไม่สามารถเพิ่มข้อมูลได้ เนื่องจากยอดการชำระต่ำกว่าที่ได้กำหนด");';
            echo 'window.location.href="search_paydebt.php";';
            echo '</script>';
            exit;
        }
    }
      }
        // แก้ไข
        $updateSQL = sprintf("UPDATE receipt SET receiptLeft = %s WHERE receiptID = %s",
            GetSQLValueString($undebt, "double"),
            GetSQLValueString($selectedReceiptID, "double"));
            
      // ดำเนินการแก้ไข
      mysql_query($updateSQL);
      $result = mysql_query($query_rec_receipt);
      mysql_select_db($doss_connection, $doss);
      $Result1 = mysql_query($updateSQL, $doss) or die(mysql_error());
    
      // เรียกดูครั้งการชำระ
      $query_rec_ordinalPay = sprintf("SELECT ordinalPay FROM paydebt WHERE receiptID = %s ORDER BY ordinalPay DESC",GetSQLValueString($selectedReceiptID, "double"));
      $resultordinalPay = mysql_query($query_rec_ordinalPay);
      $row_Rec_pay = mysql_fetch_assoc($resultordinalPay);
      $totalRows_Rec_pay = mysql_num_rows($resultordinalPay);

      if($totalRows_Rec_pay == 0){
        $ordinalPay = 1;
      } else{
        $ordinalPay = $row_Rec_pay['ordinalPay']+1;
      }
      
      // เพิ่มข้อมูล
        $insertSQL = sprintf("INSERT INTO paydebt(receiptID, ordinalPay, amount, unpaiddebt, debt, note) VALUES (%s, %s, %s, %s, %s, %s)",
            GetSQLValueString($_POST['receiptID'], "double"),
            GetSQLValueString($ordinalPay, "double"),
            GetSQLValueString($_POST['amount'], "double"),
            GetSQLValueString($undebt, "double"),
            GetSQLValueString($debt, "double"),
            GetSQLValueString($_POST['note'], "text"));

        mysql_select_db($doss_connection, $doss);
        $Result2 = mysql_query($insertSQL, $doss) or die(mysql_error());
        // Check if the update was successful
        if ($Result2) {
          echo '<script>';
          echo 'let add_success = "บันทึกข้อมูลการชำระเสร็จสิ้น\nต้องการดำเนินการต่อหรือไม่";';
          echo 'if (confirm(add_success) == true) {
            window.location.href="search_paydebt.php";
          } else {
            window.location.href="home.php";
          };';
          echo '</script>'; 
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

<script language="javascript">
function validate() {
	if(document.form1.amount.value==""){
		alert("กรุณาใส่จำนวนเงิน")
		document.form1.amount.focus();
		return false
		}
}
</script>

<html>
<head>
    <title>เพิ่มข้อมูลการชำะหนี้</title>
    <link rel="icon" type="image" href="img/logo_page.png">
    <link href="css/navisbar.css" rel="stylesheet" type="text/css" />
    <link href="css/addpaydebt_design-2.css" rel="stylesheet" type="text/css"/>
    
</head>
<style>
  .enter{
    appearance: button;
    backface-visibility: hidden;
    background-color: #ffffff;
    border-radius: 6px;
    border-width: 0;
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset,rgba(50, 50, 93, .1) 0 2px 5px 0,rgba(0, 0, 0, .07) 0 1px 1px 0;
    box-sizing: border-box;
    color: #09090c;
    cursor: pointer;
    font-family: 'LINE Seed Sans TH';
    font-size: 20px;
    height: 50px;
    line-height: 1.15;
    margin: 0 auto;
    outline: none;
    overflow: hidden;
    padding: 0 25px;
    position: relative;
    text-align: center;
    text-transform: none;
    transform: translateZ(0);
    transition: all .2s,box-shadow .08s ease-in;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    width: 180px;
}
.enter:disabled {
    cursor: default;
}
  
.enter:focus {
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .2) 0 6px 15px 0, rgba(0, 0, 0, .1) 0 2px 2px 0, rgba(50, 151, 211, .3) 0 0 0 4px;
  }
  
.enter:hover,
.enter:focus { 
    background-color: #70d578;
    color: #000000;
}

.enter:active {
    background: #007e28;
    color: rgb(255, 255, 255, .7);
}

.enter:disabled { 
    cursor: not-allowed;
    background: rgba(0, 0, 0, .08);
    color: rgba(0, 0, 0, .3);
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
<p>&nbsp;</p>
<center><h1>เพิ่มข้อมูลการชำระหนี้</h1>
<form action="<?php echo $editFormAction;?>" method="post" enctype="multipart/form-data"
name="form1" id="form1" onsubmit="return validate();">
<table align="center" width="680"  height="500">
            <tr>
                <td width="250" align="left" >&nbsp;&nbsp;รหัสลูกหนี้  </td>
                <td style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);">&nbsp;<?php echo $row_Rec_debtor['debtorID']; ?></td>
            </tr>
            <tr>
                <td align="left">&nbsp;&nbsp;ชื่อ-นามสกุล </td>
                <td style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);"><?php echo $row_Rec_debtor['FirstName'] . ' ' . $row_Rec_debtor['LastName']; ?></td>
            </tr>
            <tr>
                <td align="left">&nbsp;&nbsp;รหัสการค้างชำระ  </td>
                <td style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);">&nbsp;<?php echo $row_Rec_receipt['receiptID']?></td>
            </tr>
            <tr>
                <td align="left">&nbsp;&nbsp;ยอดหนี้คงเหลือ </td>
                <td style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);">&nbsp;<?php echo $row_Rec_receipt['receiptLeft']?></td>
            </tr>
            <tr>
                <td align="left">&nbsp;&nbsp;จำนวนขั้นต่ำที่ต้องชำระ</td>
                <td style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);">&nbsp;<?php echo $row_Rec_receipt['mustpay']?></td>
            </tr>
            <tr>
                <td align="left">&nbsp;&nbsp;จำนวนหนี้ที่ชำระ </td>
                <td ><input type="number" name="amount" value="" placeholder="กรุณากรอกจำนวนเงิน"/> บาท <span class="color_warning">*</span></td>
            </tr>
            <tr>                  
                <td align="left">&nbsp;&nbsp;หมายเหตุ </td>
                <td><textarea type="text" name="note" value="" class="text" id="addr_note" placeholder="กรุณากรอกหมายเหตุ(ถ้ามี)"></textarea></td>
            </tr>
            <tr>
                <td align="center" colspan="2" ><span class="color_warning">*กรุณาเพิ่มข้อมูล</span></td>
            </tr>
            <tr>
                <td colspan="2"  align="center"height="30"><input type="submit" value="เพิ่มข้อมูล" class="enter">
                <a href="search_receipt_result.php"><input value=" กลับไป " class="button-9"></a></td>
            </tr>
    </table>
    </center>
    <input type="hidden" name="MM_insert" value="form1" />
    <input type="hidden" name="debtorID" value="<?php echo $row_Rec_debtor['debtorID']; ?>">
    <input type="hidden" name="receiptID" value="<?php echo $receiptID; ?>">
</form>
</body>
</html>