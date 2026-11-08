<?php
require_once 'db.php';

$orderId = $_GET['order_id'] ?? 0;
$status = $_GET['status'] ?? '';
$isSuccess = false;
$refId = '';
$order = null;

// اطلاعات اختصاصی بات بله
$botToken = "1326094037:BLAB3Hb6PWvp7FF5qLyM3jMEH4HsmbiakIk";
$chatId = "2122828186";

if ($orderId > 0) {
    if ($status === 'success') {
        $isSuccess = true;
        $refId = "ZB-" . rand(1000000, 9999999);

        // ۱. به‌روزرسانی وضعیت نوبت در پایگاه‌داده
        $stmt = $pdo->prepare("UPDATE appointments SET payment_status = 'paid', ref_id = ? WHERE id = ?");
        $stmt->execute([$refId, $orderId]);

        // ۲. دریافت مشخصات نوبت برای ارسال به بله
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        // ۳. ارسال خودکار و رایگان پیام به بله
        if ($order) {
            $msg = "⚖️ ثبت نوبت مشاوره جدید (تایید پرداخت)\n\n"
                 . "👤 نام موکل: " . $order['fullname'] . "\n"
                 . "📞 شماره تماس: " . $order['phone'] . "\n"
                 . "📂 موضوع حقوقی: " . $order['topic'] . "\n"
                 . "📅 موعد نوبت: " . $order['booking_date'] . " ساعت " . $order['booking_time'] . "\n"
                 . "🏢 نوع مشاوره: " . $order['consultation_type'] . " (" . $order['urgency'] . ")\n"
                 . "💳 کد پیگیری: " . $refId . "\n"
                 . "💰 مبلغ: " . number_format($order['amount']) . " تومان\n"
                 . "📝 شرح خلاصه: " . ($order['description'] ?: 'توضیحی ثبت نشده است');

            $url = "https://tapi.bale.ai/bot{$botToken}/sendMessage";
            $data = json_encode([
                'chat_id' => $chatId,
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
        }
    } else {
        $stmt = $pdo->prepare("UPDATE appointments SET payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$orderId]);

        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>نتیجه ثبت نوبت مشاوره</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4 font-sans">
  <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-slate-200 shadow-xl text-center">
    <?php if ($isSuccess && $order): ?>
      <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4">✓</div>
      <h2 class="text-xl font-black text-slate-800">نوبت شما با موفقیت قطعی شد</h2>
      <p class="text-xs text-slate-500 mt-2">اطلاعات نوبت برای دفتر وکالت ارسال شد.</p>
      
      <div class="bg-slate-50 rounded-2xl p-4 my-6 text-xs text-slate-700 space-y-2 border border-slate-100 text-right">
        <div class="flex justify-between"><span>کد پیگیری:</span><strong class="text-slate-900"><?= $refId ?></strong></div>
        <div class="flex justify-between"><span>نام موکل:</span><strong><?= htmlspecialchars($order['fullname']) ?></strong></div>
       <div class="flex justify-between"><span>زمان نوبت:</span><strong><?= htmlspecialchars($order['booking_date']) ?> (<?= htmlspecialchars($order['booking_time']) ?>)</strong></div>
        <div class="flex justify-between"><span>مبلغ پرداخت شده:</span><strong class="text-emerald-600"><?= number_format($order['amount']) ?> تومان</strong></div>
      </div>

      <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 mb-4">
        پیام تایید این نوبت هم‌اکنون به دفتر وکیل ارسال گردید.
      </div>
    <?php else: ?>
      <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mx-auto mb-4">✕</div>
      <h2 class="text-xl font-black text-slate-800">پرداخت ناموفق بود</h2>
      <p class="text-xs text-slate-500 mt-2">نوبت شما نهایی نگردید.</p>
    <?php endif; ?>

    <a href="index.html" class="inline-block w-full py-3 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition-all">
      بازگشت به صفحه اصلی
    </a>
  </div>
</body>
</html>