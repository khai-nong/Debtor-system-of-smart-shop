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

if (isset($_GET['did'])) {
  $colname_Rec_debtor = $_GET['did'];
}
mysql_select_db($doss_connection, $doss);
$query_Rec_debtor = sprintf("SELECT * FROM debtor WHERE debtorID = %s", GetSQLValueString($colname_Rec_debtor, "int"));
$Rec_debtor = mysql_query($query_Rec_debtor, $doss) or die(mysql_error());
$row_Rec_debtor = mysql_fetch_assoc($Rec_debtor);
$totalRows_Rec_debtor = mysql_num_rows($Rec_debtor);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE debtor SET FirstName=%s, LastName=%s, Addr=%s, tell=%s WHERE debtorID=%s",
                       GetSQLValueString($_POST['FirstName'], "text"),
                       GetSQLValueString($_POST['LastName'], "text"),
                       GetSQLValueString($_POST['Addr'], "text"),
                       GetSQLValueString($_POST['tell'], "text"),
                       GetSQLValueString($_POST['debtorID'], "int"));

  mysql_select_db($doss_connection, $doss);
  $Result1 = mysql_query($updateSQL, $doss) or die(mysql_error());

  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
    echo '<script>';
    echo 'let add_success = "แก้ไขข้อมูลเสร็จสิ้น\nต้องการดำเนินการต่อหรือไม่";';
    echo 'if (confirm(add_success) == true) {
      window.location.href="edit_menu_alldebtor.php";
    } else {
      window.location.href="home.php";
    };';
    echo '</script>';  
  }
}

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
<title>แก้ไขข้อมูลลูกหนี้</title>
<link rel="icon" type="image" href="img/logo_page.png">
<link href="css/navisbar.css" rel="stylesheet" type="text/css" />
</head>

<script language="javascript">
function validate() {
	if(document.form1.FirstName.value==""){
		alert("กรุณาป้อนชื่อ")
		document.form1.FirstName.focus();
		return false
		}
	if(document.form1.LastName.value==""){
		alert("กรุณาป้อนนามสกุล")
		document.form1.LastName.focus();
		return false
		}
	if(document.form1.Addr.value==""){
		alert("กรุณาป้อนที่อยู่")
		document.form1.Addr.focus();
		return false
		}
	if(document.form1.tell.value==""){
		alert("กรุณาป้อนเบอร์โทรและเป็นตัวเลขเท่านั้น")
		document.form1.tell.focus();
		return false
		}
}
function clearForm() {
    // Get all input elements and textareas in the form
    var form = document.getElementById("form1");
    var inputs = form.querySelectorAll("input, textarea");

    // Loop through the input elements and textareas
    for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];

        // Check if the input type is not "submit," "button," "reset," or "hidden"
        if (
            input.type !== "submit" &&
            input.type !== "button" &&
            input.type !== "reset" &&
            input.type !== "hidden"
        ) {
            // Clear the input value
            input.value = "";
        }
    }
}

</script>

<style>
body{
    font-family: 'LINE Seed Sans TH';
    margin: 0 auto;
    background: #E8E8E8;
}
#addr_note{
    font-family: 'LINE Seed Sans TH';
    font-size: 17px;
    width: 90%;
    height: 130px;
    padding: 10px;
    display: inline-block;
    border-radius: 10px;
    background: #FFF;
    box-shadow: 0px 1px 1px 0px rgba(151, 151, 151, 0.25);
    box-sizing: border-box;
}

textarea[type=text], select{
    vertical-align: top;
    font-family: 'LINE Seed Sans TH';
    font-size: 17px;
    width: 90%;
    height: 50px;
    display: inline-block;
    border-radius: 10px;
    background: #FFF;
    box-shadow: 0px 1px 1px 0px rgba(151, 151, 151, 0.25);
    box-sizing: border-box;
    padding: 10px;
    border: 2px solid #4b4a4a;
    resize: none;
}

input[type=text], select {
    vertical-align: top;
    font-family: 'LINE Seed Sans TH';
    font-size: 17px;
    width: 90%;
    padding: 10px;
    display: inline-block;
    border-radius: 10px;
    background: #FFF;
    box-shadow: 0px 1px 1px 0px rgba(151, 151, 151, 0.25);
    line-height: 30px;
}

