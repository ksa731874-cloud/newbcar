# Railway MySQL Setup Guide

هذا الدليل يشرح كيفية إعداد وتوصيل قاعدة البيانات MySQL مع تطبيق BCare على منصة Railway.

## المتطلبات

- حساب على [Railway.app](https://railway.app)
- مشروع Railway مُنشأ
- خدمة MySQL مُنشأة في المشروع

## خطوات الإعداد

### 1. إنشاء خدمة MySQL في Railway

1. انتقل إلى مشروعك على Railway
2. اضغط على "New" → "Database" → "MySQL"
3. اختر الإصدار المطلوب (الإصدار الأخير موصى به)
4. انتظر حتى تكتمل عملية الإنشاء

### 2. الحصول على بيانات الاتصال

بعد إنشاء خدمة MySQL، ستجد المتغيرات التالية تلقائياً:

```
MYSQLHOST=<host>
MYSQLPORT=3306
MYSQLUSER=root
MYSQLPASSWORD=<password>
MYSQL_DATABASE=railway
MYSQL_ROOT_PASSWORD=<password>
RAILWAY_PRIVATE_DOMAIN=<private-domain>
RAILWAY_TCP_PROXY_DOMAIN=<tcp-proxy-domain>
RAILWAY_TCP_PROXY_PORT=3306
```

### 3. ربط قاعدة البيانات مع تطبيق PHP

#### الطريقة الأولى: استخدام Reference Variables (موصى به)

1. انتقل إلى خدمة PHP/Apache الخاصة بك
2. اذهب إلى قسم "Variables"
3. أضف المتغيرات التالية:

```
MYSQLHOST=${{ MySQL.MYSQLHOST }}
MYSQLPORT=${{ MySQL.MYSQLPORT }}
MYSQLUSER=${{ MySQL.MYSQLUSER }}
MYSQLPASSWORD=${{ MySQL.MYSQLPASSWORD }}
MYSQL_DATABASE=${{ MySQL.MYSQL_DATABASE }}
MYSQL_ROOT_PASSWORD=${{ MySQL.MYSQL_ROOT_PASSWORD }}
RAILWAY_PRIVATE_DOMAIN=${{ MySQL.RAILWAY_PRIVATE_DOMAIN }}
```

#### الطريقة الثانية: نسخ المتغيرات يدويًا

1. انسخ قيم المتغيرات من خدمة MySQL
2. أضفها إلى خدمة PHP/Apache يدويًا

### 4. التحقق من الاتصال

بعد إضافة المتغيرات، قم بـ:

1. إعادة نشر التطبيق (Deploy)
2. افتح التطبيق وتحقق من أنه يعمل بدون أخطاء اتصال
3. تحقق من السجلات (Logs) للتأكد من عدم وجود أخطاء

### 5. استيراد قاعدة البيانات

إذا كان لديك ملف SQL (مثل `dsdcatabase.sql`):

#### الطريقة الأولى: استخدام Railway CLI

```bash
# تثبيت Railway CLI
npm install -g @railway/cli

# تسجيل الدخول
railway login

# الاتصال بقاعدة البيانات
railway connect mysql

# استيراد الملف
mysql -h <host> -u <user> -p <database> < dsdcatabase.sql
```

#### الطريقة الثانية: استخدام phpMyAdmin

1. أضف خدمة phpMyAdmin إلى مشروعك
2. استخدمها لاستيراد ملف SQL

#### الطريقة الثالثة: استخدام أداة أخرى

استخدم أي أداة MySQL مثل:
- MySQL Workbench
- DBeaver
- Sequel Pro

## متغيرات البيئة

### متغيرات Railway التلقائية

يوفر Railway المتغيرات التالية تلقائياً:

| المتغير | الوصف |
|--------|-------|
| `MYSQLHOST` | عنوان خادم قاعدة البيانات |
| `MYSQLPORT` | منفذ قاعدة البيانات (عادة 3306) |
| `MYSQLUSER` | اسم المستخدم (عادة root) |
| `MYSQLPASSWORD` | كلمة المرور |
| `MYSQL_DATABASE` | اسم قاعدة البيانات |
| `MYSQL_ROOT_PASSWORD` | كلمة مرور المسؤول |
| `RAILWAY_PRIVATE_DOMAIN` | النطاق الخاص (للاتصال من داخل Railway) |
| `RAILWAY_TCP_PROXY_DOMAIN` | النطاق العام (للاتصال من خارج Railway) |
| `RAILWAY_TCP_PROXY_PORT` | منفذ الاتصال العام |

### متغيرات إضافية

يمكنك إضافة متغيرات إضافية حسب احتياجاتك:

```
DEBUG_MODE=false
APP_ENV=production
```

## ملفات التكوين

### DB_CON.php

ملف الاتصال بقاعدة البيانات يدعم:
- متغيرات Railway التلقائية
- ملف `.env` المحلي (للتطوير)
- معالجة الأخطاء الآمنة
- ترميز UTF-8

### .env.example

ملف مثال يحتوي على جميع المتغيرات المطلوبة. استخدمه كمرجع.

## استكشاف الأخطاء

### خطأ: "Database Connection Failed"

1. تحقق من أن خدمة MySQL تعمل
2. تحقق من صحة المتغيرات
3. تحقق من السجلات (Logs) للحصول على تفاصيل الخطأ

### خطأ: "Connection timeout"

1. تأكد من أن التطبيق والقاعدة في نفس المشروع
2. استخدم `RAILWAY_PRIVATE_DOMAIN` للاتصال الداخلي
3. تحقق من حالة الشبكة

### خطأ: "Access denied for user"

1. تحقق من اسم المستخدم وكلمة المرور
2. تأكد من أن المستخدم له صلاحيات على قاعدة البيانات
3. أعد تعيين كلمة المرور إذا لزم الأمر

## الأمان

### نصائح الأمان

1. **لا تشارك كلمات المرور**: لا تضع كلمات المرور في الكود
2. **استخدم متغيرات البيئة**: استخدم متغيرات Railway بدلاً من القيم الثابتة
3. **استخدم HTTPS**: تأكد من استخدام HTTPS للاتصالات
4. **قيود الوصول**: حدد من يمكنه الوصول إلى قاعدة البيانات

### حماية البيانات الحساسة

- لا تضع ملف `.env` في Git
- استخدم `.gitignore` لاستبعاد الملفات الحساسة
- استخدم متغيرات Railway بدلاً من الملفات المحلية

## الدعم والمساعدة

- [توثيق Railway](https://docs.railway.app)
- [منتدى Railway](https://railway.app/support)
- [GitHub Issues](https://github.com/ksa731874-cloud/newbcar/issues)

---

آخر تحديث: 2026-06-26

