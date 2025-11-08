<?php
include "backend/db_connect.php";

// 🔒 Ensure user logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

// 🧠 Fetch trusted contact email
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, trusted_email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$user_name = $user['name'];
$trusted_email = $user['trusted_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SOS — SafeSpace</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <!-- ✅ KEEPING HEADER AS IT IS -->
  <header class="header">
    <div class="container">
      <div class="logo">
        <div class="mark">SS</div>
        <div>
          <div style="font-weight:700">SafeSpace</div>
          <div style="font-size:12px;color:var(--muted)">SOS Alert System</div>
        </div>
      </div>

      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="report.html">Report</a>
        <a href="map.html">Map</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="backend/logout.php" class="cta">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container center" style="height:80vh;">
    <div class="glass-card" style="text-align:center;max-width:500px;">
      <h2>🚨 Emergency SOS</h2>
      <p class="small" style="margin-bottom:12px;">
        Press the button below to send an emergency alert to your trusted contact.
      </p>

      <?php if (!empty($trusted_email)): ?>
        <button id="sosButton" class="btn cta" style="font-size:22px;padding:18px 36px;background:red;">
          🚨 Trigger SOS
        </button>
        <p id="sosStatus" style="margin-top:10px;color:var(--muted)"></p>
      <?php else: ?>
        <p style="color:orange;">⚠️ Please add a trusted contact in your dashboard first.</p>
      <?php endif; ?>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <div>© SafeSpace — Built by Rohan</div>
    </div>
  </footer>

 <script>
  const btn = document.getElementById("sosButton");
  const status = document.getElementById("sosStatus");

  if (btn) {
    btn.addEventListener("click", async () => {
      status.innerText = "⏳ Getting your location...";
      status.style.color = "orange";

      // ✅ Step 1: Ask for location
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (pos) => {
          const latitude = pos.coords.latitude;
          const longitude = pos.coords.longitude;
          const locationLink = `https://www.google.com/maps?q=${latitude},${longitude}`;

          status.innerText = "📡 Sending SOS...";

          try {
            const response = await fetch("http://localhost:5000/send-sos", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                userName: "<?php echo htmlspecialchars($user_name); ?>",
                trustedEmail: "<?php echo htmlspecialchars($trusted_email); ?>",
                location: locationLink
              })
            });

            const data = await response.json();
            status.innerText = data.message;
            status.style.color = data.success ? "lime" : "red";
          } catch (error) {
            console.error(error);
            status.innerText = "❌ Network error — SOS failed.";
            status.style.color = "red";
          }
        }, (err) => {
          console.error(err);
          status.innerText = "❌ Location permission denied.";
          status.style.color = "red";
        });
      } else {
        status.innerText = "❌ Geolocation not supported by browser.";
        status.style.color = "red";
      }
    });
  }
</script>


</body>
</html>
