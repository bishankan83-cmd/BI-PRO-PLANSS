<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRe Wesak Thorana</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #282c34;
            margin: 0;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .thorana {
            position: relative;
            width: 600px;
            height: 600px;
            text-align: center;
            overflow: hidden;
        }

        .title {
            position: absolute;
            top: 10px;
            width: 100%;
            font-size: 28px;
            font-weight: bold;
            color: #ffe600;
            text-shadow: 2px 2px 4px #000;
        }

        .arch {
            position: absolute;
            top: 50px;
            width: 100%;
            height: 80%;
            background: radial-gradient(circle, #ffe600, #ff8c00);
            border-radius: 50% / 10%;
            border: 5px solid #fff;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .decorations {
            position: absolute;
            top: 30px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
        }

        .decoration {
            width: 50px;
            height: 50px;
            background-color: #ff8c00;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(255, 140, 0, 0.5);
            animation: spin 3s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .lights {
            position: absolute;
            top: 100px;
            width: 100%;
            height: calc(100% - 150px);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            align-items: center;
            overflow: hidden;
        }

        .light {
            width: 10px;
            height: 10px;
            background-color: red;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.5);
            margin: 1px;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.3;
            }
        }

        .carousel {
            position: absolute;
            bottom: 10px;
            width: 100%;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #000;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .carousel img {
            max-height: 100px;
            border-radius: 10px;
        }

        .carousel p {
            margin: 10px 20px;
        }

        /* Style the photo */
        .photo {
            position: absolute;
            top: 120px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
            z-index: 10; /* Ensure the photo appears above other elements */
        }
    </style>
</head>
<body>
    <div class="thorana">
        <div class="title">ATIRe Wesak Thorana</div>
        <div class="arch"></div>
        <div class="decorations">
            <div class="decoration"></div>
            <div class="decoration"></div>
            <div class="decoration"></div>
        </div>
        <div class="lights" id="lights"></div>
        <!-- Photo inside the Thorana -->
        <img class="photo" src="thorana.jpg" alt="Buddhist Photo Inside Thorana">
        <!-- End of Photo -->
        <div class="carousel">
            <img src="https://via.placeholder.com/150" alt="Jataka Story Image 1">
            <p>Story of the Golden Deer</p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lightsContainer = document.getElementById('lights');
            for (let i = 0; i < 1000; i++) { // Adjust the number for more/less bulbs
                const light = document.createElement('div');
                light.classList.add('light');
                light.style.backgroundColor = ['red', 'orange', 'yellow', 'green', 'blue', 'indigo', 'violet'][i % 7];
                light.style.animationDelay = `${Math.random() * 1.5}s`;
                lightsContainer.appendChild(light);
            }

            const stories = [
                {
                    img: 'https://via.placeholder.com/150',
                    text: 'Story of the Golden Deer'
                },
                {
                    img: 'https://via.placeholder.com/150',
                    text: 'The Monkey King'
                },
                {
                    img: 'https://via.placeholder.com/150',
                    text: 'The Hare\'s Self-Sacrifice'
                }
            ];

            let currentStory = 0;
            const carouselImg = document.querySelector('.carousel img');
            const carouselText = document.querySelector('.carousel p');

            setInterval(() => {
                currentStory = (currentStory + 1) % stories.length;
                carouselImg.src = stories[currentStory].img;
                carouselText.textContent = stories[currentStory].text;
            }, 3000);
        });
    </script>
</body>
</html>
