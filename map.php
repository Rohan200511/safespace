<?php
include "backend/db_connect.php";
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$user_name = $_SESSION['user_name'];

// ✅ Fetch reports (with coordinates)
$sql = "SELECT id, type, description, status, latitude, longitude, created_at FROM reports WHERE latitude IS NOT NULL AND longitude IS NOT NULL ORDER BY created_at DESC";
$result = $conn->query($sql);
$reports = [];
while ($row = $result->fetch_assoc()) {
  $reports[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Safety Map — SafeSpace</title>
  <link rel="stylesheet" href="css/style.css" />

  <!-- ✅ Leaflet Core -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- ✅ Leaflet Routing Machine -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
  <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

  <style>
    html, body {
      margin: 0;
      padding: 0;
      background: #f5f8ff;
      color: #333;
      font-family: "Poppins", sans-serif;
      scroll-behavior: smooth;
    }

    header.header {
      background: rgba(255,255,255,0.95);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    main {
      min-height: 100vh;
      padding: 30px 20px;
    }

    /* ✅ Map full width, scrollable down */
    #map {
      width: 100%;
      height: 80vh;
      border-radius: 16px;
      margin-top: 14px;
      box-shadow: 0 0 20px rgba(0,0,0,0.25);
    }

    .search-bar, .route-panel {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 10px;
      flex-wrap: wrap;
    }

    .search-bar input, .route-panel input {
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      outline: none;
      background: #fff;
      color: #333;
      width: 250px;
    }

    .leaflet-popup-content {
      color: #222;
      font-size: 14px;
    }

    footer.footer {
      background: #007bff;
      color: white;
      text-align: center;
      padding: 14px 0;
      box-shadow: 0 -3px 12px rgba(0,0,0,0.15);
      font-size: 14px;
    }

    footer.footer a {
      color: #fff;
      text-decoration: underline;
    }

    .leaflet-marker-icon.pulse {
      animation: pulse 2s infinite;
      filter: drop-shadow(0 0 6px rgba(255, 50, 50, 0.8));
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.3); }
      100% { transform: scale(1); }
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
      <div class="logo" style="display:flex;align-items:center;gap:8px;">
        <div class="mark" style="background:#007bff;color:#fff;padding:8px 10px;border-radius:6px;">SS</div>
        <div>
          <div style="font-weight:700;color:#007bff;">SafeSpace</div>
          <div style="font-size:12px;color:gray;">Daylight Safety Map</div>
        </div>
      </div>
      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="report.php">Report</a>
        <a href="sos.php">SOS</a>
        <a href="dashboard.php">Dashboard</a>
      </nav>
      <button class="btn" style="background:#007bff;color:#fff;" onclick="location.href='backend/logout.php'">Logout</button>
    </div>
  </header>

  <main class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h2 style="color:#007bff;">🗺️ SafeSpace — Real-Time Map (Day Mode)</h2>
      <div class="search-bar">
        <input id="searchBox" type="text" placeholder="Search report type or status...">
        <button id="searchBtn" class="btn cta">Search</button>
      </div>
    </div>
    <p class="small" style="color:gray;">Find reports, view safe zones, and get your safest route — in bright daylight view.</p>

    <div class="route-panel">
      <input id="destInput" class="input" type="text" placeholder="Enter destination (e.g. Connaught Place)">
      <button id="routeBtn" class="btn cta">Find Safe Route</button>
    </div>

    <div id="map"></div>
  </main>

  

  <script>
  // ✅ Initialize daylight map
  const map = L.map("map").setView([28.6139, 77.2090], 11); // Center on Delhi NCR

  // ☀️ Bright OpenStreetMap tiles
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
  }).addTo(map);

  // ✅ Marker Icons
  const redIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/535/535239.png",
    iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -28], className: "pulse"
  });
  const greenIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/535/535183.png",
    iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -28]
  });
  const blueIcon = L.icon({
    iconUrl: "https://cdn-icons-png.flaticon.com/512/447/447031.png",
    iconSize: [30, 30], iconAnchor: [15, 30], popupAnchor: [0, -28]
  });

  // ✅ Add Verified Safe Zones (Delhi, Haryana, NCR)
  const safeZones = [
    // 🌆 Delhi
    { name: "Connaught Place Police Station", coords: [28.6328, 77.2197] },
    { name: "AIIMS Trauma Centre", coords: [28.5672, 77.2091] },
    { name: "India Gate Patrol Booth", coords: [28.6129, 77.2295] },
    { name: "Delhi Women Helpline Center", coords: [28.6517, 77.2219] },
    { name: "Vasant Kunj Police Station", coords: [28.5271, 77.1505] },
    { name: "Saket District Police HQ", coords: [28.5244, 77.2050] },

    // 🏙️ Noida / Greater Noida (UP)
    { name: "Sector 62 Police Chowki", coords: [28.6248, 77.3683] },
    { name: "Knowledge Park III Security Office", coords: [28.4743, 77.4996] },
    { name: "Noida City Centre Safe Point", coords: [28.5746, 77.3566] },

    // 🌇 Gurugram (Haryana)
    { name: "Gurugram Sector 29 Police Station", coords: [28.4669, 77.0670] },
    { name: "DLF CyberHub Patrol Unit", coords: [28.4953, 77.0890] },
    { name: "Civil Hospital Gurugram", coords: [28.4637, 77.0319] },

    // 🏥 Faridabad (Haryana)
    { name: "Faridabad Sector 15 Police Station", coords: [28.3933, 77.3165] },
    { name: "B.K. Hospital Emergency Center", coords: [28.3968, 77.3032] },
    { name: "Surajkund Police Outpost", coords: [28.4765, 77.2741] },
  ];

  safeZones.forEach(zone => {
    L.marker(zone.coords, { icon: greenIcon })
      .addTo(map)
      .bindPopup(`<b>${zone.name}</b><br>🟢 Verified Safe Zone`);
  });

  // ✅ Reports from Database
  const reports = <?php echo json_encode($reports); ?>;
  let reportMarkers = [];

  reports.forEach(r => {
    if (!r.latitude || !r.longitude) return;
    const icon = r.status === "Resolved" ? greenIcon : redIcon;
    const marker = L.marker([r.latitude, r.longitude], { icon })
      .addTo(map)
      .bindPopup(`
        <b>${r.type}</b><br>
        <i>${r.description}</i><br>
        <small>Status: ${r.status}</small><br>
        <small>${new Date(r.created_at).toLocaleString()}</small>
      `);

    if (r.status !== "Resolved") {
      L.circle([r.latitude, r.longitude], {
        radius: 150,
        color: "red",
        fillColor: "#ff4b4b",
        fillOpacity: 0.25
      }).addTo(map);
    }
    reportMarkers.push({ marker, data: r });
  });

  // ✅ Dynamic Zone Coloring
  const dangerCounts = {};
  reports.forEach(r => {
    if (!r.latitude || !r.longitude) return;
    const lat = Math.round(r.latitude * 100) / 100;
    const lon = Math.round(r.longitude * 100) / 100;
    const key = `${lat},${lon}`;
    if (!dangerCounts[key]) dangerCounts[key] = { danger: 0, safe: 0, lat, lon };
    if (r.status === "Resolved") dangerCounts[key].safe++;
    else dangerCounts[key].danger++;
  });

  Object.values(dangerCounts).forEach(zone => {
    const total = zone.danger + zone.safe;
    const dangerRatio = total === 0 ? 0 : zone.danger / total;

    let color = "green", fill = "#00ff88", label = "🟩 Safe Zone";
    if (dangerRatio > 0.6) { color = "red"; fill = "#ff4b4b"; label = "🟥 Danger Zone"; }
    else if (dangerRatio > 0.3) { color = "orange"; fill = "#ffaa33"; label = "🟧 Caution Zone"; }

    L.circle([zone.lat, zone.lon], {
      radius: 300,
      color,
      fillColor: fill,
      fillOpacity: 0.35,
      weight: 2
    })
      .addTo(map)
      .bindPopup(`${label}<br>🚨 Reports: ${zone.danger}<br>✅ Resolved: ${zone.safe}`);
  });

  // ✅ User location
  let userLocation = null;
  map.locate({ setView: true, maxZoom: 14 });
  map.on("locationfound", e => {
    userLocation = e.latlng;
    L.marker(e.latlng, { icon: blueIcon })
      .addTo(map)
      .bindPopup("📍 You are here")
      .openPopup();
    L.circle(e.latlng, { radius: 150, color: "#007bff", opacity: 0.4 }).addTo(map);
  });

  // ✅ Search
  document.getElementById("searchBtn").addEventListener("click", () => {
    const query = document.getElementById("searchBox").value.trim().toLowerCase();
    if (!query) return alert("Enter a keyword to search reports!");
    let found = false;
    reportMarkers.forEach(obj => {
      const { data } = obj;
      if (data.type.toLowerCase().includes(query) || data.status.toLowerCase().includes(query)) {
        map.setView([data.latitude, data.longitude], 15);
        obj.marker.openPopup();
        found = true;
      }
    });
    if (!found) alert("No reports found for: " + query);
  });

  // ✅ Route Finder
  document.getElementById("routeBtn").addEventListener("click", () => {
    const dest = document.getElementById("destInput").value.trim();
    if (!dest) return alert("Enter destination name.");
    if (!userLocation) return alert("Please allow location access first!");

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(dest)}`)
      .then(res => res.json())
      .then(data => {
        if (data.length === 0) return alert("Destination not found!");
        const destCoords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
        L.Routing.control({
          waypoints: [userLocation, L.latLng(destCoords)],
          routeWhileDragging: false,
          lineOptions: { styles: [{ color: "#007bff", weight: 5, opacity: 0.9 }] },
          createMarker: function() { return null; }
        }).addTo(map);
      })
      .catch(() => alert("Error fetching destination!"));
  });
</script>

</body>
</html>
