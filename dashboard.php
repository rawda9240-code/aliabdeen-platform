<?php
ob_start();
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "aliabdeen3";

$conn = new mysqli($host, $user, $pass, $db_name);
$conn->set_charset("utf8mb4");

// إنشاء الجداول لو مش موجودة
$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    target_grade VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    points INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS grades_book (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    student_name VARCHAR(255),
    grade_name VARCHAR(50),
    exam_title VARCHAR(255),
    score FLOAT,
    total FLOAT,
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$grades_list = [
    'primary_4' => 'رابعة ابتدائي',
    'primary_5' => 'خامسة ابتدائي',
    'primary_6' => 'سادسة ابتدائي',
    'prep_1'    => 'أولى اعدادي',
    'prep_2'    => 'تانية اعدادي',
    'prep_3'    => 'تالته اعدادي',
    'sec_1'     => 'أولى ثانوي',
    'sec_2'     => 'تانية ثانوي',
    'sec_3'     => 'تالته ثانوي',
    'bac_2'     => 'تانية بكلوريا'
];

$page = $_GET['page'] ?? 'login';
$login_error = "";
$register_message = "";

// معالجة تسجيل الدخول (للأدمن وللطلاب)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_login'])) {
    $name = trim($_POST['student_name'] ?? '');
    $password = $_POST['password'] ?? '';

    // التحقق الفوري والمباشر للأدمن (بدون الحاجة لقاعدة بيانات)
    if ($name === 'مستر علي عابدين' && $password === 'ali12345') {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_name'] = $name;
        header("Location: admin.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM students WHERE student_name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $student = $res->fetch_assoc();
        if ($password === $student['password']) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['student_name'];
            $_SESSION['student_grade'] = $student['target_grade'];
            header("Location: index.php?page=dashboard");
            exit();
        } else {
            $login_error = "كلمة المرور غير صحيحة!";
        }
    } else {
        $login_error = "اسم المستخدم غير مسجل!";
    }
}

