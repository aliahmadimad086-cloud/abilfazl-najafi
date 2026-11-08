<?php
session_start();
require_once 'db.php';

$admin_user = "admin";
$admin_pass = "vakil1403";
$error = "";

// خروج از حساب
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header("Location: admin.php");
    exit;
}

// بررسی فرم لاگین
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "نام کاربری یا رمز عبور اشتباه است.";
    }
}

// اگر وارد نشده بود، فرم لاگین را نشان بده
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true):
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ورود به پنل مدیریت دفتر وکالت</title>
  <script src="assets/js/tailwind.min.js"></script>
  <link rel="stylesheet" href="assets/css/vazirmatn.css">
  <style> body { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
  <div class="max-w-sm w-full bg-white rounded-3xl p-8 border border-slate-200 shadow-xl">
    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-2xl mx-auto mb-4 font-black">
      ⚖️
    </div>
    <h2 class="text-lg font-black text-slate-800 text-center mb-1">ورود به مدیریت نوبت‌ها</h2>
    <p class="text-xs text-slate-400 text-center mb-6">دفتر وکالت دکتر ابوالفضل نجفی</p>

    <?php if ($error): ?>
      <div class="p-3 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-xs text-center font-bold">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">نام کاربری</label>
        <input type="text" name="username" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500 text-left dir-ltr">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">رمز عبور</label>
        <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500 text-left dir-ltr">
      </div>
      <button type="submit" name="login" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition-all shadow-md mt-2 cursor-pointer">
        ورود به پنل
      </button>
    </form>
  </div>
</body>
</html>
<?php
exit;
endif;

// تعیین تب فعال (نوبت‌ها یا کارگاه‌ها)
$tab = $_GET['tab'] ?? 'appointments';

// واکشی اطلاعات بر اساس تب
if ($tab === 'workshops') {
    $stmt = $pdo->query("SELECT * FROM workshop_registrations ORDER BY id DESC");
    $rows = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM appointments ORDER BY id DESC");
    $rows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>میز مدیریت دفتر وکالت دکتر نجفی</title>
  <script src="assets/js/tailwind.min.js"></script>
  <link rel="stylesheet" href="assets/css/vazirmatn.css">
  <style> body { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-100 p-6">
  <div class="max-w-6xl mx-auto bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
    
    <!-- هدر پنل -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b border-slate-100 gap-4">
      <div>
        <h1 class="text-xl font-black text-slate-900">میز مدیریت دفتر وکالت</h1>
        <p class="text-xs text-slate-400 mt-1">مدیریت نوبت‌های مشاوره و ثبت‌نام کارگاه‌های آموزشی</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="index.html" target="_blank" class="text-xs bg-slate-100 text-slate-700 hover:bg-slate-200 py-2 px-3.5 rounded-xl font-bold transition-all">مشاهده سایت</a>
        <a href="admin.php?action=logout" class="text-xs bg-rose-50 text-rose-600 hover:bg-rose-100 py-2 px-3.5 rounded-xl font-bold transition-all">خروج</a>
      </div>
    </div>

    <!-- تب‌های جابه‌جایی -->
    <div class="flex gap-2 mb-6">
      <a href="admin.php?tab=appointments" class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all <?= $tab === 'appointments' ? 'bg-slate-900 text-amber-400 shadow' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
        📅 نوبت‌های مشاوره (<?= count($pdo->query("SELECT id FROM appointments")->fetchAll()) ?>)
      </a>
      <a href="admin.php?tab=workshops" class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all <?= $tab === 'workshops' ? 'bg-slate-900 text-amber-400 shadow' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
        🎓 ثبت‌نامی‌های کارگاه آموزشی (<?= count($pdo->query("SELECT id FROM workshop_registrations")->fetchAll()) ?>)
      </a>
    </div>

    <!-- جدول نمایش اطلاعات -->
    <div class="overflow-x-auto">
      <?php if ($tab === 'workshops'): ?>
        <!-- جدول کارگاه‌ها -->
        <table class="w-full text-right text-xs">
          <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
            <tr>
              <th class="p-3">#</th>
              <th class="p-3">نام و تلفن شرکت‌کننده</th>
              <th class="p-3">حوزه فعالیت</th>
              <th class="p-3">شیوه حضور</th>
              <th class="p-3">کد پیگیری</th>
              <th class="p-3">پرسش حقوقی</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php if (empty($rows)): ?>
              <tr><td colspan="6" class="p-6 text-center text-slate-400">هنوز هیچ ثبت‌نامی در کارگاه انجام نشده است.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="p-3 font-bold text-slate-400"><?= $r['id'] ?></td>
                <td class="p-3">
                  <div class="font-bold text-slate-900"><?= htmlspecialchars($r['fullname']) ?></div>
                  <div class="text-[11px] text-slate-500 dir-ltr text-right mt-0.5"><?= htmlspecialchars($r['phone']) ?></div>
                </td>
                <td class="p-3 font-medium"><?= htmlspecialchars($r['role']) ?></td>
                <td class="p-3"><?= htmlspecialchars($r['attendance']) ?></td>
                <td class="p-3 font-mono text-[11px] text-slate-600 font-bold"><?= htmlspecialchars($r['tracking_code']) ?></td>
                <td class="p-3 max-w-xs truncate text-slate-500" title="<?= htmlspecialchars($r['question']) ?>">
                  <?= htmlspecialchars($r['question'] ?: 'ندارد') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <!-- جدول نوبت‌های مشاوره -->
        <table class="w-full text-right text-xs">
          <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
            <tr>
              <th class="p-3">#</th>
              <th class="p-3">نام و تلفن موکل</th>
              <th class="p-3">موضوع حقوقی</th>
              <th class="p-3">زمان نوبت</th>
              <th class="p-3">نوع مشاوره</th>
              <th class="p-3">وضعیت پرداخت</th>
              <th class="p-3">کد رهگیری</th>
              <th class="p-3">شرح پرونده</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php if (empty($rows)): ?>
              <tr><td colspan="8" class="p-6 text-center text-slate-400">هنوز هیچ نوبتی ثبت نشده است.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="p-3 font-bold text-slate-400"><?= $r['id'] ?></td>
                <td class="p-3">
                  <div class="font-bold text-slate-900"><?= htmlspecialchars($r['fullname']) ?></div>
                  <div class="text-[11px] text-slate-500 dir-ltr text-right mt-0.5"><?= htmlspecialchars($r['phone']) ?></div>
                </td>
                <td class="p-3 font-medium"><?= htmlspecialchars($r['topic']) ?></td>
                <td class="p-3">
                  <div class="font-bold text-slate-800"><?= htmlspecialchars($r['booking_date']) ?></div>
                  <div class="text-[11px] text-slate-500"><?= htmlspecialchars($r['booking_time']) ?></div>
                </td>
                <td class="p-3"><?= htmlspecialchars($r['consultation_type']) ?> <span class="text-slate-400 text-[10px]">(<?= htmlspecialchars($r['urgency']) ?>)</span></td>
                <td class="p-3">
                  <?php if ($r['payment_status'] === 'paid'): ?>
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-[11px]">پرداخت شده</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold text-[11px]">در انتظار پرداخت</span>
                  <?php endif; ?>
                </td>
                <td class="p-3 font-mono text-[11px] text-slate-600"><?= htmlspecialchars($r['ref_id'] ?? '-') ?></td>
                <td class="p-3 max-w-xs truncate text-slate-500" title="<?= htmlspecialchars($r['description']) ?>">
                  <?= htmlspecialchars($r['description'] ?: 'ندارد') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>