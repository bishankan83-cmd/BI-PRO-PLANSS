<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prosper Logo - Blush Pink</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #FFC0CB 0%, #FFB6C1 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Georgia', serif;
        }
        
        .logo-container {
            background: linear-gradient(135deg, #FFD6E0 0%, #FFC0CB 50%, #FFB3C6 100%);
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        svg {
            filter: drop-shadow(0 10px 30px rgba(0,0,0,0.3));
        }
        
        .download-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #FFD700;
            color: #000;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            transition: transform 0.3s;
        }
        
        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }

        .wheat-left, .wheat-right {
            animation: sway 3s ease-in-out infinite;
        }
        
        .wheat-right {
            animation-delay: 1.5s;
        }
        
        @keyframes sway {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(2deg); }
        }
        
        .coin {
            animation: fall 2s ease-in-out infinite;
        }
        
        @keyframes fall {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(5px); }
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <svg width="600" height="600" viewBox="0 0 600 600" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="goldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#FFD700;stop-opacity:1" />
                    <stop offset="50%" style="stop-color:#FFA500;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#FF8C00;stop-opacity:1" />
                </linearGradient>
                
                <linearGradient id="goldGradient2" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#FFED4E;stop-opacity:1" />
                    <stop offset="50%" style="stop-color:#FFD700;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#DAA520;stop-opacity:1" />
                </linearGradient>
                
                <filter id="glow">
                    <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                    <feMerge>
                        <feMergeNode in="coloredBlur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>
            
            <!-- Outer Circle -->
            <circle cx="300" cy="240" r="180" fill="none" stroke="url(#goldGradient)" stroke-width="4" filter="url(#glow)"/>
            <circle cx="300" cy="240" r="170" fill="none" stroke="url(#goldGradient)" stroke-width="2"/>
            
            <!-- Decorative Elements -->
            <circle cx="260" cy="140" r="3" fill="#FFD700"/>
            <circle cx="340" cy="140" r="3" fill="#FFD700"/>
            <circle cx="220" cy="180" r="2" fill="#FFD700"/>
            <circle cx="380" cy="180" r="2" fill="#FFD700"/>
            <circle cx="200" cy="240" r="2.5" fill="#FFD700"/>
            <circle cx="400" cy="240" r="2.5" fill="#FFD700"/>
            
            <!-- Left Wheat Laurel -->
            <g class="wheat-left" transform-origin="240 240">
                <path d="M 150 240 Q 180 200 200 160" fill="none" stroke="url(#goldGradient2)" stroke-width="3"/>
                <ellipse cx="185" cy="190" rx="8" ry="15" fill="url(#goldGradient2)" transform="rotate(-30 185 190)"/>
                <ellipse cx="192" cy="175" rx="7" ry="14" fill="url(#goldGradient2)" transform="rotate(-25 192 175)"/>
                <ellipse cx="197" cy="160" rx="6" ry="12" fill="url(#goldGradient2)" transform="rotate(-20 197 160)"/>
                <ellipse cx="175" cy="200" rx="8" ry="15" fill="url(#goldGradient2)" transform="rotate(-35 175 200)"/>
                <ellipse cx="168" cy="215" rx="7" ry="14" fill="url(#goldGradient2)" transform="rotate(-40 168 215)"/>
            </g>
            
            <!-- Right Wheat Laurel -->
            <g class="wheat-right" transform-origin="360 240">
                <path d="M 450 240 Q 420 200 400 160" fill="none" stroke="url(#goldGradient2)" stroke-width="3"/>
                <ellipse cx="415" cy="190" rx="8" ry="15" fill="url(#goldGradient2)" transform="rotate(30 415 190)"/>
                <ellipse cx="408" cy="175" rx="7" ry="14" fill="url(#goldGradient2)" transform="rotate(25 408 175)"/>
                <ellipse cx="403" cy="160" rx="6" ry="12" fill="url(#goldGradient2)" transform="rotate(20 403 160)"/>
                <ellipse cx="425" cy="200" rx="8" ry="15" fill="url(#goldGradient2)" transform="rotate(35 425 200)"/>
                <ellipse cx="432" cy="215" rx="7" ry="14" fill="url(#goldGradient2)" transform="rotate(40 432 215)"/>
            </g>
            
            <!-- Blindfold -->
            <path d="M 240 210 Q 300 205 360 210" fill="none" stroke="url(#goldGradient)" stroke-width="12" stroke-linecap="round"/>
            
            <!-- Head -->
            <ellipse cx="300" cy="230" rx="45" ry="55" fill="url(#goldGradient2)"/>
            
            <!-- Hair (flowing) -->
            <path d="M 255 200 Q 250 220 245 250 Q 243 280 245 310" fill="none" stroke="url(#goldGradient2)" stroke-width="8"/>
            <path d="M 265 195 Q 260 225 258 260 Q 256 290 258 320" fill="none" stroke="url(#goldGradient2)" stroke-width="6"/>
            <path d="M 345 200 Q 350 220 355 250 Q 357 280 355 310" fill="none" stroke="url(#goldGradient2)" stroke-width="8"/>
            <path d="M 335 195 Q 340 225 342 260 Q 344 290 342 320" fill="none" stroke="url(#goldGradient2)" stroke-width="6"/>
            <path d="M 275 190 Q 270 230 268 280" fill="none" stroke="url(#goldGradient2)" stroke-width="5"/>
            <path d="M 325 190 Q 330 230 332 280" fill="none" stroke="url(#goldGradient2)" stroke-width="5"/>
            
            <!-- Hair top -->
            <path d="M 260 190 Q 280 175 300 170 Q 320 175 340 190" fill="url(#goldGradient2)"/>
            
            <!-- Neck -->
            <rect x="285" y="280" width="30" height="25" fill="url(#goldGradient2)" rx="5"/>
            
            <!-- Torso/Draped Cloth -->
            <path d="M 260 305 Q 280 300 300 300 Q 320 300 340 305 L 350 360 Q 300 370 250 360 Z" fill="url(#goldGradient2)"/>
            <path d="M 270 310 Q 285 307 300 307 Q 315 307 330 310" fill="none" stroke="#DAA520" stroke-width="1.5"/>
            <path d="M 268 325 Q 285 322 300 322 Q 315 322 332 325" fill="none" stroke="#DAA520" stroke-width="1"/>
            
            <!-- Cornucopia -->
            <path d="M 320 340 Q 350 350 380 380 Q 390 400 385 420 Q 380 430 370 425 Q 340 410 320 390 Z" fill="url(#goldGradient)" stroke="#DAA520" stroke-width="2"/>
            <path d="M 325 345 Q 345 352 365 370" fill="none" stroke="#B8860B" stroke-width="1.5"/>
            <path d="M 330 355 Q 350 362 370 380" fill="none" stroke="#B8860B" stroke-width="1.5"/>
            
            <!-- Arm holding cornucopia -->
            <path d="M 310 320 Q 315 330 320 345" fill="url(#goldGradient2)" stroke="#DAA520" stroke-width="8"/>
            
            <!-- Coins falling -->
            <g class="coin">
                <ellipse cx="360" cy="400" rx="10" ry="6" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="350" cy="420" rx="9" ry="5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="370" cy="415" rx="8" ry="5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="340" cy="440" rx="10" ry="6" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="360" cy="445" rx="9" ry="5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="330" cy="460" rx="8" ry="4" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="350" cy="465" rx="10" ry="6" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                <ellipse cx="320" cy="480" rx="9" ry="5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
            </g>
            
            <!-- Text: PROSPER -->
            <text x="300" y="520" font-family="Georgia, serif" font-size="72" font-weight="bold" fill="url(#goldGradient)" text-anchor="middle" letter-spacing="8" filter="url(#glow)">PROSPER</text>
            
            <!-- Text: WEAR YOUR WORTH -->
            <text x="300" y="555" font-family="Georgia, serif" font-size="20" font-weight="normal" fill="url(#goldGradient2)" text-anchor="middle" letter-spacing="6">WEAR YOUR WORTH</text>
            
            <!-- Underline -->
            <line x1="200" y1="530" x2="400" y2="530" stroke="url(#goldGradient)" stroke-width="2"/>
        </svg>
    </div>
    
    <a href="#" class="download-btn" onclick="downloadLogo(event)">⬇ Download Logo</a>
    
    <script>
        function downloadLogo(e) {
            e.preventDefault();
            
            // Create a canvas
            const canvas = document.createElement('canvas');
            canvas.width = 1200;
            canvas.height = 1200;
            const ctx = canvas.getContext('2d');
            
            // Fill with blush pink gradient background
            const gradient = ctx.createLinearGradient(0, 0, 1200, 1200);
            gradient.addColorStop(0, '#FFD6E0');
            gradient.addColorStop(0.5, '#FFC0CB');
            gradient.addColorStop(1, '#FFB3C6');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, 1200, 1200);
            
            // Get SVG data
            const svg = document.querySelector('svg');
            const svgData = new XMLSerializer().serializeToString(svg);
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            
            // Create image from SVG
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 300, 300, 600, 600);
                
                // Download
                canvas.toBlob(function(blob) {
                    const link = document.createElement('a');
                    link.download = 'prosper-logo-blush-pink.png';
                    link.href = URL.createObjectURL(blob);
                    link.click();
                });
                
                URL.revokeObjectURL(url);
            };
            img.src = url;
        }
    </script>
</body>
</html>