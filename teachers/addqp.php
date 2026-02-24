<?php 
error_reporting(0);
session_start();
if (!isset($_SESSION["fname"])){
    header("Location: ../login_teacher.php");
}
include('../config.php');

$questions = [];
$exid      = '';
$nq        = 0;
$source    = $_GET['source'] ?? '';

// ─── FLOW 1: CSV — read questions from SESSION ────────────────
if($source === 'csv' && isset($_SESSION['csv_questions'])){

    $questions = $_SESSION['csv_questions'];
    $exid      = $_SESSION['csv_exid'];
    $nq        = $_SESSION['csv_nq'];

    // Clear session after reading so refresh doesn't re-use stale data
    unset($_SESSION['csv_questions'], $_SESSION['csv_exid'], $_SESSION['csv_nq']);

// ─── FLOW 2: Manual edit — load existing questions from DB ────
} elseif(isset($_POST['exid']) && isset($_POST['nq'])){

    $exid   = intval($_POST['exid']);
    $nq     = intval($_POST['nq']);
    $source = 'manual';

    // Fetch existing questions from qstn_list using correct column names
    $sql = "SELECT * FROM qstn_list WHERE exid='$exid' ORDER BY sno ASC";
    $res = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($res)){
        $questions[] = [
            'q'  => $row['qstn'],
            'o1' => $row['qstn_o1'],
            'o2' => $row['qstn_o2'],
            'o3' => $row['qstn_o3'],
            'o4' => $row['qstn_o4'],
            'a'  => $row['qstn_ans'],
        ];
    }

    // If no questions saved yet, generate empty slots
    if(empty($questions)){
        for($i = 0; $i < $nq; $i++){
            $questions[] = ['q'=>'','o1'=>'','o2'=>'','o3'=>'','o4'=>'','a'=>''];
        }
    }

    $nq = count($questions);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title>Edit Question Paper</title>
    <link rel="stylesheet" href="css/dash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      .home-section { display: flex; flex-direction: column; min-height: 100vh; }
      .home-content { flex: 1; }
      footer {
        background: #2933CC;
        color: #fff;
        padding: 16px 30px;
        width: 100%;
        margin-top: auto;
        box-sizing: border-box;
      }
      .question-block {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
      }
      .question-block h4 {
        color: #2933cc;
        margin-bottom: 12px;
        font-size: 15px;
      }
      .csv-info {
        background: #2933CC;
        border-left: 4px solid #2933cc;
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-size: 13px;
        color: #333;
      }
      .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #888;
      }
      .empty-state i {
        font-size: 48px;
        color: #ccc;
        display: block;
        margin-bottom: 12px;
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
      <li><a href="dash.php"><i class='bx bx-grid-alt'></i><span class="links_name">Dashboard</span></a></li>
      <li><a href="exams.php" class="active"><i class='bx bx-book-content'></i><span class="links_name">Exams</span></a></li>
      <li><a href="results.php"><i class='bx bxs-bar-chart-alt-2'></i><span class="links_name">Results</span></a></li>
      <li><a href="records.php"><i class='bx bxs-user-circle'></i><span class="links_name">Records</span></a></li>
      <li><a href="messages.php"><i class='bx bx-message'></i><span class="links_name">Messages</span></a></li>
      <li><a href="settings.php"><i class='bx bx-cog'></i><span class="links_name">Settings</span></a></li>
      <li><a href="help.php"><i class='bx bx-help-circle'></i><span class="links_name">Help</span></a></li>
      <li class="log_out">
        <a href="http://localhost:9000/#/homeProfile"><i class='bx bx-log-out-circle'></i><span class="links_name">Back</span></a>
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
        <span class="admin_name"><?php echo $_SESSION['fname']; ?></span>
      </div>
    </nav>

    <div class="home-content">
      <div class="stat-boxes">
        <div class="recent-stat box" style="width:85%;">

          <div class="title">
            <?php echo $source === 'csv' ? 'Review & Edit Questions' : 'Add / Edit Questions'; ?>
          </div>
          <br>

          <?php if($source === 'csv'): ?>
          <div class="csv-info" style="color: white;">
            ✅ <strong><?php echo $nq; ?> question(s)</strong> randomly selected from CSV.
            Review and edit below before saving.
          </div>
          <?php endif; ?>

          <?php if($nq > 0): ?>

          <form action="addexam.php" method="post">
            <input type="hidden" name="nq"   value="<?php echo $nq; ?>">
            <input type="hidden" name="exid" value="<?php echo $exid; ?>">

            <?php for($i = 0; $i < $nq; $i++):
              $q = $questions[$i];
            ?>
            <div class="question-block">
              <h4>Question <?php echo $i + 1; ?></h4>

              <label>Question:</label>
              <input class="inputbox" type="text" name="q<?php echo $i+1; ?>"
                value="<?php echo htmlspecialchars($q['q']); ?>"
                placeholder="Enter question" required><br>

              <label>Option A:</label>
              <input class="inputbox" type="text" name="o1<?php echo $i+1; ?>"
                value="<?php echo htmlspecialchars($q['o1']); ?>"
                placeholder="Option A" required><br>

              <label>Option B:</label>
              <input class="inputbox" type="text" name="o2<?php echo $i+1; ?>"
                value="<?php echo htmlspecialchars($q['o2']); ?>"
                placeholder="Option B" required><br>

              <label>Option C:</label>
              <input class="inputbox" type="text" name="o3<?php echo $i+1; ?>"
                value="<?php echo htmlspecialchars($q['o3']); ?>"
                placeholder="Option C" required><br>

              <label>Option D:</label>
              <input class="inputbox" type="text" name="o4<?php echo $i+1; ?>"
                value="<?php echo htmlspecialchars($q['o4']); ?>"
                placeholder="Option D" required><br>

              <label>Correct Answer:</label>
              <input class="inputbox" type="text" name="a<?php echo $i+1; ?>"
                value="<?php echo htmlspecialchars($q['a']); ?>"
                placeholder="Paste correct answer" required><br>
            </div>
            <?php endfor; ?>

            <button type="submit" name="addqp" class="btn">
              <i class='bx bx-save'></i> Save Questions
            </button>
          </form>

          <?php else: ?>
          <div class="empty-state">
            <i class='bx bx-error-circle'></i>
            <p>No questions to display.</p>
            <a href="exams.php" style="color:#2933CC;">← Go back to Exams</a>
          </div>
          <?php endif; ?>

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