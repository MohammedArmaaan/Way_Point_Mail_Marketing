<?php
require_once 'includes/config.php';

// 1. Raw payload capture karein
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// 2. Check karein ki data sahi hai aur event type 'delivered' hai
if ($data && isset($data['type']) && $data['type'] === 'email_message.delivered') {
    
    // Waypoint Message ID (e.g., em_mUNZCkvQ8EFJYtgk)
    $waypoint_msg_id = $data['data']['id']; 
    $delivered_at = $data['data']['deliveredAt'] ?? date('Y-m-d H:i:s');

    try {
        // 3. Database update karein
        // Hum 'waypoint_message_id' ke basis par matching record status 'delivered' set karenge
        $stmt = $pdo->prepare("UPDATE campaigns SET status = 'delivered', error_count = 0 WHERE waypoint_message_id = ?");
        $stmt->execute([$waypoint_msg_id]);

        // Success response Svix ko bhejein
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Record updated"]);
    } catch (PDOException $e) {
        error_log("Webhook DB Error: " . $e->getMessage());
        http_response_code(500);
    }
} else {
    // Agar event delivered nahi hai ya payload galat hai
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid payload"]);
}