h1{
    color: #000;
    text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    font-size: 75px;
    font-family: 'LINE Seed Sans TH';
    font-weight: 310;
    line-height: normal;
}

.head{
    color: #000;
    text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    font-size: 25px;
    width: 0px;
    font-weight: 300px;
    padding: 0px 10px;
    text-align: left;
}
.color_warning{
    color: #F40000;
    font-size: 18px;
    line-height: normal;
}

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
    font-size: 100%;
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


/*-----------Resetbut----------*/
.button-9_reset{
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
    font-size: 100%;
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
  
.button-9_reset:disabled {
    cursor: default;
}
  
.button-9_reset:focus {
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .2) 0 6px 15px 0, rgba(0, 0, 0, .1) 0 2px 2px 0, rgba(50, 151, 211, .3) 0 0 0 4px;
  }
  
.button-9_reset:hover,
.button-9_reset:focus { 
    background-color: #bf4141;
    color: #ffffff;
}

.button-9_reset:active {
    background: #900b0b;
    color: rgb(255, 255, 255, .7);
}

.button-9_reset:disabled { 
    cursor: not-allowed;
    background: rgba(0, 0, 0, .08);
    color: rgba(0, 0, 0, .3);
}

/*------------------------------*/
.color_warning {
	color: #ff0000;
}

/*-----------Resetbut----------*/
.button-9_clear{
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
    font-size: 100%;
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
  
.button-9_clear:disabled {
    cursor: default;
}
  
.button-9_clear:focus {
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .2) 0 6px 15px 0, rgba(0, 0, 0, .1) 0 2px 2px 0, rgba(50, 151, 211, .3) 0 0 0 4px;
  }
  
.button-9_clear:hover,
.button-9_clear:focus { 
    background-color: #eca952;
    color: #ffffff;
}

.button-9_clear:active {
    background: #eca952;
    color: rgb(255, 255, 255, .7);
}

.button-9_clear:disabled { 
    cursor: not-allowed;
    background: rgba(0, 0, 0, .08);
    color: rgba(0, 0, 0, .3);
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
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
  <center><h1>แก้ไขข้อมูลลูกหนี้</h1>
  <p>&nbsp;<font size="7" color="#007CEF">รหัส <?php echo $row_Rec_debtor['debtorID']; ?></font></p>
  <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" onsubmit="return validate();">
    <table align="center" width="620" height="400">
        <td class="head">ชื่อ</td>
        <td><input type="text" name="FirstName" value="<?php echo htmlentities($row_Rec_debtor['FirstName'], ENT_COMPAT, 'utf-8'); ?>"/>
        <span class="color_warning">*</span></td>
      </tr>
      <tr>
        <td class="head">นามสกุล </td>
        <td><input type="text" name="LastName" value="<?php echo htmlentities($row_Rec_debtor['LastName'], ENT_COMPAT, 'utf-8'); ?>"/>
        <span class="color_warning">*</span></td>
      </tr>
      <tr>
        <td class="head">ที่อยู่ </td>
        <td><textarea type="text" name="Addr" class="text" id="addr_note"><?php echo htmlentities($row_Rec_debtor['Addr'], ENT_COMPAT, 'utf-8'); ?></textarea>
        <span class="color_warning">*</span></td>
      </tr>
      <tr>
        <td class="head">เบอร์โทร </td>
        <td><input type="text" name="tell" value="<?php echo htmlentities($row_Rec_debtor['tell'], ENT_COMPAT, 'utf-8'); ?>"/>
        <span class="color_warning">*</span></td>
      </tr>
      <tr height="20">
        <p>
      </tr>
            <tr> 
              <td colspan="2" align="center"><input type="submit" value="บันทึก" class="button-9" />
                  <input type="reset" value="รีเซ็ต" class="button-9_reset" />
                  <input type="button" value="Clear" class="button-9_clear" onclick="clearForm();" />
              </td>
            </tr>
          </table>
          <input type="hidden" name="MM_update" value="form1" />
          <input type="hidden" name="debtorID" value="<?php echo $row_Rec_debtor['debtorID']; ?>" />
        </form>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</center>
</body>
</html>
<?php
mysql_free_result($Rec_debtor);
?>
