<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "aliabdeen3";
$conn = new mysqli($host, $user, $pass, $db_name);
$conn->set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فحص طلاب المنصة</title>
    <style>
        body { background: #0f172a; color: #fff; font-family: Tahoma; padding: 30px; direction: rtl; }
        .box { background: #1e293b; padding: 25px; border-radius: 12px; max-width: 600px; margin: 0 auto; border: 1px solid #334155; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #334155; padding: 10px; text-align: center; }
        th { background: #334155; color: #6366f1; }
    </style>
</head>
<body>
    <div class="box">
        <h2>قائمة الطلاب المسجلين</h2>
        <table>
            <tr>
                <th>(ID)</th>
                <th>اسم الطالب</th>
                <th>رقم الهاتف</th>
                <th>الصف الدراسي</th>
                <th>كلمة المرور</th>
            </tr>
            <?php
            $res = $conn->query("SELECT * FROM students");
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['student_name']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['target_grade']}</td>
                        <td>{$row['password']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='color:#94a3b8;'>لا يوجد طلاب مسجلين حالياً!</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>