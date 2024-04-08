<?php require_once('Connections/doss.php'); ?>
<?php
mysql_select_db($doss_connection, $doss);
$query_Rec_debtor = "SELECT debtorID, FirstName, LastName FROM debtor";
$Rec_debtor = mysql_query($query_Rec_debtor, $doss) or die(mysql_error());

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


<script language="javascript">
function getOption() {
  selectElement = document.getElementById("format");
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

function validate() {
  if(document.form1.debtorID.value=="เลือกลูกหนี้ที่ต้องการ"){
		alert("กรุณาเลือกลูกหนี้")
		document.form1.debtorID.focus();
		return false
	}
}
</script>

<html>
<head>
    <title>เพิ่มข้อมูลการชำะหนี้</title>
    <link rel="icon" type="image" href="img/logo_page.png">
    <link href="css/navisbar.css" rel="stylesheet" type="text/css" />
  <link href="css/addpaydebt_design.css" rel="stylesheet" type="text/css" />
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
    <center><h1>เพิ่มข้อมูลการชำระหนี้</h1>
    <p>&nbsp;</p>
    <form method="post" enctype="multipart/form-data" name="form1" id="form1" action="select_receipt.php" onsubmit="return validate();">
        <table align="center">
          <tr>
              <td class="head2" width="120">รหัสลูกหนี้</td>
              <td class="select">
              <select name="debtorID" id="format">
                <?php
                echo '<option selected disabled>เลือกลูกหนี้ที่ต้องการ</option>';
                while ($row_Rec_debtor = mysql_fetch_assoc($Rec_debtor)) {
                  echo '<option value="' . $row_Rec_debtor['debtorID'] . '">' . $row_Rec_debtor['debtorID'] .' - '. $row_Rec_debtor['FirstName'] . ' ' . $row_Rec_debtor['LastName'].'</option>';
                }
                ?>
              </select><span class="color_warning">*</span></td>
          </tr>
          <tr height="20"></tr>
              <tr>
                <td colspan="2" align="center"><input type="submit" value=" ค้นหา " class="button-9">
              </tr>
          </table>
        <input type="hidden" name="MM_insert" value="form1" />
        </center>
    </form>
</body>
</html>
