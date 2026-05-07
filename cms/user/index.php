<?php 
session_start(); 
error_reporting(0); 
include("include/config.php"); 

if(isset($_POST['submit'])) {
    $email = $_POST['emailid'];
    $password = md5($_POST['inputuserpwd']); 
    $query = mysqli_query($con,"SELECT id,fullName FROM users WHERE userEmail='$email' and password='$password'"); 
    $num = mysqli_fetch_array($query); 
    if($num > 0) { 
        $_SESSION['login'] = $_POST['emailid']; 
        $_SESSION['id'] = $num['id']; 
        $_SESSION['username'] = $num['fullName']; 
        echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>"; 
    } else {     
        $error = "Invalid email or password. Please try again.";
    } 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE | Sign In</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-ultralight-58646b19bf205.otf') format('opentype'); font-weight:100; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-thin-58646e9b26e8b.otf') format('opentype'); font-weight:200; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); font-weight:400; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); font-weight:600; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); font-weight:700; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); font-weight:800; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); font-weight:900; }

        :root {
            --orange:       #F28018;
            --orange-dark:  #d96e0e;
            --orange-glow:  rgba(242,128,24,0.18);
            --orange-soft:  rgba(242,128,24,0.08);
            --dark:         #0f0f0f;
            --mid:          #3a3a3a;
            --muted:        #888;
            --border:       #e8e8e8;
            --white:        #ffffff;
            --off-white:    #fafafa;
            --success:      #22a65a;
            --err-bg:       rgba(211,47,47,0.07);
            --err-color:    #c62828;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html, body { height:100%; }

        body {
            font-family: 'SF UI Display', -apple-system, sans-serif;
            background: var(--white);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ═══════════════ LEFT PANEL ═══════════════ */
        .panel-left {
            width: 45%;
            min-width: 420px;
            background: var(--white);
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 2;
            overflow-y: auto;
        }

        /* ambient warm glow bottom-left */
        .panel-left::before {
            content: '';
            position: fixed;
            bottom: -100px; left: -100px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(242,128,24,0.06) 0%, transparent 65%);
            pointer-events: none;
            z-index: 0;
        }

        /* hairline divider */
        .panel-left::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 1px;
            background: linear-gradient(180deg,
                transparent 0%, var(--border) 15%,
                var(--border) 85%, transparent 100%);
            z-index: 10;
        }

        /* ── Nav ─────────────────────────────────── */
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 52px;
            position: relative;
            z-index: 2;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }
       
        .back-link {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border: 1px solid var(--border);
            border-radius: 50px;
            transition: all .22s;
        }
       

        /* ── Form area ───────────────────────────── */
        .form-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px 52px 52px;
            position: relative;
            z-index: 2;
        }

        /* ── Heading ─────────────────────────────── */
        .heading-block { margin-bottom: 36px; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        
            margin-bottom: 16px;
        }
        .eyebrow .pulse-dot {
            width: 6px; height: 6px;
            background: var(--orange);
            border-radius: 50%;
            animation: pulseDot 2s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:.4; transform:scale(.65); }
        }

        .heading-block h1 {
            font-size: 43px;
            font-weight: 900;
            color: var(--dark);
            line-height: 1.07;
            letter-spacing: -0.034em;
            margin-bottom: 13px;
        }
        .heading-block h1 .hl {
            color: var(--orange);
            position: relative;
        }
        .heading-block h1 .hl::after {
            content: '';
            position: absolute;
            left: 0; bottom: -3px;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, var(--orange), var(--orange-dark));
            border-radius: 2px;
            transform-origin: left;
            animation: lineIn .5s cubic-bezier(.22,.8,.32,1) .75s both;
        }
        @keyframes lineIn {
            from { transform:scaleX(0); }
            to   { transform:scaleX(1); }
        }

        .heading-block p {
            font-size: 14.5px;
            font-weight: 400;
            color: var(--muted);
            line-height: 1.7;
        }

        /* ── Error ───────────────────────────────── */
        .error-box {
            display: flex;
            align-items: center;
            gap: 11px;
            background: var(--err-bg);
            border: 1px solid rgba(198,40,40,0.18);
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 600;
            color: var(--err-color);
            animation: shake .4s ease;
        }
        @keyframes shake {
            0%,100%{transform:translateX(0)} 25%{transform:translateX(-6px)} 75%{transform:translateX(6px)}
        }

        /* ── Fields ──────────────────────────────── */
        .field { margin-bottom: 20px; }

        .field-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--mid);
            margin-bottom: 9px;
            display: block;
        }

        .input-shell {
            position: relative;
            border-radius: 12px;
            transition: box-shadow .25s;
        }
        .input-shell:focus-within {
            box-shadow: 0 0 0 3.5px var(--orange-glow);
        }
        .input-shell .ico {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            color: #c8c8c8;
            font-size: 13.5px;
            pointer-events: none;
            transition: color .22s;
        }
        .input-shell:focus-within .ico { color: var(--orange); }

        .field-input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            font-size: 14.5px;
            font-weight: 400;
            color: var(--dark);
            background: var(--off-white);
            outline: none;
            transition: border-color .22s, background .22s;
        }
        .field-input::placeholder { color:#c0c0c0; }
        .field-input:focus { border-color: var(--orange); background: var(--white); }

        .pw-eye {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #c0c0c0;
            font-size: 13px;
            cursor: pointer;
            padding: 4px;
            transition: color .2s;
        }
        .pw-eye:hover { color: var(--orange); }

        /* ── Meta row ────────────────────────────── */
        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
        }
        .check-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 600; color: var(--mid);
            cursor: pointer; user-select: none;
        }
        .check-label input[type=checkbox] { accent-color:var(--orange); width:15px; height:15px; }
        .forgot-link {
            font-size: 13px; font-weight: 600;
            color: var(--orange); text-decoration: none; transition: opacity .2s;
        }
        .forgot-link:hover { opacity:.6; }

        /* ── Sign In btn ─────────────────────────── */
        .btn-signin {
            width: 100%;
            padding: 15px 20px;
            background: var(--orange);
            border: none; border-radius: 12px;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            font-size: 15px; font-weight: 800;
            color: var(--white); cursor: pointer;
            letter-spacing: 0.06em; text-transform: uppercase;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            position: relative; overflow: hidden;
            transition: background .25s, transform .2s, box-shadow .25s;
        }
        .btn-signin::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(105deg, transparent 20%, rgba(255,255,255,.16) 50%, transparent 80%);
            transform: translateX(-120%);
            transition: transform .6s ease;
        }
        .btn-signin:hover { background:var(--orange-dark); transform:translateY(-2px); box-shadow:0 12px 32px rgba(242,128,24,.38); }
        .btn-signin:hover::before { transform:translateX(120%); }
        .btn-signin:active { transform:none; box-shadow:none; }
        .btn-icon {
            width: 28px; height: 28px;
            background: rgba(255,255,255,.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
            transition: transform .25s;
        }
        .btn-signin:hover .btn-icon { transform: translateX(4px); }

        /* ── Divider ─────────────────────────────── */
        .divider {
            display: flex; align-items: center; gap: 14px;
            margin: 24px 0 20px;
            color: #d0d0d0; font-size: 11px; font-weight: 700;
            letter-spacing: .15em; text-transform: uppercase;
        }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:var(--border); }

        /* ── Register card ───────────────────────── */
        .register-card {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 16px 20px;
            border: 1.5px solid var(--border);
            border-radius: 14px; background: var(--off-white);
            transition: border-color .22s;
        }
        .register-card:hover { border-color: rgba(242,128,24,.28); }
        .reg-text p { font-size:13px; color:var(--muted); line-height:1.5; }
        .reg-text strong { display:block; font-size:14px; font-weight:700; color:var(--dark); margin-bottom:2px; }
        .btn-register {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 22px; background: var(--success);
            border-radius: 50px;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            font-size: 13px; font-weight: 700; color: var(--white);
            text-decoration: none; white-space: nowrap;
            transition: background .22s, transform .2s, box-shadow .22s;
        }
        .btn-register:hover { background:#1a8f4c; transform:translateY(-1px); box-shadow:0 8px 20px rgba(34,166,90,.3); color:var(--white); }

        /* ═══════════════ RIGHT PANEL ══════════════ */
        .panel-right {
            flex: 1;
            background: var(--white);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* diagonal pattern overlay */
        .panel-right::before {
            content: '';
            position: absolute;
            inset: 0;
            
            z-index: 1;
            pointer-events: none;
        }

       

        /* glowing orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }
        .orb-1 {
            width: 500px; height: 500px;
            top: -160px; right: -140px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 65%);
        }
        .orb-2 {
            width: 380px; height: 380px;
            bottom: -120px; left: -100px;
            background: radial-gradient(circle, rgba(0,0,0,0.12) 0%, transparent 65%);
        }

        /* decorative rings */
        .ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.1);
            pointer-events: none;
            z-index: 1;
        }
        .ring-1 { width:500px; height:500px; top:-120px; right:-120px; }
        .ring-2 { width:700px; height:700px; top:-220px; right:-220px; }
        .ring-3 { width:280px; height:280px; bottom:40px; left:-60px; border-color:rgba(255,255,255,0.07); }

       
        @keyframes floatImg {
            0%,100% { transform: translateY(0px) rotate(0deg); }
            33%      { transform: translateY(-10px) rotate(0.4deg); }
            66%      { transform: translateY(-5px) rotate(-0.3deg); }
        }

        /* corner accent badge */
        .corner-badge {
            position: absolute;
            top: 36px;
            right: 36px;
            z-index: 4;
            background: rgba(0,0,0,0.18);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 12px;
            padding: 10px 16px;
            text-align: center;
        }
        .corner-badge .cb-num {
            display: block;
            font-size: 22px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .corner-badge .cb-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.7);
            margin-top: 3px;
        }

        /* stats row bottom-left */
        .stats-row {
            position: absolute;
            bottom: 80px;
            left: 36px;
            z-index: 4;
            display: flex;
            gap: 16px;
        }
        .stat-pill {
            background: rgba(0,0,0,0.18);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 50px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .stat-pill i { font-size:12px; color:rgba(255,255,255,0.8); }
        .stat-pill span { font-size:11px; font-weight:700; letter-spacing:0.08em; color:#fff; }

        /* bottom tag */
        .brand-tag {
            position: absolute;
            bottom: 34px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.18);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 50px;
            padding: 9px 22px;
            white-space: nowrap;
        }
        .brand-tag span {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255,255,255,.88);
        }
        .sep {
            width: 4px; height: 4px;
            background: rgba(255,255,255,.45);
            border-radius: 50%;
        }

        /* headline text on right panel */
        .panel-headline {
            position: absolute;
            top: 38px;
            left: 36px;
            z-index: 4;
        }
        .panel-headline .ph-eyebrow {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.65);
            margin-bottom: 6px;
            display: block;
        }
        .panel-headline h2 {
            font-size: 28px;
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        /* ═══════════════ ANIMATIONS ════════════════ */
        .panel-left  { animation: sLeft  .68s cubic-bezier(.22,.8,.32,1) both; }
        .panel-right { animation: sRight .68s cubic-bezier(.22,.8,.32,1) .1s both; }
        @keyframes sLeft  { from{opacity:0;transform:translateX(-30px)} to{opacity:1;transform:none} }
        @keyframes sRight { from{opacity:0;transform:translateX(30px)}  to{opacity:1;transform:none} }

        .top-nav           { animation: fUp .5s ease .24s both; }
        .heading-block     { animation: fUp .5s ease .34s both; }
        .field:nth-child(1){ animation: fUp .45s ease .44s both; }
        .field:nth-child(2){ animation: fUp .45s ease .50s both; }
        .meta-row          { animation: fUp .4s ease .54s both; }
        .btn-signin        { animation: fUp .4s ease .58s both; }
        .divider           { animation: fUp .35s ease .62s both; }
        .register-card     { animation: fUp .35s ease .66s both; }
        @keyframes fUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }

        /* ═══════════════ RESPONSIVE ════════════════ */
        @media(max-width:960px){
            body { flex-direction:column-reverse; overflow-y:auto; overflow-x:hidden; }
            .panel-left { width:100%; min-width:unset; }
            .panel-left::after { display:none; }
            .panel-right { width:100%; height:380px; flex:none; }
            .brand-img-wrap { width:64%; max-width:340px; }
            .stats-row { bottom:54px; left:20px; gap:10px; }
            .panel-headline { top:24px; left:20px; }
            .panel-headline h2 { font-size:22px; }
            .corner-badge { top:20px; right:20px; }
            .brand-tag { bottom:14px; padding:7px 16px; }
        }
        @media(max-width:560px){
            .top-nav  { padding:24px 26px; }
            .form-area{ padding:16px 26px 44px; }
            .heading-block h1 { font-size:34px; }
            .panel-right { height:300px; }
            .brand-img-wrap { width:75%; }
            .stats-row { display:none; }
        }
    </style>
