<?php include ('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Partner</title>
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        body {
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 102, 0, 0.9)),
                url('asstes/images/partners.png') no-repeat;
            background-size: contain;
            background-position: left center;
        }

        .highlight-text {
            color: white;
            background-color: rgb(255,102,0);
            font-family: Georgia, serif;
            font-size: 24px;
            padding: 5px 10px;
            border-radius: 6px;
            text-align: center;
            margin-top: 20px;
        }
        
        .highlight-text a {
            text-decoration: none;
            color: yellow;
        }

        .containerr { 
            background: #fff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 30px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 500px; 
            margin: 20px auto;
        }
        
        h1 { text-align: center; color: #1a1a1a; margin-bottom: 10px; }
        p.subtitle { text-align: center; color: #666; margin-top: 0; margin-bottom: 30px; }
        
        /* Role Switcher */
        .role-switcher { 
            display: flex; 
            border: 1px solid #ddd; 
            border-radius: 25px; 
            margin-bottom: 30px; 
            overflow: hidden; 
        }
        
        .role-switcher label { 
            flex: 1; 
            text-align: center; 
            padding: 12px 0; 
            cursor: pointer; 
            transition: background-color 0.3s, color 0.3s; 
            font-weight: 600; 
            color: #555; 
        }
        
        .role-switcher input { display: none; }
        .role-switcher input:checked + label { 
            background-color: rgb(255,102,0); 
            color: #fff; 
        }

        /* Form Fields */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .form-group input { 
            width: 100%; 
            padding: 12px 15px; 
            border: 1px solid #ccc; 
            border-radius: 8px; 
            box-sizing: border-box; 
            transition: border-color 0.3s; 
        }
        
        .form-group input:focus { 
            border-color: rgb(255,102,0); 
            outline: none; 
        }
        
        /* Map Container */
        .map-container {
            margin: 20px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        #map {
            height: 300px;
            width: 100%;
        }
        
        .map-controls {
            background: #f9f9f9;
            padding: 15px;
            border-top: 1px solid #ddd;
        }
        
        .map-controls input {
            width: calc(100% - 100px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
        }
        
        .map-controls button {
            width: 100px;
            padding: 10px;
            background: rgb(255,102,0);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .coordinates-display {
            background: #f0f0f0;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
        
        /* File Upload */
        .file-upload {
            margin: 10px 0;
        }
        
        .file-upload input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        
        button[type="submit"] { 
            width: 100%; 
            padding: 15px; 
            background-color: rgb(255,102,0); 
            color: #fff; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background-color 0.3s; 
        }
        
        button[type="submit"]:hover { background-color: rgb(255,102,0); }
        button[type="submit"]:disabled { background-color: #ccc; cursor: not-allowed; }

        #vendor-fields, #rider-fields, #form-response { display: none; }
        #form-response { 
            margin-top: 20px; 
            padding: 15px; 
            border-radius: 8px; 
            text-align: center; 
            font-weight: bold; 
        }
        
        .success { background-color: #e4f8f0; color: #2d6a4f; }
        .error { background-color: #fbe9e9; color: #9d2525; }
        .loading { background-color: #e6f7ff; color: #0066cc; }
        /* Add to your CSS in partners.php */
.leaflet-top, .leaflet-bottom {
    z-index: 999 !important;
}

.leaflet-control-zoom {
    z-index: 1000 !important;
}

.leaflet-control {
    z-index: 1000 !important;
}
/* For the cuisine dropdown menu */
select {
    z-index: 100 !important;
    position: relative;
}

    </style>
</head>
<body>
    <div class="containerr">
        <h1>Partner with Us</h1>
        <p class="subtitle">Join our network and start growing today.</p>
        
        <form id="partner-form" enctype="multipart/form-data">
            <div class="role-switcher">
                <input type="radio" id="role-vendor" name="role" value="vendor" checked>
                <label for="role-vendor">Become a Restaurant</label>
                
                <input type="radio" id="role-rider" name="role" value="delivery">
                <label for="role-rider">Become a Rider</label>
            </div>

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>

            <!-- RESTAURANT FIELDS -->
            <div id="vendor-fields">
                <div class="form-group">
                    <label for="restaurant_name">Restaurant Name *</label>
                    <input type="text" id="restaurant_name" name="restaurant_name">
                </div>
                
               <div class="form-group">
        <label for="cuisine_type">Cuisine Type *</label>
        <select id="cuisine_type" name="cuisine_type" required>
            <option value="">Select Cuisine Type</option>
            <option value="Burmese">Burmese</option>
            <option value="Chinese">Chinese</option>
            <option value="Indian">Indian</option>
            <option value="Thai">Thai</option>
            <option value="Japanese">Japanese</option>
            <option value="Korean">Korean</option>
            <option value="Italian">Italian</option>
            <option value="Mexican">Mexican</option>
            <option value="Pizza">Pizza</option>
            <option value="Burger">Burger</option>
            <option value="Fast Food">Fast Food</option>
            <option value="Vegetarian">Vegetarian</option>
            <option value="Seafood">Seafood</option>
            <option value="BBQ">BBQ</option>
            <option value="Desserts">Desserts</option>
            <option value="Beverages">Beverages</option>
            <option value="Fusion">Fusion</option>
            <option value="Other">Other</option>
        </select>
    </div>
                
                <!-- Restaurant Location Map -->
                <div class="form-group">
                    <label>Restaurant Location *</label>
                    <div class="map-container">
                        <div id="map"></div>
                    </div>
                    <div class="coordinates-display">
                        <strong>Selected Location:</strong><br>
                        Latitude: <span id="selected_lat">Click on map</span><br>
                        Longitude: <span id="selected_lng">Click on map</span><br>
                        Address: <span id="selected_address">Click on map</span>
                    </div>
                    <input type="hidden" id="lat" name="lat">
                    <input type="hidden" id="lng" name="lng">
                    <input type="hidden" id="address" name="address">
                </div>
                
                <!-- Restaurant Logo -->
                <div class="form-group">
                    <label for="logo">Your Restaurant Image *</label>
                    <div class="file-upload">
                        <input type="file" id="logo" name="logo" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- RIDER FIELDS -->
            <div id="rider-fields">
                <div class="form-group">
                    <label for="rider_address">Your Address *</label>
                    <input type="text" id="rider_address" name="rider_address">
                </div>
            </div>

            <button type="submit" id="submit-btn">Submit Application</button>
            <div id="form-response"></div>
        </form>
    </div>
    
    <p class="highlight-text">
        By submitting this form, you agree to our <a href="terms-and-conditions.php">Terms and Conditions</a>
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Role switcher
            const roleVendorRadio = document.getElementById('role-vendor');
            const roleRiderRadio = document.getElementById('role-rider');
            const vendorFields = document.getElementById('vendor-fields');
            const riderFields = document.getElementById('rider-fields');
            const partnerForm = document.getElementById('partner-form');
            const formResponse = document.getElementById('form-response');

            // Map variables
            let map;
            let marker;
            let defaultLat = 16.8409;
            let defaultLng = 96.1735;

            // Initialize map for restaurant
            function initializeMap() {
                map = L.map('map').setView([defaultLat, defaultLng], 13);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Add click event to map
                map.on('click', function(e) {
                    setMarkerPosition(e.latlng.lat, e.latlng.lng);
                    reverseGeocode(e.latlng.lat, e.latlng.lng);
                });

                // Set default marker
                setMarkerPosition(defaultLat, defaultLng);
            }

            function setMarkerPosition(lat, lng) {
                // Update hidden fields
                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;
                
                // Update display
                document.getElementById('selected_lat').textContent = lat.toFixed(6);
                document.getElementById('selected_lng').textContent = lng.toFixed(6);
                
                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }
                
                // Add new marker
                marker = L.marker([lat, lng]).addTo(map);
                map.setView([lat, lng], 15);
            }

            function reverseGeocode(lat, lng) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            const address = data.display_name;
                            document.getElementById('selected_address').textContent = address;
                            document.getElementById('address').value = address;
                        }
                    })
                    .catch(error => {
                        console.error('Reverse geocoding error:', error);
                    });
            }

            // Toggle between vendor and rider fields
            function toggleFields() {
                if (roleVendorRadio.checked) {
                    vendorFields.style.display = 'block';
                    riderFields.style.display = 'none';
                    
                    // Initialize map when vendor is selected
                    if (!map) {
                        initializeMap();
                    }
                } else {
                    vendorFields.style.display = 'none';
                    riderFields.style.display = 'block';
                }
            }
            
            toggleFields();
            roleVendorRadio.addEventListener('change', toggleFields);
            roleRiderRadio.addEventListener('change', toggleFields);

            // Form submission
            // Change this part in the form submission:
// Form submission
partnerForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button');
    
    submitButton.textContent = 'Submitting...';
    submitButton.disabled = true;
    formResponse.style.display = 'block';
    formResponse.className = 'loading';
    formResponse.textContent = 'Submitting your application...';

    // ALWAYS use submit_partner_request.php - it handles both roles
    const apiUrl = '/foodandme/api/submit_partner_request.php';

    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        formResponse.style.display = 'block';
        formResponse.textContent = data.message;
        
        if (data.success) {
            formResponse.className = 'success';
            partnerForm.reset();
            toggleFields();
            
            // Reset map to default
            if (map) {
                setMarkerPosition(defaultLat, defaultLng);
            }
        } else {
            formResponse.className = 'error';
        }
    })
    .catch(error => {
        formResponse.style.display = 'block';
        formResponse.className = 'error';
        formResponse.textContent = 'An unexpected error occurred.';
        console.error('Error:', error);
    })
    .finally(() => {
        submitButton.textContent = 'Submit Application';
        submitButton.disabled = false;
    });
});
        });
    </script>
</body>
</html>
<?php include 'includes/footer.php'; ?>