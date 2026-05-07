<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranbika Restaurant Menu</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Lato:wght@400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Lato', sans-serif;
            background: linear-gradient(135deg, #3d1f0f 0%, #5c2e13 50%, #3d1f0f 100%);
            padding: 20px;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: #FFF8F0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            border-radius: 8px;
            overflow: hidden;
            border: 4px solid #D2691E;
        }
        
        .header {
            background: linear-gradient(135deg, #2b1810 0%, #3d1f0f 100%);
            color: #D4AF37;
            padding: 25px 30px;
            text-align: center;
            border-bottom: 6px solid #D2691E;
            position: relative;
        }
        
        .logo-upload-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            position: relative;
            border-radius: 50%;
            border: 4px solid #D2691E;
            background: #2b1810;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(210, 105, 30, 0.5);
        }
        
        .logo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        
        .logo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3d1f0f, #5c2e13);
            color: #D2691E;
            font-size: 3em;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .logo-placeholder:hover {
            background: linear-gradient(135deg, #5c2e13, #D2691E);
            transform: scale(1.05);
        }
        
        .logo-placeholder span {
            font-size: 0.3em;
            margin-top: 5px;
        }
        
        .logo-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }
        
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3em;
            letter-spacing: 8px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.6);
            color: #D2691E;
            margin: 0;
        }
        
        .tagline {
            font-size: 1.3em;
            font-style: italic;
            letter-spacing: 4px;
            color: #D4AF37;
            margin-top: 8px;
        }
        
        .decorative-line {
            width: 180px;
            height: 2px;
            background: linear-gradient(to right, transparent, #D2691E, #D4AF37, #D2691E, transparent);
            margin: 15px auto;
        }
        
        .content { padding: 30px 25px; background: linear-gradient(to bottom, #FFF8F0, #FAEBD7); }
        
        .section {
            margin-bottom: 25px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 15px rgba(61, 31, 15, 0.1);
            border: 2px solid #D2691E;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6em;
            color: #3d1f0f;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px double #D2691E;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 900;
        }
        
        .menu-items {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            column-gap: 15px;
        }
        
        .menu-item {
            padding: 6px 0 6px 22px;
            font-size: 0.85em;
            color: #3d1f0f;
            position: relative;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .menu-item:hover {
            border-left-color: #D2691E;
            padding-left: 26px;
        }
        
        .menu-item::before { content: '🍴'; position: absolute; left: 0; font-size: 0.9em; }
        
        .item-name { flex: 1; }
        
        .item-price {
            font-weight: 700;
            color: #D2691E;
            margin-left: 10px;
            white-space: nowrap;
        }
        
        .item-weight {
            font-size: 0.85em;
            color: #8B4513;
            font-style: italic;
            margin-left: 5px;
        }
        
        .subsection { margin-bottom: 20px; }
        
        .subsection-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2em;
            color: #5c2e13;
            margin-bottom: 12px;
            padding-left: 22px;
            position: relative;
        }
        
        .subsection-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 15px;
            height: 3px;
            background: linear-gradient(to right, #D2691E, #D4AF37);
        }
        
        .food-images {
            display: none;
        }
        
        .footer {
            background: linear-gradient(135deg, #2b1810, #3d1f0f);
            padding: 20px;
            text-align: center;
            color: #D4AF37;
            border-top: 6px solid #D2691E;
        }
        
        .footer-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iNDgiIGZpbGw9IiMyYjE4MTAiIHN0cm9rZT0iI0QyNjkxRSIgc3Ryb2tlLXdpZHRoPSIzIi8+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iNDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI0QyNjkxRSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1kYXNoYXJyYXk9IjIgMiIvPjx0ZXh0IHg9IjUwIiB5PSI2MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iI0QyNjkxRSIgZm9udC1mYW1pbHk9Ikdlb3JnaWEsIHNlcmlmIiBmb250LXNpemU9IjQwIiBmb250LXdlaWdodD0iYm9sZCI+UjwvdGV4dD48L3N2Zz4=') center/contain no-repeat;
        }
        
        .footer-brand {
            font-size: 1.3em;
            color: #D2691E;
            letter-spacing: 4px;
            font-family: 'Playfair Display', serif;
            margin-bottom: 5px;
        }
        
        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #5c2e13, #D2691E);
            color: white;
            border: 3px solid #D2691E;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1em;
            cursor: pointer;
            font-weight: 700;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        .print-button:hover { 
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }
        
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            
            body { 
                background: white; 
                padding: 0;
                margin: 0;
            }
            
            .container { 
                box-shadow: none; 
                border: none;
                max-width: 100%;
                margin: 0;
                border-radius: 0;
            }
            
            .print-button { display: none; }
            
            .logo-placeholder span {
                display: none;
            }
            
            .logo-input {
                display: none;
            }
            
            .section {
                page-break-inside: avoid;
                margin-bottom: 15px;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-upload-container">
                <input type="file" class="logo-input" accept="image/*" id="logoUpload">
                <img src="" alt="Restaurant Logo" class="logo" id="logoImage">
                <div class="logo-placeholder" id="logoPlaceholder">
                    📷
                    <span>Click to Upload</span>
                </div>
            </div>
            <div class="decorative-line"></div>
            <h1>RANBIKA</h1>
            <div class="tagline">Simply Delicious</div>
            <div class="decorative-line"></div>
        </div>
        
        <div class="content">
            <div class="section">
                <h2 class="section-title">Beverages</h2>
                <div class="menu-items">
                    <div class="menu-item"><span class="item-name">King Coconut</span><span class="item-price">Rs. 250</span></div>
                    <div class="menu-item"><span class="item-name">Papaya Juice</span><span class="item-price">Rs. 350</span></div>
                    <div class="menu-item"><span class="item-name">Watermelon Juice</span><span class="item-price">Rs. 350</span></div>
                    <div class="menu-item"><span class="item-name">Mango Juice</span><span class="item-price">Rs. 400</span></div>
                    <div class="menu-item"><span class="item-name">Lime Juice</span><span class="item-price">Rs. 450</span></div>
                    <div class="menu-item"><span class="item-name">Mojito</span><span class="item-price">Rs. 500</span></div>
                    <div class="menu-item"><span class="item-name">Sprite</span><span class="item-price">Rs. 300</span></div>
                    <div class="menu-item"><span class="item-name">Coca-Cola</span><span class="item-price">Rs. 300</span></div>
                    <div class="menu-item"><span class="item-name">Water</span><span class="item-price">Rs. 200</span></div>
                    <div class="menu-item"><span class="item-name">Soda</span><span class="item-price">Rs. 250</span></div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Soup</h2>
                <div class="menu-items">
                    <div class="menu-item"><span class="item-name">Vegetable Soup</span><span class="item-price">Rs. 700</span></div>
                    <div class="menu-item"><span class="item-name">Chicken Soup</span><span class="item-price">Rs. 850</span></div>
                    <div class="menu-item"><span class="item-name">Fish Soup</span><span class="item-price">Rs. 850</span></div>
                    <div class="menu-item"><span class="item-name">Seafood Soup</span><span class="item-price">Rs. 950</span></div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Appetizer</h2>
                <div class="menu-items">
                    <div class="menu-item"><span class="item-name">Normal Prawns<span class="item-weight">(250g)</span></span><span class="item-price">Rs. 1,200</span></div>
                    <div class="menu-item"><span class="item-name">Battered Prawns<span class="item-weight">(250g)</span></span><span class="item-price">Rs. 1,500</span></div>
                    <div class="menu-item"><span class="item-name">Crabs<span class="item-weight">(250g)</span></span><span class="item-price">Rs. 1,350</span></div>
                    <div class="menu-item"><span class="item-name">Cuttle Fish<span class="item-weight">(250g)</span></span><span class="item-price">Rs. 1,300</span></div>
                    <div class="menu-item"><span class="item-name">Grilled Fish<span class="item-weight">(300g)</span></span><span class="item-price">Rs. 1,100</span></div>
                    <div class="menu-item"><span class="item-name">BBQ Fish<span class="item-weight">(300g)</span></span><span class="item-price">Rs. 1,300</span></div>
                    <div class="menu-item"><span class="item-name">Sri Lankan Omelet</span><span class="item-price">Rs. 400</span></div>
                    <div class="menu-item"><span class="item-name">Potato Chips</span><span class="item-price">Rs. 450</span></div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Salads</h2>
                <div class="menu-items">
                    <div class="menu-item"><span class="item-name">Coleslaw</span><span class="item-price">Rs. 450</span></div>
                    <div class="menu-item"><span class="item-name">Chicken Salad</span><span class="item-price">Rs. 650</span></div>
                    <div class="menu-item"><span class="item-name">Fish Salad</span><span class="item-price">Rs. 650</span></div>
                    <div class="menu-item"><span class="item-name">Egg Salad</span><span class="item-price">Rs. 550</span></div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Main Dishes</h2>
                
                <div class="subsection">
                    <h3 class="subsection-title">Fried Rice</h3>
                    <div class="menu-items">
                        <div class="menu-item"><span class="item-name">Vegetable Fried Rice</span><span class="item-price">Rs. 850</span></div>
                        <div class="menu-item"><span class="item-name">Chicken Fried Rice</span><span class="item-price">Rs. 1,000</span></div>
                        <div class="menu-item"><span class="item-name">Seafood Fried Rice</span><span class="item-price">Rs. 1,100</span></div>
                        <div class="menu-item"><span class="item-name">Mixed Fried Rice</span><span class="item-price">Rs. 1,200</span></div>
                    </div>
                </div>
                
                <div class="subsection">
                    <h3 class="subsection-title">Noodles</h3>
                    <div class="menu-items">
                        <div class="menu-item"><span class="item-name">Vegetable Noodles</span><span class="item-price">Rs. 850</span></div>
                        <div class="menu-item"><span class="item-name">Chicken Noodles</span><span class="item-price">Rs. 950</span></div>
                        <div class="menu-item"><span class="item-name">Seafood Noodles</span><span class="item-price">Rs. 1,000</span></div>
                    </div>
                </div>
                
                <div class="subsection">
                    <h3 class="subsection-title">Sri Lankan Special - Rice and Curry</h3>
                    <div class="menu-items">
                        <div class="menu-item"><span class="item-name">Rice and Curry with Chicken</span><span class="item-price">Rs. 950</span></div>
                        <div class="menu-item"><span class="item-name">Rice and Curry with Vegetables</span><span class="item-price">Rs. 850</span></div>
                        <div class="menu-item"><span class="item-name">Rice and Curry with Seafood</span><span class="item-price">Rs. 1,000</span></div>
                    </div>
                </div>
                
                <div class="subsection">
                    <h3 class="subsection-title">Traditional Dishes</h3>
                    <div class="menu-items">
                        <div class="menu-item"><span class="item-name">Pol Roti with Lunumiris</span><span class="item-price">Rs. 650</span></div>
                        <div class="menu-item"><span class="item-name">Pol Roti with Polos Curry</span><span class="item-price">Rs. 700</span></div>
                        <div class="menu-item"><span class="item-name">Pol Roti with Chicken Curry</span><span class="item-price">Rs. 800</span></div>
                        <div class="menu-item"><span class="item-name">Jack Fruit with Coconut Sambol</span><span class="item-price">Rs. 650</span></div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title">Dessert</h2>
                <div class="menu-items">
                    <div class="menu-item"><span class="item-name">Fruit Salad</span><span class="item-price">Rs. 500</span></div>
                    <div class="menu-item"><span class="item-name">Ice Cream</span><span class="item-price">Rs. 600</span></div>
                    <div class="menu-item"><span class="item-name">Mixed Fruit Plate</span><span class="item-price">Rs. 500</span></div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-logo"></div>
            <div class="footer-brand">RANBIKA RESTAURANTS</div>
            <div class="tagline">Simply Delicious</div>
            <div class="decorative-line"></div>
        </div>
    </div>
    
    <button class="print-button" onclick="window.print()">📄 DOWNLOAD PDF</button>
    
    <script>
        // Logo upload functionality
        const logoUpload = document.getElementById('logoUpload');
        const logoImage = document.getElementById('logoImage');
        const logoPlaceholder = document.getElementById('logoPlaceholder');
        
        logoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    logoImage.src = event.target.result;
                    logoImage.style.display = 'block';
                    logoPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Click on placeholder to trigger file input
        logoPlaceholder.addEventListener('click', function() {
            logoUpload.click();
        });
    </script>
</body>
</html>