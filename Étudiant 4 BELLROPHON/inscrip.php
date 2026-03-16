<?php
// =============================================
//  inscrip.php — Inscription utilisateur
// =============================================
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include('config.php');

$error   = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($name) && !empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "⚠️ Adresse email invalide.";
        } elseif (strlen($password) < 6) {
            $error = "⚠️ Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            // Vérifier si l'email est déjà utilisé
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "⚠️ Cet email est déjà utilisé.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Utiliser la procédure stockée create_user (crée l'user + attribue le rôle "user")
                $insert = $conn->prepare("CALL create_user(?, ?, ?)");
                $insert->execute([$name, $email, $hashedPassword]);

                $success = true;
            }
        }
    } else {
        $error = "❌ Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Bryhann.B</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Montserrat', sans-serif;
      background: radial-gradient(circle at top left, #1b2735, #090a0f);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
      color: #e0e0e0;
    }

    .register-container {
      position: relative;
      width: 400px;
      padding: 40px 35px;
      background: rgba(25,25,25,0.75);
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.6);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      text-align: center;
      animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    h2 { color: #fff; font-weight: 600; margin-bottom: 25px; }

    label {
      display: block;
      text-align: left;
      color: #ccc;
      font-weight: 500;
      margin-bottom: 8px;
    }

    input {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      margin-bottom: 20px;
      background: rgba(255,255,255,0.1);
      color: #fff;
      font-size: 14px;
      outline: none;
      transition: all .3s ease;
    }

    input::placeholder { color: #aaa; }
    input:focus {
      background: rgba(255,255,255,0.15);
      box-shadow: 0 0 5px #2196f3;
    }

    button {
      width: 100%;
      background: linear-gradient(135deg, #1e88e5, #0d47a1);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all .3s ease;
    }

    button:hover {
      background: linear-gradient(135deg, #2196f3, #1565c0);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(33,150,243,0.4);
    }

    p { margin-top: 20px; font-size: 14px; }

    a { color: #64b5f6; text-decoration: none; font-weight: 600; }
    a:hover { text-decoration: underline; }

    .error, .success {
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: center;
    }
    .error   { background: rgba(255,0,0,0.1);   color: #ef5350; }
    .success { background: rgba(0,255,0,0.1);   color: #81c784; }

    .brand {
      font-size: 1.3rem;
      font-weight: 600;
      color: #64b5f6;
      margin-bottom: 15px;
      letter-spacing: 1px;
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      filter: blur(100px);
      opacity: 0.3;
      animation: float 8s ease-in-out infinite;
    }
    .circle1 { width:200px; height:200px; background:#1e88e5; top:-80px; right:-100px; }
    .circle2 { width:180px; height:180px; background:#0d47a1; bottom:-60px; left:-70px; }

    @keyframes float {
      0%,100% { transform: translateY(0); }
      50%      { transform: translateY(20px); }
    }

    @media (max-width: 450px) {
      .register-container { width: 90%; padding: 30px; }
    }
  </style>
</head>
<body>

  <div class="circle circle1"></div>
  <div class="circle circle2"></div>

  <div class="register-container">

    <!-- Language toggle -->
    <div style="position:absolute;top:14px;right:14px;">
      <button id="lang-toggle" onclick="toggleLang()"
        style="font-size:11px;font-weight:700;background:rgba(33,150,243,.12);color:#64b5f6;border:1px solid rgba(33,150,243,.3);border-radius:7px;padding:4px 10px;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s;"
        onmouseover="this.style.background='rgba(33,150,243,.25)'"
        onmouseout="this.style.background='rgba(33,150,243,.12)'">🌐 EN</button>
    </div>

    <div class="brand">Bryhann.B</div>
    <h2 data-i18n="title">Créer un compte</h2>

    <?php if (!empty($error)) : ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success) : ?>
      <div class="success" id="success-msg">
        ✅ <span data-i18n="success-text">Inscription réussie !</span>
        <a href="login.php" data-i18n="success-link">Connecte-toi ici</a>
      </div>
    <?php else : ?>
    <form method="post" action="">
      <label data-i18n="label-name">Nom</label>
      <input type="text" name="name" placeholder="Votre nom complet" data-i18n-ph="ph-name" required>

      <label data-i18n="label-email">Email</label>
      <input type="email" name="email" placeholder="Adresse e-mail" data-i18n-ph="ph-email" required>

      <label data-i18n="label-password">Mot de passe</label>
      <input type="password" name="password" placeholder="Minimum 6 caractères" data-i18n-ph="ph-password" required>

      <button type="submit" data-i18n="btn-register">S'inscrire</button>
    </form>

    <p data-i18n-html="already-account">Déjà un compte ? <a href="login.php">Se connecter</a></p>
    <?php endif; ?>
  </div>

<script>
const inscripTranslations = {
  fr: {
    'title':           'Créer un compte',
    'label-name':      'Nom',
    'ph-name':         'Votre nom complet',
    'label-email':     'Email',
    'ph-email':        'Adresse e-mail',
    'label-password':  'Mot de passe',
    'ph-password':     'Minimum 6 caractères',
    'btn-register':    "S'inscrire",
    'already-account': 'Déjà un compte ? <a href="login.php">Se connecter</a>',
    'success-text':    'Inscription réussie !',
    'success-link':    'Connecte-toi ici',
  },
  en: {
    'title':           'Create an account',
    'label-name':      'Name',
    'ph-name':         'Your full name',
    'label-email':     'Email',
    'ph-email':        'Email address',
    'label-password':  'Password',
    'ph-password':     'Minimum 6 characters',
    'btn-register':    'Sign up',
    'already-account': 'Already have an account? <a href="login.php">Sign in</a>',
    'success-text':    'Registration successful!',
    'success-link':    'Log in here',
  }
};

let lang = 'fr';

function applyLang(l) {
  const t = inscripTranslations[l];

  // Text nodes
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (t[key] !== undefined) el.textContent = t[key];
  });

  // Placeholders
  document.querySelectorAll('[data-i18n-ph]').forEach(el => {
    const key = el.getAttribute('data-i18n-ph');
    if (t[key] !== undefined) el.placeholder = t[key];
  });

  // HTML content (for paragraph with embedded link)
  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const key = el.getAttribute('data-i18n-html');
    if (t[key] !== undefined) el.innerHTML = t[key];
  });

  // Toggle button label
  const btn = document.getElementById('lang-toggle');
  if (btn) btn.textContent = l === 'fr' ? '🌐 EN' : '🌐 FR';

  document.documentElement.lang = l;
}

function toggleLang() {
  lang = lang === 'fr' ? 'en' : 'fr';
  applyLang(lang);
}

applyLang('fr');
</script>

</body>
</html>
