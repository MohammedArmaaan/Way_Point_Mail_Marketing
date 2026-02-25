<?php
require_once '../includes/config.php';

$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-7 days'));
$end_date = $_GET['end'] ?? date('Y-m-d');

try {
    // Din-wise success count fetch karein
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date, SUM(success_count) as total 
        FROM campaigns 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ");
    $stmt->execute([$start_date, $end_date]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Date gap bharne ke liye logic (agar kisi din 0 send hue hon)
    $data = [];
    $period = new DatePeriod(
        new DateTime($start_date),
        new DateInterval('P1D'),
        (new DateTime($end_date))->modify('+1 day')
    );

    foreach ($period as $dt) {
        $dateStr = $dt->format("Y-m-d");
        $found = false;
        foreach ($results as $res) {
            if ($res['date'] == $dateStr) {
                $data[] = ['date' => $dt->format('d M'), 'total' => (int)$res['total']];
                $found = true;
                break;
            }
        }
        if (!$found) $data[] = ['date' => $dt->format('d M'), 'total' => 0];
    }

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}