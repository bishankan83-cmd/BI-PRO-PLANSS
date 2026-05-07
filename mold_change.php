<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Under Construction</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    min-height: 100vh;
    background: #0f0f0f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', sans-serif;
    overflow: hidden;
  }
  .stars { position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: 0; }
  .star {
    position: absolute; width: 2px; height: 2px;
    background: white; border-radius: 50%;
    animation: twinkle 3s infinite; opacity: 0.4;
  }
  @keyframes twinkle {
    0%, 100% { opacity: 0.2; }
    50% { opacity: 0.8; }
  }
  .container { position: relative; z-index: 1; text-align: center; padding: 3rem 2rem; max-width: 600px; }
  .icon-wrap { position: relative; display: inline-block; margin-bottom: 2rem; }
  .gear { width: 100px; height: 100px; animation: spin 6s linear infinite; filter: drop-shadow(0 0 20px #f5a623aa); }
  .gear-small {
    position: absolute; width: 50px; height: 50px;
    bottom: -10px; right: -15px;
    animation: spin-reverse 4s linear infinite;
    filter: drop-shadow(0 0 10px #f5a623aa);
  }
  @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  @keyframes spin-reverse { from { transform: rotate(0deg); } to { transform: rotate(-360deg); } }
  .badge {
    display: inline-block; background: #f5a623; color: #1a0e00;
    font-size: 11px; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; padding: 5px 16px; border-radius: 20px; margin-bottom: 1.5rem;
  }
  h1 { font-size: clamp(2rem, 6vw, 3.5rem); font-weight: 800; color: #ffffff; line-height: 1.1; margin-bottom: 1rem; letter-spacing: -1px; }
  h1 span { color: #f5a623; }
  p { font-size: 1rem; color: #888; line-height: 1.7; margin-bottom: 2.5rem; max-width: 400px; margin-left: auto; margin-right: auto; }
  .progress-label { display: flex; justify-content: space-between; font-size: 12px; color: #555; margin-bottom: 8px; }
  .progress-bar-wrap { background: #1e1e1e; border-radius: 8px; height: 8px; overflow: hidden; margin-bottom: 2.5rem; border: 1px solid #2a2a2a; }
  .progress-bar-fill { height: 100%; width: 0%; background: linear-gradient(90deg, #f5a623, #e8401c); border-radius: 8px; transition: width 2s ease; }
  .notify-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
  .notify-form input {
    flex: 1; min-width: 220px; max-width: 300px; padding: 12px 18px;
    background: #1a1a1a; border: 1px solid #2e2e2e; border-radius: 10px;
    color: #fff; font-size: 14px; outline: none; transition: border-color 0.2s;
  }
  .notify-form input:focus { border-color: #f5a623; }
  .notify-form input::placeholder { color: #444; }
  .notify-form button {
    padding: 12px 24px; background: #f5a623; color: #1a0e00;
    border: none; border-radius: 10px; font-weight: 700; font-size: 14px;
    cursor: pointer; transition: background 0.2s, transform 0.1s;
  }
  .notify-form button:hover { background: #e89a10; }
  .notify-form button:active { transform: scale(0.97); }
  .success-msg { display: none; color: #4ade80; font-size: 14px; margin-top: 1rem; }
  .tape { position: fixed; top: 0; width: 100%; background: repeating-linear-gradient(90deg, #f5a623 0px, #f5a623 40px, #1a0e00 40px, #1a0e00 80px); height: 8px; z-index: 10; }
  .tape-bottom { bottom: 0; top: auto; }
  .social-links { display: flex; gap: 16px; justify-content: center; margin-top: 2rem; }
  .social-links a { color: #444; font-size: 13px; text-decoration: none; transition: color 0.2s; }
  .social-links a:hover { color: #f5a623; }
</style>
</head>
<body>

<div class="tape"></div>
<div class="tape tape-bottom"></div>
<div class="stars" id="stars"></div>

<div class="container">
  <div class="icon-wrap">
    <svg class="gear" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path fill="#f5a623" d="M43.3 5 L56.7 5 L59 17.5 C62.6 18.6 66 20.2 69 22.3 L81 16.5 L90.5 26 L84.7 38 C86.8 41 88.4 44.4 89.5 48 L102 50.3 L102 63.7 L89.5 66 C88.4 69.6 86.8 73 84.7 76 L90.5 88 L81 97.5 L69 91.7 C66 93.8 62.6 95.4 59 96.5 L56.7 109 L43.3 109 L41 96.5 C37.4 95.4 34 93.8 31 91.7 L19 97.5 L9.5 88 L15.3 76 C13.2 73 11.6 69.6 10.5 66 L-2 63.7 L-2 50.3 L10.5 48 C11.6 44.4 13.2 41 15.3 38 L9.5 26 L19 16.5 L31 22.3 C34 20.2 37.4 18.6 41 17.5 Z" transform="scale(0.9) translate(3,3)"/>
      <circle cx="50" cy="50" r="18" fill="#0f0f0f"/>
    </svg>
    <svg class="gear-small" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path fill="#e8401c" d="M43.3 5 L56.7 5 L59 17.5 C62.6 18.6 66 20.2 69 22.3 L81 16.5 L90.5 26 L84.7 38 C86.8 41 88.4 44.4 89.5 48 L102 50.3 L102 63.7 L89.5 66 C88.4 69.6 86.8 73 84.7 76 L90.5 88 L81 97.5 L69 91.7 C66 93.8 62.6 95.4 59 96.5 L56.7 109 L43.3 109 L41 96.5 C37.4 95.4 34 93.8 31 91.7 L19 97.5 L9.5 88 L15.3 76 C13.2 73 11.6 69.6 10.5 66 L-2 63.7 L-2 50.3 L10.5 48 C11.6 44.4 13.2 41 15.3 38 L9.5 26 L19 16.5 L31 22.3 C34 20.2 37.4 18.6 41 17.5 Z" transform="scale(0.9) translate(3,3)"/>
      <circle cx="50" cy="50" r="18" fill="#0f0f0f"/>
    </svg>
  </div>

  <div class="badge">🚧 Under Construction</div>
  <h1>We're building<br>something <span>awesome</span></h1>
  <p>This Page is currently under construction. We're working hard to bring you a great experience. Stay tuned!</p>

  <div class="progress-label">
    <span>Progress</span>
    <span id="pct">0%</span>
  </div>
  <div class="progress-bar-wrap">
    <div class="progress-bar-fill" id="bar"></div>
  </div>

  
  <p class="success-msg" id="success">Thanks! We'll notify you when we launch.</p>

 
</div>

<script>
  const starsEl = document.getElementById('stars');
  for (let i = 0; i < 80; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    s.style.left = Math.random() * 100 + '%';
    s.style.top = Math.random() * 100 + '%';
    s.style.animationDelay = Math.random() * 3 + 's';
    s.style.animationDuration = (2 + Math.random() * 3) + 's';
    starsEl.appendChild(s);
  }

  setTimeout(() => {
    document.getElementById('bar').style.width = '72%';
    document.getElementById('pct').textContent = '72%';
  }, 400);

  function handleNotify() {
    const val = document.getElementById('email-input').value;
    if (!val || !val.includes('@')) {
      document.getElementById('email-input').style.borderColor = '#e8401c';
      return;
    }
    document.querySelector('.notify-form').style.display = 'none';
    document.getElementById('success').style.display = 'block';
  }
</script>
</body>
</html>