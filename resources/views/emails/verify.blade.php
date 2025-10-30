<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f6f9fc; color: #333; padding: 40px; }
  .container { background: #fff; border-radius: 12px; padding: 30px; max-width: 500px; margin: auto; }
  h2 { color: #0f1a1f; }
  a.button {
    display: inline-block;
    padding: 12px 24px;
    background: #4365FF;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 20px;
  }
  footer { margin-top: 40px; font-size: 12px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="container">
  <h2>Üdv, {{ $user->name }}!</h2>
  <p>Kérjük, erősítsd meg az e-mail címed a lenti gombbal:</p>
  <a href="{{ $verifyUrl }}" class="button">Email megerősítése</a>
  <p style="margin-top:20px;font-size:14px;color:#555;">
    Ha nem te regisztráltál, ezt az üzenetet figyelmen kívül hagyhatod.
  </p>
</div>
<footer>PetRescue • {{ now()->format('Y') }}</footer>
</body>
</html>
