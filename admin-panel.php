<?php
session_start();
if (!isset($_SESSION['admin_logged']) \vert{}\vert{}$_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - منصة مستر علي عابدين</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Cairo', sans-serif; }
        body { background-color: #0f172a; color: #f8fafc; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 280px; background: #1e293b; display: flex; flex-direction: column; border-left: 1px solid #334155; }
        .sidebar .logo-area { padding: 20px; text-align: center; background: #0f172a; font-size: 20px; font-weight: bold; color: #38bdf8; border-bottom: 1px solid #334155; }
        .sidebar menu { list-style: none; padding: 15px 0; overflow-y: auto; flex: 1; }
        .sidebar menu li { margin-bottom: 5px; }
        .sidebar menu li a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #94a3b8; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .sidebar menu li a:hover, .sidebar menu li a.active { background: #38bdf8; color: #0f172a; font-weight: bold; border-radius: 6px; margin: 0 10px; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        header { background: #1e293b; padding: 15px 30px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; }
        .container { padding: 30px; }
        .section-box { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .section-box h3 { margin-bottom: 15px; color: #38bdf8; font-size: 18px; border-bottom: 2px solid #334155; padding-bottom: 8px; display: inline-block; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px; }
        input, select, textarea { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #475569; color: #fff; border-radius: 6px; font-size: 14px; }
        input:focus, select:focus, textarea:focus { border-color: #38bdf8; outline: none; }
        button.btn-primary { background: #38bdf8; color: #0f172a; border: none; padding: 12px 20px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: 0.3s; margin-top: 15px; }
        button.btn-primary:hover { background: #0ea5e9; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #0f172a; border-radius: 6px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: right; border-bottom: 1px solid #334155; font-size: 14px; }
        th { background: #334155; color: #38bdf8; }
        .badge { background: #10b981; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .output-box { background: #0f172a; border: 1px dashed #38bdf8; padding: 15px; border-radius: 6px; margin-top: 15px; display: none; color: #38bdf8; font-family: monospace; }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="logo-area">مستر علي عابدين 🚀</div>
        <menu>
            <li><a href="#" class="active"><i class="fa-solid fa-chart-line"></i> الرئيسية والتحكم</a></li>
            <li><a href="#videos"><i class="fa-solid fa-video"></i> رفع الفيديوهات</a></li>
            <li><a href="#codes"><i class="fa-solid fa-key"></i> توليد أكواد التفعيل</a></li>
            <li><a href="#homework"><i class="fa-solid fa-book-open"></i> صانع الواجبات</a></li>
            <li><a href="#exams"><i class="fa-solid fa-file-pen"></i> صانع الامتحانات الشاملة</a></li>
            <li><a href="#grades"><i class="fa-solid fa-users-rectangle"></i> سجل درجات الطلاب</a></li>
            <li><a href="#leaderboard"><i class="fa-solid fa-trophy"></i> لوحة الشرف</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a></li>
        </menu>
    </div>

    <div class="main-content">
        <header>
            <h2>لوحة الإدارة والتحكم الشاملة</h2>
            <span>أهلاً بك يا مستر علي 🔥</span>
        </header>

        <div class="container">
            <?php
            function renderGradesOptions() {
                echo '
                <option value="">-- اختار الصف الدراسي --</option>
                <optgroup label="المرحلة الابتدائية">
                    <option value="primary_4">رابعة ابتدائي</option>
                    <option value="primary_5">خامسة ابتدائي</option>
                    <option value="primary_6">سادسة ابتدائي</option>
                </optgroup>
                <optgroup label="المرحلة الإعدادية">
                    <option value="prep_1">أولى إعدادي</option>
                    <option value="prep_2">تانية إعدادي</option>
                    <option value="prep_3">تالتة إعدادي</option>
                </optgroup>
                <optgroup label="المرحلة الثانوية والبكلوريا">
                    <option value="sec_1">أولى ثانوي</option>
                    <option value="sec_2">تانية ثانوي</option>
                    <option value="bac_2">تانية بكلوريا</option>
                    <option value="sec_3">تالتة ثانوي / البكلوريا</option>
                </optgroup>
                ';
            }
            ?>

            <div id="videos" class="section-box">
                <h3><i class="fa-solid fa-video"></i> رفع فيديو للدرس</h3>
                <div class="form-grid">
                    <input type="text" placeholder="عنوان الدرس">
                    <select><?php renderGradesOptions(); ?></select>
                    <select id="videoType" onchange="switchVideoInput()">
                        <option value="link">رابط خارجي (يوتيوب / سيرفر)</option>
                        <option value="upload">رفع ملف فيديو مباشر للمنصة</option>
                    </select>
                </div>
                <div class="form-grid" style="margin-top: 15px;">
                    <input type="text" id="videoLinkInput" placeholder="ضع رابط الفيديو هنا">
                    <input type="file" id="videoFileUpload" style="display: none;" accept="video/*">
                </div>
                <button class="btn-primary">نشر الفيديو الآن</button>
            </div>

            <div id="codes" class="section-box">
                <h3><i class="fa-solid fa-key"></i> توليد أكواد تفعيل واشتراك</h3>
                <div class="form-grid">
                    <select id="codeGrade"><?php renderGradesOptions(); ?></select>
                    <select id="codePeriod">
                        <option value="month">صلاحية لمدة شهر</option>
                        <option value="term">صلاحية ترم كامل</option>
                        <option value="year">صلاحية سنة كاملة</option>
                    </select>
                    <input type="number" id="codeCount" placeholder="عدد الأكواد المطلوبة (مثال: 10)">
                </div>
                <button class="btn-primary" onclick="generateCodes()">إنشاء الأكواد واستخراجها</button>
                <div id="codesOutput" class="output-box"></div>
            </div>

            <div id="homework" class="section-box">
                <h3><i class="fa-solid fa-book-open"></i> صانع الواجبات المنزلية (تصحيح فوري)</h3>
                <div class="form-grid">
                    <input type="text" placeholder="عنوان الواجب">
                    <select><?php renderGradesOptions(); ?></select>
                    <input type="number" placeholder="عدد أسئلة الاختيار من متعدد">
                </div>
                <div style="margin-top: 15px;">
                    <textarea rows="3" placeholder="أدخل نموذج الإجابة للواجب (مثلاً: 1:أ، 2:ب)..."></textarea>
                </div>
                <button class="btn-primary">حفظ ونشر الواجب</button>
            </div>

            <div id="exams" class="section-box">
                <h3><i class="fa-solid fa-file-pen"></i> صانع الامتحانات الشاملة</h3>
                <div class="form-grid">
                    <input type="text" placeholder="عنوان الامتحان الشامل">
                    <select><?php renderGradesOptions(); ?></select>
                    <input type="number" placeholder="⏰ وقت الامتحان بالدقائق">
                    <select>
                        <option value="hidden">إخفاء النتيجة مؤقتاً</option>
                        <option value="immediate">إظهار النتيجة فوراً</option>
                    </select>
                </div>
                <div class="form-grid" style="margin-top: 15px;">
                    <textarea rows="3" placeholder="اكتب أسئلة الامتحان بالتفصيل..."></textarea>
                    <textarea rows="3" placeholder="📋 نموذج الإجابة (Model Answer)..."></textarea>
                </div>
                <button class="btn-primary">حفظ ونشر الامتحان</button>
            </div>

            <div id="grades" class="section-box">
                <h3><i class="fa-solid fa-users-rectangle"></i> سجل درجات الطلاب</h3>
                <div style="margin-bottom: 15px;">
                    <select style="width: 250px;"><?php renderGradesOptions(); ?></select>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>اسم الطالب</th>
                            <th>الصف الدراسي</th>
                            <th>الامتحان / الواجب</th>
                            <th>الدرجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>محمد أحمد محمود</td>
                            <td>تالتة ثانوي</td>
                            <td>امتحان النحو الشامل</td>
                            <td><span class="badge">95 / 100</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="leaderboard" class="section-box">
                <h3><i class="fa-solid fa-trophy"></i> لوحة الشرف (الأوائل)</h3>
                <div class="form-grid">
                    <input type="text" placeholder="اسم الطالب الأول">
                    <input type="text" placeholder="المركز أو المجموع">
                    <select><?php renderGradesOptions(); ?></select>
                </div>
                <button class="btn-primary">إضافة للوحة الشرف</button>
            </div>
        </div>
    </div>

    <script>
        function switchVideoInput() {
            const type = document.getElementById('videoType').value;
            const linkInput = document.getElementById('videoLinkInput');
            const fileInput = document.getElementById('videoFileUpload');
            if (type === 'upload') {
                linkInput.style.display = 'none';
                fileInput.style.display = 'block';
            } else {
                linkInput.style.display = 'block';
                fileInput.style.display = 'none';
            }
        }
        function generateCodes() {
            const count = document.getElementById('codeCount').value;
            const outputBox = document.getElementById('codesOutput');
            if(!count || count <= 0) {
                alert('الرجاء إدخال عدد صحيح للأكواد المطلوبة!');
                return;
            }
            let codesHTML = "<strong>الأكواد التي تم توليدها بنجاح:</strong><br><br>";
            for(let i = 1; i <= count; i++) {
                let randomCode = 'ALI-' + Math.floor(1000 + Math.random() * 9000);
                codesHTML += `<code>${randomCode}</code> &nbsp;&nbsp;|&nbsp;&nbsp; `;
                if(i % 4 === 0) codesHTML += "<br>";
            }
            outputBox.style.display = 'block';
            outputBox.innerHTML = codesHTML;
        }
    </script>
</body>
</html>