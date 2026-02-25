<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../includes/config.php';

/* 1. SINGLE ADD */
if(isset($_POST['add_subscriber'])){
    $name = trim($_POST['subscriber_name']);
    $email = trim($_POST['subscriber_email']);
    $list_id = (int)$_POST['list_id'];

    if(empty($email)){
        die("Email is required");
    }

    $stmt = $pdo->prepare("INSERT INTO subscribers (subscriber_name, subscriber_email, list_id, subscriber_status) VALUES (?, ?, ?, 'active')");
    $stmt->execute([$name, $email, $list_id]);

    header("Location: ../listSubscriber.php?list_id=".$list_id."&status=added");
    exit();
}

/* 2. BULK IMPORT (Name, Email) */
if(isset($_POST['import_bulk'])){
    $list_id = (int)$_POST['list_id'];
    $raw_data = trim($_POST['bulk_data']);

    if($raw_data != ''){
        $lines = explode("\n", $raw_data);
        $stmt = $pdo->prepare("INSERT INTO subscribers (subscriber_name, subscriber_email, list_id, subscriber_status) VALUES (?, ?, ?, 'active')");
        
        foreach($lines as $line){
            $parts = explode(",", $line);
            if(count($parts) >= 2){
                $name = trim($parts[0]);
                $email = trim($parts[1]);
                if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $stmt->execute([$name, $email, $list_id]);
                }
            }
        }
    }
    header("Location: ../listSubscriber.php?list_id=".$list_id."&status=bulk_success");
    exit();
}

/* 3. DELETE SINGLE */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $list_id = (int)$_GET['list_id'];

    $stmt = $pdo->prepare("DELETE FROM subscribers WHERE subscriber_id = ?");
    $stmt->execute([$id]);

    header("Location: ../listSubscriber.php?list_id=".$list_id."&status=deleted");
    exit();
}

/* 4. BULK DELETE */
if(isset($_POST['bulk_delete'])){
    $list_id = (int)$_POST['list_id'];

    if(!empty($_POST['bulk_ids'])){
        $placeholders = str_repeat('?,', count($_POST['bulk_ids']) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM subscribers WHERE subscriber_id IN ($placeholders)");
        $stmt->execute($_POST['bulk_ids']);
    }

    header("Location: ../listSubscriber.php?list_id=".$list_id."&status=bulk_deleted");
    exit();
}

/* CSV IMPORT LOGIC */
if(isset($_POST['import_csv'])){
    $list_id = (int)$_POST['list_id'];
    
    if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0){
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        
        // Skip the first line (headers: Name, Email)
        fgetcsv($handle);
        
        $stmt = $pdo->prepare("INSERT INTO subscribers (subscriber_name, subscriber_email, list_id, subscriber_status) VALUES (?, ?, ?, 'active')");
        
        $count = 0;
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
            if(count($data) >= 2){
                $name = trim($data[0]);
                $email = trim($data[1]);
                
                if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $stmt->execute([$name, $email, $list_id]);
                    $count++;
                }
            }
        }
        fclose($handle);
        
        header("Location: ../listSubscriber.php?list_id=".$list_id."&status=csv_success&count=".$count);
        exit();
    } else {
        die("Error uploading file.");
    }
}