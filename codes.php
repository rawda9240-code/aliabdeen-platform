<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}

$generated_codes = [];
$selected_expiry = "شهر واحد";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = intval($_POST['code_count']);
    $selected_expiry = $_POST['code_expiry'];
    
    for ($i = 1; $i <= $count; $i++) {
        $generated_codes[] = 'ABDEN-' . rand(100000, 999999);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توليد الأكواد العشوائية - مستر علي عابدين</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Tahoma, sans-serif; }
        body { background-color: #0f172a; color: #fff; padding: 40px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .box { background: #1e293b; padding: 30px; border-radius: 8px; border: 1px solid #334155; width: 100%; max-width: 550px; }
        h3 { color: #38bdf8; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        label { display: block; margin-top: 12px; margin-bottom: 5px; color: #94a3b8; font-size: 13px; }
        input, select { width: 100%; padding: 10px; background: #0f172a; border: 1px solid #475569; color: #fff; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
        button { background: #38bdf8; color: #0f172a; border: none; padding: 10px 20px; font-weight: bold; border-radius: 5px; cursor: pointer; margin-top: 15px; width: 100%; }
        button:hover { background: #0ea5e9; }
        .codes-result { background: #0f172a; border: 1px dashed #38bdf8; padding: 15px; border-radius: 6px; margin-top: 15px; color: #38bdf8; font-family: monospace; line-height: 1.8; text-align: right; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; }
        .back-link:hover { color: #38bdf8; }
    </style>
</head>
<body>
    <div class="box">
        <h3><i class="fa-solid fa-key"></i> توليد الأكواد العشوائية بالصلاحية</h3>
        
        <form method="POST">
            <label>عدد الأكواد المطلوبة</label>
            <input type="number" name="code_count" value="5" min="1" max="50">
            
            <label>مدة الصلاحية</label>
            <select name="code_expiry">
                <option value="شهر واحد" <?php if($selected_expiry == "شهر واحد") echo "selected"; ?>>شهر واحد</option>
                <option value="3 أشهر" <?php if($selected_expiry == "3 أشهر") echo "selected"; ?>>3 أشهر</option>
                <option value="6 أشهر" <?php if($selected_expiry == "6 أشهر") echo "selected"; ?>>6 أشهر</option>
                <option value="سنة كاملة" <?php if($selected_expiry == "سنة كاملة") echo "selected"; ?>>سنة كاملة</option>
            </select>

            <button type="submit">توليد الأكواد فوراً</button>
        </form>

        <?php if (!empty($generated_codes)): ?>
            <div class="codes-result">
                <strong>الأكواد المولدة (الصلاحية: <?php echo htmlspecialchars($selected_expiry); ?>):</strong><br><br>
                <?php foreach ($generated_codes as $index => $c): ?>
                    <?php echo ($index + 1) . "- [ " . $c . " ] &nbsp; (صالح لمدة: " . htmlspecialchars($selected_expiry) . ")"; ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-right"></i> العودة إلى لوحة التحكم الرئيسية</a>
    </div>
</body>
</html>