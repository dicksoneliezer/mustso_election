<?php
session_start();
include "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    /* ===============================
       Validate Required Fields
    =================================*/
    $required_fields = ['name', 'reg_no', 'college', 'department', 'password', 'study_mode'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo "<script>
                    alert('All fields are required');
                    window.location='register.html';
                  </script>";
            exit();
        }
    }

    /* ===============================
       Clean Input Data
    =================================*/
    $name = trim($_POST['name']);
    $reg_no = trim($_POST['reg_no']);
    $college = trim($_POST['college']);
    $department = trim($_POST['department']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $study_mode = $_POST['study_mode'];
    
    /* ===============================
       Validate Study Mode
    =================================*/
    $valid_modes = ['on_campus', 'off_campus'];
    if (!in_array($study_mode, $valid_modes)) {
        echo "<script>
                alert('Invalid study mode selected');
                window.location='register.html';
              </script>";
        exit();
    }

    /* ===============================
       Jimbo Logic (Very Safe)
    =================================*/
    $jimbo = NULL;

    if($study_mode === "off_campus"){
        
        // Check if jimbo was submitted for off_campus
        if (!isset($_POST['jimbo']) || empty($_POST['jimbo'])) {
            echo "<script>
                    alert('Please select Jimbo for off-campus students');
                    window.location='register.html';
                  </script>";
            exit();
        }
        
        // Validate jimbo value
        $valid_jimbos = ['kati', 'mashariki', 'magharibi'];
        $submitted_jimbo = $_POST['jimbo'];
        
        if (in_array($submitted_jimbo, $valid_jimbos)) {
            $jimbo = $submitted_jimbo;
        } else {
            echo "<script>
                    alert('Invalid Jimbo selection');
                    window.location='register.html';
                  </script>";
            exit();
        }
    }

    /* ===============================
       Check Duplicate Registration
    =================================*/
    $check = $conn->prepare("SELECT id FROM students WHERE reg_no=?");
    if (!$check) {
        error_log("Prepare failed (check): " . $conn->error);
        echo "<script>
                alert('System error. Please try again later.');
                window.location='register.html';
              </script>";
        exit();
    }
    
    $check->bind_param("s", $reg_no);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        echo "<script>
                alert('Registration number already exists');
                window.location='register.html';
              </script>";
        exit();
    }
    $check->close();

    /* ===============================
       Insert Student Record
    =================================*/
    $sql = "INSERT INTO students
            (name, reg_no, college, department, password, study_mode, jimbo)
            VALUES (?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    
    // Check if prepare failed
    if (!$stmt) {
        error_log("Prepare failed (insert): " . $conn->error);
        echo "<script>
                alert('System error. Please try again later.');
                window.location='register.html';
              </script>";
        exit();
    }

    $stmt->bind_param("sssssss",
        $name,
        $reg_no,
        $college,
        $department,
        $password,
        $study_mode,
        $jimbo
    );

    if($stmt->execute()){
        // Registration successful
        echo "<script>
                alert('Registration Successful! You can now login.');
                window.location='index.php';
              </script>";
        exit();
    }
    else{
        // Log detailed error for debugging
        error_log("Registration failed for reg_no: $reg_no - " . $stmt->error);
        
        // Show user-friendly message
        echo "<script>
                alert('Registration failed. Please try again later or contact support.');
                window.location='register.html';
              </script>";
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access this file directly without POST
    header("Location: register.html");
    exit();
}
?>