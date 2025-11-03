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
    <p>Az alábbi linken változtathatod meg a jelszavad:</p>
       <a href="{{ $fallbackUrl }}"
        style="display:inline-block;padding:12px 24px;background:#4365ff;color:white;border-radius:8px;text-decoration:none"
        target="_blank">
        Jelszó megváltoztatása
        </a>
        <p style="margin-top:8px;font-size:12px">
            Ha mobilon nem működik, kattints ide:
            <a href="{{ $fallbackUrl }}" target="_blank" style="color:#0f1a1f;">Webes jelszóváltoztatás</a>
        </p>
</div>
<footer>Hadház Szeku • {{ now()->format('Y') }}</footer>
</body>
</html>
