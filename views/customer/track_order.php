<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Track Order #<?= htmlspecialchars($order_id) ?></title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    body { font-family: sans-serif; margin: 0; padding: 12px; }
    #map{height:70vh; border-radius:8px;}
    #status { margin: 8px 0; font-weight: bold; }
  </style>
</head>
<body>
  <div style="display:flex;align-items:center;gap:10px;justify-content:space-between;margin-bottom:8px;">
    <button
      type="button"
      style="padding:8px 12px;border-radius:6px;border:none;background:#ff6a00;color:#fff;font-weight:700;cursor:pointer;"
      onclick="(window.parent && window.parent.closeTrack) ? window.parent.closeTrack() : history.back();"
    >
      ← Back
    </button>
    <h2 style="margin:0;">Order #<?= htmlspecialchars($order_id) ?></h2>
    <span></span>
  </div>
  
  
  
  
  
  
  
  
  <div id="status">Loading...</div>
  <div id="map"></div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    const orderId = <?= $order_id ?: 0 ?>;
    const map = L.map('map').setView([16.8409,96.1735], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    let driverMarker = null;
    let customerMarker = null;
    let routeLine = null;

    // Distinct icons
    const riderIcon = L.icon({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png'
    });
    const customerIcon = L.divIcon({
      className: 'customer-icon',
      html: '<div style="width:14px;height:14px;border-radius:50%;background:#ff6a00;border:2px solid white;box-shadow:0 0 6px rgba(0,0,0,.3);"></div>',
      iconSize: [14, 14]
    });
    let timer = null;

    async function update() {
      try {
        const res = await fetch('../../api/get_driver_location.php?order_id=' + orderId);
        const j = await res.json();
        if (!j.success) { document.getElementById('status').textContent = 'No tracking data yet.'; return; }
        const d = j.data;
        document.getElementById('status').textContent = 'Status: ' + (d.order_status || 'pending') + (d.rider_name ? ' — Rider: ' + d.rider_name : '');
        if (d.lat && d.lng) {
          const lat = parseFloat(d.lat);
          const lng = parseFloat(d.lng);
          if (!driverMarker) {
            driverMarker = L.marker([lat, lng], { icon: riderIcon, title: 'Rider' }).addTo(map).bindPopup('Rider');
            map.setView([lat, lng], 14);
          } else {
            driverMarker.setLatLng([lat, lng]);
            // pan smoothly
            map.panTo([lat, lng]);
          }
        }

        // Customer marker
        if (d.customer_lat && d.customer_lng) {
          const clat = parseFloat(d.customer_lat);
          const clng = parseFloat(d.customer_lng);
          if (!customerMarker) {
            customerMarker = L.marker([clat, clng], { icon: customerIcon, title: 'Customer' }).addTo(map).bindPopup('Customer');
          } else {
            customerMarker.setLatLng([clat, clng]);
          }
        }

        // Draw/update orange route polyline between rider and customer
        if (driverMarker && customerMarker) {
          const riderPos = driverMarker.getLatLng();
          const custPos = customerMarker.getLatLng();
          const points = [riderPos, custPos];
          if (!routeLine) {
            routeLine = L.polyline(points, { color: '#ff6a00', weight: 5, opacity: 0.8 }).addTo(map);
          } else {
            routeLine.setLatLngs(points);
          }
        }
        if (d.order_status === 'delivered') {
          document.getElementById('status').textContent += ' — Delivered. Tracking stopped.';
          if (timer) clearInterval(timer);
        }
      } catch (err) {
        console.error(err);
        document.getElementById('status').textContent = 'Network error while fetching tracking.';
      }
    }

    if (orderId > 0) {
      update();
      timer = setInterval(update, 5000);
    } else {
      document.getElementById('status').textContent = 'Invalid order id.';
    }
  </script>
</body>
</html>
