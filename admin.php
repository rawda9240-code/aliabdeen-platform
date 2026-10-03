<?php
ob_start();
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ali_abdeen_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// مصفوفة الصفوف الدراسية (مضاف إليها الثانية باكالوريا)
$all_grades = [
    'primary_1' => 'الصف الأول الابتدائي',
    'primary_2' => 'الصف الثاني الابتدائي',
    'primary_3' => 'الصف الثالث الابتدائي',
    'primary_4' => 'الصف الرابع الابتدائي',
    'primary_5' => 'الصف الخامس الابتدائي',
    'primary_6' => 'الصف السادس الابتدائي',
    'prep_1' => 'الصف الأول الإعدادي',
    'prep_2' => 'الصف الثاني الإعدادي',
    'prep_3' => 'الصف الثالث الإعدادي',
    'sec_1' => 'الصف الأول الثانوي',
    'sec_2' => 'الصف الثاني الثانوي',
    'sec_2_bac' => 'الثانية باكالوريا',
    'sec_3' => 'الصف الثالث الثانوي (الثالثة باكالوريا)'
];

$admin_error = '';

// تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['admin_logged'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $admin_error = 'اسم المستخدم أو كلمة المرور غير صحيحة!';
    }
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged']);
    header("Location: admin.php");
    exit();
}

