<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE CUSTOMER SERVICE - Your Voice Matters</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-ultralight-58646b19bf205.otf') format('opentype'); font-weight: 100; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-thin-58646e9b26e8b.otf') format('opentype'); font-weight: 200; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); font-weight: 400; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); font-weight: 600; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); font-weight: 700; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); font-weight: 800; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); font-weight: 900; }

        :root {
            --primary-orange: #F28018;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --border-gray: #e0e0e0;
            --bg-light: #f9f9f9;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #555555;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --white: #ffffff;
            --shadow-soft: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
            --shadow-active: 0 12px 40px rgba(242,128,24,0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--dark-gray);
            overflow-x: hidden;
            background-color: var(--white);
        }

        /* ============ HEADER ============ */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            transition: all 0.3s ease;
        }
        .header.scrolled { background: rgba(255,255,255,0.98); box-shadow: var(--shadow-soft); }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%;
            max-width: 1600px;
            margin: 0 auto;
        }

        .brand {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
            z-index: 1001;
        }
        .brand-logo { max-width: 160px; height: auto; }
        .brand-tagline {
            font-size: 0.78rem;
            color: #F28018;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        /* Desktop nav */
        .nav-menu { display: flex; align-items: center; gap: 2.5rem; }
        .nav-links { display: flex; list-style: none; gap: 2rem; }
        .nav-links a {
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover { color: #F28018; background: var(--orange-light); transform: translateY(-2px); }

        .nav-cta {
            background: #F28018;
            color: white !important;
            padding: 0.8rem 2rem !important;
            border-radius: 25px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
        }
        .nav-cta:hover { background: #d4700f !important; transform: translateY(-2px); box-shadow: var(--shadow-active); }

        /* ============ HAMBURGER ============ */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 0.5rem;
            z-index: 1101;
            background: none;
            border: none;
            gap: 5px;
        }
        .mobile-toggle span {
            display: block;
            width: 26px;
            height: 2.5px;
            background: var(--dark-gray);
            border-radius: 3px;
            transition: all 0.35s cubic-bezier(0.23, 1, 0.32, 1);
            transform-origin: center;
        }
        /* X state */
        .mobile-toggle.open span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); background: #F28018; }
        .mobile-toggle.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .mobile-toggle.open span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); background: #F28018; }

        /* ============ MOBILE DRAWER ============ */
        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            z-index: 1098;
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .mobile-overlay.active { display: block; opacity: 1; }

        .mobile-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: min(340px, 88vw);
            height: 100dvh;
            background: white;
            z-index: 1099;
            transform: translateX(110%);
            transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            box-shadow: -8px 0 40px rgba(0,0,0,0.15);
        }
        .mobile-drawer.open { transform: translateX(0); }

        .drawer-header {
            padding: 1.4rem 1.8rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .drawer-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }
        .drawer-logo { max-width: 100px; height: auto; }
        .drawer-tagline { font-size: 0.68rem; color: #F28018; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }

        .drawer-close {
            width: 36px; height: 36px;
            border: none;
            background: var(--light-gray);
            border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
            color: var(--dark-gray);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .drawer-close:hover { background: #F28018; color: white; }

        .drawer-body { padding: 1.5rem 1.8rem; flex: 1; display: flex; flex-direction: column; gap: 0.4rem; }

        /* Section label */
        .drawer-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #aaa;
            margin: 1rem 0 0.5rem;
        }
        .drawer-label:first-child { margin-top: 0; }

        .drawer-nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            text-decoration: none;
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 1rem;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            transition: all 0.25s ease;
            border: 1.5px solid transparent;
        }
        .drawer-nav-link:hover {
            background: var(--orange-light);
            color: #F28018;
            border-color: rgba(242,128,24,0.2);
            transform: translateX(4px);
        }
        .drawer-nav-link .link-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: var(--light-gray);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
            color: var(--text-gray);
            flex-shrink: 0;
            transition: all 0.25s ease;
        }
        .drawer-nav-link:hover .link-icon { background: #F28018; color: white; }

        .drawer-divider { height: 1px; background: var(--border-gray); margin: 0.6rem 0; }

        /* Auth buttons in drawer */
        .drawer-auth { padding: 1.5rem 1.8rem; border-top: 1px solid var(--border-gray); display: flex; flex-direction: column; gap: 0.8rem; }

        .drawer-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1rem 1.5rem;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }
        .drawer-btn-login {
            background: var(--orange-light);
            color: #F28018;
            border-color: rgba(242,128,24,0.25);
        }
        .drawer-btn-login:hover { background: #F28018; color: white; border-color: #F28018; transform: translateY(-2px); box-shadow: var(--shadow-active); }

        .drawer-btn-register {
            background: #F28018;
            color: white;
        }
        .drawer-btn-register:hover { background: #d4700f; transform: translateY(-2px); box-shadow: var(--shadow-active); }

        /* Quick contact strip */
        .drawer-contact {
            padding: 1rem 1.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.82rem;
            color: var(--text-gray);
            font-weight: 500;
        }
        .drawer-contact i { color: #F28018; }

        /* ============ HERO ============ */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-light) 0%, rgba(242,128,24,0.05) 100%);
            display: flex;
            align-items: center;
            position: relative;
            z-index: 1;
            overflow: hidden;
            padding-top: 100px;
        }
        .hero::before {
            content: '';
            position: absolute; top: 0; right: 0; width: 60%; height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="rg1"><stop offset="0%" style="stop-color:rgba(242,128,24,0.1)"/><stop offset="100%" style="stop-color:transparent"/></radialGradient></defs><circle cx="80" cy="20" r="30" fill="url(%23rg1)"/><circle cx="20" cy="80" r="25" fill="rgba(39,174,96,0.05)"/></svg>') no-repeat;
            background-size: cover; opacity: 0.8; pointer-events: none;
        }
        .hero-container {
            max-width: 1600px; margin: 0 auto; padding: 0 5%;
            display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: center;
            position: relative; z-index: 2;
        }
        .hero-content { animation: slideUp 1s ease-out; }
        .hero-badge {
            background: var(--orange-light); color: #F28018;
            padding: 0.6rem 1.5rem; border-radius: 50px;
            font-size: 0.9rem; font-weight: 700; display: inline-block;
            margin-bottom: 2rem; border: 1px solid rgba(242,128,24,0.2);
        }
        .hero-title {
            font-size: clamp(2.8rem, 5vw, 4.5rem);
            font-weight: 800; color: var(--dark-gray);
            margin-bottom: 1.5rem; line-height: 1.1; letter-spacing: -0.02em;
        }
        .hero-title .highlight {
            background: linear-gradient(135deg, #F28018 0%, #F28018 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .hero-subtitle {
            font-size: 1.3rem; color: var(--text-gray);
            margin-bottom: 2rem; line-height: 1.7; max-width: 500px; font-weight: 400;
        }
        .hero-pills { display: flex; gap: 0.8rem; flex-wrap: wrap; margin-bottom: 2.5rem; }
        .hero-pill {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: white; border: 1.5px solid var(--border-gray);
            color: var(--dark-gray); padding: 0.5rem 1.2rem;
            border-radius: 50px; font-size: 0.92rem; font-weight: 600; transition: all 0.3s ease;
        }
        .hero-pill i { color: #F28018; }
        .hero-pill:hover { border-color: #F28018; background: var(--orange-light); transform: translateY(-2px); }
        .hero-actions { display: flex; gap: 1.5rem; flex-wrap: wrap; }

        .btn {
            padding: 1rem 2.5rem; border: none; border-radius: 50px;
            font-size: 1.1rem; font-weight: 700;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            text-decoration: none; transition: all 0.3s ease; cursor: pointer;
            display: inline-flex; align-items: center; gap: 0.8rem;
            position: relative; overflow: hidden;
        }
        .btn::before {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn:hover::before { left: 100%; }
        .btn-primary { background: #F28018; color: white; box-shadow: var(--shadow-soft); }
        .btn-primary:hover { background: #d4700f; transform: translateY(-3px); box-shadow: var(--shadow-active); }
        .btn-outline { background: transparent; color: #F28018; border: 2px solid #F28018; }
        .btn-outline:hover { background: #F28018; color: white; transform: translateY(-3px); box-shadow: var(--shadow-hover); }

        /* Dashboard preview */
        .hero-visual {
            display: flex; justify-content: center; align-items: center;
            position: relative; animation: slideUp 1s ease-out 0.2s both;
        }
        .dashboard-preview {
            background: white; border-radius: 20px; padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15); max-width: 450px; width: 100%;
            transform: perspective(1000px) rotateY(-10deg) rotateX(5deg); transition: transform 0.3s ease;
        }
        .dashboard-preview:hover { transform: perspective(1000px) rotateY(0) rotateX(0) scale(1.02); }
        .dashboard-header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-gray);
        }
        .dashboard-title { font-size: 1.2rem; font-weight: 700; color: var(--dark-gray); flex: 1; }
        .status-indicator { width: 12px; height: 12px; background: var(--success); border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(39,174,96,0.7); }
            70% { box-shadow: 0 0 0 10px rgba(39,174,96,0); }
            100% { box-shadow: 0 0 0 0 rgba(39,174,96,0); }
        }
        .dashboard-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 0.8rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--bg-light); padding: 1rem; border-radius: 12px; text-align: center; border: 1px solid var(--border-gray); }
        .stat-number { font-size: 1.6rem; font-weight: 800; color: #F28018; margin-bottom: 0.3rem; }
        .stat-number.blue { color: #3498db; }
        .stat-number.green { color: var(--success); }
        .stat-label { font-size: 0.75rem; color: var(--text-gray); font-weight: 600; }
        .recent-items { display: flex; flex-direction: column; gap: 0.8rem; }
        .item-row {
            display: flex; align-items: center; gap: 0.8rem;
            padding: 0.8rem; background: var(--bg-light);
            border-radius: 10px; border: 1px solid var(--border-gray); transition: all 0.3s ease;
        }
        .item-row:hover { background: white; box-shadow: var(--shadow-soft); }
        .item-icon {
            width: 36px; height: 36px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .icon-orange { background: var(--orange-light); color: #F28018; }
        .icon-blue { background: rgba(52,152,219,0.1); color: #3498db; }
        .icon-green { background: var(--success-light); color: var(--success); }
        .item-details { flex: 1; }
        .item-title { font-weight: 600; font-size: 0.88rem; color: var(--dark-gray); margin-bottom: 0.15rem; }
        .item-meta { font-size: 0.75rem; color: var(--text-gray); }
        .item-badge { padding: 0.25rem 0.7rem; border-radius: 12px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
        .badge-pending { background: var(--warning-light); color: var(--warning); }
        .badge-resolved { background: var(--success-light); color: var(--success); }
        .badge-processing { background: rgba(52,152,219,0.1); color: #3498db; }

        /* ============ SERVICES ============ */
        .services { padding: 5rem 0; background: white; position: relative; z-index: 2; }
        .services-container { max-width: 1600px; margin: 0 auto; padding: 0 5%; }
        .services-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 2rem; margin-top: 4rem; }
        .service-card {
            background: white; border-radius: 20px; border: 1px solid var(--border-gray);
            padding: 2.5rem; text-align: center; transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .service-card::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 4px;
            transform: scaleX(0); transition: transform 0.3s ease;
        }
        .service-card.claim-card::after    { background: #F28018; }
        .service-card.order-card::after    { background: linear-gradient(135deg, #3498db, #2980b9); }
        .service-card.feedback-card::after { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .service-card:hover::after { transform: scaleX(1); }
        .service-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); }
        .service-icon-wrap {
            width: 80px; height: 80px; border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 1.5rem; transition: all 0.3s ease;
        }
        .claim-card .service-icon-wrap    { background: var(--orange-light); color: #F28018; }
        .order-card .service-icon-wrap    { background: rgba(52,152,219,0.1); color: #3498db; }
        .feedback-card .service-icon-wrap { background: var(--success-light); color: var(--success); }
        .service-card:hover .service-icon-wrap { transform: scale(1.1) rotate(5deg); }
        .claim-card:hover .service-icon-wrap    { background: #F28018; color: white; }
        .order-card:hover .service-icon-wrap    { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
        .feedback-card:hover .service-icon-wrap { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; }
        .service-title { font-size: 1.4rem; font-weight: 700; color: var(--dark-gray); margin-bottom: 1rem; }
        .service-description { color: var(--text-gray); line-height: 1.7; font-size: 0.97rem; margin-bottom: 1.5rem; font-weight: 400; }
        .service-steps { list-style: none; text-align: left; display: flex; flex-direction: column; gap: 0.6rem; }
        .service-steps li { display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; color: var(--text-gray); }
        .service-steps li i { font-size: 0.8rem; flex-shrink: 0; }
        .claim-card .service-steps li i    { color: #F28018; }
        .order-card .service-steps li i    { color: #3498db; }
        .feedback-card .service-steps li i { color: var(--success); }

        /* ============ FEATURES ============ */
        .features { padding: 6rem 0; background: var(--bg-light); position: relative; z-index: 2; }
        .features-container { max-width: 1600px; margin: 0 auto; padding: 0 5%; }
        .section-header { text-align: center; margin-bottom: 4rem; }
        .section-badge {
            background: var(--success-light); color: var(--success);
            padding: 0.5rem 1.2rem; border-radius: 50px;
            font-size: 0.9rem; font-weight: 700; display: inline-block; margin-bottom: 1.5rem;
        }
        .section-badge.orange { background: var(--orange-light); color: #F28018; }
        .section-title { font-size: clamp(2.5rem, 4vw, 3.5rem); font-weight: 800; color: var(--dark-gray); margin-bottom: 1.5rem; letter-spacing: -0.02em; }
        .section-description { font-size: 1.2rem; color: var(--text-gray); max-width: 600px; margin: 0 auto; line-height: 1.7; font-weight: 400; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem; margin-top: 4rem; }
        .feature-card {
            background: white; padding: 3rem; border-radius: 20px;
            border: 1px solid var(--border-gray); transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: #F28018; transform: scaleX(0); transition: transform 0.3s ease;
        }
        .feature-card:hover::before { transform: scaleX(1); }
        .feature-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); border-color: #F28018; }
        .feature-icon {
            width: 70px; height: 70px; background: var(--orange-light); border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: #F28018; margin-bottom: 2rem; transition: all 0.3s ease;
        }
        .feature-card:hover .feature-icon { background: #F28018; color: white; transform: scale(1.1) rotate(5deg); }
        .feature-title { font-size: 1.5rem; font-weight: 700; color: var(--dark-gray); margin-bottom: 1rem; }
        .feature-description { color: var(--text-gray); line-height: 1.7; font-size: 1rem; font-weight: 400; }

        /* ============ PROCESS ============ */
        .process { background: white; padding: 6rem 0; position: relative; z-index: 2; }
        .process-container { max-width: 1600px; margin: 0 auto; padding: 0 5%; }
        .process-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; margin-top: 4rem; }
        .process-step { text-align: center; position: relative; }
        .process-step::after {
            content: ''; position: absolute; top: 35px; right: -1.5rem;
            width: 3rem; height: 2px; background: #F28018; z-index: 1;
        }
        .process-step:last-child::after { display: none; }
        .process-number {
            width: 70px; height: 70px; background: #F28018; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 800; color: white; margin: 0 auto 2rem;
            position: relative; z-index: 2; box-shadow: var(--shadow-soft);
        }
        .process-title { font-size: 1.3rem; font-weight: 700; color: var(--dark-gray); margin-bottom: 1rem; }
        .process-description { color: var(--text-gray); line-height: 1.6; font-weight: 400; }

        /* ============ CTA ============ */
        .cta {
            background: #F28018; padding: 5rem 0; color: white;
            text-align: center; position: relative; overflow: hidden; z-index: 2;
        }
        .cta::before {
            content: ''; position: absolute; inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="20" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="20" cy="80" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="90" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: floatBg 20s linear infinite; pointer-events: none;
        }
        @keyframes floatBg {
            from { transform: translate(0,0) rotate(0); }
            to   { transform: translate(-100px,-100px) rotate(360deg); }
        }
        .cta-content { position: relative; z-index: 2; max-width: 800px; margin: 0 auto; padding: 0 2rem; }
        .cta-title { font-size: clamp(2.5rem, 4vw, 3.5rem); font-weight: 800; margin-bottom: 1.5rem; letter-spacing: -0.02em; }
        .cta-description { font-size: 1.3rem; margin-bottom: 3rem; opacity: 0.9; line-height: 1.7; font-weight: 400; }
        .cta-actions { display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; }
        .btn-white { background: white; color: #F28018; border: 2px solid white; }
        .btn-white:hover { background: transparent; color: white; border-color: white; }

        /* ============ FOOTER ============ */
        .footer { background: var(--dark-gray); color: white; padding: 3rem 0 2rem; position: relative; z-index: 2; }
        .footer-container { max-width: 1600px; margin: 0 auto; padding: 0 5%; }
        .footer-content { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; margin-bottom: 2rem; }
        .footer-brand { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .footer-brand-logo { max-width: 150px; height: auto; }
        .footer-brand-text { font-size: 1rem; font-weight: 700; }
        .footer-description { color: rgba(255,255,255,0.7); line-height: 1.6; margin-bottom: 2rem; font-weight: 400; }
        .footer-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 0.8rem; }
        .footer-links a { color: rgba(255,255,255,0.7); text-decoration: none; transition: color 0.3s ease; font-weight: 400; }
        .footer-links a:hover { color: #F28018; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem; text-align: center; color: rgba(255,255,255,0.7); font-weight: 400; }

        /* ============ ANIMATIONS ============ */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .scroll-reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s ease; }
        .scroll-reveal.revealed { opacity: 1; transform: translateY(0); }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .hero-container { grid-template-columns: 1fr; text-align: center; }
            .hero-pills { justify-content: center; }
            .hero-actions { justify-content: center; }
            .dashboard-preview { transform: none; max-width: 420px; margin: 0 auto; }
            .services-grid { grid-template-columns: 1fr; }
            .process-step::after { display: none; }
            .footer-content { grid-template-columns: 1fr 1fr; gap: 2rem; }
        }

        @media (max-width: 768px) {
            .nav-menu { display: none; } /* Hide desktop nav on mobile */
            .mobile-toggle { display: flex; }
            .features-grid { grid-template-columns: 1fr; }
            .process-steps { grid-template-columns: 1fr; }
            .cta-actions { flex-direction: column; align-items: center; }
            .footer-content { grid-template-columns: 1fr; }
            .brand-logo { max-width: 110px; }
            .brand-tagline { font-size: 0.7rem; }
            .dashboard-stats { grid-template-columns: repeat(3,1fr); }
            .hero-subtitle { font-size: 1.1rem; }
        }

        @media (max-width: 480px) {
            .navbar { padding: 1rem 4%; }
            .hero { padding-top: 80px; }
            .hero-title { font-size: 2.4rem; }
            .btn { padding: 0.9rem 1.8rem; font-size: 1rem; }
        }
    </style>
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="header" id="header">
        <nav class="navbar">
            <a href="#" class="brand">
                <img src="/atire.png" alt="ATIRE Logo" class="brand-logo">
                <div>
                    <div class="brand-tagline">CUSTOMER SERVICE</div>
                </div>
            </a>

            <!-- Desktop nav -->
            <div class="nav-menu">
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#process">Process</a></li>
                    <li><a href="user/index.php">Login</a></li>
                </ul>
                <a href="user/registration.php" class="nav-cta">Get Started</a>
            </div>

            <!-- Hamburger button (mobile) -->
            <button class="mobile-toggle" id="mobileToggle" aria-label="Open menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>
    </header>

    <!-- ===== MOBILE OVERLAY ===== -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- ===== MOBILE DRAWER ===== -->
    <aside class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Navigation menu">
        <!-- Drawer header -->
        <div class="drawer-header">
            <a href="#" class="drawer-brand" onclick="closeDrawer()">
                <img src="/atire.png" alt="ATIRE Logo" class="drawer-logo">
                <span class="drawer-tagline">CUSTOMER SERVICE</span>
            </a>
            <button class="drawer-close" onclick="closeDrawer()" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Drawer nav links -->
        <div class="drawer-body">
            <p class="drawer-label">Navigate</p>

            <a href="#home" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-home"></i></span>
                Home
            </a>
            <a href="#services" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-layer-group"></i></span>
                Services
            </a>
            <a href="#features" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-star"></i></span>
                Features
            </a>
            <a href="#process" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-cogs"></i></span>
                How It Works
            </a>

            <div class="drawer-divider"></div>

            <p class="drawer-label">Quick Access</p>

            <a href="user/index.php#claims" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-file-alt"></i></span>
                Submit a Claim
            </a>
            <a href="user/index.php#orders" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-shopping-cart"></i></span>
                Place an Order
            </a>
            <a href="user/index.php#feedback" class="drawer-nav-link" onclick="closeDrawer()">
                <span class="link-icon"><i class="fas fa-comment-alt"></i></span>
                Give Feedback
            </a>
        </div>

        <!-- Drawer auth buttons -->
        <div class="drawer-auth">
            <a href="user/index.php" class="drawer-btn drawer-btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Login to Your Account
            </a>
            <a href="user/registration.php" class="drawer-btn drawer-btn-register">
                <i class="fas fa-user-plus"></i>
                Create Account — Free
            </a>
        </div>

        <!-- Contact strip -->
        <div class="drawer-contact">
            <i class="fas fa-headset"></i>
            <span>Need help? <strong>24/7 Support Available</strong></span>
        </div>
    </aside>

    <!-- ===== HERO ===== -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Your Voice <span class="highlight">Matters</span> to Us
                </h1>
                <p class="hero-subtitle">
                    Submit claims, place orders, and share your feedback all in one place.
                    Experience exceptional customer service powered by our advanced management system.
                </p>

                <div class="hero-pills">
                    <span class="hero-pill"><i class="fas fa-file-alt"></i> Claim Management</span>
                    <span class="hero-pill"><i class="fas fa-shopping-cart"></i> Place an Order</span>
                    <span class="hero-pill"><i class="fas fa-star"></i> Submit Feedback</span>
                </div>

                <div class="hero-actions">
                    <a href="user/registration.php" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        Get Started Now
                    </a>
                    <a href="#services" class="btn btn-outline">
                        <i class="fas fa-play"></i>
                        How It Works
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="dashboard-preview">
                    <div class="dashboard-header">
                        <div class="dashboard-title">My Dashboard</div>
                        <div class="status-indicator"></div>
                    </div>
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-number">12</div>
                            <div class="stat-label">Claims</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number blue">8</div>
                            <div class="stat-label">Orders</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number green">5</div>
                            <div class="stat-label">Feedbacks</div>
                        </div>
                    </div>
                    <div class="recent-items">
                        <div class="item-row">
                            <div class="item-icon icon-orange"><i class="fas fa-file-alt"></i></div>
                            <div class="item-details">
                                <div class="item-title">Claim #1042 — Wrong Item</div>
                                <div class="item-meta">Submitted 2 hours ago</div>
                            </div>
                            <span class="item-badge badge-pending">Pending</span>
                        </div>
                        <div class="item-row">
                            <div class="item-icon icon-blue"><i class="fas fa-shopping-cart"></i></div>
                            <div class="item-details">
                                <div class="item-title">Order #ORD-2087</div>
                                <div class="item-meta">Placed yesterday</div>
                            </div>
                            <span class="item-badge badge-processing">Processing</span>
                        </div>
                        <div class="item-row">
                            <div class="item-icon icon-green"><i class="fas fa-star"></i></div>
                            <div class="item-details">
                                <div class="item-title">Feedback — Delivery Service</div>
                                <div class="item-meta">Reviewed 1 day ago</div>
                            </div>
                            <span class="item-badge badge-resolved">Submitted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== SERVICES ===== -->
    <section class="services" id="services">
        <div class="services-container">
            <div class="section-header scroll-reveal">
                <div class="section-badge orange"><i class="fas fa-layer-group"></i> Our Services</div>
                <h2 class="section-title">Everything You Need, In One Place</h2>
                <p class="section-description">
                    From submitting claims to placing orders and sharing your feedback,
                    our platform handles it all — fast, simple, and reliable.
                </p>
            </div>
            <div class="services-grid">
                <div class="service-card claim-card scroll-reveal">
                    <div class="service-icon-wrap"><i class="fas fa-file-alt"></i></div>
                    <h3 class="service-title">Claim Management</h3>
                    <p class="service-description">Experienced a problem with your product or service? Submit a claim in minutes. Our team reviews every case and keeps you updated every step of the way.</p>
                    <ul class="service-steps">
                        <li><i class="fas fa-check-circle"></i> Fill in your claim details quickly</li>
                        <li><i class="fas fa-check-circle"></i> Attach supporting files or images</li>
                        <li><i class="fas fa-check-circle"></i> Track your claim status in real-time</li>
                        <li><i class="fas fa-check-circle"></i> Receive updates until resolved</li>
                    </ul>
                </div>
                <div class="service-card order-card scroll-reveal">
                    <div class="service-icon-wrap"><i class="fas fa-shopping-cart"></i></div>
                    <h3 class="service-title">Order Management</h3>
                    <p class="service-description">Browse and place customer orders directly through our platform. Manage order details, quantities, and delivery preferences all in one streamlined flow.</p>
                    <ul class="service-steps">
                        <li><i class="fas fa-check-circle"></i> Select products and quantities</li>
                        <li><i class="fas fa-check-circle"></i> Confirm order information</li>
                        <li><i class="fas fa-check-circle"></i> Receive order confirmation instantly</li>
                        <li><i class="fas fa-check-circle"></i> Monitor your order progress</li>
                    </ul>
                </div>
                <div class="service-card feedback-card scroll-reveal">
                    <div class="service-icon-wrap"><i class="fas fa-star"></i></div>
                    <h3 class="service-title">Feedback Management</h3>
                    <p class="service-description">Your opinion drives our improvement. Share your experience, rate our service, and help us deliver even better customer experiences going forward.</p>
                    <ul class="service-steps">
                        <li><i class="fas fa-check-circle"></i> Rate your experience easily</li>
                        <li><i class="fas fa-check-circle"></i> Leave detailed comments</li>
                        <li><i class="fas fa-check-circle"></i> Tag the service or product</li>
                        <li><i class="fas fa-check-circle"></i> Feedback reviewed by our team</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-header scroll-reveal">
                <div class="section-badge"><i class="fas fa-star"></i> Why Choose Us</div>
                <h2 class="section-title">Exceptional Service Features</h2>
                <p class="section-description">Discover the powerful features that make our customer service platform the preferred choice for businesses and customers worldwide.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">Round-the-clock customer support ensures your claims, orders, and feedback are addressed at any time, providing you peace of mind and quick resolutions.</p>
                </div>
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="feature-title">Real-Time Tracking</h3>
                    <p class="feature-description">Monitor your claim or order status in real-time with detailed progress updates, notifications, and estimated resolution or delivery times.</p>
                </div>
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="feature-title">Data Security</h3>
                    <p class="feature-description">Enterprise-grade security protocols ensure your personal information, order data, and claim details remain confidential and protected at all times.</p>
                </div>
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="feature-title">Mobile Optimized</h3>
                    <p class="feature-description">Access our platform seamlessly across all devices. Submit claims, track orders, or leave feedback from your phone, tablet, or desktop anytime, anywhere.</p>
                </div>
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <h3 class="feature-title">Expert Team</h3>
                    <p class="feature-description">Our dedicated team reviews every claim, processes every order, and reads every piece of feedback ensuring a professional and personal touch in every interaction.</p>
                </div>
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="feature-title">Feedback-Driven</h3>
                    <p class="feature-description">Customer feedback is at the heart of everything we do. Our platform collects and analyzes your input to continuously improve our products and service quality.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== PROCESS ===== -->
    <section class="process" id="process">
        <div class="process-container">
            <div class="section-header scroll-reveal">
                <div class="section-badge orange"><i class="fas fa-cogs"></i> Simple Process</div>
                <h2 class="section-title">How It Works</h2>
                <p class="section-description">Whether you're filing a claim, placing an order, or sharing feedback — our streamlined process gets things done in just a few steps.</p>
            </div>
            <div class="process-steps">
                <div class="process-step scroll-reveal">
                    <div class="process-number">1</div>
                    <h3 class="process-title">Create Your Account</h3>
                    <p class="process-description">Register in seconds to gain access to the full platform — claims, orders, and feedback all under one roof.</p>
                </div>
                <div class="process-step scroll-reveal">
                    <div class="process-number">2</div>
                    <h3 class="process-title">Submit Your Request</h3>
                    <p class="process-description">File a claim, place an order, or submit your feedback using our guided smart forms designed to be fast and intuitive.</p>
                </div>
                <div class="process-step scroll-reveal">
                    <div class="process-number">3</div>
                    <h3 class="process-title">Expert Review</h3>
                    <p class="process-description">Our team immediately picks up your request. Claims are assigned to specialists, orders are processed, and feedback is reviewed by management.</p>
                </div>
                <div class="process-step scroll-reveal">
                    <div class="process-number">4</div>
                    <h3 class="process-title">Resolution & Follow-up</h3>
                    <p class="process-description">Receive updates, resolutions, and confirmations at every stage — with follow-up support to ensure your complete satisfaction.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="cta">
        <div class="cta-content scroll-reveal">
            <h2 class="cta-title">Ready to Experience Excellence?</h2>
            <p class="cta-description">Join thousands of satisfied customers who trust ATIRE Customer Service for their claims, orders, and feedback. Start your journey today.</p>
            <div class="cta-actions">
                <a href="user/registration.php" class="btn btn-white">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </a>
                <a href="user/index.php" class="btn btn-outline" style="color:white; border-color:white;">
                    <i class="fas fa-sign-in-alt"></i>
                    Login Now
                </a>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <img src="/atire.png" alt="ATIRE Logo" class="footer-brand-logo">
                        <div class="footer-brand-text">CUSTOMER SERVICE</div>
                    </div>
                    <p class="footer-description">Dedicated to providing exceptional customer service experiences through innovative technology and professional support — from claims and orders to feedback management.</p>
                    <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                        <a href="#" style="color:rgba(255,255,255,0.7); font-size:1.2rem; transition:color 0.3s;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color:rgba(255,255,255,0.7); font-size:1.2rem; transition:color 0.3s;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color:rgba(255,255,255,0.7); font-size:1.2rem; transition:color 0.3s;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color:rgba(255,255,255,0.7); font-size:1.2rem; transition:color 0.3s;"><i class="fab fa-instagram"></i></a>
                        <a href="https://wa.me/1234567890" style="color:rgba(255,255,255,0.7); font-size:1.2rem; transition:color 0.3s;"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#process">How It Works</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-title">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Live Chat</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Guidelines</a></li>
                        <li><a href="#">Status Page</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4 class="footer-title">Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Disclaimer</a></li>
                        <li><a href="#">Compliance</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 ATIRE Software Development. All Rights Reserved. Designed &amp; Developed with Excellence.</p>
            </div>
        </div>
    </footer>

    <script>
        /* ====== DRAWER LOGIC ====== */
        const toggle   = document.getElementById('mobileToggle');
        const drawer   = document.getElementById('mobileDrawer');
        const overlay  = document.getElementById('mobileOverlay');

        function openDrawer() {
            drawer.classList.add('open');
            overlay.classList.add('active');
            toggle.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            overlay.classList.remove('active');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', () => {
            drawer.classList.contains('open') ? closeDrawer() : openDrawer();
        });

        overlay.addEventListener('click', closeDrawer);

        // Close on Escape key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && drawer.classList.contains('open')) closeDrawer();
        });

        // Close drawer on anchor link click (smooth scroll)
        drawer.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    closeDrawer();
                    setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 350);
                }
            });
        });

        /* ====== HEADER SCROLL ====== */
        window.addEventListener('scroll', () => {
            document.getElementById('header').classList.toggle('scrolled', window.scrollY > 50);
        });

        /* ====== SMOOTH SCROLL (desktop) ====== */
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        /* ====== SCROLL REVEAL ====== */
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('revealed');
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.scroll-reveal').forEach(el => revealObserver.observe(el));

        /* ====== STATS COUNTER ====== */
        function animateStats() {
            document.querySelectorAll('.stat-number').forEach(stat => {
                const final = parseInt(stat.textContent);
                let current = 0;
                const inc = final / 50;
                const timer = setInterval(() => {
                    current += inc;
                    if (current >= final) { stat.textContent = final; clearInterval(timer); }
                    else { stat.textContent = Math.floor(current); }
                }, 50);
            });
        }

        const dashObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { animateStats(); dashObs.unobserve(entry.target); }
            });
        });
        const dashboard = document.querySelector('.dashboard-preview');
        if (dashboard) dashObs.observe(dashboard);

        /* ====== SOCIAL HOVER ====== */
        document.querySelectorAll('footer a[href]').forEach(link => {
            link.addEventListener('mouseenter', function() { this.style.color = '#F28018'; });
            link.addEventListener('mouseleave', function() { this.style.color = 'rgba(255,255,255,0.7)'; });
        });
    </script>
</body>
</html>