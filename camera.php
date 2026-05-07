<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed QR Code Scanner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f0f0;
        }
        #scanner-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        #video {
            width: 100%;
            max-height: 400px;
            border: 3px solid #2196F3;
            border-radius: 8px;
        }
        #canvas {
            display: none;
        }
        #qr-details {
            background-color: #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .detail-item {
            margin-bottom: 10px;
            display: flex;
        }
        .detail-label {
            font-weight: bold;
            margin-right: 10px;
            min-width: 120px;
        }
        .detail-value {
            flex-grow: 1;
            word-break: break-all;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>QR Code Scanner</h1>
    
    <div id="scanner-container">
        <video id="video" autoplay playsinline></video>
        <canvas id="canvas"></canvas>
    </div>

    <div>
        <button id="start-scan" class="button">Start Scanning</button>
        <button id="stop-scan" class="button" style="display:none;">Stop Scanning</button>
    </div>

    <div id="qr-details" style="display:none;">
        <div class="detail-item">
            <span class="detail-label">Content:</span>
            <span id="detail-content" class="detail-value"></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Type:</span>
            <span id="detail-type" class="detail-value"></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Timestamp:</span>
            <span id="detail-timestamp" class="detail-value"></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Location:</span>
            <span id="detail-location" class="detail-value">N/A</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        // DOM Elements
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const startScanBtn = document.getElementById('start-scan');
        const stopScanBtn = document.getElementById('stop-scan');
        const qrDetailsContainer = document.getElementById('qr-details');

        // QR Code Detail Elements
        const contentElem = document.getElementById('detail-content');
        const typeElem = document.getElementById('detail-type');
        const timestampElem = document.getElementById('detail-timestamp');
        const locationElem = document.getElementById('detail-location');

        // State variables
        let scanning = false;
        let stream = null;

        // Event Listeners
        startScanBtn.addEventListener('click', startScanning);
        stopScanBtn.addEventListener('click', stopScanning);

        // Start QR Code Scanning
        async function startScanning() {
            // Reset previous scan
            qrDetailsContainer.style.display = 'none';

            try {
                // Request camera access
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: "environment",
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                });

                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                video.play();

                // Update UI
                startScanBtn.style.display = 'none';
                stopScanBtn.style.display = 'block';

                // Start QR code detection
                scanning = true;
                requestAnimationFrame(tick);

            } catch (err) {
                console.error("Camera error:", err);
                alert("Could not access camera. " + err.message);
            }
        }

        // Stop QR Code Scanning
        function stopScanning() {
            scanning = false;
            
            // Stop video stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Reset UI
            video.srcObject = null;
            startScanBtn.style.display = 'block';
            stopScanBtn.style.display = 'none';
        }

        // QR Code Detection Loop
        function tick() {
            if (!scanning) return;

            // Check if video is ready
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                // Set canvas to same size as video
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;

                // Draw current video frame to canvas
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Get image data and try to decode QR code
                var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                var code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                // If QR code detected
                if (code) {
                    processQRCode(code);
                }
            }

            // Continue scanning
            if (scanning) {
                requestAnimationFrame(tick);
            }
        }

        // Process Detected QR Code
        function processQRCode(code) {
            // Stop scanning
            stopScanning();

            // Determine QR Code Type
            let type = determineQRCodeType(code.data);

            // Update details
            contentElem.textContent = code.data;
            typeElem.textContent = type;
            timestampElem.textContent = new Date().toLocaleString();

            // Try to extract location (if applicable)
            try {
                if (type === 'GPS Coordinates') {
                    locationElem.textContent = code.data;
                }
            } catch (error) {
                locationElem.textContent = 'N/A';
            }

            // Show details container
            qrDetailsContainer.style.display = 'block';
        }

        // Determine QR Code Type
        function determineQRCodeType(data) {
            // URL Detection
            if (/^(http|https):\/\/[^ "]+$/.test(data)) {
                return 'Web URL';
            }
            
            // Email Detection
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data)) {
                return 'Email Address';
            }
            
            // Phone Number Detection
            if (/^\+?[\d\s-()]+$/.test(data)) {
                return 'Phone Number';
            }
            
            // GPS Coordinates Detection
            if (/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/.test(data)) {
                return 'GPS Coordinates';
            }
            
            // WiFi Configuration Detection
            if (data.startsWith('WIFI:')) {
                return 'WiFi Configuration';
            }
            
            // Generic Text
            return 'Plain Text';
        }
    </script>
</body>
</html>