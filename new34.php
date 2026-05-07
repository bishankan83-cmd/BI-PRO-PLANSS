<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Malcolm Lismore Photography</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    header {
      background-color: #333;
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    nav {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }
    nav a {
      color: #333;
      text-decoration: none;
      margin: 0 10px;
    }
    .hero {
      background-image: url('path/to/hero-image.jpg');
      background-size: cover;
      background-position: center;
      height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #fff;
    }
    .hero h1 {
      font-size: 3em;
      margin: 0;
    }
    .gallery {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }
    .gallery img {
      width: 300px;
      height: 200px;
      margin: 10px;
      object-fit: cover;
    }
  </style>
</head>
<body>

  <header>
    <h1>Malcolm Lismore Photography</h1>
    <nav>
      <a href="#about">About</a>
      <a href="#prices">Prices</a>
      <a href="#gallery">Gallery</a>
      <a href="#contact">Contact</a>
    </nav>
  </header>

  <div class="hero">
    <h1>Welcome to Malcolm Lismore Photography</h1>
  </div>

  <section id="about">
    <h2>About Malcolm</h2>
    <p>Placeholder text about Malcolm and his passion for photography.</p>
  </section>

  <section id="prices">
    <h2>Prices</h2>
    <p>Placeholder text about pricing for Malcolm's photography services.</p>
  </section>

  <section id="gallery">
    <h2>Gallery</h2>
    <div class="gallery">
      <img src="path/to/image1.jpg" alt="Image 1">
      <img src="path/to/image2.jpg" alt="Image 2">
      <!-- Add more images -->
    </div>
  </section>

  <section id="contact">
    <h2>Contact</h2>
    <p>Fill out the form below to contact Malcolm for bookings and inquiries.</p>
    <form action="submit_enquiry.php" method="POST">
      <input type="text" name="name" placeholder="Your Name" required><br><br>
      <input type="email" name="email" placeholder="Your Email" required><br><br>
      <textarea name="message" placeholder="Your Message" rows="4" required></textarea><br><br>
      <button type="submit">Send Message</button>
    </form>
  </section>

</body>
</html>
