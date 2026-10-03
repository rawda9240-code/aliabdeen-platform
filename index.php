<?php
ob_start();
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'aliabdeen3';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// التأكد من وجود الجداول والأعمدة الحديثة تلقائياً لمنع أي أخطاء
$conn->query("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), password VARCHAR(255), grade VARCHAR(50))");
$conn->query("CREATE TABLE IF NOT EXISTS honor_board (id INT AUTO_INCREMENT PRIMARY KEY, grade VARCHAR(50), content TEXT)");
$conn->query("CREATE TABLE IF NOT EXISTS videos (id INT AUTO_INCREMENT PRIMARY KEY, grade VARCHAR(50), title VARCHAR(255), youtube_link TEXT)");
$conn->query("CREATE TABLE IF NOT EXISTS exams (id INT AUTO_INCREMENT PRIMARY KEY, grade VARCHAR(50), exam_title VARCHAR(255), questions TEXT, duration INT DEFAULT 15)");
$conn->query("CREATE TABLE IF NOT EXISTS student_results (id INT AUTO_INCREMENT PRIMARY KEY, student_name VARCHAR(255), grade VARCHAR(50), exam_title VARCHAR(255), score INT, total INT, exam_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

// مصفوفة الصفوف الدراسية بدقة لكل المراحل
$all_grades = [
    'g4_primary' => 'الصف الرابع الابتدائي',
    'g5_primary' => 'الصف الخامس الابتدائي',
    'g6_primary' => 'الصف السادس الابتدائي',
    'g1_prep'    => 'الصف الأول الإعدادي',
    'g2_prep'    => 'الصف الثاني الإعدادي',
    'g3_prep'    => 'الصف الثالث الإعدادي',
    'g1_sec'     => 'الصف الأول الثانوي',
    'g2_sec'     => 'الصف الثاني الثانوي',
    'g3_sec'     => 'الصف الثالث الثانوي',
    'g2_bac'     => 'ثانية باكلوريا'
];

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// معالجة تسجيل دخول الطالب
$login_error = "";
if ($page == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_logged'] = true;
            $_SESSION['student_name'] = $row['name'];
            $_SESSION['student_grade'] = $row['grade'];
            header("Location: index.php?page=student_dashboard");
            exit();
        }
    }
    $login_error = "اسم الطالب أو كلمة المرور غير صحيحة!";
}

// معالجة حساب جديد
$reg_error = "";
if ($page == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $grade = $_POST['grade'];

    $check = $conn->prepare("SELECT id FROM users WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $reg_error = "اسم الطالب مستخدم من قبل، يرجى اختيار اسم آخر.";
    } else {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, password, grade) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $hashed_pass, $grade);
        if ($stmt->execute()) {
            header("Location: index.php?page=login");
            exit();
        } else {
            $reg_error = "حدث خطأ غير متوقع، حاول مرة أخرى.";
        }
    }
}

// دخول الإدارة
$admin_error = "";
if ($page == 'admin_login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['admin_logged'] = true;
        header("Location: index.php?page=admin_dashboard");
        exit();
    } else {
        $admin_error = "بيانات المدير غير صحيحة!";
    }
}

