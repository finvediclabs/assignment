<?php 
session_start();
if (!isset($_SESSION["user_id"])){
	header("Location: ../login_teacher.php");
}
include '../config.php';

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
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
    background: #2933CC;
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
          <a href="#" class="active">
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
          <a href="records.php">
           <i class='bx bxs-user-circle'></i>
            <span class="links_name">Records</span>
          </a>
        </li>
        <li>
          <a href="messages.php">
            <i class='bx bx-message' ></i>
            <span class="links_name">Messages</span>
          </a>
        </li>
        <li>
          <a href="settings.php">
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
        <span class="dashboard">Teacher's Dashboard</span>
      </div>
      <div class="profile-details">
        <img src="<?php echo !empty($_SESSION['img']) ? $_SESSION['img'] : '../img/anon.png'; ?>" alt="pro">
        <span class="admin_name"><?php echo $_SESSION['fname'];?></span>
      </div>
    </nav>

    <div class="home-content">
      <div class="overview-boxes">
        <div class="box">
          <div class="right-side">
            <div class="box-topic">Records</div>
            <div class="number"><?php  $sql="SELECT COUNT(1) FROM student"; $result = mysqli_query($conn, $sql); $row=mysqli_fetch_array($result); echo $row['0'] ?></div>
            <div class="brief">
              <span class="text">Total number of students</span>
            </div>
          </div>
          <i class='bx bx-user ico' ></i>
        </div>
        <div class="box">
          <div class="right-side">
            <div class="box-topic">Exams</div>
            <div class="number"><?php  $sql="SELECT COUNT(1) FROM exm_list"; $result = mysqli_query($conn, $sql); $row=mysqli_fetch_array($result); echo $row['0'] ?></div>
            <div class="brief">
              <span class="text">Total number of exams</span>
            </div>
          </div>
          <i class='bx bx-book ico two' ></i>
        </div>
        <div class="box">
          <div class="right-side">
            <div class="box-topic">Results</div>
            <div class="number"><?php  $sql="SELECT COUNT(1) FROM atmpt_list"; $result = mysqli_query($conn, $sql); $row=mysqli_fetch_array($result); echo $row['0'] ?></div>
            <div class="brief">
              <span class="text">Number of available results</span>
            </div>
          </div>
          <i class='bx bx-line-chart ico three' ></i>
        </div>
        <div class="box">
          <div class="right-side">
            <div class="box-topic">Annoucements</div>
            <div class="number"><?php  $sql="SELECT COUNT(1) FROM message"; $result = mysqli_query($conn, $sql); $row=mysqli_fetch_array($result); echo $row['0'] ?></div>
            <div class="brief">
              <span class="text">Total number of messages sent</span>
            </div>
          </div>
          <i class='bx bx-paper-plane ico four' ></i>
        </div>
      </div>

      <div class="stat-boxes">
        <div class="recent-stat box">
          <div class="title">Recent results</div>
          <table id="res">
                    <thead >
                        <tr>
                          
                            <th id="res" style="width:20%">Date</th>
                            <th id="res" style="width:35%">Name</th>
                            <th id="res" style="width:25%">Exam name</th>
                            <th id="res" style="width:20%">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $sql="SELECT * FROM atmpt_list ORDER BY subtime DESC LIMIT 8";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) > 0)        
                        {
                            while($row = mysqli_fetch_assoc($result))
                            {
                        ?>
                            <tr>
                            <td id="res"><?php $dptime=$row['subtime']; $dptime=date("M d, Y", strtotime($dptime)); echo $dptime; ?></td>
                            <td id="res"><?php $uname=$row['uname']; $sql_name="SELECT * FROM student WHERE uname='$uname'"; $result_name=mysqli_query($conn, $sql_name); $row_name=mysqli_fetch_assoc($result_name); echo $row_name['fname']; ?></td>
                            <td id="res"><?php $exid=$row['exid']; $sql_exname="SELECT * FROM exm_list WHERE exid='$exid'"; $result_exname=mysqli_query($conn, $sql_exname); $row_exname=mysqli_fetch_assoc($result_exname); echo $row_exname['exname']; ?></td>
                            <td id="res"><?php  echo $row['ptg']; ?>%</td>
                            </tr>
                            <?php
                            } 
                        }
                        ?>
                    </tbody>
                </table>
          <div class="button">
            <a href="results.php">See All</a>
        </div>
        </div>
      
      </div>
    </div>
    <footer>
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