</head>
<body>

<!-- ════════ LEFT PANEL ════════ -->
<div class="panel-left">

 

    <div class="form-area">

        <div class="heading-block">
            <div class="eyebrow"><span class="pulse-dot"></span> Welcome back</div>
            <h1>Sign in to<br><span class="hl">Your account</span></h1>
            <p>Manage your claims, orders and feedback<br>— all from one place.</p>
        </div>

        <?php if(isset($error)) { ?>
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php } ?>

        <form method="post" autocomplete="off">

            <div class="field">
                <label class="field-label">Email Address</label>
                <div class="input-shell">
                    <i class="fas fa-envelope ico"></i>
                    <input type="email" name="emailid" class="field-input" required placeholder="you@example.com">
                </div>
            </div>

            <div class="field">
                <label class="field-label">Password</label>
                <div class="input-shell">
                    <i class="fas fa-lock ico"></i>
                    <input type="password" name="inputuserpwd" id="pwField" class="field-input" required placeholder="Enter your password">
                    <i class="fas fa-eye pw-eye" id="pwEye" onclick="togglePw()"></i>
                </div>
            </div>

            <div class="meta-row">
                <label class="check-label">
                    <input type="checkbox"> Keep me signed in
                </label>
                <a href="reset-password.php" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" name="submit" class="btn-signin">
                Sign In
                <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
            </button>

        </form>

        <div class="divider">or</div>

        <div class="register-card">
            <div class="reg-text">
                <strong>New here?</strong>
                <p>Create a free account in seconds.</p>
            </div>
            <a href="registration.php" class="btn-register">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </div>

    </div>
</div>

<!-- ════════ RIGHT PANEL ════════ -->
<div class="panel-right">

    <!-- background decorations -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="ring ring-1"></div>
    <div class="ring ring-2"></div>
    <div class="ring ring-3"></div>

    
    <!-- centre brand image -->
    <div class="brand-img-wrap">
        <img src="atire-brand.png" alt="ATIRE Tyre Range">
    </div>


    <!-- bottom centre tag -->
    <div class="brand-tag">
        <span>Premium Tyres</span>
        <span class="sep"></span>
        <span>Trusted Quality</span>
        <span class="sep"></span>
        <span>Island Wide</span>
    </div>

</div>

<script>
function togglePw() {
    const f = document.getElementById('pwField');
    const i = document.getElementById('pwEye');
    if (f.type === 'password') {
        f.type = 'text';
        i.classList.replace('fa-eye','fa-eye-slash');
    } else {
        f.type = 'password';
        i.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>

</body>
</html>