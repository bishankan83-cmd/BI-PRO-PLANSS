<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DropMe UML Use Case Diagram</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 95vw;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            font-weight: 400;
        }

        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .download-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .download-btn:active {
            transform: translateY(0);
        }

        .zoom-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .zoom-btn {
            background: #34495e;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .zoom-btn:hover {
            background: #2c3e50;
            transform: translateY(-1px);
        }

        .zoom-level {
            font-weight: 600;
            color: #2c3e50;
            min-width: 60px;
            text-align: center;
        }

        .diagram-container {
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            overflow: auto;
            max-width: 100%;
            max-height: 80vh;
            background: #fafafa;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        }

        .diagram-wrapper {
            transform-origin: top left;
            transition: transform 0.3s ease;
        }

        .status {
            margin-top: 15px;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            opacity: 1;
        }

        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            opacity: 1;
        }

        .info-panel {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .info-panel h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .info-panel ul {
            color: #5a6c7d;
            line-height: 1.6;
        }

        .info-panel li {
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .container {
                padding: 20px;
            }
            
            .controls {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DropMe Mobile Application</h1>
            <p>UML Use Case Diagram</p>
        </div>

        <div class="controls">
            <button class="download-btn" onclick="downloadPNG()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7,10 12,15 17,10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download PNG
            </button>
            
            <div class="zoom-controls">
                <button class="zoom-btn" onclick="zoomOut()">−</button>
                <span class="zoom-level" id="zoomLevel">100%</span>
                <button class="zoom-btn" onclick="zoomIn()">+</button>
                <button class="zoom-btn" onclick="resetZoom()">Reset</button>
            </div>
        </div>

        <div class="diagram-container">
            <div class="diagram-wrapper" id="diagramWrapper">
                <svg viewBox="0 0 1600 1400" xmlns="http://www.w3.org/2000/svg" id="umlDiagram">
                   <!-- System boundary -->
                  <rect x="190" y="70" width="1200" height="1200" fill="#f8f9fa" stroke="#2c3e50" stroke-width="3" rx="15"/>
                  
                    <!-- System title -->
                    <text x="800" y="135" text-anchor="middle" font-size="12" font-weight="bold" fill="#000000">«System»</text>
                    <text x="800" y="155" text-anchor="middle" font-size="16" font-weight="bold" fill="#000000">DropMe Mobile Application System</text>

                    <!-- Actors -->
                    <!-- Passenger (left side) -->  
                    <g id="passenger">
                        <circle cx="100" cy="300" r="15" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                        <line x1="100" y1="315" x2="100" y2="360" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="335" x2="80" y2="355" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="335" x2="120" y2="355" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="360" x2="80" y2="390" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="360" x2="120" y2="390" stroke="#000000" stroke-width="3"/>
                        <text x="100" y="415" text-anchor="middle" font-size="14" font-weight="bold" fill="#000000">Passenger</text>
                        <text x="100" y="430" text-anchor="middle" font-size="10" fill="#000000">(App User)</text>
                    </g>

                    <!-- Driver (left side, lower) -->
                    <g id="driver">
                        <circle cx="100" cy="1000" r="15" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                        <line x1="100" y1="1015" x2="100" y2="1060" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="1035" x2="80" y2="1055" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="1035" x2="120" y2="1055" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="1060" x2="80" y2="1090" stroke="#000000" stroke-width="3"/>
                        <line x1="100" y1="1060" x2="120" y2="1090" stroke="#000000" stroke-width="3"/>
                        <text x="100" y="1115" text-anchor="middle" font-size="14" font-weight="bold" fill="#000000">Driver</text>
                        <text x="100" y="1130" text-anchor="middle" font-size="10" fill="#000000">(Fleet Member)</text>
                    </g>

                    <!-- Customer Agent (right side) -->
                    <g id="agent">
                        <circle cx="1500" cy="500" r="15" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                        <line x1="1500" y1="515" x2="1500" y2="560" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="535" x2="1480" y2="555" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="535" x2="1520" y2="555" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="560" x2="1480" y2="590" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="560" x2="1520" y2="590" stroke="#000000" stroke-width="3"/>
                        <text x="1500" y="615" text-anchor="middle" font-size="14" font-weight="bold" fill="#000000">Customer Agent</text>
                        <text x="1500" y="630" text-anchor="middle" font-size="10" fill="#000000">(Fleet Manager)</text>
                    </g>

                    <!-- System Actor (top right) -->
                    <g id="system">
                        <circle cx="1500" cy="200" r="15" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                        <line x1="1500" y1="215" x2="1500" y2="260" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="235" x2="1480" y2="255" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="235" x2="1520" y2="255" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="260" x2="1480" y2="290" stroke="#000000" stroke-width="3"/>
                        <line x1="1500" y1="260" x2="1520" y2="290" stroke="#000000" stroke-width="3"/>
                        <text x="1500" y="315" text-anchor="middle" font-size="14" font-weight="bold" fill="#000000">System</text>
                        <text x="1500" y="330" text-anchor="middle" font-size="10" fill="#000000">(Automated)</text>
                    </g>

                    <!-- Use Cases -->
                    <!-- Passenger Use Cases -->
                    <ellipse cx="350" cy="250" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="350" y="245" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Register with</text>
                    <text x="350" y="260" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">DropMe Service</text>

                    <ellipse cx="350" cy="330" rx="75" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="350" y="325" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Enter Pick-up &</text>
                    <text x="350" y="340" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Destination</text>

                    <ellipse cx="350" cy="410" rx="60" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="350" y="420" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Request Ride</text>

                    <ellipse cx="350" cy="490" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="350" y="485" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Receive</text>
                    <text x="350" y="500" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Notification</text>

                    <ellipse cx="350" cy="570" rx="60" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="350" y="580" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Rate Driver</text>

                    <ellipse cx="350" cy="650" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="350" y="660" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Provide Feedback</text>

                    <!-- Driver Use Cases -->
                    <ellipse cx="500" cy="900" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="500" y="895" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Receive Trip</text>
                    <text x="500" y="910" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Assignment</text>

                    <ellipse cx="500" cy="980" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="500" y="990" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Pick Up Passenger</text>

                    <ellipse cx="500" cy="1060" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="500" y="1055" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Drop Off at</text>
                    <text x="500" y="1070" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Destination</text>

                    <ellipse cx="500" cy="1140" rx="60" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="500" y="1150" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Collect Fee</text>

                    <ellipse cx="500" cy="1220" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="500" y="1230" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Update Status</text>

                    <!-- Customer Agent Use Cases -->
                    <ellipse cx="1100" cy="450" rx="80" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="1100" y="445" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Check Taxi</text>
                    <text x="1100" y="460" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Availability</text>

                    <ellipse cx="1100" cy="530" rx="70" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="1100" y="540" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Assign Taxi</text>

                    <ellipse cx="1100" cy="610" rx="80" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="1100" y="605" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Notify User of</text>
                    <text x="1100" y="620" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Unavailability</text>

                    <ellipse cx="1100" cy="690" rx="80" ry="25" fill="#ffffff" stroke="#000000" stroke-width="2"/>
                    <text x="1100" y="685" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Manage Fleet</text>
                    <text x="1100" y="700" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">(100+ Vehicles)</text>

                    <!-- System Use Cases (Automated Functions) -->
                    <ellipse cx="900" cy="200" rx="70" ry="25" fill="#e6f3ff" stroke="#000000" stroke-width="2"/>
                    <text x="900" y="195" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Send</text>
                    <text x="900" y="210" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Notifications</text>

                    <ellipse cx="900" cy="280" rx="70" ry="25" fill="#e6f3ff" stroke="#000000" stroke-width="2"/>
                    <text x="900" y="275" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Track Vehicle</text>
                    <text x="900" y="290" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Locations</text>

                    <ellipse cx="1200" cy="280" rx="70" ry="25" fill="#e6f3ff" stroke="#000000" stroke-width="2"/>
                    <text x="1200" y="275" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Calculate</text>
                    <text x="1200" y="290" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Fares</text>

                    <ellipse cx="900" cy="360" rx="75" ry="25" fill="#e6f3ff" stroke="#000000" stroke-width="2"/>
                    <text x="900" y="355" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Store Feedback</text>
                    <text x="900" y="370" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">& Ratings</text>

                    <ellipse cx="1200" cy="360" rx="70" ry="25" fill="#e6f3ff" stroke="#000000" stroke-width="2"/>
                    <text x="1200" y="355" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Manage User</text>
                    <text x="1200" y="370" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">Accounts</text>

                    <!-- Association lines -->
                    <!-- Passenger associations -->
                    <line x1="160" y1="300" x2="280" y2="250" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="300" x2="275" y2="330" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="300" x2="290" y2="410" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="300" x2="280" y2="490" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="300" x2="290" y2="570" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="300" x2="280" y2="650" stroke="#000000" stroke-width="2"/>

                    <!-- Driver associations -->
                    <line x1="160" y1="1000" x2="430" y2="900" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="1000" x2="430" y2="980" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="1000" x2="430" y2="1060" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="1000" x2="440" y2="1140" stroke="#000000" stroke-width="2"/>
                    <line x1="160" y1="1000" x2="430" y2="1220" stroke="#000000" stroke-width="2"/>

                    <!-- Customer Agent associations -->
                    <line x1="1440" y1="500" x2="1180" y2="450" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="500" x2="1170" y2="530" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="500" x2="1180" y2="610" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="500" x2="1180" y2="690" stroke="#000000" stroke-width="2"/>

                    <!-- System associations -->
                    <line x1="1440" y1="200" x2="1270" y2="200" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="200" x2="970" y2="200" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="200" x2="1270" y2="280" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="200" x2="970" y2="280" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="200" x2="975" y2="360" stroke="#000000" stroke-width="2"/>
                    <line x1="1440" y1="200" x2="1270" y2="360" stroke="#000000" stroke-width="2"/>

                    <!-- Include relationships (dashed lines) -->
                    <line x1="410" y1="410" x2="1020" y2="450" stroke="#000000" stroke-width="2" stroke-dasharray="8,4"/>
                    <text x="715" y="420" font-size="10" font-style="italic" fill="#000000">«include»</text>

                    <line x1="420" y1="490" x2="830" y2="200" stroke="#000000" stroke-width="2" stroke-dasharray="8,4"/>
                    <text x="625" y="340" font-size="10" font-style="italic" fill="#000000">«include»</text>

                    <line x1="420" y1="570" x2="825" y2="360" stroke="#000000" stroke-width="2" stroke-dasharray="8,4"/>
                    <text x="620" y="465" font-size="10" font-style="italic" fill="#000000">«include»</text>

                    <line x1="560" y1="1140" x2="1130" y2="280" stroke="#000000" stroke-width="2" stroke-dasharray="8,4"/>
                    <text x="845" y="710" font-size="10" font-style="italic" fill="#000000">«include»</text>

                    <line x1="570" y1="900" x2="830" y2="200" stroke="#000000" stroke-width="2" stroke-dasharray="8,4"/>
                    <text x="700" y="550" font-size="10" font-style="italic" fill="#000000">«include»</text>
                </svg>
            </div>
        </div>

        <div class="status" id="status"></div>

        <div class="info-panel">
            <h3>DropMe System Overview</h3>
            <ul>
                <li><strong>Actors:</strong> Passenger (App User), Driver (Fleet Member), Customer Agent (Fleet Manager), System (Automated)</li>
                <li><strong>Key Features:</strong> Ride request and assignment, real-time tracking, automated notifications, fare calculation</li>
                <li><strong>System Scale:</strong> Manages 100+ vehicles in the fleet</li>
                <li><strong>Relationships:</strong> Include dependencies shown with dashed lines between use cases</li>
            </ul>
        </div>
    </div>

    <script>
        let currentZoom = 1;
        const zoomStep = 0.2;
        const minZoom = 0.5;
        const maxZoom = 3;

        function updateZoom() {
            const wrapper = document.getElementById('diagramWrapper');
            wrapper.style.transform = `scale(${currentZoom})`;
            document.getElementById('zoomLevel').textContent = `${Math.round(currentZoom * 100)}%`;
        }

        function zoomIn() {
            if (currentZoom < maxZoom) {
                currentZoom += zoomStep;
                updateZoom();
            }
        }

        function zoomOut() {
            if (currentZoom > minZoom) {
                currentZoom -= zoomStep;
                updateZoom();
            }
        }

        function resetZoom() {
            currentZoom = 1;
            updateZoom();
        }

        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status ${type}`;
            
            if (type === 'success') {
                setTimeout(() => {
                    status.style.opacity = '0';
                }, 3000);
            }
        }

        function downloadPNG() {
            try {
                const svg = document.getElementById('umlDiagram');
                const svgData = new XMLSerializer().serializeToString(svg);
                
                // Create a canvas
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Set canvas size (high resolution)
                const scale = 2; // For better quality
                canvas.width = 1600 * scale;
                canvas.height = 1400 * scale;
                
                // Create an image
                const img = new Image();
                
                img.onload = function() {
                    // Clear canvas with white background
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    
                    // Draw the SVG
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    // Download the image
                    canvas.toBlob(function(blob) {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'dropme-uml-use-case-diagram.png';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        
                        showStatus('PNG downloaded successfully!', 'success');
                    }, 'image/png');
                };
                
                img.onerror = function() {
                    showStatus('Error creating PNG. Please try again.', 'error');
                };
                
                // Convert SVG to data URL
                const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
                const url = URL.createObjectURL(svgBlob);
                img.src = url;
                
            } catch (error) {
                console.error('Download error:', error);
                showStatus('Error downloading PNG. Please try again.', 'error');
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case '=':
                    case '+':
                        e.preventDefault();
                        zoomIn();
                        break;
                    case '-':
                        e.preventDefault();
                        zoomOut();
                        break;
                    case '0':
                        e.preventDefault();
                        resetZoom();
                        break;
                    case 's':
                        e.preventDefault();
                        downloadPNG();
                        break;
                }
            }
        });

        // Initialize
        updateZoom();
    </script>
</body>
</html>