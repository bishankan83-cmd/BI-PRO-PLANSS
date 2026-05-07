<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DropMe Taxi Service - Class Diagram</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mermaid/10.6.1/mermaid.min.js"></script>
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
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .controls {
            padding: 20px 30px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-badge {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #10b981;
            color: white;
        }

        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary {
            background: #6366f1;
            color: white;
        }

        .btn-secondary:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-info {
            background: #06b6d4;
            color: white;
        }

        .btn-info:hover {
            background: #0891b2;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);
        }

        .diagram-container {
            padding: 30px;
            background: white;
            overflow-x: auto;
        }

        #diagram {
            min-height: 600px;
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            background: #fafafa;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8fafc;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 4px solid #3b82f6;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .footer {
            background: #1f2937;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            flex-direction: column;
            gap: 20px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f4f6;
            border-top: 5px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-message {
            background: #10b981;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 30px;
            text-align: center;
            display: none;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .controls {
                flex-direction: column;
                text-align: center;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚕 DropMe Taxi Service</h1>
            <p>Complete Class Diagram & System Architecture</p>
        </div>

        <div class="success-message" id="successMessage">
            ✅ Diagram downloaded successfully!
        </div>

        <div class="controls">
            <div class="info-badge">
                System Design Document
            </div>
            <div class="button-group">
                <button class="btn btn-primary" onclick="downloadDiagram()">
                    📥 Download PNG
                </button>
                <button class="btn btn-secondary" onclick="downloadSVG()">
                    📄 Download SVG
                </button>
                <button class="btn btn-info" onclick="printDiagram()">
                    🖨️ Print Diagram
                </button>
            </div>
        </div>

        <div class="diagram-container">
            <div id="diagram">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading Class Diagram...</p>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">11</div>
                <div class="stat-label">Main Classes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">4</div>
                <div class="stat-label">Enumerations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100+</div>
                <div class="stat-label">Fleet Vehicles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">25+</div>
                <div class="stat-label">Relationships</div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2024 DropMe Taxi Service - System Architecture Documentation</p>
        </div>
    </div>

    <script>
        // Initialize Mermaid
        mermaid.initialize({ 
            startOnLoad: true,
            theme: 'default',
            themeVariables: {
                primaryColor: '#4f46e5',
                primaryTextColor: '#1f2937',
                primaryBorderColor: '#e5e7eb',
                lineColor: '#6b7280',
                secondaryColor: '#f3f4f6',
                tertiaryColor: '#ffffff'
            }
        });

        // Class diagram definition
        const diagramDefinition = `
classDiagram
    class User {
        -userId: String
        -name: String
        -phoneNumber: String
        -email: String
        -registrationDate: Date
        -isActive: Boolean
        +register()
        +login()
        +updateProfile()
        +requestRide()
        +cancelRide()
        +rateDriver()
        +provideFeedback()
        +viewRideHistory()
    }

    class Driver {
        -driverId: String
        -name: String
        -phoneNumber: String
        -email: String
        -licenseNumber: String
        -rating: Float
        -isAvailable: Boolean
        -currentLocation: Location
        +login()
        +updateLocation()
        +acceptRide()
        +rejectRide()
        +startRide()
        +completeRide()
        +collectPayment()
        +viewRideHistory()
    }

    class CustomerAgent {
        -agentId: String
        -name: String
        -phoneNumber: String
        -email: String
        -workShift: String
        +checkTaxiAvailability()
        +assignTaxi()
        +notifyUser()
        +handleCustomerSupport()
        +viewSystemStatus()
    }

    class Vehicle {
        -vehicleId: String
        -plateNumber: String
        -model: String
        -year: Integer
        -color: String
        -capacity: Integer
        -isActive: Boolean
        -currentLocation: Location
        +updateLocation()
        +performMaintenance()
        +checkStatus()
    }

    class RideRequest {
        -requestId: String
        -pickupLocation: Location
        -destination: Location
        -requestTime: DateTime
        -status: RequestStatus
        -estimatedFare: Float
        -specialRequirements: String
        +createRequest()
        +updateStatus()
        +calculateFare()
        +cancel()
    }

    class Ride {
        -rideId: String
        -startTime: DateTime
        -endTime: DateTime
        -actualFare: Float
        -distance: Float
        -status: RideStatus
        -paymentMethod: String
        +startRide()
        +endRide()
        +calculateActualFare()
        +processPayment()
    }

    class Location {
        -latitude: Float
        -longitude: Float
        -address: String
        +getCoordinates()
        +getAddress()
        +calculateDistance(Location)
    }

    class Rating {
        -ratingId: String
        -score: Integer
        -feedback: String
        -ratingDate: DateTime
        +submitRating()
        +updateRating()
    }

    class MobileApp {
        -appVersion: String
        -userId: String
        +displayMap()
        +sendNotification()
        +trackLocation()
        +processPayment()
        +showRideStatus()
    }

    class NotificationService {
        -notificationId: String
        -message: String
        -timestamp: DateTime
        -type: NotificationType
        +sendNotification()
        +sendSMS()
        +sendPushNotification()
    }

    class PaymentService {
        -paymentId: String
        -amount: Float
        -paymentMethod: String
        -transactionDate: DateTime
        -status: PaymentStatus
        +processPayment()
        +refundPayment()
        +generateInvoice()
    }

    class RequestStatus {
        <<enumeration>>
        PENDING
        ASSIGNED
        REJECTED
        CANCELLED
    }

    class RideStatus {
        <<enumeration>>
        ASSIGNED
        IN_PROGRESS
        COMPLETED
        CANCELLED
    }

    class NotificationType {
        <<enumeration>>
        RIDE_ASSIGNED
        DRIVER_ARRIVED
        RIDE_STARTED
        RIDE_COMPLETED
        PAYMENT_RECEIVED
    }

    class PaymentStatus {
        <<enumeration>>
        PENDING
        COMPLETED
        FAILED
        REFUNDED
    }

    User ||--o{ RideRequest : creates
    User ||--o{ Rating : provides
    User ||--|| MobileApp : uses
    
    Driver ||--|| Vehicle : drives
    Driver ||--o{ Ride : performs
    Driver ||--o{ Rating : receives
    
    CustomerAgent ||--o{ RideRequest : processes
    CustomerAgent ||--o{ Vehicle : manages
    
    RideRequest ||--|| Ride : becomes
    RideRequest ||--|| Location : has pickup
    RideRequest ||--|| Location : has destination
    
    Ride ||--|| User : involves
    Ride ||--|| Driver : involves
    Ride ||--|| Vehicle : uses
    Ride ||--|| PaymentService : processes payment through
    
    Rating ||--|| User : given by
    Rating ||--|| Driver : given to
    Rating ||--|| Ride : relates to
    
    Vehicle ||--|| Location : has current
    Driver ||--|| Location : has current
    
    NotificationService ||--o{ User : notifies
    NotificationService ||--o{ Driver : notifies
    
    MobileApp ||--|| NotificationService : uses
    MobileApp ||--|| PaymentService : integrates with
        `;

        // Render the diagram
        setTimeout(() => {
            mermaid.render('mermaidDiagram', diagramDefinition).then(({svg}) => {
                document.getElementById('diagram').innerHTML = svg;
            }).catch(error => {
                console.error('Error rendering diagram:', error);
                document.getElementById('diagram').innerHTML = '<p style="text-align: center; color: red;">Error loading diagram. Please refresh the page.</p>';
            });
        }, 1000);

        // Download functions
        function downloadDiagram() {
            const svg = document.querySelector('#diagram svg');
            if (!svg) {
                alert('Diagram not loaded yet. Please wait and try again.');
                return;
            }

            // Convert SVG to PNG
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            const svgData = new XMLSerializer().serializeToString(svg);
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            
            img.onload = function() {
                canvas.width = img.width * 2; // Higher resolution
                canvas.height = img.height * 2;
                ctx.scale(2, 2);
                ctx.drawImage(img, 0, 0);
                
                canvas.toBlob(function(blob) {
                    const link = document.createElement('a');
                    link.download = 'dropme-class-diagram.png';
                    link.href = URL.createObjectURL(blob);
                    link.click();
                    
                    showSuccessMessage();
                    URL.revokeObjectURL(url);
                    URL.revokeObjectURL(link.href);
                });
            };
            
            img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
        }

        function downloadSVG() {
            const svg = document.querySelector('#diagram svg');
            if (!svg) {
                alert('Diagram not loaded yet. Please wait and try again.');
                return;
            }

            const svgData = new XMLSerializer().serializeToString(svg);
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            
            const link = document.createElement('a');
            link.download = 'dropme-class-diagram.svg';
            link.href = url;
            link.click();
            
            showSuccessMessage();
            URL.revokeObjectURL(url);
        }

        function printDiagram() {
            const svg = document.querySelector('#diagram svg');
            if (!svg) {
                alert('Diagram not loaded yet. Please wait and try again.');
                return;
            }

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>DropMe Class Diagram</title>
                        <style>
                            body { margin: 0; padding: 20px; }
                            svg { max-width: 100%; height: auto; }
                            @media print { body { margin: 0; } }
                        </style>
                    </head>
                    <body>
                        <h1>DropMe Taxi Service - Class Diagram</h1>
                        ${svg.outerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function showSuccessMessage() {
            const message = document.getElementById('successMessage');
            message.style.display = 'block';
            setTimeout(() => {
                message.style.display = 'none';
            }, 3000);
        }
    </script>

    <?php
    // PHP code for server-side functionality
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        
        switch ($_POST['action']) {
            case 'log_download':
                // Log download activity
                $log_entry = date('Y-m-d H:i:s') . " - Diagram downloaded\n";
                file_put_contents('download_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
                echo json_encode(['status' => 'logged']);
                break;
                
            case 'get_stats':
                // Return system statistics
                $stats = [
                    'total_classes' => 11,
                    'enumerations' => 4,
                    'relationships' => 25,
                    'fleet_size' => 100
                ];
                echo json_encode($stats);
                break;
                
            default:
                echo json_encode(['error' => 'Unknown action']);
        }
        exit;
    }
    ?>
</body>
</html>