# إعداد قاعدة البيانات - Database Setup

## نظرة عامة

هذا المشروع مُعد للعمل مع قاعدة بيانات MySQL على منصة Railway. جميع ملفات الاتصال بقاعدة البيانات تدعم متغيرات البيئة تلقائياً.

## الملفات المهمة

### 1. `DB_CON.php`
ملف الاتصال الرئيسي بقاعدة البيانات. يدعم:
- متغيرات Railway التلقائية
- ملف `.env` المحلي
- معالجة الأخطاء الآمنة
- ترميز UTF-8

**الاستخدام:**
```php
<?php
require_once 'DB_CON.php';
// الآن يمكنك استخدام $con للاتصال بقاعدة البيانات
$result = mysqli_query($con, "SELECT * FROM users");
?>
```

### 2. `.env.example`
ملف مثال يحتوي على جميع متغيرات البيئة المطلوبة للإنتاج (Railway).

### 3. `.env.local.example`
ملف مثال للتطوير المحلي. انسخه إلى `.env` وعدّل القيم حسب بيئتك المحلية.

### 4. `.gitignore`
يستبعد الملفات الحساسة من Git (مثل `.env` وكلمات المرور).

## متغيرات البيئة المطلوبة

### للإنتاج (Railway)

```
MYSQLHOST=<host>
MYSQLPORT=3306
MYSQLUSER=root
MYSQLPASSWORD=<password>
MYSQL_DATABASE=railway
MYSQL_ROOT_PASSWORD=<password>
RAILWAY_PRIVATE_DOMAIN=<private-domain>
```

### للتطوير المحلي

```
MYSQLHOST=localhost
MYSQLPORT=3306
MYSQLUSER=root
MYSQLPASSWORD=your_password
MYSQL_DATABASE=railway
```

## خطوات الإعداد

### 1. التطوير المحلي

```bash
# انسخ ملف المثال
cp .env.local.example .env

# عدّل القيم حسب بيئتك المحلية
nano .env

# تأكد من أن MySQL يعمل محلياً
# ثم شغّل التطبيق
```

### 2. الإنتاج (Railway)

```bash
# 1. أنشئ خدمة MySQL في Railway
# 2. أضف المتغيرات إلى خدمة PHP/Apache
# 3. أعد نشر التطبيق
```

## استيراد قاعدة البيانات

### الملف: `dsdcatabase.sql`

يحتوي على هيكل وبيانات قاعدة البيانات الأولية.

#### الاستيراد المحلي:
```bash
mysql -u root -p railway < dsdcatabase.sql
```

#### الاستيراد على Railway:
```bash
# استخدم Railway CLI
railway connect mysql
mysql -h <host> -u <user> -p <database> < dsdcatabase.sql
```

## اختبار الاتصال

### ملف التشخيص: `diagnostic.php`

يمكنك استخدام ملف التشخيص للتحقق من الاتصال:

```bash
# افتح في المتصفح
http://localhost/diagnostic.php
```

## الأمان

### نقاط مهمة:

1. **لا تشارك `.env`**: لا تضع ملف `.env` في Git
2. **استخدم متغيرات البيئة**: لا تضع كلمات المرور في الكود
3. **استخدم HTTPS**: في الإنتاج، استخدم HTTPS دائماً
4. **قيود الوصول**: حدد من يمكنه الوصول إلى قاعدة البيانات

### ملف `.gitignore`:

```
.env
.env.local
*.log
vendor/
```

## استكشاف الأخطاء

### خطأ: "Connection refused"

```
السبب: قاعدة البيانات لا تعمل
الحل: تأكد من أن MySQL يعمل
```

### خطأ: "Access denied"

```
السبب: اسم المستخدم أو كلمة المرور خاطئة
الحل: تحقق من المتغيرات في .env
```

### خطأ: "Unknown database"

```
السبب: قاعدة البيانات لم تُنشأ بعد
الحل: استيرد ملف dsdcatabase.sql
```

## الدعم

للمزيد من المعلومات:
- اقرأ `RAILWAY_SETUP.md` لإعداد Railway
- اقرأ `DEPLOYMENT.md` لنشر التطبيق
- تحقق من `diagnostic.php` للتشخيص

---

آخر تحديث: 2026-06-26

