<?php
http_response_code(200);
$port = getenv('PORT') ?: 8080;

/**
 * 🇾🇪 نظام محرك الروابط الملكي 👑
 * المطور والمالك الرسمي: البرنس نجيب
 * المعرف: @nag1k2 | جميع الحقوق محفوظة لـ @nag1k2
 */

// 1. إعدادات السيرفر والمنفذ لضمان العمل على Railway
http_response_code(200);
$port = getenv('PORT') ?: 8080;

// 2. الإعدادات الجوهرية
$token    = "8628823665:AAES9G97DmPQF5IKeEW8TQ0d3nli9vtoy_4";
$admin_id = 7996191937; // آيدي البرنس نجيب
$channel  = "@XFY_F";   // قناة النشر الرسمية

// 3. قاعدة البيانات (ملفات نصية ذكية)
$dbUsers = 'users.txt';
$dbLinks = 'published_links.txt';
$dbBan   = 'ban_users.txt';

// 4. استقبال البيانات ومعالجتها
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    echo "<h1>Yemen Links Master 🇾🇪</h1>";
    echo "<p>Status: Active | Developer: Prince Najib</p>";
    exit;
}

$message = $update['message'] ?? null;
if ($message) {
    $chat_id = $message['chat']['id'];
    $text    = $message['text'] ?? '';
    $name    = $message['from']['first_name'] ?? 'عزيزي';

    // حفظ المستخدم الجديد تلقائياً
    if (!file_exists($dbUsers) || !strpos(file_get_contents($dbUsers), (string)$chat_id)) {
        file_put_contents($dbUsers, $chat_id . PHP_EOL, FILE_APPEND);
    }

    // أمر البداية /start
    if ($text == '/start') {
        $welcome = "مرحباً بك يا $name في نظام الروابط الملكي 👑\n\n";
        $welcome .= "هذا البوت صُنع خصيصاً لـ **البرنس نجيب** لإدارة ونشر الروابط باحترافية.\n\n";
        $welcome .= "ارسل الرابط الآن ليتم فحصه ونشره في القناة $channel";
        
        sendMessage($chat_id, $welcome, $token);
    } 
    // نظام معالجة الروابط (إذا أرسل رابط)
    elseif (filter_var($text, FILTER_VALIDATE_URL)) {
        // فحص الحظر
        $banList = file_exists($dbBan) ? file_get_contents($dbBan) : '';
        if (strpos($banList, (string)$chat_id) !== false) {
            sendMessage($chat_id, "عذراً، أنت محظور من استخدام النظام بقرار من الإدارة 🚫", $token);
            exit;
        }

        // إرسال الرابط للقناة
        $caption = "🔗 تم إضافة رابط جديد بنجاح!\n\n";
        $caption .= "👤 بواسطة: $name\n";
        $caption .= "💎 تابعنا: $channel";
        
        $post = file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$channel&text=" . urlencode("$caption\n\n$text"));
        
        if ($post) {
            sendMessage($chat_id, "✅ تم نشر رابطك بنجاح في القناة!", $token);
            file_put_contents($dbLinks, $text . PHP_EOL, FILE_APPEND);
        } else {
            sendMessage($chat_id, "❌ حدث خطأ، تأكد أن البوت مشرف في القناة $channel", $token);
        }
    }
}

// وظيفة إرسال الرسائل المختصرة
function sendMessage($chat_id, $text, $token) {
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($text) . "&parse_mode=Markdown";
    return file_get_contents($url);
}