if ($page == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة مستر علي عابدين التعليمية</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #224abe;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
            --bg-color: #f8f9fc;
            --card-bg: #ffffff;
            --text-color: #5a5c69;
            --text-dark: #2e384d;
        }
        * { box-sizing: border-box; font-family: 'Cairo', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-color); margin: 0; padding: 0; }
        header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        header h2 { margin: 0; font-size: 24px; font-weight: 900; }
        nav a { color: rgba(255,255,255,0.85); text-decoration: none; margin-right: 20px; font-weight: 600; padding: 8px 15px; border-radius: 20px; transition: 0.3s; }
        nav a:hover, nav a.active { color: #fff; background: rgba(255,255,255,0.15); }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .card { background: var(--card-bg); border-radius: 12px; padding: 30px; margin-bottom: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 5px solid var(--primary); }
        input, select, textarea { width: 100%; padding: 12px 15px; border: 1px solid #d1d3e2; border-radius: 8px; font-size: 15px; outline: none; margin-top: 8px; margin-bottom: 15px; background-color: #fdfdfd; }
        input:focus, select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15); }
        label { font-weight: 600; color: var(--text-dark); display: block; }
        .btn { background-color: var(--primary); color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 16px; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
        .btn:hover { background-color: var(--primary-dark); }
        .btn-success { background-color: var(--success); }
        .btn-danger { background-color: var(--danger); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #e3e6f0; }
        th { background-color: #f8f9fc; color: var(--text-dark); font-weight: 700; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<header>
    <h2>🎓 منصة مستر علي عابدين التعليمية</h2>
    <nav>
        <a href="index.php?page=home">🏠 الرئيسية</a>
        <?php if(isset($_SESSION['student_logged'])): ?>
            <a href="index.php?page=student_dashboard">👤 لوحة التحكم</a>
            <a href="index.php?page=logout" style="color: #ff8b8b;">🚪 تسجيل خروج</a>
        <?php elseif(isset($_SESSION['admin_logged'])): ?>
            <a href="index.php?page=admin_dashboard">🛠 لوحة الإدارة</a>
            <a href="index.php?page=logout" style="color: #ff8b8b;">🚪 تسجيل خروج</a>
        <?php else: ?>
            <a href="index.php?page=login">🔑 دخول طالب</a>
            <a href="index.php?page=register">📝 حساب جديد</a>
            <a href="index.php?page=admin_login">⚙️ دخول الإدارة</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">

<?php
// 1. الرئيسية
if ($page == 'home'):
?>
    <div class="card" style="text-align: center; padding: 50px 20px; border-top-color: var(--success);">
        <h1 style="font-size: 32px; color: #1e3c72; margin-bottom: 15px;">أهلاً بكم في المنصة التعليمية لمستر علي عابدين</h1>
        <p style="font-size: 18px; color: #6e707e; max-width: 600px; margin: 0 auto 30px auto;">تابع دروسك، شاهد الفيديوهات الخاصة بصفك، وامتحن بأحدث الاختبارات بكل سهولة.</p>
        <div>
            <a href="index.php?page=login" class="btn" style="padding: 14px 30px; margin-left: 10px;">تسجيل الدخول</a>
            <a href="index.php?page=register" class="btn btn-success" style="padding: 14px 30px;">انشاء حساب جديد</a>
        </div>
    </div>

<?php 
// 2. حساب جديد
elseif ($page == 'register'):
?>
    <div class="card" style="max-width: 450px; margin: 40px auto;">
        <h2>📝 تسجيل حساب طالب جديد</h2>
        <?php if(!empty($reg_error)) echo "<div class='alert-error'>$reg_error</div>"; ?>
        <form method="POST">
            <label>اسم الطالب:</label>
            <input type="text" name="name" placeholder="اكتب اسمك الثلاثي..." required>
            
            <label>كلمة المرور:</label>
            <input type="password" name="password" placeholder="كلمة المرور..." required>
            
            <label>الصف الدراسي:</label>
            <select name="grade" required>
                <?php foreach($all_grades as $k => $v): ?>
                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 10px;">تسجيل الحساب</button>
        </form>
    </div>

<?php 
// 3. تسجيل دخول طالب
elseif ($page == 'login'):
?>
    <div class="card" style="max-width: 450px; margin: 40px auto;">
        <h2>🔑 تسجيل دخول الطالب</h2>
        <?php if(!empty($login_error)) echo "<div class='alert-error'>$login_error</div>"; ?>
        <form method="POST">
            <label>اسم الطالب:</label>
            <input type="text" name="name" placeholder="اسم الطالب..." required>
            
            <label>كلمة المرور:</label>
            <input type="password" name="password" placeholder="كلمة المرور..." required>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">دخول المنصة</button>
        </form>
    </div>

<?php 
// 4. لوحة تحكم الطالب
elseif ($page == 'student_dashboard'):
    if (!isset($_SESSION['student_logged'])) {
        header("Location: index.php?page=login");
        exit();
    }
    $s_grade = $_SESSION['student_grade'];
    $s_name = $_SESSION['student_name'];
?>
    <div class="card" style="border-top-color: var(--success);">
        <h2>أهلاً بك يا بطل: <span style="color: var(--primary);"><?php echo htmlspecialchars($s_name); ?></span> 👋</h2>
        <p style="margin-top: 5px; font-size: 16px;">المرحلة الدراسية: <strong><?php echo isset($all_grades[$s_grade]) ? $all_grades[$s_grade] : $s_grade; ?></strong></p>
    </div>

    <!-- لوحة الشرف الخاصة بصفه -->
    <div class="card" style="border-top-color: var(--warning);">
        <h2>🏆 لوحة الشرف الخاصة بصفك</h2>
        <?php
        $h_stmt = $conn->prepare("SELECT * FROM honor_board WHERE grade = ? ORDER BY id DESC LIMIT 1");
        if($h_stmt) {
            $h_stmt->bind_param("s", $s_grade);
            $h_stmt->execute();
            $h_res = $h_stmt->get_result();
            if($h_res && $h_res->num_rows > 0) {
                $honor = $h_res->fetch_assoc();
                $txt = isset($honor['content']) ? $honor['content'] : $honor['text'];
                echo "<div style='background: #fffdf0; padding: 20px; border-radius: 8px; border: 1px solid #ffeeba; white-space: pre-line; font-size: 17px; color: #856404;'>" . htmlspecialchars($txt) . "</div>";
            } else {
                echo "<p style='color: #858796; font-style: italic;'>لا توجد لوحة شرف منشورة لهذا الصف حتى الآن.</p>";
            }
        }
        ?>
    </div>

    <!-- فيديوهات هذا الصف فقط (تدعم يوتيوب أو فيديوهات مرفوعة محلياً) -->
    <div class="card">
        <h2>🎥 الفيديوهات التعليمية المتاحة لصفك</h2>
        <?php
        $v_stmt = $conn->prepare("SELECT * FROM videos WHERE grade = ? ORDER BY id DESC");
        if($v_stmt) {
            $v_stmt->bind_param("s", $s_grade);
            $v_stmt->execute();
            $v_res = $v_stmt->get_result();
            if($v_res && $v_res->num_rows > 0) {
                while($vid = $v_res->fetch_assoc()) {
                    echo "<div style='background:#f8f9fc; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #e3e6f0;'>";
                    echo "<h3 style='margin-top:0; color:var(--primary);'>" . htmlspecialchars($vid['title']) . "</h3>";
                    
                    $link = $vid['youtube_link'];
                    // التحقق هل هو فيديو مرفوع محلياً أو رابط يوتيوب
                    if (strpos($link, 'uploads/') === 0 || strpos($link, '.mp4') !== false) {
                        echo "<video width='100%' height='315' controls style='border-radius:8px; background:#000;'><source src='" . htmlspecialchars($link) . "' type='video/mp4'>متصفحك لا يدعم عرض الفيديو.</video>";
                    } else {
                        $embed = str_replace("watch?v=", "embed/", $link);
                        echo "<iframe width='100%' height='315' src='" . htmlspecialchars($embed) . "' frameborder='0' allowfullscreen style='border-radius:8px;'></iframe>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p style='color: #858796;'>لا توجد فيديوهات مرفوعة لهذا الصف حتى الآن.</p>";
            }
        }
        ?>
    </div>

    <!-- امتحانات هذا الصف -->
    <div class="card" style="border-top-color: var(--danger);">
        <h2>📝 الامتحانات والاختبارات المتاحة لصفك</h2>
        <?php
        $ex_stmt = $conn->prepare("SELECT * FROM exams WHERE grade = ? ORDER BY id DESC");
        if($ex_stmt) {
            $ex_stmt->bind_param("s", $s_grade);
            $ex_stmt->execute();
            $ex_res = $ex_stmt->get_result();
            if($ex_res && $ex_res->num_rows > 0) {
                while($ex = $ex_res->fetch_assoc()) {
                    echo "<div style='background:#f8f9fc; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #e3e6f0;'>";
                    echo "<h3 style='margin-top:0; color:var(--danger);'>" . htmlspecialchars($ex['exam_title']) . "</h3>";
                    if(isset($ex['duration']) && $ex['duration'] > 0) {
                        echo "<p style='color:#e74a3b; font-weight:600;'>⏱ مدة الامتحان: " . $ex['duration'] . " دقيقة</p>";
                    }
                    echo "<a href='index.php?page=take_exam&exam_id=" . $ex['id'] . "' class='btn btn-success' style='padding:8px 20px;'>بدء الامتحان الآن 🚀</a>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color: #858796;'>لا توجد امتحانات مخصصة لصفك في الوقت الحالي.</p>";
            }
        }
        ?>
    </div>

<?php 
// 5. صفحة حل الامتحان للطالب (مع عداد الوقت التنازلي)
elseif ($page == 'take_exam'):
    if (!isset($_SESSION['student_logged'])) {
        header("Location: index.php?page=login");
        exit();
    }
    $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
    
    $ex_q = $conn->prepare("SELECT * FROM exams WHERE id = ?");
    $ex_q->bind_param("i", $exam_id);
    $ex_q->execute();
    $ex_data = $ex_q->get_result()->fetch_assoc();

    if(!$ex_data) {
        echo "<div class='card'><h2 style='color:var(--danger);'>الامتحان غير موجود!</h2><a href='index.php?page=student_dashboard' class='btn'>العودة للوحة التحكم</a></div>";
    } else {
        $questions = json_decode($ex_data['questions'], true);
        $duration_minutes = isset($ex_data['duration']) ? intval($ex_data['duration']) : 15;
        
        // معالجة تسليم الامتحان وحساب الدرجة
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $score = 0;
            $total = is_array($questions) ? count($questions) : 0;
            
            if(is_array($questions)) {
                foreach($questions as $idx => $q) {
                    $ans_key = "q_" . $idx;
                    if(isset($_POST[$ans_key]) && $_POST[$ans_key] == $q['correct']) {
                        $score++;
                    }
                }
            }
            
            $st_name = $_SESSION['student_name'];
            $st_grade = $_SESSION['student_grade'];
            $ex_title = $ex_data['exam_title'];
            
            $res_ins = $conn->prepare("INSERT INTO student_results (student_name, grade, exam_title, score, total) VALUES (?, ?, ?, ?, ?)");
            $res_ins->bind_param("ssssi", $st_name, $st_grade, $ex_title, $score, $total);
            $res_ins->execute();
            
            echo "<div class='card' style='text-align:center; border-top-color:var(--success);'>";
            echo "<h1 style='color:var(--success);'>🎉 انتهى الامتحان بنجاح!</h1>";
            echo "<h2 style='font-size:24px;'>درجتك هي: <span style='color:var(--primary);'>$score</span> من <span style='color:var(--text-dark);'>$total</span></h2>";
            echo "<a href='index.php?page=student_dashboard' class='btn' style='margin-top:20px;'>العودة للوحة التحكم</a>";
            echo "</div>";
        } else {
?>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e3e6f0; padding-bottom: 15px; margin-bottom: 20px;">
            <h2 style="margin:0; color:var(--danger);">📝 امتحان: <?php echo htmlspecialchars($ex_data['exam_title']); ?></h2>
            <div id="timer" style="background: #f8d7da; color: #721c24; padding: 8px 15px; border-radius: 8px; font-weight: bold; font-size: 16px;">
                ⏱ الوقت المتبقي: <span id="time_left"></span>
            </div>
        </div>
        
        <form id="exam_form" method="POST">
            <?php
            if(is_array($questions) && count($questions) > 0) {
                foreach($questions as $idx => $q) {
                    echo "<div style='background:#f8f9fc; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #e3e6f0;'>";
                    echo "<p style='font-weight:700; font-size:16px;'>السؤال " . ($idx+1) . ": " . htmlspecialchars($q['question']) . "</p>";
                    foreach($q['options'] as $o_idx => $opt) {
                        echo "<label style='font-weight:normal; margin-bottom:8px; display:block;'>";
                        echo "<input type='radio' name='q_$idx' value='$o_idx' style='width:auto; margin-left:10px;'> " . htmlspecialchars($opt);
                        echo "</label>";
                    }
                    echo "</div>";
                }
                echo "<button type='submit' class='btn btn-success' style='width:100%; padding:15px;'>إرسال وتسليم الامتحان 🚀</button>";
            } else {
                echo "<p style='color:var(--danger);'>عذراً، هذا الامتحان لا يحتوي على أسئلة حالياً.</p>";
            }
            ?>
        </form>
    </div>

    <script>
        // عداد الوقت التنازلي للامتحان
        var totalSeconds = <?php echo $duration_minutes * 60; ?>;
        function updateTimer() {
            var minutes = Math.floor(totalSeconds / 60);
            var seconds = totalSeconds % 60;
            document.getElementById('time_left').innerText = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                alert("انتهى وقت الامتحان! سيتم تسليم الإجابات تلقائياً.");
                document.getElementById('exam_form').submit();
            }
            totalSeconds--;
        }
        var timerInterval = setInterval(updateTimer, 1000);
        updateTimer();
    </script>
<?php
        }
    }
?>

<?php 
// 6. دخول الإدارة
elseif ($page == 'admin_login'):
?>
    <div class="card" style="max-width: 450px; margin: 40px auto; border-top-color: var(--danger);">
        <h2>⚙️ تسجيل دخول الإدارة (مستر علي عابدين)</h2>
        <?php if(!empty($admin_error)) echo "<div class='alert-error'>$admin_error</div>"; ?>
        <form method="POST">
            <label>اسم المستخدم:</label>
            <input type="text" name="username" required>
            
            <label>كلمة المرور:</label>
            <input type="password" name="password" required>
            
            <button type="submit" class="btn btn-danger" style="width: 100%; margin-top: 10px;">دخول لوحة التحكم</button>
        </form>
    </div>

<?php 
// 7. لوحة التحكم الرئيسية للإدارة (مع التحديثات الجديدة)
elseif ($page == 'admin_dashboard'):
    if (!isset($_SESSION['admin_logged'])) {
        header("Location: index.php?page=admin_login");
        exit();
    }

    // حذف طالب
    if (isset($_GET['delete_student'])) {
        $del_id = intval($_GET['delete_student']);
        $conn->query("DELETE FROM users WHERE id = $del_id");
        echo "<script>alert('تم حذف الطالب بنجاح'); window.location='index.php?page=admin_dashboard';</script>";
        exit();
    }

    // نشر لوحة الشرف
    if (isset($_GET['action']) && $_GET['action'] == 'save_honor' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $h_grade = $_POST['honor_grade'];
        $h_content = $_POST['honor_content'];
        $ins = $conn->prepare("INSERT INTO honor_board (grade, content) VALUES (?, ?)");
        $ins->bind_param("ss", $h_grade, $h_content);
        $ins->execute();
        echo "<script>alert('تم نشر لوحة الشرف بنجاح!'); window.location='index.php?page=admin_dashboard';</script>";
        exit();
    }

    // نشر فيديو جديد (يدعم يوتيوب أو رفع ملف)
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
        echo "<script>alert('تم نشر الفيديو بنجاح!'); window.location='index.php?page=admin_dashboard';</script>";
        exit();
    }

    // إضافة امتحان جديد (حتى 30 سؤال + مدة زمنية بالدقائق)
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
        echo "<script>alert('تم إضافة الامتحان ونشره بنجاح!'); window.location='index.php?page=admin_dashboard';</script>";
        exit();
    }
?>
    <div class="card" style="border-top-color: var(--danger);">
        <h2>🛠 لوحة التحكم الرئيسية - مستر علي عابدين</h2>
        <p>مرحباً بك يا مستر، من هنا يمكنك إدارة كل صف دراسي، نشر الفيديوهات (برابط أو رفع مباشر)، والامتحانات بمدة زمنية محددة، ومتابعة درجات الطلاب.</p>
    </div>

    <!-- رفع فيديو جديد -->
    <div class="card" style="border-top-color: var(--warning);">
        <h2>🎥 نشر فيديو تعليمي جديد</h2>
        <form action="index.php?page=admin_dashboard&action=save_video" method="POST" enctype="multipart/form-data">
            <label>اختر الصف الدراسي:</label>
            <select name="video_grade" required>
                <?php foreach($all_grades as $k => $v): ?>
                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>عنوان الفيديو:</label>
            <input type="text" name="video_title" placeholder="مثال: شرح درس القواعد..." required>
            
            <label>طريقة الإضافة:</label>
            <select name="video_type" id="video_type" onchange="toggleVideoInput()" required>
                <option value="link">رابط يوتيوب (YouTube)</option>
                <option value="upload">رفع فيديو مباشر (من الجهاز MP4)</option>
            </select>
            
            <div id="link_input_div" style="margin-top: 10px;">
                <label>رابط يوتيوب:</label>
                <input type="text" name="video_link" placeholder="https://www.youtube.com/watch?v=...">
            </div>
            
            <div id="upload_input_div" style="display:none; margin-top: 10px;">
                <label>ملف الفيديو (MP4):</label>
                <input type="file" name="video_file" accept="video/mp4">
            </div>
            
            <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 15px;">نشر الفيديو الآن 🚀</button>
        </form>
    </div>

    <script>
    function toggleVideoInput() {
        var type = document.getElementById('video_type').value;
        if(type === 'upload') {
            document.getElementById('link_input_div').style.display = 'none';
            document.getElementById('upload_input_div').style.display = 'block';
        } else {
            document.getElementById('link_input_div').style.display = 'block';
            document.getElementById('upload_input_div').style.display = 'none';
        }
    }
    </script>

    <!-- إضافة امتحان جديد (MCQ حتى 30 سؤال + وقت بالدقائق) -->
    <div class="card" style="border-top-color: var(--danger);">
        <h2>📝 إضافة امتحان جديد (حتى 30 سؤال + تحديد وقت بالدقائق)</h2>
        <form action="index.php?page=admin_dashboard&action=save_exam" method="POST">
            <label>اختر الصف الدراسي:</label>
            <select name="exam_grade" required>
                <?php foreach($all_grades as $k => $v): ?>
                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>عنوان الامتحان:</label>
            <input type="text" name="exam_title" placeholder="مثال: اختبار شامل على الوحدة الأولى..." required>
            
            <label>مدة الامتحان (بالدقائق):</label>
            <input type="number" name="exam_duration" placeholder="مثال: 15" min="1" value="15" required>
            
            <hr style="margin:20px 0; border:0; border-top:1px solid #e3e6f0;">
            <p style="color: #4e73df; font-weight: bold;">أضف الأسئلة (اترك الأسئلة الزائدة فارغة إذا أردت عدداً أقل من 30 سؤال):</p>
            
            <div id="questions_container">
                <?php for($i=1; $i<=30; $i++): ?>
                <div style="background:#f8f9fc; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #e3e6f0;">
                    <label>السؤال رقم <?php echo $i; ?>:</label>
                    <input type="text" name="q_text_<?php echo $i; ?>" placeholder="اكتب نص السؤال هنا...">
                    
                    <label>الخيارات (4 خيارات):</label>
                    <input type="text" name="q_<?php echo $i; ?>_opt_0" placeholder="الخيار الأول">
                    <input type="text" name="q_<?php echo $i; ?>_opt_1" placeholder="الخيار الثاني">
                    <input type="text" name="q_<?php echo $i; ?>_opt_2" placeholder="الخيار الثالث">
                    <input type="text" name="q_<?php echo $i; ?>_opt_3" placeholder="الخيار الرابع">
                    
                    <label>الإجابة الصحيحة:</label>
                    <select name="q_correct_<?php echo $i; ?>">
                        <option value="0">الخيار الأول</option>
                        <option value="1">الخيار الثاني</option>
                        <option value="2">الخيار الثالث</option>
                        <option value="3">الخيار الرابع</option>
                    </select>
                </div>
                <?php endfor; ?>
            </div>
            
            <button type="submit" class="btn btn-danger" style="width: 100%; margin-top: 15px;">حفظ ونشر الامتحان 🚀</button>
        </form>
    </div>

    <!-- نشر لوحة الشرف -->
    <div class="card" style="border-top-color: var(--primary);">
        <h2>🏆 نشر لوحة الشرف لكل صف</h2>
        <form action="index.php?page=admin_dashboard&action=save_honor" method="POST">
            <label>اختر الصف الدراسي:</label>
            <select name="honor_grade" required>
                <?php foreach($all_grades as $k => $v): ?>
                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>أسماء المتفوقين:</label>
            <textarea name="honor_content" rows="4" placeholder="أوائل الصف..." required></textarea>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">حفظ ونشر لوحة الشرف</button>
        </form>
    </div>

    <!-- جدول سجل الطلاب -->
    <div class="card">
        <h2>👥 سجل الطلاب المسجلين بالمنصة</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الطالب</th>
                    <th>الصف الدراسي</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $st_res = $conn->query("SELECT * FROM users ORDER BY id DESC");
                if ($st_res && $st_res->num_rows > 0) {
                    $idx = 1;
                    while($st = $st_res->fetch_assoc()) {
                        $g_name = isset($all_grades[$st['grade']]) ? $all_grades[$st['grade']] : $st['grade'];
                        echo "<tr>";
                        echo "<td>" . $idx++ . "</td>";
                        echo "<td><strong>" . htmlspecialchars($st['name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($g_name) . "</td>";
                        echo "<td><a href='index.php?page=admin_dashboard&delete_student=" . $st['id'] . "' class='btn btn-danger' style='padding: 5px 12px; font-size: 13px;' onclick='return confirm(\"هل أنت متأكد من حذف هذا الطالب؟\");'>حذف</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='color: #858796;'>لا يوجد طلاب مسجلين حتى الآن.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- سجل درجات الامتحانات لكل صف -->
    <div class="card" style="border-top-color: var(--success);">
        <h2>📊 سجل درجات الامتحانات (لكل صف لوحده)</h2>
        <form method="GET" action="index.php" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="admin_dashboard">
            <label>فلترة حسب الصف الدراسي:</label>
            <select name="filter_score_grade" onchange="this.form.submit()">
                <option value="">-- كل الصفوف الدراسية --</option>
                <?php 
                $f_grade = isset($_GET['filter_score_grade']) ? $_GET['filter_score_grade'] : '';
                foreach($all_grades as $k => $v): 
                ?>
                    <option value="<?php echo $k; ?>" <?php if($f_grade == $k) echo 'selected'; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <table>
            <thead>
                <tr>
                    <th>اسم الطالب</th>
                    <th>الصف</th>
                    <th>اسم الامتحان</th>
                    <th>الدرجة</th>
                    <th>تاريخ الحل</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(!empty($f_grade)) {
                    $res_q = $conn->prepare("SELECT * FROM student_results WHERE grade = ? ORDER BY id DESC");
                    $res_q->bind_param("s", $f_grade);
                    $res_q->execute();
                    $r_result = $res_q->get_result();
                } else {
                    $r_result = $conn->query("SELECT * FROM student_results ORDER BY id DESC");
                }

                if($r_result && $r_result->num_rows > 0) {
                    while($rw = $r_result->fetch_assoc()) {
                        $g_nm = isset($all_grades[$rw['grade']]) ? $all_grades[$rw['grade']] : $rw['grade'];
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($rw['student_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($g_nm) . "</td>";
                        echo "<td>" . htmlspecialchars($rw['exam_title']) . "</td>";
                        echo "<td><strong style='color: var(--success);'>" . $rw['score'] . "</strong> / " . $rw['total'] . "</td>";
                        echo "<td>" . $rw['exam_date'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='color: #858796;'>لا توجد نتائج امتحانات مسجلة.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<?php 
endif;
?>
</div>
</body>
</html>
<script>
    var adminBtn = document.createElement('a');
    adminBtn.href = 'admin.php';
    adminBtn.innerHTML = '⚙️ لوحة التحكم';
    adminBtn.style.cssText = 'position: fixed; bottom: 20px; left: 20px; background: #e74a3b; color: #fff; padding: 10px 15px; border-radius: 30px; font-weight: bold; text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 9999; font-family: Tahoma; font-size: 14px;';
    document.body.appendChild(adminBtn);
</script>
<?php ob_end_flush(); ?>