<?php
require_once '../includes/config.php';

define('WAYPOINT_USER', '69900d0ead28650d6d53b09e'); 
define('WAYPOINT_PASS', '9Zzos2S4mKNXLgqHC296278g');

// ... Delete Logic & Cancel Logic ...

if (isset($_POST['send_campaign'])) {
    $list_id = (int)$_POST['list_id'];
    $template_id = trim($_POST['template_id']); // This now comes from the dropdown value
    $from_email = trim($_POST['from_email']);
    $campaign_title = trim($_POST['campaign_title'] ?? 'Untitled Campaign');
    $scheduled_at = !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null;

    // 1. Fetch total recipients count
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE list_id = ?");
    $stmtCount->execute([$list_id]);
    $total_recipients = $stmtCount->fetchColumn();

    // 2. Insert initial record
    $status = $scheduled_at ? 'scheduled' : 'sending';
    $stmt = $pdo->prepare("INSERT INTO campaigns (list_id, template_id, campaign_title, total_recipients, status, scheduled_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$list_id, $template_id, $campaign_title, $total_recipients, $status, $scheduled_at]);
    $campaign_db_id = $pdo->lastInsertId();

    if ($scheduled_at) {
        header("Location: ../campaigns.php?msg=campaign_scheduled");
        exit();
    } else {
        // 3. Immediate Sending Loop
        $stmt = $pdo->prepare("SELECT subscriber_name, subscriber_email FROM subscribers WHERE list_id = ?");
        $stmt->execute([$list_id]);
        $subscribers = $stmt->fetchAll();

        $successCount = 0;
        $errorCount = 0;

        foreach ($subscribers as $sub) {
            $data = [
                "templateId" => $template_id,
                "from" => $from_email,
                "to" => $sub['subscriber_email'],
                "variables" => [
                    "displayName" => $sub['subscriber_name']
                ]
            ];

            $ch = curl_init("https://live.waypointapi.com/v1/email_messages");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERPWD, WAYPOINT_USER . ":" . WAYPOINT_PASS);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 201 || $httpCode == 200) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        // 4. Update final status
        $stmt = $pdo->prepare("UPDATE campaigns SET success_count = ?, error_count = ?, status = 'completed' WHERE campaign_id = ?");
        $stmt->execute([$successCount, $errorCount, $campaign_db_id]);

        header("Location: ../campaigns.php?msg=campaign_finished&success=$successCount");
        exit();
    }
}