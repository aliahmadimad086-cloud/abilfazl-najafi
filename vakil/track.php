<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$query = trim($_GET['query'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'لطفاً شماره تماس یا کد پیگیری را وارد کنید.']);
    exit;
}

// جستجو بر اساس شماره تلفن یا کد رهگیری
$stmt = $pdo->prepare("SELECT fullname, topic, booking_date, booking_time, consultation_type, payment_status, ref_id FROM appointments WHERE phone = ? OR ref_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$query, $query]);
$order = $stmt->fetch();

if ($order) {
    echo json_encode([
        'success' => true,
        'data' => [
            'fullname' => $order['fullname'],
            'topic' => $order['topic'],
            'datetime' => $order['booking_date'] . ' ساعت ' . $order['booking_time'],
            'type' => $order['consultation_type'],
            'status' => $order['payment_status'] === 'paid' ? 'تایید شده و پرداخت موفق' : 'در انتظار پرداخت / لغو شده',
            'is_paid' => $order['payment_status'] === 'paid',
            'ref_id' => $order['ref_id'] ?? '-'
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'هیچ نوبتی با این مشخصات یافت نشد.']);
}