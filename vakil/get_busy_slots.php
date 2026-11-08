<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$date = trim($_GET['date'] ?? '');

if (empty($date)) {
    echo json_encode(['busy_times' => []]);
    exit;
}

// واکشی ساعات رزرو شده و پرداخت‌شده برای تاریخ انتخابی
$stmt = $pdo->prepare("SELECT booking_time FROM appointments WHERE booking_date = ? AND payment_status = 'paid'");
$stmt->execute([$date]);
$busyTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['busy_times' => $busyTimes]);