// لو مش مسجل دخول، اعرض له صفحة تسجيل الدخول فقط
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true):
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول لوحة التحكم - مستر علي عابدين</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; border-top: 4px solid #e74a3b; }
        h2 { color: #e74a3b; margin-top: 0; font-size: 20px; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #5a5c69; font-size: 14px; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #d1d3e2; border-radius: 6px; box-sizing: border-box; }
        .btn { background: #e74a3b; color: white; border: none; padding: 10px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; }
        .btn:hover { opacity: 0.9; }
        .alert { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>⚙️ دخول الإدارة</h2>
        <?php if(!empty($admin_error)) echo "<div class='alert'>$admin_error</div>"; ?>
        <form method="POST">
            <input type="hidden" name="admin_login" value="1">
            <label>اسم المستخدم:</label>
            <input type="text" name="username" required>
            <label>كلمة المرور:</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn">دخول</button>
        </form>
        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="text-decoration: none; color: #4e73df; font-size: 13px;">الرجوع للموقع الرئيسي ⬅</a>
        </div>
    </div>
</body>
</html>
<?php
exit();
endif;

// --- لوحة التحكم الكاملة ---

// 1. حذف طالب
if (isset($_GET['delete_student'])) {
    $del_id = intval($_GET['delete_student']);
    $conn->query("DELETE FROM users WHERE id = $del_id");
    echo "<script>alert('تم حذف الطالب بنجاح'); window.location='admin.php';</script>";
    exit();
}

// 2. نشر لوحة الشرف
if (isset($_GET['action']) && $_GET['action'] == 'save_honor' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $h_grade = $_POST['honor_grade'];
    $h_content = $_POST['honor_content'];
    $ins = $conn->prepare("INSERT INTO honor_board (grade, content) VALUES (?, ?)");
    $ins->bind_param("ss", $h_grade, $h_content);
    $ins->execute();
    echo "<script>alert('تم نشر لوحة الشرف بنجاح!'); window.location='admin.php';</script>";
    exit();
}

// 3. نشر فيديو جديد
if (isset($_GET['action']) && $_GET['action'] == 'save_video' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $v_grade = $_POST['video_grade'];
    $v_title = $_POST['video_title'];
    $v_type = $_POST['video_type'];
    $v_link = '';

    if ($v_type == 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_name = time() . "_" . basename($_FILES["video_file"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
            $v_link = $target_file;
        }
    } else {
        $v_link = $_POST['video_link'];
    }

    $v_stmt = $conn->prepare("INSERT INTO videos (grade, title, youtube_link) VALUES (?, ?, ?)");
    $v_stmt->bind_param("sss", $v_grade, $v_title, $v_link);
    $v_stmt->execute();
    echo "<script>alert('تم نشر الفيديو بنجاح!'); window.location='admin.php';</script>";
    exit();
}

// 4. إضافة امتحان جديد
if (isset($_GET['action']) && $_GET['action'] == 'save_exam' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $ex_grade = $_POST['exam_grade'];
    $ex_title = $_POST['exam_title'];
    $ex_duration = intval($_POST['exam_duration']);
    
    $questions_array = [];
    for($i=1; $i<=30; $i++) {
        if(!empty($_POST['q_text_'.$i])) {
            $questions_array[] = [
                'question' => $_POST['q_text_'.$i],
                'options' => [
                    $_POST['q_'.$i.'_opt_0'],
                    $_POST['q_'.$i.'_opt_1'],
                    $_POST['q_'.$i.'_opt_2'],
                    $_POST['q_'.$i.'_opt_3']
                ],
                'correct' => intval($_POST['q_correct_'.$i])
            ];
        }
    }
    
    $json_questions = json_encode($questions_array, JSON_UNESCAPED_UNICODE);
    $ex_stmt = $conn->prepare("INSERT INTO exams (grade, exam_title, questions, duration) VALUES (?, ?, ?, ?)");
    $ex_stmt->bind_param("sssi", $ex_grade, $ex_title, $json_questions, $ex_duration);
    $ex_stmt->execute();
    echo "<script>alert('تم إضافة الامتحان ونشره بنجاح!'); window.location='admin.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم الرئيسية - مستر علي عابدين</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 0 15px rgba(0,0,0,0.05); border-top: 4px solid #4e73df; }
        h2 { margin-top: 0; color: #2e384d; font-size: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #4e5d6c; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #d1d3e2; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .btn { background: #4e73df; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 15px; font-weight: bold; }
        .btn:hover { opacity: 0.9; }
        .btn-danger { background: #e74a3b; }
        .btn-success { background: #1cc88a; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: right; border-bottom: 1px solid #e3e6f0; font-size: 14px; }
        th { background: #f8f9fc; color: #4e73df; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h2>🛠 لوحة التحكم الإدارية - مستر علي عابدين</h2>
        <div>
            <a href="index.php" class="btn" style="background: #5a5c69; text-decoration:none; padding: 8px 15px; font-size: 13px;">الذهاب للموقع 🌐</a>
            <a href="admin.php?logout=1" class="btn btn-danger" style="text-decoration:none; padding: 8px 15px; font-size: 13px;">تسجيل خروج 🔒</a>
        </div>
    </div>

    <!-- رفع فيديو جديد -->
    <div class="card" style="border-top-color: #f6c23e;">
        <h2>🎥 نشر فيديو تعليمي جديد</h2>
        <form action="admin.php?action=save_video" method="POST" enctype="multipart/form-data">
            <label>الصف الدراسي:</label>
            <select name="video_grade" required>
                <?php foreach($all_grades as $k => $v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
            </select>
            <label>عنوان الفيديو:</label>
            <input type="text" name="video_title" required>
            <label>نوع الإضافة:</label>
            <select name="video_type" id="video_type" onchange="toggleVideoInput()" required>
                <option value="link">رابط يوتيوب</option>
                <option value="upload">رفع ملف فيديو (MP4)</option>
            </select>
            <div id="link_input_div">
                <label>رابط يوتيوب:</label>
                <input type="text" name="video_link">
            </div>
            <div id="upload_input_div" style="display:none;">
                <label>اختر ملف الفيديو:</label>
                <input type="file" name="video_file" accept="video/mp4">
            </div>
            <button type="submit" class="btn btn-success" style="width: 100%;">نشر الفيديو 🚀</button>
        </form>
    </div>

    <script>
    function toggleVideoInput() {
        var t = document.getElementById('video_type').value;
        document.getElementById('link_input_div').style.display = (t === 'upload') ? 'none' : 'block';
        document.getElementById('upload_input_div').style.display = (t === 'upload') ? 'block' : 'none';
    }
    </script>

    <!-- إضافة امتحان -->
    <div class="card" style="border-top-color: #e74a3b;">
        <h2>📝 إضافة امتحان جديد (حتى 30 سؤالاً)</h2>
        <form action="admin.php?action=save_exam" method="POST">
            <label>الصف الدراسي:</label>
            <select name="exam_grade" required>
                <?php foreach($all_grades as $k => $v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
            </select>
            <label>عنوان الامتحان:</label>
            <input type="text" name="exam_title" required>
            <label>مدة الامتحان (بالدقائق):</label>
            <input type="number" name="exam_duration" value="15" min="1" required>
            
            <div style="background:#f8f9fc; padding:15px; border-radius:8px; margin-top:15px;">
                <?php for($i=1; $i<=30; $i++): ?>
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #d1d3e2; padding-bottom: 10px;">
                    <label>السؤال <?php echo $i; ?>:</label>
                    <input type="text" name="q_text_<?php echo $i; ?>" placeholder="نص السؤال (اتركه فارغاً لو مش محتاجه)...">
                    <input type="text" name="q_<?php echo $i; ?>_opt_0" placeholder="الخيار الأول">
                    <input type="text" name="q_<?php echo $i; ?>_opt_1" placeholder="الخيار الثاني">
                    <input type="text" name="q_<?php echo $i; ?>_opt_2" placeholder="الخيار الثالث">
                    <input type="text" name="q_<?php echo $i; ?>_opt_3" placeholder="الخيار الرابع">
                    <label>الإجابة الصحيحة:</label>
                    <select name="q_correct_<?php echo $i; ?>">
                        <option value="0">الخيار الأول</option><option value="1">الخيار الثاني</option><option value="2">الخيار الثالث</option><option value="3">الخيار الرابع</option>
                    </select>
                </div>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-danger" style="width: 100%; margin-top:15px;">حفظ ونشر الامتحان 🚀</button>
        </form>
    </div>

    <!-- لوحة الشرف -->
    <div class="card" style="border-top-color: #1cc88a;">
        <h2>🏆 نشر لوحة الشرف</h2>
        <form action="admin.php?action=save_honor" method="POST">
            <label>الصف الدراسي:</label>
            <select name="honor_grade" required>
                <?php foreach($all_grades as $k => $v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
            </select>
            <label>أسماء المتفوقين:</label>
            <textarea name="honor_content" rows="3" required></textarea>
            <button type="submit" class="btn btn-success" style="width: 100%;">نشر لوحة الشرف</button>
        </form>
    </div>

    <!-- سجل الطلاب -->
    <div class="card">
        <h2>👥 سجل الطلاب المسجلين</h2>
        <table>
            <tr><th>#</th><th>اسم الطالب</th><th>الصف</th><th>الإجراء</th></tr>
            <?php
            $st_res = $conn->query("SELECT * FROM users ORDER BY id DESC");
            if ($st_res && $st_res->num_rows > 0) {
                $idx = 1;
                while($st = $st_res->fetch_assoc()) {
                    $g_name = $all_grades[$st['grade']] ?? $st['grade'];
                    echo "<tr><td>{$idx}</td><td><strong>{$st['name']}</strong></td><td>{$g_name}</td><td><a href='admin.php?delete_student={$st['id']}' class='btn btn-danger' style='padding:4px 10px; font-size:12px; text-decoration:none;' onclick='return confirm(\"هل أنت متأكد من الحذف؟\");'>حذف</a></td></tr>";
                    $idx++;
                }
            } else { echo "<tr><td colspan='4'>لا توجد بيانات.</td></tr>"; }
            ?>
        </table>
    </div>

    <!-- الدرجات -->
    <div class="card">
        <h2>📊 سجل درجات الامتحانات</h2>
        <table>
            <tr><th>اسم الطالب</th><th>الصف</th><th>اسم الامتحان</th><th>الدرجة</th><th>التاريخ</th></tr>
            <?php
            $r_result = $conn->query("SELECT * FROM student_results ORDER BY id DESC");
            if ($r_result && $r_result->num_rows > 0) {
                while($rw = $r_result->fetch_assoc()) {
                    $g_nm = $all_grades[$rw['grade']] ?? $rw['grade'];
                    echo "<tr><td>{$rw['student_name']}</td><td>{$g_nm}</td><td>{$rw['exam_title']}</td><td><strong style='color:#1cc88a;'>{$rw['score']}</strong> / {$rw['total']}</td><td>{$rw['exam_date']}</td></tr>";
                }
            } else { echo "<tr><td colspan='5'>لا توجد نتائج مسجلة.</td></tr>"; }
            ?>
        </table>
    </div>
</div>
</body>
</html>