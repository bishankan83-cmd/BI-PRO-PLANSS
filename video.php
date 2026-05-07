<?php
// Advertisement content data with real images
$ad_scenes = [
    [
        'time' => '0 – 3 seconds',
        'scene' => 'Forklift glides across the factory floor on shiny, durable solid tires.',
        'text' => 'We stand by our quality.',
        'audio' => 'Confident, upbeat background music begins.',
        'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'alt' => 'Modern forklift operating in clean warehouse facility'
    ],
    [
        'time' => '3 – 6 seconds',
        'scene' => 'A customer shows a worn or damaged tire to a service rep. The rep smiles, nods, and gestures positively.',
        'voiceover' => 'Got a complaint?',
        'text' => 'No problem.',
        'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'alt' => 'Professional service representative helping customer'
    ],
    [
        'time' => '6 – 10 seconds',
        'scene' => 'The rep hands over a brand-new tire. Customer looks surprised and happy.',
        'voiceover' => 'We\'ll replace it… FREE!',
        'text' => 'Free Replacement Guarantee.',
        'image' => 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'alt' => 'New industrial tire being presented to customer'
    ],
    [
        'time' => '10 – 13 seconds',
        'scene' => 'Quick shots of tires being fitted and forklifts driving away smoothly.',
        'voiceover' => 'Your trust is our top priority.',
        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'alt' => 'Forklift tire installation and maintenance service'
    ],
    [
        'time' => '13 – 15 seconds',
        'scene' => 'Company logo appears with bold animated text.',
        'text' => 'Drive strong. We\'ve got your back.',
        'audio' => 'Music hits a strong closing beat and fades out.',
        'image' => 'https://images.unsplash.com/photo-1565793298595-6a879b1d9492?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
        'alt' => 'Professional industrial workspace with company branding'
    ]
];

