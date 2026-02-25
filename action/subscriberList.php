<?php
require_once '../includes/config.php';

// Waypoint Credentials (Basic Auth)
// Inhe aap config.php mein bhi define kar sakte hain
define('WAYPOINT_USER', '69900d0ead28650d6d53b09e');
define('WAYPOINT_PASS', '9Zzos2S4mKNXLgqHC296278g');

/**
 * Waypoint API Helper Function
 */
function waypoint_api_call($endpoint, $method = 'GET', $data = null) {
    // Waypoint API Base URL
    $url = "https://live.waypointapi.com/v1/" . $endpoint; 
    $ch = curl_init($url);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Basic Authentication: Key as Username, Secret as Password
    curl_setopt($ch, CURLOPT_USERPWD, WAYPOINT_USER . ":" . WAYPOINT_PASS);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'data' => json_decode($response, true)];
}

/* =========================
   ADD LIST / GROUP
========================= */
if (isset($_POST['add_list'])) {
    $name = trim($_POST['list_name']);

    if ($name == '') {
        header("Location: ../subscriberList.php?msg=empty");
        exit();
    }

    // 1. Create UNSTUBSCRIBE GROUP in Waypoint
    $apiResponse = waypoint_api_call('unsubscribe_groups', 'POST', ['name' => $name]);
    
    // Check if created successfully (HTTP 201)
    $waypoint_id = ($apiResponse['code'] == 201) ? $apiResponse['data']['id'] : null;

    try {
        // 2. Local DB mein save karein
        $stmt = $pdo->prepare("INSERT INTO subscriber_lists (list_name, waypoint_group_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$name, $waypoint_id]);

        header("Location: ../subscriberList.php?msg=added");
        exit();

    } catch (PDOException $e) {
        die("Insert Error: " . $e->getMessage());
    }
}

/* =========================
   UPDATE LIST
========================= */
if (isset($_POST['update_list'])) {
    $id   = (int)$_POST['list_id'];
    $name = trim($_POST['list_name']);

    $stmt = $pdo->prepare("SELECT waypoint_group_id FROM subscriber_lists WHERE list_id = ?");
    $stmt->execute([$id]);
    $wp_id = $stmt->fetchColumn();

    // Sync Update to Waypoint
    if ($wp_id) {
        waypoint_api_call("unsubscribe_groups/{$wp_id}", 'PUT', ['name' => $name]);
    }

    try {
        $stmt = $pdo->prepare("UPDATE subscriber_lists SET list_name = ? WHERE list_id = ?");
        $stmt->execute([$name, $id]);
        header("Location: ../subscriberList.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        die("Update Error: " . $e->getMessage());
    }
}

/* =========================
   DELETE LIST
========================= */
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    $stmt = $pdo->prepare("SELECT waypoint_group_id FROM subscriber_lists WHERE list_id = ?");
    $stmt->execute([$id]);
    $wp_id = $stmt->fetchColumn();

    // Sync Delete to Waypoint
    if ($wp_id) {
        waypoint_api_call("unsubscribe_groups/{$wp_id}", 'DELETE');
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM subscriber_lists WHERE list_id = ?");
        $stmt->execute([$id]);
        header("Location: ../subscriberList.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        die("Delete Error: " . $e->getMessage());
    }
}