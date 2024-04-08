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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$colname_Rec_debtor = "0";
if (isset($_POST['debtorID'])) {
  $colname_Rec_debtor = $_POST['debtorID'];
}

mysql_select_db($doss_connection, $doss);
$query_Rec_debtor = "SELECT debtorID, FirstName, LastName FROM debtor";
$query_collection = "SELECT perroundID, perroundName FROM `collection`";
$Rec_debtor = mysql_query($query_Rec_debtor, $doss) or die(mysql_error());
$collection = mysql_query($query_collection, $doss) or die(mysql_error());

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $receiptLeft = floatval($_POST['receiptOwe']) - floatval($_POST['deposit']);
  $mustpay = floatval($receiptLeft) / floatval($_POST['round']);
  $insertSQL = sprintf("INSERT INTO receipt(debtorID, perroundID, receiptPay, receiptOwe, receiptLeft, mustpay) VALUES (%s, %s, %s, %s, %s, %s)",
    GetSQLValueString($_POST['debtorID'], "double"),
    GetSQLValueString($_POST['perroundID'], "text"),
    GetSQLValueString($_POST['deposit'], "double"),
    GetSQLValueString($_POST['receiptOwe'], "double"),
    GetSQLValueString($receiptLeft, "double"),
    GetSQLValueString($mustpay, "double"));

  mysql_select_db($doss_connection, $doss);
  $Result1 = mysql_query($insertSQL, $doss) or die(mysql_error());

  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
    echo '<script>';
    echo 'let add_success = "ดำเนินการเสร็จสิ้น\nต้องการเพิ่มการค้างชำระต่อหรือไม่";';
    echo 'if (confirm(add_success) == true) {
      window.location.href="add_receipt.php";
    } else {
      window.location.href="home.php";
    };';
    echo '</script>';   
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
  if(document.form1.debtorID.value=="เลือกลูกหนี้ที่ต้องการ"){
		alert("กรุณาเลือกลูกหนี้")
		document.form1.debtorID.focus();
		return false
	}
  if(document.form1.perroundID.value=="เลือกรอบการชำระ"){
		alert("กรุณาเลือกรอบการชำระ")
		document.form1.perroundID.focus();
		return false
	}
	if(document.form1.receiptOwe.value==""){
		alert("กรุณาใส่จำนวนเงิน")
		document.form1.receiptOwe.focus();
		return false
		}
	if(document.form1.round.value==""){
		alert("กรุณาใส่จำนวนงวดที่ต้องการจ่าย")
		document.form1.round.focus();
		return false
		}
  if(document.form1.deposit.value==""){
		alert("กรุณาใส่จำนวนเงินมัดจำ")
		document.form1.deposit.focus();
		return false
	  }
}
function getOption() {
  selectElement = document.getElementById("cars");
  selectedOptionValue = selectElement.value;

  var debtorData = <?php
    $query_Rec_debtor = "SELECT debtorID, FirstName, LastName FROM debtor";
    $result = mysql_query($query_Rec_debtor, $doss);
    $debtorArray = array();
    while ($row = mysql_fetch_assoc($result)) {
      $debtorArray[] = $row;
    }
    echo json_encode($debtorArray); 
  ?>;
  for (var i = 0; i < debtorData.length; i++) {
    if (debtorData[i].debtorID === selectedOptionValue) {
      var selectedName = debtorData[i].FirstName + ' ' + debtorData[i].LastName;
      document.getElementById("selectedDebtorInfo").textContent = selectedName;
      break;
    }
  }
}
</script>
<html>
<head>
<title>เพิ่มข้อมูลการค้างชำระ</title>
<link rel="icon" type="image" href="img/logo_page.png">
<link href="css/navisbar.css" rel="stylesheet" type="text/css" />
<link href="css/add_design.css" rel="stylesheet" />
</head>
<style>
  .button-9{
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
    font-size: 18px;
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
    width: 25%;
  }
  
.button-9:disabled {
    cursor: default;
}
  
.button-9:focus {
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .2) 0 6px 15px 0, rgba(0, 0, 0, .1) 0 2px 2px 0, rgba(50, 151, 211, .3) 0 0 0 4px;
  }
  
.button-9:hover,
.button-9:focus { 
    background-color: #007CEF;
    color: #ffffff;
}

.button-9:active {
    background: #00427E;
    color: rgb(255, 255, 255, .7);
}

.button-9:disabled { 
    cursor: not-allowed;
    background: rgba(0, 0, 0, .08);
    color: rgba(0, 0, 0, .3);
}

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
    font-size: 18px;
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
    width: 27%;
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
<br>
<br>
<br>
<center><h1>เพิ่มการค้างชำระใหม่</h1>
<br>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" onsubmit="return validate();">
  <table align="center">
    <tr>
      <td align="left" width="200" height="50">รหัสลูกหนี้</td>
      <td class="select">
      <select name="debtorID" id="format" onchange="getOption()">
        <?php
        echo '<option selected disabled> เลือกลูกหนี้ที่ต้องการ</option>';
        while ($row_Rec_debtor = mysql_fetch_assoc($Rec_debtor)) {
          echo '<option value="' . $row_Rec_debtor['debtorID'] . '">' . $row_Rec_debtor['debtorID'] .' - '. $row_Rec_debtor['FirstName'] . ' ' . $row_Rec_debtor['LastName'].'</option>';
        }
        ?>
      </select>
      <span class="color_warning">*</span></td>
      </td>
    </tr>
    <tr>
      <td align="left" height="60">รอบการชำระ</td>
      <td class="select">
      <select name="perroundID" id="format" >
        <?php
        echo '<option selected disabled>เลือกรอบการชำระ</option>';
        while ($row_collection = mysql_fetch_assoc($collection)) {
          echo '<option value="' . $row_collection['perroundID'] . '">' . $row_collection['perroundID'] . ' - ' . $row_collection['perroundName'] . '</option>';
        }
        ?>
        </select>
        <span class="color_warning">*</span></td>
        </td>
    </tr>
  <tr>
      <td align="left" height="70">จำนวนหนี้</td>
      <td><input type="number" name="receiptOwe" value="" placeholder="กรุณากรอกจำนวนหนี้ที่ติด"/>&nbsp;&nbsp;บาท<span class="color_warning">&nbsp;*</span></td>
  </tr>
  
  <tr>
      <td align="left" height="70">เงินมัดจำ(ถ้ามี)</td>
      <td><input type="number" name="deposit" value="" placeholder="กรุณากรอกเงินมัดจำ (หากไม่มีให้ใส่ 0)"/>&nbsp;&nbsp;บาท
      <span class="color_warning">*</span></td>
    </tr>
    <tr>
      <td align="left" height="70">จำนวนงวด</td>
      <td class="frominput2"><input type="number" name="round" value="" placeholder="กรุณากรอกจำนวนงวดที่ต้องการชำระ"/>&nbsp;&nbsp;งวด<span class="color_warning">*</span></td>
    </tr>
    
    <tr>
      <td height="50">&nbsp;</td>
      <td align="left" ><span class="color_warning">*กรุณาเพิ่มข้อมูล</span></td>
    </tr>
    <tr>
    <td colspan="2" align="center"><input type="submit" value="เพิ่มข้อมูล" class="enter">
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <a href="home.php"><input value=" กลับไป " class="button-9"></td>
    </tr>
  </table>
  
<input type="hidden" name="MM_insert" value="form1" />
</form>
<p>&nbsp;</p>
</form>
</body>
</html>