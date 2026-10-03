<?php
session_start();
// ربط قاعدة البيانات
$conn = new mysqli("localhost", "root", "", "aliabdeen3");
$conn->set_charset("utf8");

$grades_list = [
    'prim4' => 'رابعة ابتدائي',
    'prim5' => 'خامسة ابتدائي',
    'prim6' => 'سادسة ابتدائي',
    'prep1' => 'أولى إعدادي',
    'prep2' => 'تانية إعدادي',
    'prep3' => 'تالتة إعدادي',
    'sec1' => 'أولى ثانوي',
    'sec2' => 'تانية ثانوي',
    'sec3' => 'تالتة ثانوي'
];

$msg = "";

// معالجة التسجيل الجديد للطالب
if(isset($_POST['register_btn'])) {
    $name = $conn->real_escape_string($_POST['reg_name']);
    $phone = $conn->real_escape_string($_POST['reg_phone']);
    $pass = $_POST['reg_pass'];
    $grade = $_POST['reg_grade'];

    $check = $conn->query("SELECT * FROM students WHERE phone = '$phone'");
    if($check->num_rows > 0) {
        $msg = "<div class='alert error'>رقم الهاتف مسجل من قبل، سجل دخولك مباشرة!</div>";
    } else {
        $conn->query("INSERT INTO students (student_name, phone, password, target_grade, points) VALUES ('$name', '$phone', '$pass', '$grade', 0)");
        $msg = "<div class='alert success'>تم إنشاء الحساب بنجاح! يمكنك تسجيل الدخول الان.</div>";
    }
}

// معالجة تسجيل الدخول (طالب أو أدمن)
if(isset($_POST['login_btn'])) {
    $identifier = $conn->real_escape_string($_POST['login_id']); // قد يكون رقم هاتف أو اسم مستخدم أدمن
    $pass = $_POST['login_pass'];

    // فحص لو كان الأدمن
    if($identifier == 'admin' && $pass == 'admin123') {
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = 'المستر';
        header("Location: index.php");
        exit;
    }

    // فحص لو كان طالب
    $st_q = $conn->query("SELECT * FROM students WHERE phone = '$identifier' AND password = '$pass'");
    if($st_q && $st_q->num_rows > 0) {
        $st_data = $st_q->fetch_assoc();
        $_SESSION['user_type'] = 'student';
        $_SESSION['user_id'] = $st_data['id'];
        $_SESSION['user_name'] = $st_data['student_name'];
        $_SESSION['user_grade'] = $st_data['target_grade'];
        $_SESSION['user_points'] = $st_data['points'];
        header("Location: index.php");
        exit;
    } else {
        $msg = "<div class='alert error'>بيانات تسجيل الدخول غير صحيحة!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول والإنشاء - منصة مستر علي عابدين</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --accent: #06b6d4; --text: #f8fafc; --success: #10b981; --danger: #ef4444; }
        body { font-family: Tahoma, sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: var(--card); padding: 30px; border-radius: 12px; width: 100%; max-width: 420px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        h2 { text-align: center; color: var(--accent); margin-bottom: 20px; }
        input, select { width: 100%; padding: 10px; margin: 8px 0 15px 0; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 11px; background: var(--accent); border: none; border-radius: 6px; color: #0f172a; font-weight: bold; cursor: pointer; font-size: 15px; }
        button:hover { opacity: 0.9; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; }
        .alert.success { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .alert.error { background: rgba(239, 68, 68, 0.2); color: var(--danger); }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 1px solid #334155; }
        .tab { flex: 1; text-align: center; padding: 10px; cursor: pointer; color: #94a3b8; font-weight: bold; }
        .tab.active { color: var(--accent); border-bottom: 2px solid var(--accent); }
        .form-section { display: none; }
        .form-section.active { display: block; }
    </style>
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>منصة مستر علي عابدين التعليمية</h2>
        <?php echo $msg; ?>
        
        <div class="tabs">
            <div class="tab active" onclick="switchTab('loginSection')">تسجيل الدخول</div>
            <div class="tab" onclick="switchTab('registerSection')">طالب جديد</div>
        </div>

        <!-- نموذج تسجيل الدخول -->
        <div id="loginSection" class="form-section active">
            <form method="POST">
                <label>رقم الهاتف (أو حساب الأدمن)</label>
                <input type="text" name="login_id" required placeholder="أدخل رقم هاتفك...">
                <label>كلمة المرور</label>
                <input type="password" name="login_pass" required placeholder="كلمة المرور...">
                <button type="submit" name="login_btn">دخول للمنصة</button>
            </form>
        </div>

        <!-- نموذج إنشاء حساب طالب جديد -->
        <div id="registerSection" class="form-section">
            <form method="POST">
                <label>اسم الطالب الكامل</label>
                <input type="text" name="reg_name" required placeholder="اكتب اسمك الصريح...">
                <label>رقم الهاتف</label>
                <input type="text" name="reg_phone" required placeholder="رقم الهاتف للتسجيل...">
                <label>كلمة المرور</label>
                <input type="password" name="reg_pass" required placeholder="اختر كلمة مرور قوية...">
                <label>الصف الدراسي</label>
                <select name="reg_grade" required>
                    <?php foreach($grades_list as $key => $name): ?>
                        <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="register_btn">إتمام التسجيل</button>
            </form>
        </div>
    </div>
</body>
</html>