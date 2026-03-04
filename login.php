<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $reg_no = $_POST['reg_no'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM students WHERE reg_no=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reg_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $student = $result->fetch_assoc();

        if (password_verify($password, $student['password'])) {

            $_SESSION['student_id'] = $student['id'];
            $_SESSION['reg_no'] = $student['reg_no'];

            header("Location: voting.php");
            exit();

        } else {
            echo "Incorrect Password";
        }

    } else {
        echo "Student Not Found";
    }
}
?>