<?php 
session_start();
if (!isset($_SESSION["user_id"])){
	header("Location: ../login_teacher.php");
}
include '../config.php';
error_reporting(0);

$sql="SELECT * FROM exm_list";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title>Select exam </title>
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
  .btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .btnres {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #2933cc;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s;
  }
  .btnres:hover { background: #1e27a8; }
  .btnpdf {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #e53935;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s;
    text-decoration: none;
  }
  .btnpdf:hover { background: #b71c1c; }
    </style>
  </head>
<body>
  <div class="sidebar">
    <div class="logo-details">
      <i class='bx bx-diamond'></i>
      <span class="logo_name">SCALEGRAD</span>
    </div>
    <ul class="nav-links">
      <li><a href="dash.php"><i class='bx bx-grid-alt'></i><span class="links_name">Dashboard</span></a></li>
      <li><a href="exams.php"><i class='bx bx-book-content'></i><span class="links_name">Exams</span></a></li>
      <li><a href="#" class="active"><i class='bx bxs-bar-chart-alt-2'></i><span class="links_name">Results</span></a></li>
      <li><a href="records.php"><i class='bx bxs-user-circle'></i><span class="links_name">Records</span></a></li>
      <li><a href="messages.php"><i class='bx bx-message'></i><span class="links_name">Messages</span></a></li>
      <li><a href="settings.php"><i class='bx bx-cog'></i><span class="links_name">Settings</span></a></li>
      <li><a href="help.php"><i class='bx bx-help-circle'></i><span class="links_name">Help</span></a></li>
      <li class="log_out"><a href="http://localhost:9000/#/homeProfile"><i class='bx bx-log-out-circle'></i><span class="links_name">Back</span></a></li>
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
      <div class="stat-boxes">
        <div class="recent-stat box" style="padding: 0px 0px;width:100%;">
          <table>
            <thead>
              <tr>
                <th>Exam no.</th>
                <th>Exam name</th>
                <th>Description</th>
                <th>No. of questions</th>
                <th>Added on</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $i = 1;
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
              ?>
              <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo htmlspecialchars($row['exname']); ?></td>
                <td><?php echo htmlspecialchars($row['desp']); ?></td>
                <td><?php echo $row['nq']; ?></td>
                <td><?php echo $row['datetime']; ?></td>
                <td>
                  <div class="btn-group">
                    <!-- View Results -->
                    <form action="viewresults.php" method="post" style="margin:0;">
                      <input type="hidden" name="exid" value="<?php echo $row['exid']; ?>">
                      <button class="btnres" type="submit" name="vw_rslts">
                        <i class='bx bx-search-alt'></i> View Result
                      </button>
                    </form>
                    <!-- Download PDF -->
                    <!-- <form action="download_results_pdf.php" method="post" style="margin:0;">
                      <input type="hidden" name="exid" value="<?php echo $row['exid']; ?>">
                      <input type="hidden" name="exname" value="<?php echo htmlspecialchars($row['exname']); ?>">
                      <button class="btnpdf" type="submit" name="dl_pdf">
                        <i class='bx bxs-file-pdf'></i> Download PDF
                      </button>
                    </form> -->
                  </div>
                </td>
              </tr>
              <?php
                $i++;
                }
              }
              ?>
            </tbody>
          </table>
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