<?php
require_once '../includes/config.php';

// --- BULK CSV IMPORT LOGIC ---
if (isset($_POST['import_csv'])) {
    if (!empty($_FILES['template_csv']['tmp_name'])) {
        $file = $_FILES['template_csv']['tmp_name'];
        $handle = fopen($file, "r");
        
        // Skip the header row
        fgetcsv($handle);
        
        $successCount = 0;
        $duplicateCount = 0;

        try {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $name = trim($data[0]);
                $w_id = trim($data[1]);

                if (!empty($name) && !empty($w_id)) {
                    // Check for duplicates before inserting
                    $check = $pdo->prepare("SELECT COUNT(*) FROM email_templates WHERE waypoint_template_id = ?");
                    $check->execute([$w_id]);
                    
                    if ($check->fetchColumn() == 0) {
                        $stmt = $pdo->prepare("INSERT INTO email_templates (waypoint_template_id, template_name) VALUES (?, ?)");
                        $stmt->execute([$w_id, $name]);
                        $successCount++;
                    } else {
                        $duplicateCount++;
                    }
                }
            }
            fclose($handle);
            header("Location: ../template_list.php?msg=import_done&success=$successCount&duplicates=$duplicateCount");
        } catch (Exception $e) {
            die("Import Error: " . $e->getMessage());
        }
    }
    exit();
}

// --- DELETE LOGIC (Existing - Keep it for Waypoint API delete) ---
if (isset($_GET['delete_id'])) {
    $local_id = (int)$_GET['delete_id'];
    $w_id = $_GET['w_id'];

    define('W_USER', '69900d0ead28650d6d53b09e'); 
    define('W_PASS', '9Zzos2S4mKNXLgqHC296278g');

    $ch = curl_init("https://live.waypointapi.com/v1/templates/" . $w_id);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, W_USER . ":" . W_PASS);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Agar Waypoint se delete ho gaya ya wahan nahi tha, toh DB se udayein
    if ($http_code == 204 || $http_code == 200 || $http_code == 404) {
        $stmt = $pdo->prepare("DELETE FROM email_templates WHERE template_id_pk = ?");
        $stmt->execute([$local_id]);
        header("Location: ../template_list.php?msg=deleted");
    } else {
        header("Location: ../template_list.php?msg=api_error");
    }
    exit();
}

// --- ADD SINGLE LOGIC ---
if (isset($_POST['add_single'])) {
    $name = trim($_POST['template_name']);
    $w_id = trim($_POST['waypoint_template_id']);

    if(!empty($name) && !empty($w_id)) {
        $stmt = $pdo->prepare("INSERT INTO email_templates (waypoint_template_id, template_name) VALUES (?, ?)");
        $stmt->execute([$w_id, $name]);
        header("Location: ../template_list.php?msg=added");
    }
    exit();
}