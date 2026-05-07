<?php
session_start();
if (isset($_SESSION['emp_id'])) {
    header('Location: verification.php');
    exit;
}

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $pswd    = trim($_POST['pswd']    ?? '');

    if (empty($user_id) || empty($pswd)) {
        $error = 'Please enter both User ID and Password.';
    } else {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            $error = 'Database connection failed.';
        } else {
            $stmt = $conn->prepare(
                "SELECT id, emp_code, emp_name, user_id, user_role, emp_pro, email_id
                 FROM emp_login
                 WHERE user_id = ? AND pswd = ? AND status = 1
                 LIMIT 1"
            );
            $stmt->bind_param("ss", $user_id, $pswd);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $emp = $result->fetch_assoc();
                $_SESSION['emp_id']    = $emp['id'];
                $_SESSION['emp_code']  = $emp['emp_code'];
                $_SESSION['emp_name']  = $emp['emp_name'];
                $_SESSION['user_id']   = $emp['user_id'];
                $_SESSION['user_role'] = $emp['user_role'];
                $_SESSION['emp_pro']   = $emp['emp_pro'];
                header('Location: verification.php');
                exit;
            } else {
                $error = 'Invalid credentials or account inactive.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Login — TireVerify</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root,[data-theme="dark"]{
    --orange:#F28018;--orange-glow:rgba(242,128,24,.22);--orange-soft:rgba(242,128,24,.1);
    --bg:#0d0d0d;--surface:#161616;--surface2:#1e1e1e;--border:rgba(255,255,255,.08);
    --border-hot:rgba(242,128,24,.45);--text:#fff;--muted:rgba(255,255,255,.42);--dim:rgba(255,255,255,.16);
    --danger:#f87171;
}
[data-theme="light"]{
    --bg:#f2f1ed;--surface:#fff;--surface2:#edebe6;--border:rgba(0,0,0,.09);
    --text:#1a1a1a;--muted:rgba(0,0,0,.45);--dim:rgba(0,0,0,.22);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    padding:20px;
}
.login-card{
    width:100%;max-width:400px;
    background:var(--surface);border:1px solid var(--border);border-radius:20px;
    padding:36px 28px 32px;
}
.login-logo{text-align:center;margin-bottom:24px;}
.login-logo img{height:42px;object-fit:contain;margin-bottom:10px;display:block;margin:0 auto 10px;}
.login-title{font-family:'Bebas Neue',sans-serif;font-size:1.9rem;letter-spacing:.05em;text-align:center;}
.login-title span{color:var(--orange);}
.login-sub{font-size:.72rem;color:var(--muted);text-align:center;margin-top:4px;margin-bottom:28px;}

.form-field{margin-bottom:14px;}
.form-label{font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:6px;}
.input-wrap{position:relative;}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--dim);font-size:.85rem;}
.form-input{
    width:100%;padding:14px 13px 14px 40px;border-radius:11px;
    background:var(--surface2);border:1.5px solid var(--border);
    color:var(--text);font-family:'Outfit',sans-serif;font-size:16px;
    transition:border-color .2s;-webkit-appearance:none;
}
.form-input:focus{outline:none;border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-soft);}
.form-input::placeholder{color:var(--dim);}

.toggle-pw{
    position:absolute;right:12px;top:50%;transform:translateY(-50%);
    background:none;border:none;color:var(--dim);cursor:pointer;font-size:.85rem;padding:4px;
}

.error-box{
    background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.35);
    border-radius:10px;padding:11px 13px;font-size:.8rem;color:var(--danger);
    margin-bottom:16px;display:flex;align-items:center;gap:8px;
}

.btn-login{
    width:100%;height:52px;border-radius:13px;border:none;
    background:var(--orange);color:#fff;
    font-family:'Outfit',sans-serif;font-weight:700;font-size:.95rem;
    cursor:pointer;transition:all .18s;margin-top:6px;
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 4px 22px var(--orange-glow);
}
.btn-login:active{transform:scale(.97);}
.btn-login:hover{background:#c8660e;}

.login-footer{text-align:center;margin-top:20px;font-size:.65rem;color:var(--dim);}
</style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <img src="atire.png" alt="ATire" onerror="this.style.display='none'">
    </div>
    <div class="login-title">Tire<span>Verify</span></div>
    <div class="login-sub">Sign in to continue &middot; UK Series</div>

    <?php if ($error): ?>
    <div class="error-box"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="on">
        <div class="form-field">
            <label class="form-label">User ID</label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input class="form-input" type="text" name="user_id"
                    placeholder="Enter your user ID"
                    value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>"
                    autocomplete="username" required>
            </div>
        </div>

        <div class="form-field">
            <label class="form-label">Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input class="form-input" type="password" name="pswd"
                    id="pwdInput" placeholder="Enter your password"
                    autocomplete="current-password" required>
                <button type="button" class="toggle-pw" onclick="togglePw()">
                    <i class="fas fa-eye" id="pwEyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>

    <div class="login-footer">
        &copy; <?php echo date('Y'); ?> Tire Label Management System
    </div>
</div>

<script>
function togglePw(){
    var inp = document.getElementById('pwdInput');
    var ico = document.getElementById('pwEyeIcon');
    if(inp.type === 'password'){
        inp.type = 'text';
        ico.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'fas fa-eye';
    }
}
// Persist theme from main app
var t = localStorage.getItem('tlsTheme') || 'dark';
document.documentElement.setAttribute('data-theme', t);
</script>
</body>
</html>