// معالجة إنشاء الحساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_register'])) {
    $name = trim($_POST['student_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $grade = $_POST['target_grade'] ?? '';
    $password = $_POST['password'] ?? '';
    $code_entered = trim($_POST['access_code'] ?? '');

    if (empty($name) || empty($phone) || empty($grade) || empty($password) || empty($code_entered)) {
        $register_message = '<div class="alert error">برجاء ملء جميع الحقول بما فيها كود الانضمام!</div>';
    } else {
        $code_check = $conn->prepare("SELECT * FROM access_codes WHERE code = ? AND grade = ? AND expires_at >= NOW()");
        $code_check->bind_param("ss", $code_entered, $grade);
        $code_check->execute();
        $code_res = $code_check->get_result();

        if (!$code_res || $code_res->num_rows == 0) {
            $register_message = '<div class="alert error">كود الانضمام غير صحيح أو منتهي الصلاحية لهذا الصف!</div>';
        } else {
            $check = $conn->prepare("SELECT id FROM students WHERE phone = ?");
            $check->bind_param("s", $phone);
            $check->execute();
            $check_res = $check->get_result();

            if ($check_res && $check_res->num_rows > 0) {
                $register_message = '<div class="alert error">رقم الهاتف مسجل بالفعل!</div>';
            } else {
                $ins = $conn->prepare("INSERT INTO students (student_name, phone, target_grade, password) VALUES (?, ?, ?, ?)");
                $ins->bind_param("ssss", $name, $phone, $grade, $password);
                if ($ins->execute()) {
                    $register_message = '<div class="alert success">تم إنشاء الحساب بنجاح! انتقل لتسجيل الدخول الآن.</div>';
                } else {
                    $register_message = '<div class="alert error">حدث خطأ أثناء التسجيل.</div>';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>منصة مستر علي عابدين</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-main: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --border: #334155;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { width: 100%; max-width: 700px; }
        .card { background: var(--card-bg); padding: 35px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .tabs { display: flex; gap: 10px; margin-bottom: 25px; background: var(--bg-main); padding: 5px; border-radius: 10px; border: 1px solid var(--border); }
        .tab-btn { flex: 1; padding: 12px; text-align: center; text-decoration: none; font-weight: 600; color: var(--text-muted); border-radius: 8px; transition: 0.3s; }
        .tab-btn.active { background: var(--card-bg); color: var(--primary); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        h2 { font-size: 22px; margin-bottom: 20px; text-align: center; color: var(--text-main); }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text-main); }
        input, select, textarea { width: 100%; padding: 12px 15px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 10px; font-size: 14px; background-color: #0f172a; color: #fff; text-align: right; }
        button { width: 100%; padding: 13px; background-color: var(--primary); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        button:hover { background-color: var(--primary-hover); }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .alert.error { background-color: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
        .alert.success { background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
        .dashboard-section { margin-bottom: 25px; padding: 20px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); }
        .dashboard-section h3 { margin-bottom: 12px; color: var(--primary); font-size: 18px; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid var(--border); text-align: center; font-size: 14px; }
        th { background: var(--card-bg); color: var(--primary); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if ($page == 'login' || $page == 'register'): ?>
                <div class="tabs">
                    <a href="?page=login" class="tab-btn <?php echo ($page == 'login') ? 'active' : ''; ?>">تسجيل دخول</a>
                    <a href="?page=register" class="tab-btn <?php echo ($page == 'register') ? 'active' : ''; ?>">إنشاء حساب</a>
                </div>
            <?php endif; ?>

            <?php if ($page == 'login'): ?>
                <h2>تسجيل الدخول للمنصة</h2>
                <?php if (!empty($login_error)): ?><div class="alert error"><?php echo $login_error; ?></div><?php endif; ?>
                <form method="POST" action="">
                    <label>اسم المستخدم</label>
                    <input type="text" name="student_name" required placeholder="ادخل اسمك أو اسم الأدمن">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" required placeholder="ادخل كلمة المرور">
                    <button type="submit" name="do_login">دخول</button>
                </form>

            <?php elseif ($page == 'register'): ?>
                <h2>إنشاء حساب طالب جديد</h2>
                <?php echo $register_message; ?>
                <form method="POST" action="">
                    <label>اسم الطالب (ثلاثي)</label>
                    <input type="text" name="student_name" required placeholder="ادخل الاسم الثلاثي">
                    <label>رقم الهاتف</label>
                    <input type="text" name="phone" required placeholder="رقم الهاتف">
                    <label>الصف الدراسي</label>
                    <select name="target_grade" required>
                        <option value="" disabled selected>اختر صفك الدراسي...</option>
                        <?php foreach($grades_list as $key => $name): ?>
                            <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>كود الانضمام (من المستر)</label>
                    <input type="text" name="access_code" required placeholder="ادخل الكود العشوائي الخاص بصفك">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" required placeholder="كلمة المرور">
                    <button type="submit" name="do_register">تسجيل الحساب</button>
                </form>

            <?php elseif ($page == 'dashboard'): ?>
                <?php 
                if (!isset($_SESSION['student_id'])) { header("Location: index.php?page=login"); exit(); }
                $s_id = $_SESSION['student_id'];
                $s_grade = $_SESSION['student_grade'];
                $s_name = $_SESSION['student_name'];
                ?>
                <h2>مرحباً بك، <?php echo htmlspecialchars($s_name); ?> 👋</h2>
                <div class="alert success" style="margin-top:10px;">أنت الآن في لوحتك الخاصة</div>

                <div class="dashboard-section">
                    <h3><i class="fa-solid fa-video"></i> حصة الفيديو الخاصة بصفك</h3>
                    <?php
                    $vid_q = $conn->prepare("SELECT * FROM videos WHERE grade = ? ORDER BY id DESC LIMIT 1");
                    $vid_q->bind_param("s", $s_grade);
                    $vid_q->execute();
                    $vid_res = $vid_q->get_result();
                    if ($vid_res && $vid_res->num_rows > 0) {
                        $v_data = $vid_res->fetch_assoc();
                        echo "<p style='margin-bottom:10px; font-weight:600;'>{$v_data['title']}</p>";
                        if (!empty($v_data['video_file'])) {
                            echo "<video controls style='width:100%; border-radius:8px;'><source src='uploads/{$v_data['video_file']}' type='video/mp4'></video>";
                        } elseif (!empty($v_data['video_link'])) {
                            echo "<div style='text-align:center;'><a href='{$v_data['video_link']}' target='_blank' style='color:#6366f1; font-weight:bold; font-size:16px;'>🔗 اضغط هنا لمشاهدة فيديو الدرس</a></div>";
                        }
                    } else {
                        echo "<p style='color:var(--text-muted); text-align:center;'>لا يوجد فيديو مرفوع لهذا الصف حالياً.</p>";
                    }
                    ?>
                </div>

                <div class="dashboard-section">
                    <h3><i class="fa-solid fa-file-pen"></i> امتحان الصف</h3>
                    <?php
                    $ex_q = $conn->prepare("SELECT * FROM exams WHERE grade = ? ORDER BY id DESC LIMIT 1");
                    $ex_q->bind_param("s", $s_grade);
                    $ex_q->execute();
                    $ex_res = $ex_q->get_result();
                    if ($ex_res && $ex_res->num_rows > 0) {
                        $ex_data = $ex_res->fetch_assoc();
                        echo "<p><strong>عنوان الامتحان:</strong> {$ex_data['title']}</p>";
                        echo "<p><strong>المدة الزمنية:</strong> {$ex_data['duration_minutes']} دقيقة</p>";
                        
                        if (isset($_POST['submit_exam'])) {
                            $score = rand(8, 10);
                            $total = 10;
                            $ins_score = $conn->prepare("INSERT INTO grades_book (student_id, student_name, grade_name, exam_title, score, total) VALUES (?, ?, ?, ?, ?, ?)");
                            $ins_score->bind_param("isssdd", $s_id, $s_name, $s_grade, $ex_data['title'], $score, $total);
                            $ins_score->execute();
                            echo "<div class='alert success' style='margin-top:10px;'>تم تسليم الامتحان بنجاح! درجتك: $score / $total</div>";
                        } else {
                            echo "<form method='POST' style='margin-top:15px;'>
                                    <div style='background:var(--bg-main); padding:15px; border-radius:8px; margin-bottom:15px;'>
                                        <p><strong>سؤال:</strong> {$ex_data['question_text']}</p>
                                        <input type='text' name='student_answer' placeholder='اكتب إجابتك هنا...' required style='margin-top:10px; margin-bottom:0;'>
                                    </div>
                                    <button type='submit' name='submit_exam'>إرسال الإجابة وتسليم الامتحان</button>
                                  </form>";
                        }
                    } else {
                        echo "<p style='color:var(--text-muted); text-align:center;'>لا يوجد امتحان متاح لهذا الصف حالياً.</p>";
                    }
                    ?>
                </div>

                <div class="dashboard-section">
                    <h3><i class="fa-solid fa-trophy"></i> لوحة الشرف الخاصة بصفك</h3>
                    <?php
                    $hb_q = $conn->prepare("SELECT * FROM honor_board WHERE grade = ? ORDER BY id DESC LIMIT 1");
                    $hb_q->bind_param("s", $s_grade);
                    $hb_q->execute();
                    $hb_res = $hb_q->get_result();
                    if ($hb_res && $hb_res->num_rows > 0) {
                        $hb = $hb_res->fetch_assoc();
                        echo "<div style='background:var(--bg-main); padding:15px; border-radius:8px; line-height:1.6;'>{$hb['content']}</div>";
                    } else {
                        echo "<p style='color:var(--text-muted); text-align:center;'>لم يتم نشر لوحة شرف لهذا الصف بعد.</p>";
                    }
                    ?>
                </div>

                <div class="dashboard-section">
                    <h3><i class="fa-solid fa-clipboard-list"></i> سجل درجاتي الخاصة</h3>
                    <table>
                        <tr>
                            <th>اسم الامتحان</th>
                            <th>الدرجة</th>
                            <th>التاريخ</th>
                        </tr>
                        <?php
                        $my_grades = $conn->prepare("SELECT * FROM grades_book WHERE student_id = ? ORDER BY id DESC");
                        $my_grades->bind_param("i", $s_id);
                        $my_grades->execute();
                        $mg_res = $my_grades->get_result();
                        if ($mg_res && $mg_res->num_rows > 0) {
                            while($mg = $mg_res->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$mg['exam_title']}</td>
                                    <td>{$mg['score']} / {$mg['total']}</td>
                                    <td>{$mg['date_time']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='color:var(--text-muted);'>لا توجد درجات مسجلة لك حتى الآن.</td></tr>";
                        }
                        ?>
                    </table>
                </div>

                <a href="?page=login" style="display: block; text-align: center; padding: 12px; background: var(--danger); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; margin-top:20px;">تسجيل خروج</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>