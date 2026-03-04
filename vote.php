<?php
session_start();
include "config.php";

if(!isset($_SESSION['student_id'])){
    exit("Unauthorized");
}

$student_id = $_SESSION['student_id'];

if(!isset($_POST['candidate_id'])){
    exit("Invalid vote request");
}

$candidate_id = $_POST['candidate_id'];

/* ===============================
   Prevent Double Voting (Per Candidate)
================================*/
$check = $conn->prepare("
SELECT id FROM votes
WHERE student_id=? AND candidate_id=?
");

$check->bind_param("ii",$student_id,$candidate_id);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
    echo "<script>
            alert('You already voted for this candidate');
            window.location='results.php';
          </script>";
    exit();
}

/* ===============================
   Record Vote
================================*/
$insert = $conn->prepare("
INSERT INTO votes(student_id,candidate_id)
VALUES(?,?)
");

$insert->bind_param("ii",$student_id,$candidate_id);
$insert->execute();

/* ===============================
   Update Candidate Vote Count
================================*/
$update = $conn->prepare("
UPDATE candidates
SET votes = votes + 1
WHERE id=?
");

$update->bind_param("i",$candidate_id);
$update->execute();

/* ===============================
   Redirect to Results Page
================================*/
echo "<script>
        alert('Vote successfully recorded!');
        window.location='results.php';
      </script>";
exit();
?>