// Handle download request
if (isset($_GET['download'])) {
    $filename = "forklift_tire_advertisement_storyboard.txt";
    $content = "FORKLIFT TIRE ADVERTISEMENT STORYBOARD\n";
    $content .= "=====================================\n\n";
    
    foreach ($ad_scenes as $index => $scene) {
        $content .= "SCENE " . ($index + 1) . " - " . $scene['time'] . "\n";
        $content .= "Scene: " . $scene['scene'] . "\n";
        if (isset($scene['voiceover'])) {
            $content .= "Voice-over: " . $scene['voiceover'] . "\n";
        }
        if (isset($scene['text'])) {
            $content .= "Text on screen: " . $scene['text'] . "\n";
        }
        if (isset($scene['audio'])) {
            $content .= "Audio: " . $scene['audio'] . "\n";
        }
        $content .= "Image Reference: " . $scene['image'] . "\n";
        $content .= "\n" . str_repeat("-", 50) . "\n\n";
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forklift Tire Advertisement - Premium Quality Guarantee</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #333;
            min-height: 100vh;
        }

        .hero-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.4)), 
                        url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            z-index: 2;
            max-width: 800px;
            padding: 20px;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            opacity: 0.1;
            z-index: 1;
        }

        .hero-title {
            font-size: 3.5em;
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
            margin-bottom: 15px;
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.3em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            margin-bottom: 25px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .hero-tagline {
            font-size: 2em;
            font-weight: bold;
            color: #ff6b35;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            margin: -50px auto 30px auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 3;
            max-width: 900px;
        }

        .header h2 {
            color: #1e3c72;
            font-size: 2.2em;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .header p {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 20px;
        }

        .download-btn {
            display: inline-block;
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.6);
        }

        .ad-timeline {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .scene {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            margin: 30px 0;
            padding: 25px;
            border-radius: 12px;
            border-left: 5px solid #ff6b35;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .scene:hover {
            transform: translateX(10px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .scene-content {
            flex: 1;
        }

        .scene-image {
            flex: 0 0 300px;
            text-align: center;
        }

        .scene-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .scene-image img:hover {
            transform: scale(1.05);
        }

        .scene-time {
            background: #1e3c72;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .scene-description {
            font-size: 1.1em;
            margin-bottom: 10px;
            color: #333;
        }

        .voiceover {
            background: #e8f4f8;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 3px solid #17a2b8;
        }

        .voiceover::before {
            content: "🎙️ ";
            font-weight: bold;
        }

        .text-overlay {
            background: #fff3cd;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 3px solid #ffc107;
            font-weight: bold;
        }

        .text-overlay::before {
            content: "📺 ";
        }

        .audio-note {
            background: #d1ecf1;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 3px solid #bee5eb;
            font-style: italic;
        }

        .audio-note::before {
            content: "🎵 ";
        }

        .cta-section {
            text-align: center;
            margin-top: 30px;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
        }

        .cta-section h3 {
            margin-bottom: 15px;
            font-size: 1.8em;
        }

        .features-banner {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 30px 20px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: center;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .feature-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5em;
            }
            
            .hero-subtitle {
                font-size: 1.1em;
            }
            
            .hero-tagline {
                font-size: 1.5em;
            }
            
            .container {
                padding: 10px;
            }
            
            .scene {
                flex-direction: column;
                margin: 20px 0;
                padding: 20px;
            }
            
            .scene-image {
                flex: none;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content">
            <h1 class="hero-title">🏭 PREMIUM FORKLIFT TIRES</h1>
            <p class="hero-subtitle">Professional Quality Guarantee Campaign</p>
            <div class="hero-tagline">"Drive Strong. We've Got Your Back."</div>
        </div>
    </div>

    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h2>15-Second Commercial Storyboard</h2>
            <p>Complete visual breakdown with professional imagery and production notes</p>
            <a href="?download=1" class="download-btn">📥 Download Full Storyboard</a>
            <a href="#features" class="download-btn" style="background: linear-gradient(45deg, #28a745, #20c997);">📋 View Features</a>
        </div>

        <!-- Advertisement Timeline -->
        <div class="ad-timeline">
            <h2 style="text-align: center; margin-bottom: 30px; color: #1e3c72;">🎬 Advertisement Timeline</h2>
            
            <?php foreach ($ad_scenes as $index => $scene): ?>
            <div class="scene">
                <div class="scene-content">
                    <div class="scene-time">Scene <?= $index + 1 ?> - <?= $scene['time'] ?></div>
                    
                    <div class="scene-description">
                        🎥 <strong>Scene:</strong> <?= $scene['scene'] ?>
                    </div>
                    
                    <?php if (isset($scene['voiceover'])): ?>
                    <div class="voiceover">
                        <strong>Voice-over:</strong> "<?= $scene['voiceover'] ?>"
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($scene['text'])): ?>
                    <div class="text-overlay">
                        <strong>Text on screen:</strong> "<?= $scene['text'] ?>"
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($scene['audio'])): ?>
                    <div class="audio-note">
                        <strong>Audio:</strong> <?= $scene['audio'] ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="scene-image">
                    <img src="<?= $scene['image'] ?>" alt="<?= $scene['alt'] ?>" loading="lazy">
                    <p style="font-size: 0.9em; color: #666; margin-top: 8px;"><?= $scene['alt'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Features Banner -->
        <div class="features-banner" id="features">
            <h2 style="font-size: 2.5em; margin-bottom: 20px;">🚀 Why Choose Our Tires?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">🛡️</div>
                    <h3>FREE Replacement</h3>
                    <p>No questions asked guarantee</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <h3>Premium Quality</h3>
                    <p>Industrial-grade durability</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🎯</div>
                    <h3>Expert Service</h3>
                    <p>Professional installation</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📞</div>
                    <h3>24/7 Support</h3>
                    <p>Always here when you need us</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="cta-section">
            <h3>🎬 Ready to Produce Your Commercial?</h3>
            <p style="margin-bottom: 20px; font-size: 1.1em;">Download the complete storyboard with all production details, timing, and visual references.</p>
            <a href="?download=1" class="download-btn" style="font-size: 1.2em; padding: 18px 35px;">📥 Download Complete Package</a>
        </div>
    </div>

    <script>
        // Enhanced interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animated scene reveals
            const scenes = document.querySelectorAll('.scene');
            
            scenes.forEach((scene, index) => {
                scene.style.opacity = '0';
                scene.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    scene.style.transition = 'all 0.8s ease';
                    scene.style.opacity = '1';
                    scene.style.transform = 'translateY(0)';
                }, index * 300);
            });

            // Download button effects
            const downloadBtns = document.querySelectorAll('.download-btn');
            downloadBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.95) translateY(-3px)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-3px)';
                    }, 150);
                });
            });

            // Image lazy loading with animation
            const images = document.querySelectorAll('.scene-image img');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '0';
                    this.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 100);
                });
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Parallax effect for hero banner
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const hero = document.querySelector('.hero-banner');
                if (hero) {
                    hero.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });
        });
    </script>
</body>
</html>