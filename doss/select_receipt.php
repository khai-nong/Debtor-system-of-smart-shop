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

if (isset($_POST['debtorID'])) {
  $debtorID = $_POST['debtorID'];
}

// รับค่าค้นหามาแสดงผล
mysql_select_db($doss_connection, $doss);
$query_Rec_debtor = sprintf("SELECT debtorID, FirstName, LastName FROM debtor WHERE debtorID =%s", GetSQLValueString($debtorID, "text"));
$Rec_debtor = mysql_query($query_Rec_debtor, $doss) or die(mysql_error());
$row_Rec_debtor = mysql_fetch_assoc($Rec_debtor);

//ค้นหาการค้างชำระ
$selected_Rec_receipt = sprintf("SELECT receiptID, debtorID, receiptLeft FROM receipt WHERE debtorID = %s", GetSQLValueString($debtorID, "double"));
$Rec_receipt = mysql_query($selected_Rec_receipt , $doss) or die(mysql_error());

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

<html>
<head>
    <title>เพิ่มข้อมูลการชำะหนี้</title>
    <link rel="icon" type="image" href="img/logo_page.png">
    <link href="css/navisbar.css" rel="stylesheet" type="text/css" />
  <link href="css/addpaydebt_design.css" rel="stylesheet" type="text/css" />
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
<p>&nbsp;</p>
    <center><h1>เพิ่มข้อมูลการชำระหนี้</h1>
    <br>
    <form action="add_paydebt.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
        <table align="center" width="600" height="310" >
            <tr>
              <td align="left">&nbsp;&nbsp;&nbsp;ชื่อ-นามสกุล
              </td>
              <td align="center" style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);"><?php echo $row_Rec_debtor['FirstName'] . ' ' . $row_Rec_debtor['LastName']; ?></td>
            </tr>
            <tr>
                <td class="head2" width="160">&nbsp;&nbsp;&nbsp;&nbsp;รหัสลูกหนี้
                <td width="120" align="center" style="color: #1D92FC; text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);"><?php echo $row_Rec_debtor['debtorID']; ?></td>
                </td>
            </tr>
            <tr>
                <td class="head2" width="150">รหัสการค้างชำระ</td>
                <td colspan="3" width="120" align="center"><?php
                      if (mysql_num_rows($Rec_receipt) == 0) {
                        echo '<script>';
                        echo 'let result = "ไม่มีการค้างชำระ\nผู้ใช้ต้องการเพิ่มการค้างชำระใหม่ใช่หรือไม่";';
                        echo 'if (confirm(result) == true) {
                            window.location.href="add_receipt.php";
                          } else {
                            window.location.href="search_paydebt.php";
                          };';
                        echo '</script>';
                    } else {
                        echo '<select name="receiptID" id="rec" onchange="getOption()">';
                        while ($row_Rec_receipt = mysql_fetch_assoc($Rec_receipt)) {
                            $receiptID = $row_Rec_receipt['receiptID'];
                            $receiptLeft = $row_Rec_receipt['receiptLeft'];
                    
                            // Check if receiptLeft is greater than 0
                            if ($receiptLeft > 0) {
                                echo '<option value="' . $receiptID . '">' . $receiptID . ' - ยอดคงเหลือ : ' . $receiptLeft . ' บาท' . '</option>';
                            }
                        }
                        echo '</select>'.'<span class="color_warning">&nbsp;*&nbsp;</span>';

                          if ($receiptLeft == 0) {
                            echo '<script>';
                            echo 'let result = "ไม่มีการค้างชำระ เนื่องจากมีการชำระหนี้ครบตามจำนวนเรียบร้อย \nผู้ใช้ต้องการเพิ่มการค้างชำระใช่หรือไม่";';
                            echo 'if (confirm(result) == true) {
                                window.location.href="add_receipt.php";
                              } else {
                                window.location.href="search_paydebt.php";
                              };';
                            echo '</script>';
                          }
                    }
                      ?>
                </td>
            </tr>
            <tr>
              <td colspan="3" align="center" height="70">
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="color_warning">*กรุณาเพิ่มข้อมูล</span></td>
            </tr>
            <tr>
              <td colspan="3" align="center"><input type="submit" value="เพิ่มข้อมูล" class="enter">&nbsp;&nbsp;
              <a href="javascript:history.go(-1)"><input value=" กลับไป " class="button-9"></a></td>
            </tr>
        </table>
            <input type="hidden" name="MM_insert" value="form1" />
            <input type="hidden" name="debtorID" value="<?php echo $debtorID; ?>">
    </form>
    </center>
    </body>
</html>