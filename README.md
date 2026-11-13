# Rdl — dlclanguagecenter.ir

- `astra.zip` — قالب والد (Astra 4.14.0)
- `dlc-child/` — قالب فرزند سایت. منبع به‌روزرسانی سایت همین پوشه در شاخه‌ی `main` است.

## انتشار نسخه‌ی جدید
1. تغییرات را در `dlc-child/` اعمال کنید.
2. مقدار `Version` را در `dlc-child/style.css` بالا ببرید و در `main` ادغام کنید.
3. پیشخوان وردپرس ← نمایش ← «به‌روزرسانی قالب DLC» ← «بررسی نسخه‌ی جدید» ← «به‌روزرسانی».

## wp-config.php
```php
define( 'DLC_GITHUB_TOKEN', 'github_pat_...' ); // برای مخزن خصوصی الزامی است
```
اختیاری: `DLC_GITHUB_REPO`، `DLC_GITHUB_BRANCH`، `DLC_GITHUB_PATH`.
