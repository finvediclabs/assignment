<?php 
date_default_timezone_set('Asia/Kolkata');
session_start();
if (!isset($_SESSION["fname"])){
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
    <title>Exams</title>
    <link rel="stylesheet" href="css/dash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      .home-section { display: flex; flex-direction: column; min-height: 100vh; }
      .home-content { flex: 1; }
      footer { background: #2933CC; color: #fff; padding: 16px 30px; width: 100%; margin-top: auto; box-sizing: border-box; }
      #csvInfo {
        display: none;
        background: #2933CC;
        border-left: 4px solid #2933CC;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        color: #333;
        margin-top: 8px;
      }
      #nqWrapper { display: none; margin-top: 10px; }
      #csvError {
        display: none;
        background: #fdecea;
        border-left: 4px solid #e53935;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        color: #c62828;
        margin-top: 8px;
      }
      .table-scroll-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 600px;
        border-radius: 8px;
      }
      .table-scroll-wrapper table {
        width: 100%;
        table-layout: auto;
        border-collapse: collapse;
      }
      .table-scroll-wrapper thead {
        position: sticky;
        top: 0;
        background-color: #2933CC;
        color: white;
        z-index: 10;
      }
      .table-scroll-wrapper thead th {
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #1f2499;
      }
      .table-scroll-wrapper tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #e0e0e0;
      }
      .table-scroll-wrapper tbody tr:hover {
        background-color: #f5f5f5;
      }
      .table-scroll-wrapper::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }
      .table-scroll-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }
      .table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: rgba(41, 51, 204, 0.7); /* 0.7 = 70% visible */
        border-radius: 10px;
      }
      .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: #1f2499;
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
      <li><a href="#" class="active"><i class='bx bx-book-content'></i><span class="links_name">Exams</span></a></li>
      <li><a href="results.php"><i class='bx bxs-bar-chart-alt-2'></i><span class="links_name">Results</span></a></li>
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

        <!-- LEFT: Exam Table -->
        <div class="recent-stat box" style="padding: 0px 0px; width:70%;">
          <div class="table-scroll-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Exam no.</th>
                  <th>Exam name</th>
                  <th>Description</th>
                  <th>No. of questions</th>
                  <th>Exam time</th>
                  <th>Submission time</th>
                  <th>EDIT</th>
                  <th>DELETE</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;
                if(mysqli_num_rows($result) > 0){
                  while($row = mysqli_fetch_assoc($result)){
                ?>
                <tr>
                  <td><?php echo $i; ?></td>
                  <td><?php echo $row['exname']; ?></td>
                  <td><?php echo $row['desp']; ?></td>
                  <td><?php echo $row['nq']; ?></td>
                  <td><?php echo $row['extime']; ?></td>
                  <td><?php echo $row['subt']; ?></td>
                  <td>
                    <form action="addqp.php" method="post">
                      <input type="hidden" name="nq" value="<?php echo $row['nq']; ?>">
                      <input type="hidden" name="exid" value="<?php echo $row['exid']; ?>">
                      <button type="submit" name="edit_btn" class="rounded-button-updt">
                        <i class='bx bxs-edit'></i>
                      </button>
                    </form>
                  </td>
                  <td>
                    <form action="delexam.php" method="post">
                      <input type="hidden" name="delete_id" value="<?php echo $row['exid']; ?>">
                      <button type="submit" name="delete_btn" class="rounded-button-del">
                        <i class='bx bx-x'></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php $i++; } } ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- RIGHT: Add New Exam WITH CSV Upload -->
        <div class="top-stat box">
          <div class="title">Add new exam</div>
          <br>
          <form action="addexam.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="subject">

            <label for="exname">Exam name</label><br>
            <input class="inputbox" type="text" id="exname" name="exname"
              placeholder="Enter exam name" minlength="3" required /><br>

            <label for="desp">Description</label><br>
            <input class="inputbox" type="text" id="desp" name="desp"
              placeholder="Enter exam description" minlength="5" required /><br>

            <label for="extime">Exam time</label><br>
            <input class="inputbox" type="datetime-local" id="extime" name="extime" required /><br>

            <label for="subt">Submission time</label><br>
            <input class="inputbox" type="datetime-local" id="subt" name="subt" required /><br>

            <label for="csvfile">Upload Questions (CSV):</label><br>
            <input class="inputbox" type="file" name="csvfile" id="csvfile" accept=".csv" required /><br>
            <small style="color:#777; font-size:11px;">
              Format: question, optA, optB, optC, optD, correct_answer
            </small>

            <div id="csvInfo"></div>
            <div id="csvError"></div>

            <div id="nqWrapper">
              <label for="nq">No. of questions to pick (randomly):</label><br>
              <input class="inputbox" type="number" id="nq" name="nq"
                min="1" placeholder="Enter number" required /><br>
              <small id="nqHint" style="color:#2933CC; font-size:11px;"></small>
            </div>

            <br>
            <button type="submit" name="addexm" class="btn">
              <i class='bx bx-plus'></i> Add & Preview Questions
            </button>
          </form>
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
  <script>
    const csvInput  = document.getElementById('csvfile');
    const csvInfo   = document.getElementById('csvInfo');
    const csvError  = document.getElementById('csvError');
    const nqWrapper = document.getElementById('nqWrapper');
    const nqInput   = document.getElementById('nq');
    const nqHint    = document.getElementById('nqHint');
    let totalRows   = 0;

      csvInput.addEventListener('change', function(){
    const file = this.files[0];
    if(!file) return;

    // Validate file extension
    if(!file.name.endsWith('.csv')){
      csvError.style.display  = 'block';
      csvError.textContent    = '⚠ Invalid file type. Please upload a .csv file only.';
      csvInfo.style.display   = 'none';
      nqWrapper.style.display = 'none';
      this.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e){
      const rawLines = e.target.result.split('\n').filter(l => l.trim() !== '');

      if(rawLines.length === 0){
        showError('⚠ CSV file is empty.');
        return;
      }

      const validRows   = [];
      const invalidRows = [];

      rawLines.forEach((line, index) => {
        const cols = line.split(',').map(c => c.trim());

        // Must have exactly 6 columns
        if(cols.length !== 6){
          invalidRows.push(`Row ${index + 1}: expected 6 columns, found ${cols.length}`);
          return;
        }

        // All 6 columns must be non-empty
        const emptyCol = cols.findIndex(c => c === '');
        if(emptyCol !== -1){
          invalidRows.push(`Row ${index + 1}: column ${emptyCol + 1} is empty`);
          return;
        }

        // Correct answer (col 6) must match one of the 4 options (cols 2-5)
        const optA   = cols[1];
        const optB   = cols[2];
        const optC   = cols[3];
        const optD   = cols[4];
        const answer = cols[5];

        if(![optA, optB, optC, optD].includes(answer)){
          invalidRows.push(`Row ${index + 1}: correct answer "${answer}" doesn't match any option`);
          return;
        }

        validRows.push(cols);
      });

      // Show errors if any invalid rows
      if(invalidRows.length > 0){
        const errorList = invalidRows.slice(0, 5).join('<br>');
        const more      = invalidRows.length > 5 ? `<br>...and ${invalidRows.length - 5} more` : '';
        csvError.style.display  = 'block';
        csvError.innerHTML      = `⚠ <strong>${invalidRows.length} invalid row(s) found:</strong><br>${errorList}${more}
                                  <br><br><b>Required format:</b> question, optA, optB, optC, optD, correct_answer
                                  <br><small>correct_answer must exactly match one of the 4 options</small>`;
        csvInfo.style.display   = 'none';
        nqWrapper.style.display = 'none';
        return;
      }

      // All rows valid
      totalRows               = validRows.length;
      csvError.style.display  = 'none';
      csvInfo.style.display   = 'block';
      csvInfo.innerHTML = `<span style="color: #fff;">✅ <strong>${totalRows} valid question(s)</strong> detected in CSV.</span>`;
      nqWrapper.style.display = 'block';
      nqHint.textContent      = `Max allowed: ${totalRows}`;
      nqInput.max             = totalRows;
      nqInput.value           = '';
    };

    reader.readAsText(file);
  });

  function showError(msg){
    csvError.style.display  = 'block';
    csvError.innerHTML      = msg;
    csvInfo.style.display   = 'none';
    nqWrapper.style.display = 'none';
  }
  </script>
</body>
</html>