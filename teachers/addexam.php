<?php
date_default_timezone_set('Asia/Kolkata');
session_start();
if (!isset($_SESSION["fname"])){
    header("Location: ../login_teacher.php");
}
include '../config.php';
error_reporting(0);

// ─── FLOW 1: Add new exam + process CSV ──────────────────────
if(isset($_POST['addexm'])){

    // Escape all inputs to prevent SQL errors (apostrophes etc.)
    $exname  = mysqli_real_escape_string($conn, $_POST['exname']);
    $desp    = mysqli_real_escape_string($conn, $_POST['desp']);
    $extime  = mysqli_real_escape_string($conn, $_POST['extime']);
    $subt    = mysqli_real_escape_string($conn, $_POST['subt']);
    $nq      = intval($_POST['nq']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);

    // Step 1: Insert exam into exm_list
    $sql = "INSERT INTO exm_list (exname, desp, subject, nq, extime, subt) 
            VALUES ('$exname','$desp','$subject','$nq','$extime','$subt')";
    mysqli_query($conn, $sql);
    $exid = mysqli_insert_id($conn);

    // Step 2: Parse CSV file
    if(isset($_FILES['csvfile']) && $_FILES['csvfile']['size'] > 0){

        $allQuestions = [];
        $file = $_FILES['csvfile']['tmp_name'];

        if(($handle = fopen($file, "r")) !== FALSE){
            while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                if(count($data) >= 6){
                    $allQuestions[] = [
                        'q'  => trim($data[0]),
                        'o1' => trim($data[1]),
                        'o2' => trim($data[2]),
                        'o3' => trim($data[3]),
                        'o4' => trim($data[4]),
                        'a'  => trim($data[5]),
                    ];
                }
            }
            fclose($handle);
        }

        // Step 3: Randomly pick $nq questions from CSV
        if(count($allQuestions) > $nq){
            $keys = array_rand($allQuestions, $nq);
            if(!is_array($keys)) $keys = [$keys];
            $selected = array_map(fn($k) => $allQuestions[$k], $keys);
        } else {
            $selected = $allQuestions;
        }

        // Step 4: Store selected questions in SESSION
        $_SESSION['csv_questions'] = $selected;
        $_SESSION['csv_exid']      = $exid;
        $_SESSION['csv_nq']        = count($selected);

        // Step 5: Redirect to addqp.php for preview & edit
        header("Location: addqp.php?source=csv");
        exit;

    } else {
        // No CSV: go to manual entry
        header("Location: addqp.php?source=manual&exid=$exid&nq=$nq");
        exit;
    }
}

// ─── FLOW 2: Save questions submitted from addqp.php ─────────
if(isset($_POST['addqp'])){

    $exid = intval($_POST['exid']);
    $nq   = intval($_POST['nq']);

    // Delete any existing questions for this exam
    mysqli_query($conn, "DELETE FROM qstn_list WHERE exid='$exid'");

    // Insert each question into qstn_list (correct table & columns)
    for($i = 1; $i <= $nq; $i++){
        $q  = mysqli_real_escape_string($conn, $_POST['q'.$i]);
        $o1 = mysqli_real_escape_string($conn, $_POST['o1'.$i]);
        $o2 = mysqli_real_escape_string($conn, $_POST['o2'.$i]);
        $o3 = mysqli_real_escape_string($conn, $_POST['o3'.$i]);
        $o4 = mysqli_real_escape_string($conn, $_POST['o4'.$i]);
        $a  = mysqli_real_escape_string($conn, $_POST['a'.$i]);

        $sql = "INSERT INTO qstn_list (exid, sno, qstn, qstn_o1, qstn_o2, qstn_o3, qstn_o4, qstn_ans)
                VALUES ('$exid','$i','$q','$o1','$o2','$o3','$o4','$a')";
        mysqli_query($conn, $sql);
    }

    // Redirect back to exams list
    header("Location: exams.php");
    exit;
}
?>