<?php include "backend/db_connect.php";  // starts the session?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Contact — SafeSpace</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="header">
    <div class="container">
      <div class="logo"><div class="mark">SS</div><div><div style="font-weight:700">SafeSpace</div><div style="font-size:12px;color:var(--muted)">Contact</div></div></div>
      <nav class="nav"><a href="index.html">Home</a><a href="about.html">About</a><a href="dashboard.html">Dashboard</a></nav>
      <button class="hamburger">☰</button>
    </div>
  </header>

  <main class="container" style="padding:18px 0">
    <h2>Contact Us</h2>
    <p class="small">Have questions or feedback? Send a message (mock submission).</p>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:16px;margin-top:12px">
      <div class="card">
        <form id="contactForm" class="form" action="backend/contact_submit.php" method="POST">
          <input class="input" type="text" name="name" placeholder="Your name" required>
          <input class="input" type="email" name="email" placeholder="Email" required>
          <textarea class="input" name="message" rows="6" placeholder="Your message" required></textarea>
          <div style="display:flex;justify-content:flex-end">
            <button class="btn cta" type="submit">Send Message</button>
          </div>
        </form>
      </div>

      <aside class="card">
        <h4>Contact Info</h4>
        <div style="margin-top:8px">
          <div class="small"><strong>Email</strong><div class="small" style="color:var(--muted)">support@safespace.app</div></div>
          <div style="margin-top:8px" class="small"><strong>Phone</strong><div class="small" style="color:var(--muted)">1800-XXX-XXXX</div></div>
        </div>

        <div style="margin-top:12px">
          <a href="#" class="btn">Community Guidelines</a>
        </div>
      </aside>
    </div>
  </main>

  <footer class="footer">
    <div class="container"><div class="small">We'll respond to mock messages promptly (demo)</div><div class="small">© SafeSpace</div></div>
  </footer>

  <script src="js/script.js"></script>
</body>
</html>
