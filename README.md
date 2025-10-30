<p align="center">
  <img src="https://laravel.com/img/logomark.min.svg" width="80">
  <img src="https://storage.googleapis.com/cms-storage-bucket/6a07d8a62f4308d2b854.svg" width="100" style="margin-left: 10px">
</p>

<h1 align="center">🚀 PetRescue App – Laravel + Flutter Fullstack Projekt</h1>

<p align="center">
  <b>Modern, biztonságos és bővíthető REST API Laravel 12 + Flutter klienssel.</b><br>
  Teljes autentikációs réteggel, email-verifikációval, jelszó-visszaállítással és Google bejelentkezéssel.
</p>

---

## 🧱 Technológiai stack

**Backend:** Laravel 12 (PHP 8.2)
**Frontend / App:** Flutter 3.35
**Adatbázis:** MySQL 8
**Hitelesítés:** Laravel Sanctum + Socialite (Google OAuth2)
**Email küldés:** Gmail SMTP (HTML sablonokkal)
**Környezeti változók:** `.env` konfiguráció
**Deployment:** LAN / Localhost fejlesztés, később production-ready

---

## ⚙️ Főbb funkciók

| Modul                           | Leírás                                            |
| ------------------------------- | ------------------------------------------------- |
| 🔐 **Autentikáció**             | Regisztráció, bejelentkezés, kijelentkezés        |
| 📬 **Email-verifikáció**        | Egyedi HTML sablon, rövidített linkekkel          |
| 🔁 **Jelszó-visszaállítás**     | Biztonságos tokenes rendszer, rövid URL redirect  |
| 🌐 **Google Login**             | OAuth 2.0 alapú harmadik fél belépés (Socialite)  |
| 🧾 **Token alapú hozzáférés**   | Laravel Sanctum API tokenekkel                    |
| 🧰 **Flutter integráció**       | HTTP REST hívások LAN-on keresztül                |
| 💌 **Testreszabott levelek**    | Saját HTML template + dinamikus link generálás    |
| 🧩 **Moduláris route rendszer** | Külön `routes/api/auth.php`, `routes/web.php`     |
| 🗂 **.env alapú konfiguráció**   | Teljesen környezetfüggetlen API_URL, FRONTEND_URL |

---

## 📁 Projekt struktúra

```bash
project/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   ├── PasswordResetController.php
│   │   └── SocialAuthController.php
│   └── Providers/
│       └── AuthServiceProvider.php
├── routes/
│   ├── api.php
│   ├── api/
│   │   └── auth.php
│   └── web.php
├── resources/views/emails/
│   ├── verify.blade.php
│   └── reset.blade.php
├── config/
│   ├── app.php
│   └── urls.php
├── .env
└── .env.example
```
