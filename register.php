<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $grade = trim($_POST['grade']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!empty($fullname) && !empty($phone) && !empty($grade) && !empty($_POST['password'])) {
        // التحقق هل الرقم مسجل من قبل
        $check = $conn->prepare("SELECT id FROM students WHERE phone = ?");
        $check->bind_param("s", $phone);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('رقم الهاتف مسجل مسبقاً!'); window.location.href='index.php';</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (fullname, phone, grade, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $phone, $grade, $password);

            if ($stmt->execute()) {
                echo "<div style='font-family: Cairo, sans-serif; text-align: center; margin-top: 100px; direction: rtl; color: #fff; background: #0f172a; padding: 40px; border-radius: 20px; max-width: 450px; margin-left: auto; margin-right: auto;'>";
                echo "<h2 style='color: #10b981;'>🎉 تم التسجيل بنجاح في منصة عابدين التعليمية!</h2>";
                echo "<p style='margin-top: 15px; color: #94a3b8;'>اسم الطالب: <strong>$fullname</strong></p>";
                echo "<p style='color: #94a3b8;'>رقم الهاتف: <strong>$phone</strong></p>";
                echo "<p style='color: #94a3b8;'>الصف الدراسي: <strong>$grade</strong></p>";
                echo "<br><a href='index.php' style='background: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: bold;'>العودة للصفحة الرئيسية</a>";
                echo "</div>";
            } else {
                echo "<p style='text-align: center; color: red;'>خطأ في الحفظ: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        echo "<script>alert('الرجاء ملء جميع الحقول!'); window.location.href='index.php';</script>";
    }
}
$conn->close();
?>