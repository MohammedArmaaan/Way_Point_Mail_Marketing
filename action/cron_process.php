<?php
require_once __DIR__ . '/../includes/config.php';

// Same credentials as above
define('WAYPOINT_USER', '69900d0ead28650d6d53b09e'); 
define('WAYPOINT_PASS', '9Zzos2S4mKNXLgqHC296278g');

$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("SELECT * FROM campaigns WHERE status = 'scheduled' AND scheduled_at <= ?");
$stmt->execute([$now]);
$due = $stmt->fetchAll();

foreach ($due as $camp) {
    // 1. Mark as sending
    $pdo->prepare("UPDATE campaigns SET status = 'sending' WHERE campaign_id = ?")->execute([$camp['campaign_id']]);

    // 2. Send Emails
    $stmtSub = $pdo->prepare("SELECT subscriber_name, subscriber_email FROM subscribers WHERE list_id = ?");
    $stmtSub->execute([$camp['list_id']]);
    $subscribers = $stmtSub->fetchAll();

    $success = 0; $error = 0;
    foreach ($subscribers as $sub) {
        $data = [
            "templateId" => $camp['template_id'],
            "from" => "mail@zaveristudios.store", // Update to your verified sender
            "to" => $sub['subscriber_email'],
            "variables" => ["displayName" => $sub['subscriber_name']]
        ];

        $ch = curl_init("https://live.waypointapi.com/v1/email_messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, WAYPOINT_USER . ":" . WAYPOINT_PASS);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        ($code == 201 || $code == 200) ? $success++ : $error++;
    }

    // 3. Mark as completed
    $pdo->prepare("UPDATE campaigns SET success_count = ?, error_count = ?, status = 'completed' WHERE campaign_id = ?")
        ->execute([$success, $error, $camp['campaign_id']]);
}