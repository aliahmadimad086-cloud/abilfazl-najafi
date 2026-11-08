<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $attendance = trim($_POST['attendance'] ?? '');
    $question = trim($_POST['question'] ?? '');

    if (empty($fullname) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'لطفاً نام و شماره تلفن را وارد کنید.']);
        exit;
    }

    // تولید کد پیگیری
    $trackingCode = 'WS-' . mt_rand(10000, 99999);

    try {
        // ۱. ذخیره اطلاعات در دیتابیس
        $stmt = $pdo->prepare("INSERT INTO workshop_registrations (fullname, phone, role, attendance, question, tracking_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $phone, $role, $attendance, $question, $trackingCode]);

        // ۲. ارسال پیام به ربات بله از طریق سرور (کاملاً امن بدون لو رفتن توکن)
        $msg = "🎓 ثبت‌نام جدید در کارگاه آموزشی:\n\n"
             . "👤 شرکت‌کننده: " . $fullname . "\n"
             . "📞 تلفن همراه: " . $phone . "\n"
             . "💼 وضعیت شغلی/تحصیلی: " . $role . "\n"
             . "📍 شیوه حضور: " . $attendance . "\n"
             . "❓ پرسش مطرح‌شده: " . ($question ?: 'ندارد') . "\n"
             . "🔖 کد ثبت‌نام: " . $trackingCode;

        $url = "https://tapi.bale.ai/bot" . BALE_BOT_TOKEN . "/sendMessage";
        $data = json_encode([
            'chat_id' => BALE_CHAT_ID,
            'text' => $msg
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_exec($ch);
        curl_close($ch);

        echo json_encode([
            'success' => true,
            'tracking_code' => $trackingCode,
            'message' => 'ثبت‌نام با موفقیت انجام شد.'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'خطا در ثبت اطلاعات: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'درخواست غیرمجاز']);
}
?>