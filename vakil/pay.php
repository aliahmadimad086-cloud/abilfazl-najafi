<?php
// نمایش خطاها برای تست راحت‌تر
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $topic = trim($_POST['subject'] ?? 'عمومی');
    $date = trim($_POST['date'] ?? 'تعیین نشده');
    $time = trim($_POST['time'] ?? '۱۶:۰۰ تا ۱۷:۰۰');
    $type = $_POST['consultation_type'] ?? 'حضوری';
    $urgency = $_POST['urgency'] ?? 'عادی';
    $description = trim($_POST['description'] ?? '');
    $amount = (int)($_POST['amount'] ?? 500000);

    if (empty($fullname) || empty($phone)) {
        die("لطفاً نام و شماره تلفن را وارد کنید.");
    }

    try {
        // ذخیره درخواست با وضعیت pending در دیتابیس
        $stmt = $pdo->prepare("INSERT INTO appointments (fullname, phone, topic, booking_date, booking_time, consultation_type, urgency, description, amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$fullname, $phone, $topic, $date, $time, $type, $urgency, $description, $amount]);
        $orderId = $pdo->lastInsertId();
    } catch (PDOException $e) {
        die("خطای ثبت در دیتابیس: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>شبیه‌ساز پرداخت شاپرک</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4 font-sans">
  <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-slate-200 shadow-xl text-center">
    <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl font-black mx-auto mb-4">
      شاپرک
    </div>
    <h3 class="text-lg font-black text-slate-800">درگاه شبیه‌ساز پرداخت شاپرک</h3>
    <p class="text-xs text-slate-500 mt-2">تست محلی ثبت نوبت در دیتابیس</p>

    <div class="bg-slate-50 rounded-2xl p-4 my-6 text-xs text-slate-700 space-y-2 border border-slate-100 text-right">
      <div class="flex justify-between"><span>شناسه رزرو:</span><strong class="text-slate-900"><?= $orderId ?></strong></div>
      <div class="flex justify-between"><span>نام موکل:</span><strong class="text-slate-900"><?= htmlspecialchars($fullname) ?></strong></div>
      <div class="flex justify-between"><span>موضوع حقوقی:</span><strong class="text-slate-900"><?= htmlspecialchars($topic) ?></strong></div>
      <div class="flex justify-between"><span>مبلغ قابل پرداخت:</span><strong class="text-emerald-600"><?= number_format($amount) ?> تومان</strong></div>
    </div>

    <div class="flex gap-3">
      <a href="verify.php?order_id=<?= $orderId ?>&status=success" class="flex-1 py-3 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 transition-all flex items-center justify-center">
        تایید پرداخت (تست موفق)
      </a>
      <a href="verify.php?order_id=<?= $orderId ?>&status=failed" class="flex-1 py-3 rounded-xl bg-rose-100 text-rose-700 font-bold text-xs hover:bg-rose-200 transition-all flex items-center justify-center">
        انصراف از پرداخت
      </a>
    </div>
  </div>
</body>
</html>
<?php
    exit;
} else {
    header("Location: index.html");
    exit;
}
?>