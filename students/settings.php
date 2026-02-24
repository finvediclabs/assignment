<?php 
session_start();
if (!isset($_SESSION["fname"])){
	header("Location: ../login_student.php");
}
include '../config.php';
error_reporting(0);

$id=$_SESSION['id'];
$sql="SELECT * FROM student WHERE id='$id'";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result)>0){
  $row = mysqli_fetch_assoc($result);
}


?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="css/dash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <style>
  .home-section {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .home-content {
    flex: 1;
  }

  footer {
    background: #0b266f;
    color: #fff;
    padding: 16px 30px;
    width: 100%;
    margin-top: auto;
    box-sizing: border-box;
  }
</style>
   </head>
<body>
  <div class="sidebar">
    <div class="logo-details">
      <i class='bx bx-diamond'></i>
      <span class="logo_name">SCALEGRAD</span>
    </div>
      <ul class="nav-links">
        <li>
          <a href="dash.php">
            <i class='bx bx-grid-alt'></i>
            <span class="links_name">Dashboard</span>
          </a>
        </li>
        <li>
          <a href="exams.php">
            <i class='bx bx-book-content' ></i>
            <span class="links_name">Exams</span>
          </a>
        </li>
        <li>
          <a href="results.php">
          <i class='bx bxs-bar-chart-alt-2'></i>
            <span class="links_name">Results</span>
          </a>
        </li>
        <li>
          <a href="messages.php">
            <i class='bx bx-message' ></i>
            <span class="links_name">Messages</span>
          </a>
        </li>
        <li>
         <a href="#" class="active">
            <i class='bx bx-cog' ></i>
            <span class="links_name">Settings</span>
          </a>
        </li>
        <li>
          <a href="help.php">
            <i class='bx bx-help-circle' ></i>
            <span class="links_name">Help</span>
          </a>
        </li>
        <li class="log_out">
           <a href="http://localhost:9000/#/homeProfile">
            <i class='bx bx-log-out-circle' ></i>
            <span class="links_name">Back</span>
          </a>
        </li>
      </ul>
  </div>
  <section class="home-section">
    <nav>
      <div class="sidebar-button">
        <i class='bx bx-menu sidebarBtn'></i>
        <span class="dashboard">Student Dashboard</span>
      </div>
    </nav>

    <div class="home-content">

      <div class="stat-boxes">
        <div class="recent-stat box" style="width:40%;">

          <div class="title">My Profile</div>
          <br><br>
          <img src="<?php echo !empty($_SESSION['img']) ? $_SESSION['img'] : (!empty($row['img']) ? $row['img'] : (isset($row['gender']) && $row['gender']=='M' ? '../img/mp.png' : '../img/fp.png')); ?>" alt="pro" style="display:block;margin-left:auto;margin-right:auto;width:50%;max-width:200px" onerror="this.onerror=null;this.src='../img/anon.png';">
            <form action="" method="post">
              <label for="fname">Full Name</label><br>
				      <input class="inputbox" type="text" id="fname" name="fname" placeholder="Enter your full name" value="<?php echo $_SESSION['fname']; ?>" minlength ="4" maxlength="30" required /></br>
              <label for="uname">Username</label><br>
				      <input class="inputbox" type="text" id="uname" name="uname" value="<?php echo $_SESSION['uname']; ?>" disabled required /></br>
              <label for="email">Email</label><br>
				      <input class="inputbox" type="email" id="email" name="email" placeholder="Enter your email"value="<?php echo $_SESSION['email']; ?>" minlength ="5" maxlength="50" required />
              <label for="dob">Date of Birth</label><br>
				      <input class="inputbox" type="date" id="dob" name="dob" placeholder="Enter your DOB" value="<?php echo $_SESSION['dob']; ?>" required /><br>
              <label for="gender">Gender</label><br>
				      <input class="inputbox" type="text" id="gender" name="gender" placeholder="Enter your gender (M or F)" value="<?php echo $_SESSION['gender']; ?>" minlength ="1" maxlength="1" required /><br>    
              <br><br>             
              <button type="submit" name="submit" class="btn">Update</button>    
          </form>
        </div>
      </div>
    </div>
     <footer style="background: #0b266f; color: #fff; padding: 16px 30px; margin-top: 40px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; font-size: 13px; font-family: 'Poppins', sans-serif;">
          <span>&#169; 2025 FinVedic. All rights reserved.</span>
          <span>
            <a href="#" style="color: #fff; text-decoration: none;">Privacy Policy</a>
            <span style="color: #41C0FD; margin: 0 8px;">|</span>
            <a href="#" style="color: #fff; text-decoration: none;">Terms and Conditions</a>
          </span>
        </div>
      </footer>
  </section>

<script src="../js/script.js"></script>


</body>
</html>
<?php
if(isset($_POST['submit'])){
  $fname = mysqli_real_escape_string($conn, $_POST['fname']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $dob = mysqli_real_escape_string($conn, $_POST['dob']);
  $gender = mysqli_real_escape_string($conn, $_POST['gender']);
  $sql="UPDATE student SET fname='$fname',dob='$dob',gender='$gender',email='$email' WHERE id ='{$_SESSION["user_id"]}'";
  $result=mysqli_query($conn, $sql) or die(mysqli_error($conn));
  echo "<script>alert('Profile updated successfully! Kindly re-login to see the changes.');</script>";
}
?>
