# 🚀 دليل نشر موقع BCare على Railway

## 📋 المتطلبات الأساسية

### 1. إنشاء حساب على Railway
- اذهب إلى [railway.app](https://railway.app)
- سجّل الدخول باستخدام حساب GitHub

### 2. المتطلبات التقنية
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.4+
- Composer

---

## 🗄️ الخطوة 1: إنشاء قاعدة البيانات

### إنشاء MySQL Database على Railway:
1. من لوحة تحكم Railway، اضغط على **"New Project"**
2. اختر **"Database"** → ثم **"MySQL"**
3. انتظر حتى يتم إنشاء قاعدة البيانات
4. انسخ معلومات الاتصال:
   - **HOST**: من إعدادات Database
   - **PORT**: عادةً `3306`
   - **DATABASE NAME**: `dalatew`
   - **USERNAME**: من إعدادات Database
   - **PASSWORD**: من إعدادات Database

---

## 📁 الخطوة 2: إعداد متغيرات البيئة (Environment Variables)

في لوحة تحكم Railway، اضبط المتغيرات التالية:

| المتغير | القيمة | الوصف |
|---------|--------|-------|
| `DB_HOST` | عنوان_host_من_railway | خادم قاعدة البيانات |
| `DB_PORT` | `3306` | منفذ MySQL |
| `DB_NAME` | `dalatew` | اسم قاعدة البيانات |
| `DB_USER` | اسم_المستخدم | مستخدم قاعدة البيانات |
| `DB_PASSWORD` | كلمة_المرور | كلمة مرور قاعدة البيانات |
| `PUSHER_APP_ID` | `1918568` | معرف تطبيق Pusher |
| `PUSHER_KEY` | `4a9de0023f3255d461d9` | مفتاح Pusher |
| `PUSHER_SECRET` | `3803f60c4dc433d66655` | سر Pusher |
| `PUSHER_CLUSTER` | `ap2` | منطقة Pusher |

---

## 📊 الخطوة 3: استيراد قاعدة البيانات

### الطريقة 1: باستخدام Railway CLI
```bash
# تثبيت Railway CLI
npm install -g @railway/cli

# تسجيل الدخول
railway login

# ربط المشروع
railway link

# استيراد قاعدة البيانات
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p $DB_PASSWORD $DB_NAME < dsdcatabase.sql
```

### الطريقة 2: من لوحة تحكم Railway
1. اذهب إلى **Database** → **MySQL**
2. اضغط على **"Connect"**
3. اختر **"MySQL CLI"**
4. انسخ أمر الاتصال
5. نفذ الأمر التالي:
```sql
SOURCE dsdcatabase.sql;
```

---

## 🚀 الخطوة 4: نشر الكود

### الطريقة 1: ربط مع GitHub (موصى به)
1. ارفع الكود إلى مستودع GitHub جديد
2. في Railway، اختر **"New Project"** → **"Deploy from GitHub repo"**
3. اختر المستودع
4. Railway سيكتشف تلقائياً أنه مشروع PHP

### الطريقة 2: استخدام Railway CLI
```bash
# تثبيت CLI
npm install -g @railway/cli

# الدخول
railway login

# تهيئة المشروع
railway init

# نشر
railway up
```

---

## 📝 الخطوة 5: تحديث ملفات الإعدادات

### تحديث DB_CON.php
```php
<?php
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASSWORD = getenv('DB_PASSWORD') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'dalatew';

$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
if (!$con) {
    echo "<script>alert('NO CONNECTION')</script>";
    die();
}
?>
```

### تحديث dashboard/config.php
```php
<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'dalatew');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('CAN_REGISTER', 'none');
define('DEFAULT_ROLE', 'member');
define('SECURE', true); // تغيير إلى true في الإنتاج
define('DEBUG', false); // تغيير إلى false في الإنتاج
define('PUSHER_APP_ID', getenv('PUSHER_APP_ID') ?: '1918568');
define('PUSHER_KEY', getenv('PUSHER_KEY') ?: '4a9de0023f3255d461d9');
define('PUSHER_SECRET', getenv('PUSHER_SECRET') ?: '3803f60c4dc433d66655');
define('PUSHER_CLUSTER', getenv('PUSHER_CLUSTER') ?: 'ap2');
?>
```

---

## 🔧 الخطوة 6: إعداد DNS (اختياري)

### ربط نطاق مخصص:
1. اذهب إلى **Settings** → **Networking**
2. اضغط على **"Generate Domain"**
3. أو أضف نطاقك الخاص

---

## ✅ الخطوة 7: التحقق من النشر

1. افتح رابط التطبيق من Railway
2. اختبر:
   - ✅ الصفحة الرئيسية
   - ✅ تقديم طلب جديد
   - ✅ اختيار نوع التأمين
   - ✅ الدفع

---

## 🆘 حل المشاكل الشائعة

### مشكلة: صفحة بيضاء
- تحقق من متغيرات البيئة
- فعّل `DEBUG` في config.php

### مشكلة: خطأ في الاتصال بقاعدة البيانات
- تحقق من صحة بيانات MySQL
- تأكد من أن Database متصل

### مشكلة: Composer not found
```bash
# أضف Build command في Railway:
composer install --no-dev --optimize-autoloader
```

---

## 💰 التكلفة

- **Railway Free Tier**: يشمل 500 ساعة/شهر
- **MySQL**: مجاني في البداية
- [لوحة الأسعار](https://railway.app/pricing)

---

## 📞 مصادر إضافية

- [Railway PHP Guide](https://docs.railway.app/getting-started)
- [Railway CLI](https://docs.railway.app/railway-cli)
- [MySQL on Railway](https://docs.railway.app/databases/mysql)
