<?php
include "backend/db_connect.php";

// 🔒 Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user_id = $_SESSION['user_id'];
  $type = trim($_POST["type"]);
  $location = trim($_POST["location"]);
  $description = trim($_POST["description"]);

  // 🖼️ Handle image upload (optional)
  $image_path = null;
  if (!empty($_FILES["image"]["name"])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    $image_path = $target_file;
  }

  // 💾 Insert report into DB
  $stmt = $conn->prepare("INSERT INTO reports (user_id, type, location, description, image_path) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("issss", $user_id, $type, $location, $description, $image_path);

  if ($stmt->execute()) {
    echo "<script>alert('✅ Report Submitted Successfully!'); window.location.href='dashboard.php';</script>";
  } else {
    echo "<script>alert('❌ Failed to submit report. Try again.');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report — SafeSpace</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="header">
    <div class="container">
      <div class="logo">
        <div class="mark">SS</div>
        <div>
          <div style="font-weight:700">SafeSpace</div>
          <div style="font-size:12px;color:var(--muted)">Report an Incident</div>
        </div>
      </div>
      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="map.html">Map</a>
        <a href="sos.php">SOS</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="backend/logout.php" class="cta">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container center" style="min-height:80vh;padding-top:40px;">
    <div class="glass-card" style="width:540px;max-width:96%;">
      <h2>📝 Report an Incident</h2>
      <p class="small">Provide as much detail as possible. You can remain anonymous.</p>

      <form method="POST" enctype="multipart/form-data" class="form">
        <select class="input" name="type" required>
          <option value="">Select Incident Type</option>
          <option value="Harassment">Harassment</option>
          <option value="Assault">Assault</option>
          <option value="Stalking">Stalking</option>
          <option value="Theft">Theft</option>
          <option value="Other">Other</option>
        </select>

        <div style="display:flex;gap:8px;">
          <input class="input" type="text" name="location" id="location" placeholder="Enter location" required>
          <button type="button" class="btn" id="getLoc">📍 Get My Location</button>
        </div>

        <textarea class="input" name="description" rows="4" placeholder="Describe the incident..." required></textarea>

        <label class="small">Attach Image (optional):</label>
        <input class="input" type="file" name="image" accept="image/*">

        <button class="btn cta" type="submit">Submit Report</button>
      </form>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <div>© SafeSpace — Built by Rohan</div>
    </div>
  </footer>

  <script>
    // 📍 Fill location using GPS
    document.getElementById("getLoc").addEventListener("click", () => {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          pos => {
            const { latitude, longitude } = pos.coords;
            document.getElementById("location").value = `https://www.google.com/maps?q=${latitude},${longitude}`;
          },
          () => alert("❌ Failed to fetch location.")
        );
      } else {
        alert("❌ Geolocation not supported by your browser.");
      }
    });
  </script>
</body>
</html>
