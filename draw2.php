<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DropMe System Analysis and Design - UML Diagrams</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.5;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 16pt;
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        h2 {
            font-size: 14pt;
            color: #34495e;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 40px;
        }
        .diagram-container {
            margin: 20px 0;
            text-align: center;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            position: relative;
        }
        svg {
            max-width: 100%;
            height: auto;
            border: 1px solid #ccc;
            background-color: white;
        }
        .download-btn {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12pt;
            margin: 10px;
            transition: background-color 0.3s;
        }
        .download-btn:hover {
            background-color: #2980b9;
        }
        .description {
            font-size: 12pt;
            text-align: left;
            margin: 15px 0;
            color: #555;
        }
        .page-number {
            text-align: center;
            font-size: 10pt;
            margin-top: 30px;
            color: #666;
        }
        .button-container {
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>DropMe System Analysis and Design Report</h1>
        <div style="text-align: center; margin-bottom: 30px;">
            <p style="font-size: 12pt; color: #666;">
                <strong>Student:</strong> [Your Name] | <strong>Module:</strong> BAY2210 - System Analysis and Design<br>
                <strong>Assignment:</strong> Case Study and Report Preparation | <strong>Due Date:</strong> 28th April 2025
            </p>
        </div>
        
        <h2>1. Activity Diagram</h2>
        <div class="diagram-container">
            <svg id="activity-diagram" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg">
                <!-- Swimlanes -->
                <text x="100" y="30" text-anchor="middle" font-size="14" font-weight="bold" fill="#000">Passenger</text>
                <text x="450" y="30" text-anchor="middle" font-size="14" font-weight="bold" fill="#000">Customer Agent</text>
                <text x="800" y="30" text-anchor="middle" font-size="14" font-weight="bold" fill="#000">Driver</text>
                <text x="1100" y="30" text-anchor="middle" font-size="14" font-weight="bold" fill="#000">System</text>
                
                <!-- Vertical swimlane dividers -->
                <line x1="275" y1="50" x2="275" y2="1150" stroke="#000" stroke-width="1" stroke-dasharray="5,5"/>
                <line x1="625" y1="50" x2="625" y2="1150" stroke="#000" stroke-width="1" stroke-dasharray="5,5"/>
                <line x1="975" y1="50" x2="975" y2="1150" stroke="#000" stroke-width="1" stroke-dasharray="5,5"/>
                
                <!-- Start node -->
                <circle cx="100" cy="80" r="12" fill="#000" />
                
                <!-- Passenger Activities -->
                <ellipse cx="100" cy="130" rx="60" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="100" y="135" text-anchor="middle" font-size="11" fill="#000">Open App</text>
                
                <ellipse cx="100" cy="190" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="100" y="195" text-anchor="middle" font-size="11" fill="#000">Enter Pickup Location</text>
                
                <ellipse cx="100" cy="250" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="100" y="255" text-anchor="middle" font-size="11" fill="#000">Enter Destination</text>
                
                <ellipse cx="100" cy="310" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="100" y="315" text-anchor="middle" font-size="11" fill="#000">Submit Request</text>
                
                <!-- Agent Activities -->
                <ellipse cx="450" cy="370" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="450" y="375" text-anchor="middle" font-size="11" fill="#000">Receive Request</text>
                
                <ellipse cx="450" cy="430" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="450" y="435" text-anchor="middle" font-size="11" fill="#000">Check Availability</text>
                
                <!-- Decision diamond -->
                <polygon points="450,480 490,505 450,530 410,505" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="450" y="510" text-anchor="middle" font-size="10" fill="#000">Available?</text>
                
                <!-- No path -->
                <ellipse cx="450" cy="580" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="450" y="585" text-anchor="middle" font-size="11" fill="#000">Send Rejection</text>
                
                <!-- Yes path -->
                <ellipse cx="450" cy="620" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="450" y="625" text-anchor="middle" font-size="11" fill="#000">Assign Taxi</text>
                
                <!-- System Activities -->
                <ellipse cx="1100" cy="680" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="1100" y="685" text-anchor="middle" font-size="11" fill="#000">Send Notification</text>
                
                <!-- Driver Activities -->
                <ellipse cx="800" cy="740" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="800" y="745" text-anchor="middle" font-size="11" fill="#000">Accept Ride</text>
                
                <ellipse cx="800" cy="800" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="800" y="805" text-anchor="middle" font-size="11" fill="#000">Navigate to Pickup</text>
                
                <ellipse cx="800" cy="860" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="800" y="865" text-anchor="middle" font-size="11" fill="#000">Pick Up Passenger</text>
                
                <ellipse cx="800" cy="920" rx="80" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="800" y="925" text-anchor="middle" font-size="11" fill="#000">Drive to Destination</text>
                
                <ellipse cx="800" cy="980" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="800" y="985" text-anchor="middle" font-size="11" fill="#000">Complete Trip</text>
                
                <!-- Passenger final activities -->
                <ellipse cx="100" cy="1040" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="100" y="1045" text-anchor="middle" font-size="11" fill="#000">Make Payment</text>
                
                <ellipse cx="100" cy="1100" rx="70" ry="20" fill="#ffffff" stroke="#000" stroke-width="2"/>
                <text x="100" y="1105" text-anchor="middle" font-size="11" fill="#000">Rate Driver</text>
                
                <!-- End nodes -->
                <circle cx="100" cy="620" r="8" fill="#000" />
                <circle cx="100" cy="620" r="12" fill="#fff" stroke="#000" stroke-width="2"/>
                
                <circle cx="100" cy="1150" r="8" fill="#000" />
                <circle cx="100" cy="1150" r="12" fill="#fff" stroke="#000" stroke-width="2"/>
                
                <!-- Arrows -->
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#000" />
                    </marker>
                </defs>
                
                <!-- Passenger flow -->
                <line x1="100" y1="92" x2="100" y2="110" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="100" y1="150" x2="100" y2="170" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="100" y1="210" x2="100" y2="230" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="100" y1="270" x2="100" y2="290" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Cross-swimlane communication -->
                <line x1="100" y1="330" x2="450" y2="350" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Agent flow -->
                <line x1="450" y1="390" x2="450" y2="410" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="450" y1="450" x2="450" y2="480" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Decision branches -->
                <line x1="410" y1="505" x2="100" y2="600" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <text x="250" y="550" text-anchor="middle" font-size="10" fill="#000">[No]</text>
                
                <line x1="450" y1="530" x2="450" y2="600" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <text x="470" y="565" text-anchor="middle" font-size="10" fill="#000">[Yes]</text>
                
                <!-- Agent to System -->
                <line x1="520" y1="625" x2="1020" y2="680" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- System to Driver -->
                <line x1="1020" y1="685" x2="870" y2="740" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Driver flow -->
                <line x1="800" y1="760" x2="800" y2="780" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="800" y1="820" x2="800" y2="840" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="800" y1="880" x2="800" y2="900" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="800" y1="940" x2="800" y2="960" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Driver to Passenger -->
                <line x1="730" y1="985" x2="170" y2="1040" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Final passenger flow -->
                <line x1="100" y1="1060" x2="100" y2="1080" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                <line x1="100" y1="1120" x2="100" y2="1138" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
                
                <!-- Rejection flow -->
                <line x1="450" y1="600" x2="100" y2="608" stroke="#000" stroke-width="2" marker-end="url(#arrowhead)"/>
            </svg>
            <div class="button-container">
                <button class="download-btn" onclick="downloadSVG('activity-diagram', 'DropMe_Activity_Diagram')">Download Activity Diagram</button>
            </div>
        </div>
        
        <div class="description">
            <strong>Activity Diagram Description:</strong> This diagram illustrates the complete workflow of the DropMe taxi booking system. It shows the sequence of activities from when a user opens the app until trip completion or unavailability notification. The diamond shape represents a decision point where the system checks taxi availability, leading to two possible paths.
        </div>

        <h2>2. Use Case Diagram</h2>
        <div class="diagram-container">
       <svg width="1200" height="900" xmlns="http://www.w3.org/2000/svg">
  <!-- System boundary -->
  <rect x="120" y="80" width="960" height="720" fill="#f8f9fa" stroke="#2c3e50" stroke-width="3" rx="15"/>
  <text x="600" y="60" text-anchor="middle" font-family="Arial, sans-serif" font-size="20" font-weight="bold" fill="#2c3e50">DropMe Mobile Application System</text>
  
  <!-- Actors -->
  <!-- Passenger -->
  <g id="passenger">
    <circle cx="60" cy="180" r="15" fill="none" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="195" x2="60" y2="230" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="215" x2="45" y2="245" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="215" x2="75" y2="245" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="230" x2="45" y2="260" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="230" x2="75" y2="260" stroke="#34495e" stroke-width="2"/>
    <text x="60" y="285" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="#2c3e50">Passenger</text>
  </g>
  
  <!-- Driver -->
  <g id="driver">
    <circle cx="60" cy="550" r="15" fill="none" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="565" x2="60" y2="600" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="585" x2="45" y2="615" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="585" x2="75" y2="615" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="600" x2="45" y2="630" stroke="#34495e" stroke-width="2"/>
    <line x1="60" y1="600" x2="75" y2="630" stroke="#34495e" stroke-width="2"/>
    <text x="60" y="655" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="#2c3e50">Driver</text>
  </g>
  
  <!-- Customer Agent -->
  <g id="customer-agent">
    <circle cx="1140" cy="300" r="15" fill="none" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="315" x2="1140" y2="350" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="335" x2="1125" y2="365" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="335" x2="1155" y2="365" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="350" x2="1125" y2="380" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="350" x2="1155" y2="380" stroke="#34495e" stroke-width="2"/>
    <text x="1140" y="405" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="#2c3e50">Customer Agent</text>
  </g>
  
  <!-- System Actor -->
  <g id="system-actor">
    <circle cx="1140" cy="650" r="15" fill="none" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="665" x2="1140" y2="700" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="685" x2="1125" y2="715" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="685" x2="1155" y2="715" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="700" x2="1125" y2="730" stroke="#34495e" stroke-width="2"/>
    <line x1="1140" y1="700" x2="1155" y2="730" stroke="#34495e" stroke-width="2"/>
    <text x="1140" y="755" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="#2c3e50">System</text>
  </g>
  
  <!-- Use Cases - Passenger Side -->
  <ellipse cx="280" cy="140" rx="85" ry="30" fill="#e8f4fd" stroke="#3498db" stroke-width="2"/>
  <text x="280" y="133" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Register with</text>
  <text x="280" y="148" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">DropMe Service</text>
  
  <ellipse cx="280" cy="210" rx="85" ry="30" fill="#e8f4fd" stroke="#3498db" stroke-width="2"/>
  <text x="280" y="203" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Enter Pick-up &amp;</text>
  <text x="280" y="218" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Destination</text>
  
  <ellipse cx="280" cy="280" rx="75" ry="30" fill="#e8f4fd" stroke="#3498db" stroke-width="2"/>
  <text x="280" y="287" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Request Ride</text>
  
  <ellipse cx="280" cy="350" rx="85" ry="30" fill="#e8f4fd" stroke="#3498db" stroke-width="2"/>
  <text x="280" y="343" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Receive</text>
  <text x="280" y="358" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Notification</text>
  
  <ellipse cx="280" cy="420" rx="75" ry="30" fill="#e8f4fd" stroke="#3498db" stroke-width="2"/>
  <text x="280" y="427" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Rate Driver</text>
  
  <ellipse cx="280" cy="490" rx="85" ry="30" fill="#e8f4fd" stroke="#3498db" stroke-width="2"/>
  <text x="280" y="497" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Provide Feedback</text>
  
  <!-- Use Cases - Center System -->
  <ellipse cx="600" cy="140" rx="95" ry="30" fill="#fff3cd" stroke="#f39c12" stroke-width="2"/>
  <text x="600" y="133" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Send Automated</text>
  <text x="600" y="148" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Notifications</text>
  
  <ellipse cx="600" cy="350" rx="95" ry="30" fill="#fff3cd" stroke="#f39c12" stroke-width="2"/>
  <text x="600" y="343" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Track Vehicle</text>
  <text x="600" y="358" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Location</text>
  
  <!-- Use Cases - Customer Agent Side -->
  <ellipse cx="920" cy="180" rx="85" ry="30" fill="#d4edda" stroke="#27ae60" stroke-width="2"/>
  <text x="920" y="173" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Check Taxi</text>
  <text x="920" y="188" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Availability</text>
  
  <ellipse cx="920" cy="250" rx="75" ry="30" fill="#d4edda" stroke="#27ae60" stroke-width="2"/>
  <text x="920" y="257" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Assign Taxi</text>
  
  <ellipse cx="920" cy="320" rx="95" ry="30" fill="#d4edda" stroke="#27ae60" stroke-width="2"/>
  <text x="920" y="313" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Notify User of</text>
  <text x="920" y="328" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Unavailability</text>
  
  <ellipse cx="920" cy="390" rx="85" ry="30" fill="#d4edda" stroke="#27ae60" stroke-width="2"/>
  <text x="920" y="383" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Manage Fleet</text>
  <text x="920" y="398" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">(100+ Vehicles)</text>
  
  <!-- Use Cases - Driver Side -->
  <ellipse cx="420" cy="580" rx="85" ry="30" fill="#f8d7da" stroke="#e74c3c" stroke-width="2"/>
  <text x="420" y="573" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Receive Trip</text>
  <text x="420" y="588" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Assignment</text>
  
  <ellipse cx="420" cy="650" rx="75" ry="30" fill="#f8d7da" stroke="#e74c3c" stroke-width="2"/>
  <text x="420" y="643" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Pick Up</text>
  <text x="420" y="658" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Passenger</text>
  
  <ellipse cx="420" cy="720" rx="85" ry="30" fill="#f8d7da" stroke="#e74c3c" stroke-width="2"/>
  <text x="420" y="713" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Drop Off at</text>
  <text x="420" y="728" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Destination</text>
  
  <ellipse cx="280" cy="720" rx="75" ry="30" fill="#f8d7da" stroke="#e74c3c" stroke-width="2"/>
  <text x="280" y="727" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Collect Fee</text>
  
  <ellipse cx="280" cy="650" rx="75" ry="30" fill="#f8d7da" stroke="#e74c3c" stroke-width="2"/>
  <text x="280" y="657" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Update Status</text>
  
  <!-- System Payment Processing -->
  <ellipse cx="750" cy="580" rx="95" ry="30" fill="#fff3cd" stroke="#f39c12" stroke-width="2"/>
  <text x="750" y="573" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Process Payment</text>
  <text x="750" y="588" text-anchor="middle" font-family="Arial, sans-serif" font-size="11" font-weight="bold">Transaction</text>
  
  <!-- Actor-Use Case Relationships -->
  <!-- Passenger relationships -->
  <line x1="75" y1="180" x2="195" y2="140" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="200" x2="195" y2="210" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="220" x2="205" y2="280" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="240" x2="195" y2="350" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="260" x2="205" y2="420" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="280" x2="195" y2="490" stroke="#34495e" stroke-width="2"/>
  
  <!-- Driver relationships -->
  <line x1="75" y1="550" x2="335" y2="580" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="570" x2="345" y2="650" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="590" x2="335" y2="720" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="610" x2="205" y2="720" stroke="#34495e" stroke-width="2"/>
  <line x1="75" y1="630" x2="205" y2="650" stroke="#34495e" stroke-width="2"/>
  
  <!-- Customer Agent relationships -->
  <line x1="1125" y1="280" x2="1005" y2="180" stroke="#34495e" stroke-width="2"/>
  <line x1="1125" y1="300" x2="995" y2="250" stroke="#34495e" stroke-width="2"/>
  <line x1="1125" y1="320" x2="1015" y2="320" stroke="#34495e" stroke-width="2"/>
  <line x1="1125" y1="340" x2="1005" y2="390" stroke="#34495e" stroke-width="2"/>
  
  <!-- System relationships -->
  <line x1="1125" y1="630" x2="695" y2="140" stroke="#34495e" stroke-width="2"/>
  <line x1="1125" y1="640" x2="695" y2="350" stroke="#34495e" stroke-width="2"/>
  <line x1="1125" y1="650" x2="845" y2="580" stroke="#34495e" stroke-width="2"/>
  
  <!-- Include relationships (dashed lines) -->
  <line x1="365" y1="280" x2="505" y2="140" stroke="#8e44ad" stroke-width="2" stroke-dasharray="8,4"/>
  <text x="435" y="205" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" font-style="italic" fill="#8e44ad">&lt;&lt;includes&gt;&gt;</text>
  
  <line x1="365" y1="350" x2="505" y2="140" stroke="#8e44ad" stroke-width="2" stroke-dasharray="8,4"/>
  <text x="420" y="230" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" font-style="italic" fill="#8e44ad">&lt;&lt;includes&gt;&gt;</text>
  
  <line x1="835" y1="250" x2="695" y2="140" stroke="#8e44ad" stroke-width="2" stroke-dasharray="8,4"/>
  <text x="765" y="185" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" font-style="italic" fill="#8e44ad">&lt;&lt;includes&gt;&gt;</text>
  
  <line x1="505" y1="720" x2="655" y2="580" stroke="#8e44ad" stroke-width="2" stroke-dasharray="8,4"/>
  <text x="580" y="655" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" font-style="italic" fill="#8e44ad">&lt;&lt;includes&gt;&gt;</text>
</svg>
            <div class="button-container">
                <button class="download-btn" onclick="downloadSVG('usecase-diagram', 'DropMe_UseCase_Diagram')">Download Use Case Diagram</button>
            </div>
        </div>
        
        <div class="description">
            <strong>Use Case Diagram Description:</strong> This diagram shows the main actors (Passenger, Driver, Customer Agent) and their interactions with the DropMe system. The system boundary contains all use cases that represent the functionality available to users. Include relationships show dependencies between use cases. The layout is organized to minimize line crossings.
        </div>

        <h2>3. Class Diagram</h2>
        <div class="diagram-container">
            <svg id="class-diagram" viewBox="0 0 1400 1000" xmlns="http://www.w3.org/2000/svg">
                <!-- Top row: User (center) -->
                <g id="user-class">
                    <rect x="600" y="50" width="200" height="140" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="600" y="50" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="700" y="70" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">User</text>
                    <line x1="600" y1="80" x2="800" y2="80" stroke="#000" stroke-width="1"/>
                    <text x="610" y="100" font-size="10" fill="#000">- userID: String</text>
                    <text x="610" y="115" font-size="10" fill="#000">- name: String</text>
                    <text x="610" y="130" font-size="10" fill="#000">- phone: String</text>
                    <text x="610" y="145" font-size="10" fill="#000">- email: String</text>
                    <text x="610" y="160" font-size="10" fill="#000">- registrationDate: Date</text>
                    <line x1="600" y1="170" x2="800" y2="170" stroke="#000" stroke-width="1"/>
                    <text x="610" y="185" font-size="10" fill="#000">+ register(): void</text>
                </g>
                
                <!-- Second row: Passenger, Driver, CustomerAgent (left to right) -->
                <g id="passenger-class">
                    <rect x="200" y="280" width="200" height="120" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="200" y="280" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="300" y="300" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">Passenger</text>
                    <line x1="200" y1="310" x2="400" y2="310" stroke="#000" stroke-width="1"/>
                    <text x="210" y="330" font-size="10" fill="#000">- rating: Float</text>
                    <text x="210" y="345" font-size="10" fill="#000">- paymentMethod: String</text>
                    <text x="210" y="360" font-size="10" fill="#000">- tripHistory: List</text>
                    <line x1="200" y1="370" x2="400" y2="370" stroke="#000" stroke-width="1"/>
                    <text x="210" y="385" font-size="10" fill="#000">+ requestRide(): void</text>
                    <text x="210" y="400" font-size="10" fill="#000">+ rateDriver(): void</text>
                </g>
                
                <g id="driver-class">
                    <rect x="600" y="280" width="200" height="120" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="600" y="280" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="700" y="300" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">Driver</text>
                    <line x1="600" y1="310" x2="800" y2="310" stroke="#000" stroke-width="1"/>
                    <text x="610" y="330" font-size="10" fill="#000">- licenseNumber: String</text>
                    <text x="610" y="345" font-size="10" fill="#000">- vehicleID: String</text>
                    <text x="610" y="360" font-size="10" fill="#000">- rating: Float</text>
                    <line x1="600" y1="370" x2="800" y2="370" stroke="#000" stroke-width="1"/>
                    <text x="610" y="385" font-size="10" fill="#000">+ acceptRide(): void</text>
                    <text x="610" y="400" font-size="10" fill="#000">+ updateStatus(): void</text>
                </g>
                
                <g id="agent-class">
                    <rect x="1000" y="280" width="200" height="120" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="1000" y="280" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="1100" y="300" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">CustomerAgent</text>
                    <line x1="1000" y1="310" x2="1200" y2="310" stroke="#000" stroke-width="1"/>
                    <text x="1010" y="330" font-size="10" fill="#000">- employeeID: String</text>
                    <text x="1010" y="345" font-size="10" fill="#000">- shift: String</text>
                    <text x="1010" y="360" font-size="10" fill="#000">- workstation: String</text>
                    <line x1="1000" y1="370" x2="1200" y2="370" stroke="#000" stroke-width="1"/>
                    <text x="1010" y="385" font-size="10" fill="#000">+ assignTaxi(): void</text>
                    <text x="1010" y="400" font-size="10" fill="#000">+ checkAvailability(): void</text>
                </g>
                
                <!-- Third row: Vehicle (top right) -->
                <g id="vehicle-class">
                    <rect x="1000" y="50" width="200" height="140" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="1000" y="50" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="1100" y="70" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">Vehicle</text>
                    <line x1="1000" y1="80" x2="1200" y2="80" stroke="#000" stroke-width="1"/>
                    <text x="1010" y="100" font-size="10" fill="#000">- vehicleID: String</text>
                    <text x="1010" y="115" font-size="10" fill="#000">- model: String</text>
                    <text x="1010" y="130" font-size="10" fill="#000">- licensePlate: String</text>
                    <text x="1010" y="145" font-size="10" fill="#000">- status: String</text>
                    <text x="1010" y="160" font-size="10" fill="#000">- capacity: Integer</text>
                    <line x1="1000" y1="170" x2="1200" y2="170" stroke="#000" stroke-width="1"/>
                    <text x="1010" y="185" font-size="10" fill="#000">+ updateStatus(): void</text>
                </g>

                <!-- Fourth row: Rating, RideRequest, Trip, Location -->
                <g id="rating-class">
                    <rect x="50" y="500" width="200" height="120" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="50" y="500" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="150" y="520" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">Rating</text>
                    <line x1="50" y1="530" x2="250" y2="530" stroke="#000" stroke-width="1"/>
                    <text x="60" y="550" font-size="10" fill="#000">- ratingID: String</text>
                    <text x="60" y="565" font-size="10" fill="#000">- score: Integer</text>
                    <text x="60" y="580" font-size="10" fill="#000">- comment: String</text>
                    <text x="60" y="595" font-size="10" fill="#000">- timestamp: DateTime</text>
                    <line x1="50" y1="605" x2="250" y2="605" stroke="#000" stroke-width="1"/>
                    <text x="60" y="620" font-size="10" fill="#000">+ submitRating(): void</text>
                </g>
                
                <g id="request-class">
                    <rect x="350" y="500" width="200" height="140" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="350" y="500" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="450" y="520" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">RideRequest</text>
                    <line x1="350" y1="530" x2="550" y2="530" stroke="#000" stroke-width="1"/>
                    <text x="360" y="550" font-size="10" fill="#000">- requestID: String</text>
                    <text x="360" y="565" font-size="10" fill="#000">- pickupLocation: String</text>
                    <text x="360" y="580" font-size="10" fill="#000">- destination: String</text>
                    <text x="360" y="595" font-size="10" fill="#000">- requestTime: DateTime</text>
                    <text x="360" y="610" font-size="10" fill="#000">- status: String</text>
                    <line x1="350" y1="620" x2="550" y2="620" stroke="#000" stroke-width="1"/>
                    <text x="360" y="635" font-size="10" fill="#000">+ createRequest(): void</text>
                </g>
                
                <g id="trip-class">
                    <rect x="650" y="500" width="200" height="140" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="650" y="500" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="750" y="520" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">Trip</text>
                    <line x1="650" y1="530" x2="850" y2="530" stroke="#000" stroke-width="1"/>
                    <text x="660" y="550" font-size="10" fill="#000">- tripID: String</text>
                    <text x="660" y="565" font-size="10" fill="#000">- startTime: DateTime</text>
                    <text x="660" y="580" font-size="10" fill="#000">- endTime: DateTime</text>
                    <text x="660" y="595" font-size="10" fill="#000">- fare: Float</text>
                    <text x="660" y="610" font-size="10" fill="#000">- distance: Float</text>
                    <line x1="650" y1="620" x2="850" y2="620" stroke="#000" stroke-width="1"/>
                    <text x="660" y="635" font-size="10" fill="#000">+ calculateFare(): Float</text>
                </g>
                
                <g id="location-class">
                    <rect x="950" y="500" width="200" height="120" fill="#fff" stroke="#000" stroke-width="2"/>
                    <rect x="950" y="500" width="200" height="30" fill="#f0f0f0" stroke="#000" stroke-width="1"/>
                    <text x="1050" y="520" text-anchor="middle" font-size="12" font-weight="bold" fill="#000">Location</text>
                    <line x1="950" y1="530" x2="1150" y2="530" stroke="#000" stroke-width="1"/>
                    <text x="960" y="550" font-size="10" fill="#000">- latitude: Double</text>
                    <text x="960" y="565" font-size="10" fill="#000">- longitude: Double</text>
                    <text x="960" y="580" font-size="10" fill="#000">- address: String</text>
                    <text x="960" y="595" font-size="10" fill="#000">- city: String</text>
                    <line x1="950" y1="605" x2="1150" y2="605" stroke="#000" stroke-width="1"/>
                    <text x="960" y="620" font-size="10" fill="#000">+ getCoordinates(): String</text>
                </g>
                
                <!-- Relationship markers -->
                <defs>
                    <marker id="inheritance" markerWidth="12" markerHeight="8" refX="12" refY="4" orient="auto">
                        <polygon points="0,0 0,8 12,4" fill="#fff" stroke="#000" stroke-width="1"/>
                    </marker>
                    <marker id="association" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#000" />
                    </marker>
                    <marker id="composition" markerWidth="12" markerHeight="8" refX="12" refY="4" orient="auto">
                        <polygon points="0,4 6,0 12,4 6,8" fill="#000" stroke="#000" stroke-width="1"/>
                    </marker>
                </defs>
                
                <!-- Inheritance relationships (no crossing lines) -->
                <line x1="300" y1="280" x2="650" y2="190" stroke="#000" stroke-width="2" marker-end="url(#inheritance)"/>
                <line x1="700" y1="280" x2="700" y2="190" stroke="#000" stroke-width="2" marker-end="url(#inheritance)"/>
                <line x1="1100" y1="280" x2="750" y2="190" stroke="#000" stroke-width="2" marker-end="url(#inheritance)"/>
                
                <!-- Association relationships (arranged to minimize crossing) -->
                <!-- Passenger creates RideRequest -->
                <line x1="350" y1="400" x2="400" y2="500" stroke="#000" stroke-width="1" marker-end="url(#association)"/>
                <text x="320" y="450" font-size="10" fill="#000">creates</text>
                <text x="325" y="465" font-size="10" fill="#000">1..*</text>
                
                <!-- Driver assigned to Trip -->
                <line x1="700" y1="400" x2="750" y2="500" stroke="#000" stroke-width="1" marker-end="url(#association)"/>
                <text x="680" y="450" font-size="10" fill="#000">assigned to</text>
                <text x="720" y="465" font-size="10" fill="#000">0..*</text>
                
                <!-- RideRequest becomes Trip -->
                <line x1="550" y1="570" x2="650" y2="570" stroke="#000" stroke-width="1" marker-end="url(#association)"/>
                <text x="580" y="560" font-size="10" fill="#000">becomes</text>
                <text x="595" y="590" font-size="10" fill="#000">1</text>
                
                <!-- Driver operates Vehicle -->
                <line x1="800" y1="340" x2="1000" y2="120" stroke="#000" stroke-width="1" marker-end="url(#association)"/>
                <text x="870" y="220" font-size="10" fill="#000">operates</text>
                <text x="920" y="160" font-size="10" fill="#000">1</text>
                
                <!-- Passenger gives Rating -->
                <line x1="250" y1="400" x2="200" y2="500" stroke="#000" stroke-width="1" marker-end="url(#association)"/>
                <text x="180" y="450" font-size="10" fill="#000">gives</text>
                <text x="205" y="465" font-size="10" fill="#000">0..*</text>
                
                <!-- Trip has Location (composition) -->
                <line x1="850" y1="560" x2="950" y2="560" stroke="#000" stroke-width="1" marker-end="url(#composition)"/>
                <text x="880" y="550" font-size="10" fill="#000">has</text>
                <text x="885" y="580" font-size="10" fill="#000">2</text>
                
                <!-- CustomerAgent manages RideRequest -->
                <line x1="1000" y1="380" x2="550" y2="520" stroke="#000" stroke-width="1" marker-end="url(#association)"/>
                <text x="750" y="440" font-size="10" fill="#000">manages</text>
                <text x="780" y="455" font-size="10" fill="#000">0..*</text>
                
                <!-- Multiplicity labels -->
                <text x="290" y="425" font-size="10" fill="#000">1</text>
                <text x="690" y="425" font-size="10" fill="#000">1</text>
                <text x="1090" y="425" font-size="10" fill="#000">1</text>
            </svg>
            <div class="button-container">
                <button class="download-btn" onclick="downloadSVG('class-diagram', 'DropMe_Class_Diagram')">Download Class Diagram</button>
            </div>
        </div>
        
        <div class="description">
            <strong>Class Diagram Description:</strong> This diagram shows the main classes in the DropMe system and their relationships. The User class serves as a parent class for Passenger, Driver, and CustomerAgent. Key relationships include passengers creating ride requests, drivers being assigned to trips, and trips being associated with locations and ratings. The layout is organized to minimize line crossings.
        </div>

        <h2>System Analysis Summary</h2>
        <div style="font-size: 12pt; text-align: justify; margin: 20px 0;">
            <p><strong>System Overview:</strong> The DropMe taxi booking system is designed to facilitate efficient ride-hailing services through a mobile application. The system connects passengers with available drivers through a centralized customer agent who manages ride assignments.</p>
            
            <p><strong>Key Features:</strong></p>
            <ul style="text-align: left;">
                <li>User registration and authentication for passengers and drivers</li>
                <li>Real-time ride request processing and taxi availability checking</li>
                <li>Automated taxi assignment through customer agents</li>
                <li>Notification system for all parties involved</li>
                <li>Payment processing and trip completion tracking</li>
                <li>Driver rating and feedback system for quality assurance</li>
                <li>Trip history and location tracking capabilities</li>
            </ul>
            
            <p><strong>System Benefits:</strong> The proposed system addresses DropMe's competitive challenges by providing a comprehensive digital solution that streamlines operations, improves customer experience, and enables efficient fleet management. The modular design ensures scalability and maintainability for future enhancements.</p>
        </div>

        <div class="page-number">
            Page 1 of 1 | DropMe System Analysis and Design Report
        </div>
    </div>

    <script>
        function downloadSVG(svgId, filename) {
            const svg = document.getElementById(svgId);
            const svgData = new XMLSerializer().serializeToString(svg);
            
            // Create a canvas element
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Set canvas size based on SVG viewBox
            const viewBox = svg.getAttribute('viewBox').split(' ');
            canvas.width = parseInt(viewBox[2]);
            canvas.height = parseInt(viewBox[3]);
            
            // Create an image element
            const img = new Image();
            
            img.onload = function() {
                // Clear canvas with white background
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                
                // Draw the SVG image onto the canvas
                ctx.drawImage(img, 0, 0);
                
                // Convert canvas to PNG and download
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename + '.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }, 'image/png');
            };
            
            // Convert SVG to data URL
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            img.src = url;
        }
        
        // Add some styling for better print layout
        window.addEventListener('beforeprint', function() {
            document.body.style.fontSize = '10pt';
        });
        
        window.addEventListener('afterprint', function() {
            document.body.style.fontSize = '';
        });
    </script>
</body>
</html>