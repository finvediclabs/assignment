<?php 
session_start();
if (!isset($_SESSION["fname"])){
	header("Location: ../login_student.php");
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title>About&help</title>
    <link rel="stylesheet" href="css/dash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      /* Make the home-section a flex column that fills full height */
      .home-section {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
      }

      /* Push footer to bottom by making content grow */
      .home-content {
        flex: 1;
      }

      /* Footer stays at bottom */
      footer {
        background: #0b266f;
        color: #fff;
        padding: 16px 30px;
        width: 100%;
        margin-top: auto; /* <-- key change: replaces margin-top: 40px */
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
          <a href="settings.php">
            <i class='bx bx-cog' ></i>
            <span class="links_name">Settings</span>
          </a>
        </li>
        <li>
          <a href="#" class="active">
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
      <div class="profile-details">
      <img src="<?php echo !empty($_SESSION['img']) ? $_SESSION['img'] : '../img/anon.png'; ?>" alt="pro">
        <span class="admin_name"><?php echo $_SESSION['fname'];?></span>
      </div>
    </nav>

    <div class="home-content">

      <div class="stat-boxes">
        <div class="recent-stat box" style="width:100%">
          <div class="title" ><b>How to use</b></div>
          <br><br>
          <h4>Q1. How to go back ?</h4>
          <p>Ans: Click on the back button at the left bottom on the navigation bar.</p><br>
          <h4>Q2. How to edit my profile details ?</h4>
          <p>Ans: Click on the settings option from the left naviation bar. After filling the required columns, click on update.</p><br>
          <h4>Q3. How to view the results ?</h4>
          <p>Ans: Go to the results option from the left navigation bar to view the results. </p><br>
          <h4>Q4. How to attempt exams ?</h4>
          <p>Ans: Navigate to the exams tab by clicking on the exams button from the left navigation bar. Tests can be attempted from here.</p><br>
          <h4>Q5. How to view annoucements ?</h4>
          <p>Ans: Click on the messages option from the left navigation bar.</p><br>
         
          
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

