<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CvSU Silang - Campus Navigation & Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; font-family: system-ui, sans-serif; }
        #map { height: 100vh; width: 100vw; z-index: 1; background: #1a2e1a; }
        .control-panel {
            position: absolute; top: 15px; left: 15px; z-index: 1000;
            width: 390px; max-height: 94vh; overflow-y: auto;
            background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(8px);
            border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.18);
        }
        .room-badge { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; cursor: pointer; }
        .step-list { max-height: 135px; overflow-y: auto; font-size: 12px; }
        .calib-dot {
            background: #dc2626; border: 2px solid #ffffff; border-radius: 50%;
            box-shadow: 0 0 6px rgba(0,0,0,0.85); cursor: grab;
        }
        .calib-dot:active {
            cursor: grabbing;
            background: #facc15;
        }
        .calib-dot-connect {
            cursor: pointer;
        }
        .calib-dot-selected {
            background: #facc15;
            border: 3px solid #1d4ed8;
            transform: scale(1.2);
        }
        .delete-line-cursor {
            cursor: pointer !important;
        }
        .map-chip {
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; border: 2px solid #ffffff;
            color: #ffffff; font-weight: 700; font-size: 9px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.35);
        }
        .chip-cr { background: #0d9488; }
        .chip-cr-drag {
            background: #0d9488;
            border: 2px solid #facc15;
            box-shadow: 0 0 8px rgba(0,0,0,0.85);
            cursor: grab;
        }
        .chip-cr-drag:active { cursor: grabbing; }
        .chip-kubo { background: #a16207; }
        .chip-pwd { background: #2563eb; }
        .chip-exit { background: #b91c1c; }
        .emergency-box {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px;
        }
        .pwd-active-btn {
            background: #1d4ed8 !important; color: #ffffff !important; border-color: #1d4ed8 !important;
        }
    </style>
</head>
<body>

<div class="control-panel p-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h5 class="fw-bold mb-0 text-success">CvSU Silang Navigator</h5>
        <a href="/admin" class="btn btn-sm btn-outline-dark">Admin</a>
    </div>
    <p class="text-muted small mb-2">GPS, Digital Map, PWD & Emergency Finder</p>

    <div class="mb-2">
        <label class="form-label small fw-bold mb-1">Smart Search (Try "221", "CL1", "CR", "Kubo", "Kitchen")</label>
        <div class="input-group input-group-sm">
            <input type="text" id="searchInput" class="form-control" placeholder="Room #, Building, CR, Kubo, Office..." onkeydown="if(event.key==='Enter') handleSmartSearch()">
            <button class="btn btn-success" onclick="handleSmartSearch()">Locate</button>
        </div>
        <div id="roomDecodeBox" class="alert alert-info py-2 px-3 mt-2 mb-0 small d-none"></div>
    </div>

    <div class="emergency-box p-2 mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-bold text-danger">Emergency Location Finder</span>
            <span class="badge bg-danger" style="font-size: 10px;">Quick Route</span>
        </div>
        <div class="d-grid gap-1" style="grid-template-columns: repeat(2, 1fr);">
            <button class="btn btn-sm btn-outline-danger py-1 fw-semibold" style="font-size: 11px;" onclick="routeEmergency('clinic')">Clinic</button>
            <button class="btn btn-sm btn-danger py-1 fw-semibold" style="font-size: 11px;" onclick="routeEmergency('exit')">Main Emergency Exit</button>
            <button class="btn btn-sm btn-danger py-1 fw-semibold" style="font-size: 11px; grid-column: span 2;" onclick="routeEmergency('evac')">Silang Evacuation Area</button>
        </div>
    </div>

    <div class="card bg-light border-0 mb-2">
        <div class="card-body p-2">
            <div class="mb-2">
                <label class="small fw-semibold">Starting Point (Origin)</label>
                <div class="d-flex gap-1">
                    <select id="originSelect" class="form-select form-select-sm" onchange="calculateRoute()"></select>
                    <button class="btn btn-sm btn-success text-nowrap" onclick="useRealGPS()">My GPS</button>
                </div>
            </div>
            <div class="mb-2">
                <label class="small fw-semibold">Destination</label>
                <select id="destSelect" class="form-select form-select-sm" onchange="calculateRoute()"></select>
            </div>
            <div class="d-grid gap-1 mb-2">
                <button id="pwdToggleBtn" type="button" class="btn btn-outline-primary btn-sm fw-semibold" onclick="togglePwdMode()">
                    PWD / Wheelchair Route: OFF
                </button>
            </div>
            <div class="d-grid gap-2 d-md-flex">
                <button class="btn btn-primary btn-sm flex-grow-1 fw-semibold" onclick="calculateRoute()">Start Route</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearRoute()">Clear</button>
            </div>
            <div id="routeSummary" class="mt-2 small fw-bold text-success d-none"></div>
            <ol id="routeSteps" class="step-list mt-2 mb-0 ps-3 d-none"></ol>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-3 mb-2 px-1" style="font-size: 11px;">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="toggleCr" checked onchange="toggleOverlayLayer('cr', this.checked)">
            <label class="form-check-label fw-semibold" for="toggleCr">CRs</label>
        </div>
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="toggleKubo" checked onchange="toggleOverlayLayer('kubo', this.checked)">
            <label class="form-check-label fw-semibold" for="toggleKubo">Kubos</label>
        </div>
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="togglePwdPins" onchange="toggleOverlayLayer('pwd', this.checked)">
            <label class="form-check-label fw-semibold text-primary" for="togglePwdPins">PWD Ramps</label>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-1">
        <h6 class="fw-bold small text-uppercase text-secondary mb-0">Campus Directory</h6>
        <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="resetCampusView()">Reset View</button>
    </div>
    <div class="accordion accordion-flush mb-2" id="directoryAccordion"></div>

    <div class="border-top pt-2 mt-2">
        <div class="d-flex justify-content-between align-items-center">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="calibSwitch" onchange="toggleCalibration(this.checked)">
                <label class="form-check-label small text-muted" for="calibSwitch">Align / Edit Paths & CRs</label>
            </div>
            <button id="resetCalibBtn" class="btn btn-outline-danger btn-sm py-0 px-2 d-none" style="font-size:11px" onclick="resetCalibration()">Reset All</button>
        </div>
        <div id="calibToolbar" class="mt-2 d-none">
            <div class="btn-group btn-group-sm w-100" role="group">
                <button type="button" id="btnModeDrag" class="btn btn-dark" style="font-size:11px" onclick="setCalibToolMode('drag')">Drag Dots</button>
                <button type="button" id="btnModeDelete" class="btn btn-outline-danger" style="font-size:11px" onclick="setCalibToolMode('delete')">Delete Line</button>
                <button type="button" id="btnModeConnect" class="btn btn-outline-primary" style="font-size:11px" onclick="setCalibToolMode('connect')">Add Line</button>
            </div>
            <div id="calibHint" class="text-muted mt-1" style="font-size:11px">Drag any red dot or CR pin to reposition.</div>
        </div>
    </div>
</div>

<div id="map"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const cvsuBounds = L.latLngBounds(
        [14.2397, 120.9778],
        [14.2421, 120.9800]
    );

    const map = L.map('map', {
        zoomControl: false,
        center: [14.24120, 120.97898],
        zoom: 19,
        minZoom: 18,
        maxZoom: 21,
        maxBounds: cvsuBounds,
        maxBoundsViscosity: 1.0
    });
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 21,
        attribution: '&copy; OpenStreetMap'
    });

    const esriSat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxNativeZoom: 19,
        maxZoom: 21,
        attribution: 'Tiles &copy; Esri'
    });

    osmLayer.addTo(map);
    L.control.layers({ "OpenStreetMap": osmLayer, "Esri Satellite": esriSat }, null, { position: 'topright' }).addTo(map);

    const defaultCampusNodes = {
        w0:            { lat: 14.240890, lng: 120.978745, name: "Guard House / Main Gate" },
        w_b1_jct:      { lat: 14.241042, lng: 120.978742, name: "West Walkway (First Building Front)" },
        w_back_b1:     { lat: 14.241155, lng: 120.978766, name: "West Walkway (Behind First Building)" },
        w_gym_mid:     { lat: 14.241265, lng: 120.978788, name: "West Walkway (Gymnasium Side)" },
        w_gym_n:       { lat: 14.241428, lng: 120.978822, name: "West Walkway (Upper Gym Corner)" },
        w_b3_front:    { lat: 14.241530, lng: 120.978844, name: "West Walkway (Third Building Front)" },
        w_north:       { lat: 14.241715, lng: 120.978882, name: "North-West Corner (Above Third Building)" },

        pwd_ramp_1:    { lat: 14.241038, lng: 120.978702, name: "PWD Slanted Ramp 1 (First Building Side)" },
        pwd_ramp_2:    { lat: 14.241155, lng: 120.978726, name: "PWD Slanted Ramp 2 (Bldg 1 & Gym Walkway)" },
        pwd_ramp_3:    { lat: 14.241530, lng: 120.978804, name: "PWD Slanted Ramp 3 (Third Building Corner)" },

        b1_pin:        { lat: 14.241010, lng: 120.978915, name: "Building 1 - First Building" },
        clinic_pin:    { lat: 14.240982, lng: 120.979065, name: "University Clinic (Building 1 - 1st Floor)" },
        s_gap:         { lat: 14.240972, lng: 120.979122, name: "Central Walkway Intersection (Between Bldg 1 & 2)" },
        b2_pin:        { lat: 14.240938, lng: 120.979310, name: "Building 2 - Second Building" },
        b2_east:       { lat: 14.240905, lng: 120.979485, name: "Second Building East End" },

        kubo_1:        { lat: 14.240945, lng: 120.978880, name: "Kubo 1 (Resting Area - Bldg 1 Front)" },
        kubo_2:        { lat: 14.240870, lng: 120.979285, name: "Kubo 2 (Resting Area - Bldg 2 Front)" },
        kubo_3:        { lat: 14.241655, lng: 120.979045, name: "Kubo 3 (Resting Area - Back of Bldg 3)" },

        m_gap:         { lat: 14.241088, lng: 120.979145, name: "Walkway Behind Bldg 1 & 2 (Gym South)" },
        m_east:        { lat: 14.241018, lng: 120.979515, name: "East Walkway Behind Second Building" },

        gym_c:         { lat: 14.241215, lng: 120.979168, name: "Campus Gymnasium / Covered Court" },
        gym_back:      { lat: 14.241155, lng: 120.979465, name: "Likod ng Gym (Back of Stage Rooms)" },
        n_gap:         { lat: 14.241365, lng: 120.979198, name: "Gymnasium North Exit" },

        b3_turn:       { lat: 14.241382, lng: 120.979092, name: "Upper Walkway (South of Third Building)" },
        b3_front:      { lat: 14.241485, lng: 120.979112, name: "Third Building Front Corridor" },
        b3_pin:        { lat: 14.241525, lng: 120.979118, name: "Building 3 - Third Building" },
        e_alley_b3:    { lat: 14.241442, lng: 120.979325, name: "East Alley (Third Building Front Corner)" },

        e_alley_s:     { lat: 14.241345, lng: 120.979310, name: "East Alley South Intersection" },
        hm_alley_s:    { lat: 14.241328, lng: 120.979405, name: "Hostel Walkway" },
        hm_pin:        { lat: 14.241465, lng: 120.979430, name: "Hostel for HM & Tourism" },
        n_east:        { lat: 14.241635, lng: 120.979360, name: "North-East Corner (Student Park)" },
        canteen_jct:   { lat: 14.241670, lng: 120.979175, name: "North Walkway to Canteen" },
        canteen_pin:   { lat: 14.241785, lng: 120.979205, name: "Student Canteen" },

        exit_road_mid: { lat: 14.240620, lng: 120.978675, name: "Main Campus Road (Southbound)" },
        main_exit:     { lat: 14.240320, lng: 120.978560, name: "Main Emergency Exit (Roundabout)" },
        evac_jct:      { lat: 14.240620, lng: 120.979050, name: "Open Grounds Path to Evacuation Center" },
        evac_area:     { lat: 14.240360, lng: 120.979140, name: "Silang Evacuation Area (Monitoring Center)" }
    };

    const defaultCrLocations = [
        { lat: 14.240985, lng: 120.978825, name: "Building 1 - Female CR (1st Floor Left)", node: "b1_pin" },
        { lat: 14.240950, lng: 120.979015, name: "Building 1 - Male CR (1st Floor Right)", node: "b1_pin" },
        { lat: 14.240915, lng: 120.979205, name: "Building 2 - Female CR (1st Floor Left)", node: "b2_pin" },
        { lat: 14.240880, lng: 120.979395, name: "Building 2 - Male CR (1st Floor Right)", node: "b2_pin" },
        { lat: 14.241495, lng: 120.978945, name: "Building 3 - Female CR (1st Floor Left)", node: "b3_front" },
        { lat: 14.241445, lng: 120.979245, name: "Building 3 - Male CR (1st Floor Right)", node: "b3_front" },
        { lat: 14.241535, lng: 120.979275, name: "Building 3 - 3rd & 4th Floor CRs (Male & Female)", node: "b3_pin" },
        { lat: 14.241435, lng: 120.979425, name: "Hostel - CR Hallway", node: "hm_pin" }
    ];

    const defaultCampusEdges = [
        ['w0', 'w_b1_jct'],
        ['w_b1_jct', 'w_back_b1'],
        ['w_back_b1', 'w_gym_mid'],
        ['w_gym_mid', 'w_gym_n'],
        ['w_gym_n', 'w_b3_front'],
        ['w_b3_front', 'w_north'],

        ['w0', 'pwd_ramp_1'],
        ['pwd_ramp_1', 'w_b1_jct'],
        ['pwd_ramp_1', 'pwd_ramp_2'],
        ['pwd_ramp_2', 'w_back_b1'],
        ['pwd_ramp_2', 'pwd_ramp_3'],
        ['pwd_ramp_3', 'w_b3_front'],

        ['w_b1_jct', 'b1_pin'],
        ['b1_pin', 'clinic_pin'],
        ['clinic_pin', 's_gap'],
        ['s_gap', 'b2_pin'],
        ['b2_pin', 'b2_east'],

        ['b1_pin', 'kubo_1'],
        ['b2_pin', 'kubo_2'],
        ['w_north', 'kubo_3'],
        ['canteen_jct', 'kubo_3'],

        ['w_back_b1', 'm_gap'],
        ['m_gap', 'm_east'],
        ['b2_east', 'm_east'],

        ['w_gym_mid', 'gym_c'],
        ['gym_c', 'gym_back'],

        ['s_gap', 'm_gap'],
        ['m_gap', 'gym_c'],
        ['gym_c', 'n_gap'],

        ['w_gym_n', 'b3_turn'],
        ['b3_turn', 'n_gap'],
        ['n_gap', 'e_alley_s'],
        ['e_alley_s', 'hm_alley_s'],
        ['hm_alley_s', 'hm_pin'],

        ['w_b3_front', 'b3_front'],
        ['b3_front', 'e_alley_b3'],
        ['b3_turn', 'b3_front'],
        ['b3_front', 'b3_pin'],

        ['e_alley_s', 'e_alley_b3'],
        ['e_alley_b3', 'n_east'],
        ['w_north', 'canteen_jct'],
        ['canteen_jct', 'n_east'],
        ['canteen_jct', 'canteen_pin'],

        ['w0', 'exit_road_mid'],
        ['exit_road_mid', 'main_exit'],
        ['exit_road_mid', 'evac_jct'],
        ['s_gap', 'evac_jct'],
        ['evac_jct', 'evac_area'],
        ['main_exit', 'evac_area']
    ];

    const STORAGE_KEY = 'cvsu_silang_paths_v9';
    const CR_STORAGE_KEY = 'cvsu_silang_crs_v1';
    const EDGE_STORAGE_KEY = 'cvsu_silang_edges_v1';
    const CLINIC_MIGRATION_KEY = 'cvsu_clinic_b1_migrated_v1';

    let campusNodes = JSON.parse(localStorage.getItem(STORAGE_KEY)) || JSON.parse(JSON.stringify(defaultCampusNodes));
    let crLocations = JSON.parse(localStorage.getItem(CR_STORAGE_KEY)) || JSON.parse(JSON.stringify(defaultCrLocations));
    let campusEdges = JSON.parse(localStorage.getItem(EDGE_STORAGE_KEY)) || JSON.parse(JSON.stringify(defaultCampusEdges));

    if (!localStorage.getItem(CLINIC_MIGRATION_KEY)) {
        campusNodes.clinic_pin = { lat: 14.240982, lng: 120.979065, name: "University Clinic (Building 1 - 1st Floor)" };
        const removePairs = [
            ['b1_pin', 's_gap'],
            ['b2_pin', 'clinic_pin'],
            ['clinic_pin', 'b2_east']
        ];
        campusEdges = campusEdges.filter(([u, v]) =>
            !removePairs.some(([a, b]) => (u === a && v === b) || (u === b && v === a))
        );
        const addPairs = [
            ['b1_pin', 'clinic_pin'],
            ['clinic_pin', 's_gap'],
            ['b2_pin', 'b2_east']
        ];
        addPairs.forEach(([a, b]) => {
            const exists = campusEdges.some(([u, v]) => (u === a && v === b) || (u === b && v === a));
            if (!exists) campusEdges.push([a, b]);
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(campusNodes));
        localStorage.setItem(EDGE_STORAGE_KEY, JSON.stringify(campusEdges));
        localStorage.setItem(CLINIC_MIGRATION_KEY, '1');
    }

    let campusData = { buildings: [], facilities: [] };
    let markers = {};
    let facilityLayerGroup = L.layerGroup().addTo(map);
    let emergencyLayerGroup = L.layerGroup().addTo(map);
    let crLayerGroup = L.layerGroup().addTo(map);
    let kuboLayerGroup = L.layerGroup().addTo(map);
    let pwdLayerGroup = L.layerGroup();
    let activeRouteLayer = null;
    let calibLayer = null;
    let calibLines = [];
    let isCalibMode = false;
    let calibToolMode = 'drag';
    let connectStartKey = null;
    let userMarker = null;
    let activeRoomInstruction = '';
    let pwdModeEnabled = false;
    let isEmergencyRoute = false;
    let lastRouteState = null;

    fetch('/api/campus-data')
        .then(res => res.json())
        .then(data => {
            campusData = data;
            renderCampusData();
            renderStaticOverlays();
        });

    function resetCampusView() {
        map.setView([14.24120, 120.97898], 19);
    }

    function distMeters(lat1, lng1, lat2, lng2) {
        return map.distance([lat1, lng1], [lat2, lng2]);
    }

    function toOrdinal(num) {
        const suffixes = ['th', 'st', 'nd', 'rd'];
        const v = num % 100;
        return num + (suffixes[(v - 20) % 10] || suffixes[v] || suffixes[0]);
    }

    function formatRoomDirection(roomNumber, roomName = '') {
        if (!roomNumber) return '';
        const clean = roomNumber.toUpperCase().trim();

        if (clean === '360') {
            return 'Go to 3rd building, 5th floor, 10th room (Central Student Government)';
        }
        if (clean === 'CLINIC') {
            return 'Go to 1st building, 1st floor — University Clinic';
        }
        if (clean === '323A') {
            return 'Go to 3rd building, 2nd floor — Computer Lab 2 (CL2)';
        }
        if (clean === '323B') {
            return 'Go to 3rd building, 2nd floor — Computer Lab 3 (CL3)';
        }
        if (clean === '324') {
            return 'Go to 3rd building, 2nd floor — Computer Lab 4 (CL4)';
        }
        if (clean.startsWith('H-')) {
            return `Enter Hostel — ${roomName}`;
        }
        if (clean.startsWith('GYM-')) {
            return `Go to Back of the Gym Stage (Likod ng Gym) — ${roomName}`;
        }
        if (clean.includes('CR')) {
            return `Follow signs to ${roomName}`;
        }

        const match = clean.match(/^(\d)(\d)(\d)([A-Z])?$/);
        if (match) {
            const bNum = parseInt(match[1]);
            const floorNum = parseInt(match[2]);
            const rNum = parseInt(match[3]);
            const wing = match[4] ? ` (Section ${match[4]})` : '';
            const floorText = floorNum <= 1 ? '1st floor' : `${toOrdinal(floorNum)} floor`;
            const extra = roomName && !roomName.toLowerCase().includes('lecture room') ? ` — ${roomName}` : '';
            return `Go to ${toOrdinal(bNum)} building, ${floorText}, ${toOrdinal(rNum)} room${wing}${extra}`;
        }

        return roomName ? `Proceed to ${roomName}` : '';
    }

    function toggleOverlayLayer(type, show) {
        const layerMap = {
            cr: crLayerGroup,
            kubo: kuboLayerGroup,
            pwd: pwdLayerGroup
        };
        const layer = layerMap[type];
        if (!layer) return;
        if (show) layer.addTo(map);
        else map.removeLayer(layer);
    }

    function renderStaticOverlays() {
        crLayerGroup.clearLayers();
        kuboLayerGroup.clearLayers();
        pwdLayerGroup.clearLayers();
        emergencyLayerGroup.clearLayers();

        const crDraggable = isCalibMode && calibToolMode === 'drag';

        crLocations.forEach((cr, idx) => {
            const chipClass = crDraggable ? 'map-chip chip-cr-drag' : 'map-chip chip-cr';
            const size = crDraggable ? 26 : 22;
            const icon = L.divIcon({ className: chipClass, html: 'CR', iconSize: [size, size], iconAnchor: [size / 2, size / 2] });
            const marker = L.marker([cr.lat, cr.lng], {
                icon,
                draggable: crDraggable,
                autoPan: false,
                zIndexOffset: crDraggable ? 2100 : 500
            }).addTo(crLayerGroup);

            if (crDraggable) {
                marker.bindTooltip(`<b>CR:</b> ${cr.name}`, { direction: 'top', offset: [0, -12] });
                marker.on('dragstart', function() {
                    marker.closeTooltip();
                });
                marker.on('dragend', function(e) {
                    const pos = e.target.getLatLng();
                    crLocations[idx].lat = parseFloat(pos.lat.toFixed(6));
                    crLocations[idx].lng = parseFloat(pos.lng.toFixed(6));
                    localStorage.setItem(CR_STORAGE_KEY, JSON.stringify(crLocations));
                });
            } else {
                marker.bindPopup(`<b>${cr.name}</b><br><button class="btn btn-sm btn-success mt-1 w-100" onclick="routeDirectlyToNode('${cr.node}', '${cr.name}', false)">Route Here</button>`);
            }
        });

        ['kubo_1', 'kubo_2', 'kubo_3'].forEach((kKey, idx) => {
            const k = campusNodes[kKey];
            const icon = L.divIcon({ className: 'map-chip chip-kubo', html: `K${idx + 1}`, iconSize: [22, 22], iconAnchor: [11, 11] });
            L.marker([k.lat, k.lng], { icon })
                .addTo(kuboLayerGroup)
                .bindPopup(`<b>${k.name}</b><br><button class="btn btn-sm btn-warning mt-1 w-100" onclick="routeDirectlyToNode('${kKey}', '${k.name}', false)">Route Here</button>`);
        });

        ['pwd_ramp_1', 'pwd_ramp_2', 'pwd_ramp_3'].forEach((pKey) => {
            const p = campusNodes[pKey];
            const icon = L.divIcon({ className: 'map-chip chip-pwd', html: 'PWD', iconSize: [26, 26], iconAnchor: [13, 13] });
            L.marker([p.lat, p.lng], { icon })
                .addTo(pwdLayerGroup)
                .bindPopup(`<b>${p.name}</b><br><span class="small text-muted">Wheelchair-accessible slanted walkway</span>`);
        });

        const exitIcon = L.divIcon({ className: 'map-chip chip-exit', html: 'EXIT', iconSize: [28, 28], iconAnchor: [14, 14] });
        L.marker([campusNodes.main_exit.lat, campusNodes.main_exit.lng], { icon: exitIcon })
            .addTo(emergencyLayerGroup)
            .bindPopup(`<b>Main Emergency Exit (Roundabout)</b><br><button class="btn btn-sm btn-danger mt-1 w-100" onclick="routeEmergency('exit')">Evacuate Here</button>`);

        const evacIcon = L.divIcon({ className: 'map-chip chip-exit', html: 'EVAC', iconSize: [30, 30], iconAnchor: [15, 15] });
        L.marker([campusNodes.evac_area.lat, campusNodes.evac_area.lng], { icon: evacIcon })
            .addTo(emergencyLayerGroup)
            .bindPopup(`<b>Silang Evacuation Area (Monitoring Center)</b><br><button class="btn btn-sm btn-danger mt-1 w-100" onclick="routeEmergency('evac')">Route to Evacuation Area</button>`);
    }

    function togglePwdMode() {
        pwdModeEnabled = !pwdModeEnabled;
        const btn = document.getElementById('pwdToggleBtn');
        const pwdCheck = document.getElementById('togglePwdPins');

        if (pwdModeEnabled) {
            btn.classList.add('pwd-active-btn');
            btn.innerHTML = 'PWD / Wheelchair Route: ON';
            pwdCheck.checked = true;
            toggleOverlayLayer('pwd', true);
        } else {
            btn.classList.remove('pwd-active-btn');
            btn.innerHTML = 'PWD / Wheelchair Route: OFF';
        }

        if (lastRouteState) {
            redrawLastRoute(false);
        } else if (document.getElementById('destSelect').value) {
            calculateRoute(true, false);
        }
    }

    function updateCalibHint() {
        const hint = document.getElementById('calibHint');
        if (calibToolMode === 'drag') {
            hint.innerHTML = 'Drag any red dot or CR pin to reposition.';
        } else if (calibToolMode === 'delete') {
            hint.innerHTML = '<span class="text-danger fw-semibold">Click any red line on the map to delete that path.</span>';
        } else if (calibToolMode === 'connect') {
            if (connectStartKey) {
                hint.innerHTML = `<span class="text-primary fw-semibold">Selected <b>${connectStartKey}</b>. Click another red dot to connect.</span>`;
            } else {
                hint.innerHTML = '<span class="text-primary fw-semibold">Click two red dots to connect them with a new path.</span>';
            }
        }
    }

    function setCalibToolMode(mode) {
        calibToolMode = mode;
        connectStartKey = null;

        document.getElementById('btnModeDrag').className = mode === 'drag' ? 'btn btn-dark' : 'btn btn-outline-dark';
        document.getElementById('btnModeDelete').className = mode === 'delete' ? 'btn btn-danger' : 'btn btn-outline-danger';
        document.getElementById('btnModeConnect').className = mode === 'connect' ? 'btn btn-primary' : 'btn btn-outline-primary';

        updateCalibHint();
        drawCalibNetwork();
        renderStaticOverlays();
    }

    function toggleCalibration(enabled) {
        isCalibMode = enabled;
        connectStartKey = null;
        document.getElementById('resetCalibBtn').classList.toggle('d-none', !enabled);
        document.getElementById('calibToolbar').classList.toggle('d-none', !enabled);

        if (calibLayer) {
            map.removeLayer(calibLayer);
            calibLayer = null;
            calibLines = [];
        }
        if (enabled) {
            document.getElementById('toggleCr').checked = true;
            toggleOverlayLayer('cr', true);
            calibLayer = L.layerGroup().addTo(map);
            setCalibToolMode('drag');
        } else {
            renderStaticOverlays();
        }
    }

    function redrawLastRoute(shouldFit = false) {
        if (!lastRouteState) return;
        const { pathKeys } = findCampusPath(lastRouteState.startNode, lastRouteState.endNode);
        drawPathOnMap(pathKeys, lastRouteState.emergencyStyle, shouldFit);
    }

    function drawCalibNetwork() {
        if (!calibLayer) return;
        calibLayer.clearLayers();
        calibLines = [];

        campusEdges.forEach(([a, b], idx) => {
            if (!campusNodes[a] || !campusNodes[b]) return;
            const latlngs = [
                [campusNodes[a].lat, campusNodes[a].lng],
                [campusNodes[b].lat, campusNodes[b].lng]
            ];

            if (calibToolMode === 'delete') {
                L.polyline(latlngs, {
                    color: '#dc2626',
                    weight: 4,
                    opacity: 0.9,
                    dashArray: '6, 4',
                    interactive: false
                }).addTo(calibLayer);

                const hitLine = L.polyline(latlngs, {
                    color: '#dc2626',
                    weight: 16,
                    opacity: 0.01,
                    interactive: true,
                    className: 'delete-line-cursor'
                })
                    .bindTooltip(`Click to delete: <b>${a}</b> &harr; <b>${b}</b>`, { sticky: true })
                    .addTo(calibLayer);

                hitLine.on('click', function() {
                    campusEdges.splice(idx, 1);
                    localStorage.setItem(EDGE_STORAGE_KEY, JSON.stringify(campusEdges));
                    drawCalibNetwork();
                    if (lastRouteState) {
                        redrawLastRoute(false);
                    }
                });
            } else {
                const line = L.polyline(latlngs, {
                    color: '#111827',
                    weight: 3,
                    opacity: 0.85,
                    interactive: false
                }).addTo(calibLayer);
                calibLines.push({ line, a, b });
            }
        });

        for (const [key, node] of Object.entries(campusNodes)) {
            let dotClass = 'calib-dot';
            if (calibToolMode === 'connect') {
                dotClass += ' calib-dot-connect';
                if (connectStartKey === key) dotClass += ' calib-dot-selected';
            }

            const dotIcon = L.divIcon({ className: dotClass, iconSize: [16, 16], iconAnchor: [8, 8] });
            const m = L.marker([node.lat, node.lng], {
                icon: dotIcon,
                draggable: calibToolMode === 'drag',
                autoPan: false,
                zIndexOffset: calibToolMode === 'delete' ? 1000 : 2000
            })
                .bindTooltip(`<b>${key}</b>`, { direction: 'top', offset: [0, -8] })
                .addTo(calibLayer);

            if (calibToolMode === 'drag') {
                m.on('dragstart', function() {
                    m.closeTooltip();
                });

                m.on('drag', function(e) {
                    const pos = e.target.getLatLng();
                    campusNodes[key].lat = parseFloat(pos.lat.toFixed(6));
                    campusNodes[key].lng = parseFloat(pos.lng.toFixed(6));

                    calibLines.forEach(item => {
                        if (item.a === key || item.b === key) {
                            item.line.setLatLngs([
                                [campusNodes[item.a].lat, campusNodes[item.a].lng],
                                [campusNodes[item.b].lat, campusNodes[item.b].lng]
                            ]);
                        }
                    });

                    if (lastRouteState) {
                        redrawLastRoute(false);
                    }
                });

                m.on('dragend', function() {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(campusNodes));
                    renderCampusData();
                    renderStaticOverlays();
                    if (lastRouteState) {
                        redrawLastRoute(false);
                    }
                });
            } else if (calibToolMode === 'connect') {
                m.on('click', function() {
                    if (!connectStartKey) {
                        connectStartKey = key;
                    } else if (connectStartKey === key) {
                        connectStartKey = null;
                    } else {
                        const exists = campusEdges.some(([u, v]) =>
                            (u === connectStartKey && v === key) || (u === key && v === connectStartKey)
                        );
                        if (!exists) {
                            campusEdges.push([connectStartKey, key]);
                            localStorage.setItem(EDGE_STORAGE_KEY, JSON.stringify(campusEdges));
                        }
                        connectStartKey = null;
                        if (lastRouteState) {
                            redrawLastRoute(false);
                        }
                    }
                    updateCalibHint();
                    drawCalibNetwork();
                });
            }
        }
    }

    function resetCalibration() {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(CR_STORAGE_KEY);
        localStorage.removeItem(EDGE_STORAGE_KEY);
        localStorage.setItem(CLINIC_MIGRATION_KEY, '1');
        campusNodes = JSON.parse(JSON.stringify(defaultCampusNodes));
        crLocations = JSON.parse(JSON.stringify(defaultCrLocations));
        campusEdges = JSON.parse(JSON.stringify(defaultCampusEdges));
        connectStartKey = null;
        updateCalibHint();
        drawCalibNetwork();
        renderCampusData();
        renderStaticOverlays();
        if (lastRouteState) {
            redrawLastRoute(false);
        }
    }

    function resolveGraphNode(id, lat, lng, label = '') {
        const bId = parseInt(id);
        if (bId === 1) return 'b1_pin';
        if (bId === 2) return 'b2_pin';
        if (bId === 3) return 'b3_pin';
        if (bId === 4) return 'hm_pin';
        if (bId === 5) return 'gym_back';

        const text = label.toLowerCase();
        if (text.includes('guard') || text.includes('gate')) return 'w0';
        if (text.includes('back of stage') || text.includes('likod')) return 'gym_back';
        if (text.includes('gym')) return 'gym_c';
        if (text.includes('clinic')) return 'clinic_pin';
        if (text.includes('canteen')) return 'canteen_pin';
        if (text.includes('kubo 1')) return 'kubo_1';
        if (text.includes('kubo 2')) return 'kubo_2';
        if (text.includes('kubo 3')) return 'kubo_3';
        if (text.includes('exit')) return 'main_exit';
        if (text.includes('evacuation')) return 'evac_area';
        if (text.includes('building 1') || text.includes('first building')) return 'b1_pin';
        if (text.includes('building 2') || text.includes('second building')) return 'b2_pin';
        if (text.includes('building 3') || text.includes('third building')) return 'b3_pin';
        if (text.includes('hostel') || text.includes('tourism')) return 'hm_pin';

        let bestKey = 'w0';
        let minDist = Infinity;
        for (const [key, node] of Object.entries(campusNodes)) {
            const d = distMeters(lat, lng, node.lat, node.lng);
            if (d < minDist) {
                minDist = d;
                bestKey = key;
            }
        }
        return bestKey;
    }

    function findCampusPath(startNode, endNode) {
        const b1b2Zone = ['b1_pin', 'clinic_pin', 'b2_pin', 'b2_east', 's_gap', 'm_gap', 'm_east', 'kubo_1', 'kubo_2'];
        const b1b2ShortcutNodes = ['b1_pin', 'clinic_pin', 'b2_pin', 'b2_east', 's_gap', 'm_gap', 'm_east'];
        const allowB1B2Shortcut = b1b2Zone.includes(startNode) || b1b2Zone.includes(endNode);

        const gymAllowedStarts = ['b1_pin', 'b2_pin', 'clinic_pin', 's_gap', 'm_gap', 'gym_c', 'gym_back', 'n_gap'];
        const touchesGym = startNode === 'gym_c' || endNode === 'gym_c' || startNode === 'gym_back' || endNode === 'gym_back';
        const distFromGym = distMeters(
            campusNodes[startNode].lat,
            campusNodes[startNode].lng,
            campusNodes.gym_c.lat,
            campusNodes.gym_c.lng
        );
        const allowGymShortcut = touchesGym || (!pwdModeEnabled && (gymAllowedStarts.includes(startNode) || distFromGym <= 25));

        const dists = {};
        const prev = {};
        const unvisited = new Set(Object.keys(campusNodes));

        for (const k of unvisited) dists[k] = Infinity;
        dists[startNode] = 0;

        while (unvisited.size > 0) {
            let u = null;
            for (const k of unvisited) {
                if (u === null || dists[k] < dists[u]) u = k;
            }
            if (u === null || u === endNode || dists[u] === Infinity) break;
            unvisited.delete(u);

            campusEdges.forEach(([a, b]) => {
                if (a !== u && b !== u) return;
                const v = (a === u) ? b : a;
                if (!unvisited.has(v)) return;
                if (!allowGymShortcut && (u === 'gym_c' || v === 'gym_c')) return;
                if (!allowB1B2Shortcut && (b1b2ShortcutNodes.includes(u) || b1b2ShortcutNodes.includes(v))) return;

                let weight = distMeters(campusNodes[u].lat, campusNodes[u].lng, campusNodes[v].lat, campusNodes[v].lng);

                if (pwdModeEnabled) {
                    if ((u === 'w0' && v === 'w_b1_jct') || (u === 'w_b1_jct' && v === 'w0')) weight += 35;
                    if ((u === 'w_b1_jct' && v === 'w_back_b1') || (u === 'w_back_b1' && v === 'w_b1_jct')) weight += 35;
                    if ((u === 'w_gym_n' && v === 'w_b3_front') || (u === 'w_b3_front' && v === 'w_gym_n')) weight += 35;
                    if (u.startsWith('pwd_ramp_') || v.startsWith('pwd_ramp_')) weight *= 0.65;
                } else {
                    if (u.startsWith('pwd_ramp_') || v.startsWith('pwd_ramp_')) weight += 12;
                }

                const alt = dists[u] + weight;
                if (alt < dists[v]) {
                    dists[v] = alt;
                    prev[v] = u;
                }
            });
        }

        if (dists[endNode] === Infinity) {
            return { pathKeys: [], totalCost: Infinity };
        }

        const pathKeys = [];
        let curr = endNode;
        while (curr) {
            pathKeys.unshift(curr);
            curr = prev[curr];
        }
        return { pathKeys, totalCost: dists[endNode] };
    }

    function drawPathOnMap(nodeKeys, emergencyStyle = false, shouldFitBounds = true) {
        if (activeRouteLayer) map.removeLayer(activeRouteLayer);

        const sumBox = document.getElementById('routeSummary');
        const stepList = document.getElementById('routeSteps');

        if (!nodeKeys || nodeKeys.length === 0) {
            sumBox.classList.remove('d-none', 'text-success', 'text-primary');
            sumBox.classList.add('text-danger');
            sumBox.innerHTML = 'No connected pathway found between these points.';
            stepList.classList.add('d-none');
            return;
        }

        const latlngs = [];
        const stepsHtml = [];
        let totalMeters = 0;

        nodeKeys.forEach((k) => {
            const pt = [campusNodes[k].lat, campusNodes[k].lng];
            if (latlngs.length > 0) {
                const prevPt = latlngs[latlngs.length - 1];
                totalMeters += distMeters(prevPt[0], prevPt[1], pt[0], pt[1]);
            }
            latlngs.push(pt);
            stepsHtml.push(`<li>Walk to <b>${campusNodes[k].name}</b></li>`);
        });

        if (activeRoomInstruction) {
            stepsHtml.push(`<li class="${emergencyStyle ? 'text-danger' : 'text-success'}"><b>${activeRoomInstruction}</b></li>`);
        }

        const outerColor = emergencyStyle ? '#7f1d1d' : (pwdModeEnabled ? '#1e3a8a' : '#0c4a6e');
        const innerColor = emergencyStyle ? '#ef4444' : (pwdModeEnabled ? '#60a5fa' : '#38bdf8');
        const endPt = latlngs[latlngs.length - 1];

        activeRouteLayer = L.featureGroup([
            L.polyline(latlngs, { color: outerColor, weight: 8, opacity: 0.9, lineJoin: 'miter', interactive: false }),
            L.polyline(latlngs, { color: innerColor, weight: 4, opacity: 1, lineJoin: 'miter', interactive: false }),
            L.circleMarker(endPt, { radius: 7, color: '#ffffff', weight: 2, fillColor: innerColor, fillOpacity: 1, interactive: false })
        ]).addTo(map);

        if (shouldFitBounds && !calibLayer) {
            map.fitBounds(activeRouteLayer.getBounds(), { padding: [60, 60], maxZoom: 19 });
        }

        const roundedDist = Math.max(5, Math.round(totalMeters));
        const estMinutes = Math.max(1, Math.ceil(roundedDist / (pwdModeEnabled ? 55 : 75)));
        const modeTag = emergencyStyle ? 'Emergency Route' : (pwdModeEnabled ? 'PWD Route' : 'Fastest Route');

        sumBox.classList.remove('d-none', 'text-success', 'text-danger', 'text-primary');
        sumBox.classList.add(emergencyStyle ? 'text-danger' : (pwdModeEnabled ? 'text-primary' : 'text-success'));
        sumBox.innerHTML = `${modeTag}: ${roundedDist} m &bull; Est. Time: ~${estMinutes} min`;

        stepList.classList.remove('d-none');
        stepList.innerHTML = stepsHtml.join('');
    }

    function getCurrentOriginNode() {
        const originSelect = document.getElementById('originSelect');
        const originVal = originSelect.value;
        if (!originVal) return 'w0';
        const originLabel = originSelect.options[originSelect.selectedIndex].text;
        const [oLat, oLng] = originVal.split(',').map(Number);
        return resolveGraphNode(0, oLat, oLng, originLabel);
    }

    function routeDirectlyToNode(targetNode, label, emergency = false) {
        isEmergencyRoute = emergency;
        activeRoomInstruction = label;
        const startNode = getCurrentOriginNode();
        lastRouteState = { startNode, endNode: targetNode, emergencyStyle: emergency };
        const { pathKeys } = findCampusPath(startNode, targetNode);
        drawPathOnMap(pathKeys, emergency, true);
    }

    function routeEmergency(type) {
        const startNode = getCurrentOriginNode();
        const decodeBox = document.getElementById('roomDecodeBox');
        decodeBox.classList.remove('d-none');
        decodeBox.className = 'alert alert-danger py-2 px-3 mt-2 mb-0 small';

        if (type === 'clinic') {
            isEmergencyRoute = true;
            activeRoomInstruction = 'Go to 1st building, 1st floor — University Clinic';
            decodeBox.innerHTML = '<strong>Emergency Medical:</strong> Routing to University Clinic (Building 1, 1st Floor).';
            lastRouteState = { startNode, endNode: 'clinic_pin', emergencyStyle: true };
            const { pathKeys } = findCampusPath(startNode, 'clinic_pin');
            drawPathOnMap(pathKeys, true, true);
        } else if (type === 'exit') {
            isEmergencyRoute = true;
            activeRoomInstruction = 'Proceed safely out through Main Emergency Exit (Roundabout)';
            decodeBox.innerHTML = '<strong>Main Emergency Exit:</strong> Routing straight to the Main Exit Roundabout.';
            lastRouteState = { startNode, endNode: 'main_exit', emergencyStyle: true };
            const { pathKeys } = findCampusPath(startNode, 'main_exit');
            drawPathOnMap(pathKeys, true, true);
        } else if (type === 'evac') {
            isEmergencyRoute = true;
            activeRoomInstruction = 'Assemble at Silang Evacuation Area (Evacuation Center / Silang Monitoring Center)';
            decodeBox.innerHTML = '<strong>Silang Evacuation Area:</strong> Routing to the Evacuation Center & Silang Monitoring Center.';
            lastRouteState = { startNode, endNode: 'evac_area', emergencyStyle: true };
            const { pathKeys } = findCampusPath(startNode, 'evac_area');
            drawPathOnMap(pathKeys, true, true);
        }
    }

    function calculateRoute(keepRoomNote = false, shouldFitBounds = true) {
        if (!keepRoomNote) {
            activeRoomInstruction = '';
            isEmergencyRoute = false;
        }

        const originSelect = document.getElementById('originSelect');
        const destSelect = document.getElementById('destSelect');
        const originVal = originSelect.value;
        const destVal = destSelect.value;

        if (!originVal || !destVal) {
            if (originVal && !destVal && destSelect.options.length > 1) {
                destSelect.selectedIndex = 1;
                return calculateRoute(keepRoomNote, shouldFitBounds);
            }
            return;
        }

        const originLabel = originSelect.options[originSelect.selectedIndex].text;
        const destLabel = destSelect.options[destSelect.selectedIndex].text;

        const [oLat, oLng] = originVal.split(',').map(Number);
        const [destId, destCoords] = destVal.split('|');
        const [dLat, dLng] = destCoords.split(',').map(Number);

        const startNode = resolveGraphNode(0, oLat, oLng, originLabel);
        const endNode = resolveGraphNode(destId, dLat, dLng, destLabel);
        const emergencyCheck = endNode === 'main_exit' || endNode === 'evac_area' || isEmergencyRoute;

        lastRouteState = { startNode, endNode, emergencyStyle: emergencyCheck };
        const { pathKeys } = findCampusPath(startNode, endNode);
        drawPathOnMap(pathKeys, emergencyCheck, shouldFitBounds);

        if (parseInt(destId) > 0) {
            fetch('/api/navigation-history', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ destination_id: parseInt(destId) })
            });
        }
    }

    function renderCampusData() {
        const originSelect = document.getElementById('originSelect');
        const destSelect = document.getElementById('destSelect');
        const accordion = document.getElementById('directoryAccordion');
        const prevOriginIdx = originSelect.selectedIndex;
        const prevDestIdx = destSelect.selectedIndex;

        originSelect.innerHTML = `<option value="${campusNodes.w0.lat},${campusNodes.w0.lng}">Guard House / Main Gate (Default Start)</option>`;
        destSelect.innerHTML = `<option value="">-- Select Destination --</option>`;
        accordion.innerHTML = '';

        facilityLayerGroup.clearLayers();
        Object.values(markers).forEach(m => map.removeLayer(m));
        markers = {};
        if (userMarker) map.removeLayer(userMarker);

        userMarker = L.circleMarker([campusNodes.w0.lat, campusNodes.w0.lng], {
            radius: 7, color: '#ffffff', weight: 2, fillColor: '#22c55e', fillOpacity: 1
        }).addTo(map).bindPopup('<b>Guard House / Main Gate (Start)</b>');

        const pinOverrides = {
            1: [campusNodes.b1_pin.lat, campusNodes.b1_pin.lng],
            2: [campusNodes.b2_pin.lat, campusNodes.b2_pin.lng],
            3: [campusNodes.b3_pin.lat, campusNodes.b3_pin.lng],
            4: [campusNodes.hm_pin.lat, campusNodes.hm_pin.lng],
            5: [campusNodes.gym_back.lat, campusNodes.gym_back.lng]
        };

        campusData.buildings.forEach((b, index) => {
            const coords = pinOverrides[b.building_id] || [parseFloat(b.latitude), parseFloat(b.longitude)];
            const roomListHtml = b.rooms.map(r => {
                const safeName = (r.room_name || '').replace(/'/g, "\\'");
                return `<span class="badge room-badge me-1 mb-1" onclick="selectRoomFromBadge(${b.building_id}, ${coords[0]}, ${coords[1]}, '${r.room_number}', '${safeName}')">Rm ${r.room_number}: ${r.room_name}</span>`;
            }).join('');

            const marker = L.marker(coords).addTo(map).bindPopup(`
                <div style="min-width:220px; max-height:240px; overflow-y:auto;">
                    <h6 class="fw-bold mb-1">${b.building_name}</h6>
                    <p class="small text-muted mb-2">${b.description || ''}</p>
                    <div class="mb-2">${roomListHtml || '<span class="small text-muted">No rooms listed</span>'}</div>
                    <button class="btn btn-sm btn-primary w-100" onclick="quickRouteTo(${b.building_id}, ${coords[0]}, ${coords[1]})">Navigate Here</button>
                </div>
            `);
            markers['b_' + b.building_id] = marker;

            originSelect.innerHTML += `<option value="${coords[0]},${coords[1]}">${b.building_name}</option>`;
            destSelect.innerHTML += `<option value="${b.building_id}|${coords[0]},${coords[1]}">${b.building_name}</option>`;

            accordion.innerHTML += `
                <div class="accordion-item border rounded mb-1">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-2 px-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}">
                            ${b.building_name}
                        </button>
                    </h2>
                    <div id="collapse${index}" class="accordion-collapse collapse" data-bs-parent="#directoryAccordion">
                        <div class="accordion-body p-2 small" style="max-height:190px; overflow-y:auto;">
                            <p class="text-muted mb-1">${b.description || ''}</p>
                            <div class="mb-2">${roomListHtml}</div>
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="quickRouteTo(${b.building_id}, ${coords[0]}, ${coords[1]})">Route Here</button>
                        </div>
                    </div>
                </div>
            `;
        });

        campusData.facilities.forEach(f => {
            const nameLower = f.facility_name.toLowerCase();
            if (nameLower.includes('guard') || nameLower.includes('kubo') || nameLower.includes('exit') || nameLower.includes('evacuation')) {
                if (!nameLower.includes('guard')) {
                    let optCoords = [parseFloat(f.latitude), parseFloat(f.longitude)];
                    if (nameLower.includes('kubo 1')) optCoords = [campusNodes.kubo_1.lat, campusNodes.kubo_1.lng];
                    if (nameLower.includes('kubo 2')) optCoords = [campusNodes.kubo_2.lat, campusNodes.kubo_2.lng];
                    if (nameLower.includes('kubo 3')) optCoords = [campusNodes.kubo_3.lat, campusNodes.kubo_3.lng];
                    if (nameLower.includes('exit')) optCoords = [campusNodes.main_exit.lat, campusNodes.main_exit.lng];
                    if (nameLower.includes('evacuation')) optCoords = [campusNodes.evac_area.lat, campusNodes.evac_area.lng];
                    originSelect.innerHTML += `<option value="${optCoords[0]},${optCoords[1]}">[Facility] ${f.facility_name}</option>`;
                    destSelect.innerHTML += `<option value="0|${optCoords[0]},${optCoords[1]}">[Facility] ${f.facility_name}</option>`;
                }
                return;
            }

            let fCoords = [parseFloat(f.latitude), parseFloat(f.longitude)];
            if (nameLower.includes('gym')) fCoords = [campusNodes.gym_c.lat, campusNodes.gym_c.lng];
            if (nameLower.includes('clinic')) fCoords = [campusNodes.clinic_pin.lat, campusNodes.clinic_pin.lng];
            if (nameLower.includes('canteen')) fCoords = [campusNodes.canteen_pin.lat, campusNodes.canteen_pin.lng];

            L.circleMarker(fCoords, {
                radius: 7, color: '#ffffff', weight: 2, fillColor: '#f97316', fillOpacity: 1
            }).addTo(facilityLayerGroup).bindPopup(`<b>Facility:</b> ${f.facility_name}<br><button class="btn btn-sm btn-warning mt-1 w-100" onclick="quickRouteTo(0, ${fCoords[0]}, ${fCoords[1]}, '${f.facility_name}')">Navigate Here</button>`);

            originSelect.innerHTML += `<option value="${fCoords[0]},${fCoords[1]}">[Facility] ${f.facility_name}</option>`;
            destSelect.innerHTML += `<option value="0|${fCoords[0]},${fCoords[1]}">[Facility] ${f.facility_name}</option>`;
        });

        if (prevOriginIdx > 0) originSelect.selectedIndex = prevOriginIdx;
        if (prevDestIdx > 0) destSelect.selectedIndex = prevDestIdx;
    }

    function selectRoomFromBadge(buildingId, lat, lng, roomNum, roomName) {
        const dir = formatRoomDirection(roomNum, roomName);
        const decodeBox = document.getElementById('roomDecodeBox');
        decodeBox.classList.remove('d-none');
        decodeBox.className = 'alert alert-info py-2 px-3 mt-2 mb-0 small';
        decodeBox.innerHTML = `<strong>Rm ${roomNum}:</strong> ${dir}`;
        quickRouteTo(buildingId, lat, lng, '', dir);
    }

    function handleSmartSearch() {
        const query = document.getElementById('searchInput').value.trim();
        const decodeBox = document.getElementById('roomDecodeBox');
        if (!query) return;

        const qLower = query.toLowerCase();

        if (qLower.includes('evac')) {
            return routeEmergency('evac');
        }
        if (qLower === 'exit' || qLower.includes('emergency exit')) {
            return routeEmergency('exit');
        }
        if (qLower === 'cr' || qLower.includes('restroom') || qLower.includes('comfort room')) {
            document.getElementById('toggleCr').checked = true;
            toggleOverlayLayer('cr', true);
            const startNode = getCurrentOriginNode();
            let nearestCr = crLocations[0];
            let bestRes = null;
            crLocations.forEach(cr => {
                const res = findCampusPath(startNode, cr.node);
                if (!bestRes || res.totalCost < bestRes.totalCost) {
                    bestRes = res;
                    nearestCr = cr;
                }
            });
            decodeBox.classList.remove('d-none');
            decodeBox.className = 'alert alert-info py-2 px-3 mt-2 mb-0 small';
            decodeBox.innerHTML = `<strong>Nearest Restroom:</strong> ${nearestCr.name}`;
            activeRoomInstruction = nearestCr.name;
            lastRouteState = { startNode, endNode: nearestCr.node, emergencyStyle: false };
            drawPathOnMap(bestRes.pathKeys, false, true);
            return;
        }

        let matchedTarget = null;
        let explanation = '';
        let roomInstruction = '';

        if (/^\d{3}[a-zA-Z]?$/.test(query)) {
            const bNum = parseInt(query[0]);
            for (const b of campusData.buildings) {
                const foundRoom = b.rooms.find(r => r.room_number.toLowerCase() === qLower || r.room_number.toLowerCase().startsWith(qLower));
                if (foundRoom) {
                    matchedTarget = { id: b.building_id, lat: b.latitude, lng: b.longitude, name: b.building_name };
                    roomInstruction = formatRoomDirection(foundRoom.room_number, foundRoom.room_name);
                    explanation = `<strong>Room ${foundRoom.room_number}:</strong> ${roomInstruction}`;
                    break;
                }
            }
            if (!matchedTarget) {
                roomInstruction = formatRoomDirection(query);
                explanation = `<strong>Room ${query}:</strong> ${roomInstruction}`;
                const bMatch = campusData.buildings.find(b => b.building_id == bNum || b.building_name.includes(`Building ${bNum}`));
                if (bMatch) matchedTarget = { id: bMatch.building_id, lat: bMatch.latitude, lng: bMatch.longitude, name: bMatch.building_name };
            }
        } else {
            for (const b of campusData.buildings) {
                const roomHit = b.rooms.find(r =>
                    r.room_name.toLowerCase().includes(qLower) ||
                    r.room_number.toLowerCase().includes(qLower)
                );
                if (roomHit) {
                    matchedTarget = { id: b.building_id, lat: b.latitude, lng: b.longitude, name: b.building_name };
                    roomInstruction = formatRoomDirection(roomHit.room_number, roomHit.room_name);
                    explanation = `<strong>Room ${roomHit.room_number} (${roomHit.room_name}):</strong> ${roomInstruction}`;
                    break;
                }
            }
            if (!matchedTarget) {
                for (const b of campusData.buildings) {
                    if (b.building_name.toLowerCase().includes(qLower) || (b.description && b.description.toLowerCase().includes(qLower))) {
                        matchedTarget = { id: b.building_id, lat: b.latitude, lng: b.longitude, name: b.building_name };
                        explanation = `<strong>Found Building:</strong> ${b.building_name}`;
                        break;
                    }
                }
            }
            if (!matchedTarget) {
                const facHit = campusData.facilities.find(f => f.facility_name.toLowerCase().includes(qLower));
                if (facHit) {
                    matchedTarget = { id: 0, lat: facHit.latitude, lng: facHit.longitude, name: facHit.facility_name };
                    explanation = `<strong>Found Facility:</strong> ${facHit.facility_name}`;
                }
            }
        }

        decodeBox.classList.remove('d-none');
        if (matchedTarget) {
            decodeBox.className = 'alert alert-info py-2 px-3 mt-2 mb-0 small';
            decodeBox.innerHTML = explanation;
            quickRouteTo(matchedTarget.id, matchedTarget.lat, matchedTarget.lng, matchedTarget.name, roomInstruction);
        } else {
            decodeBox.className = 'alert alert-warning py-2 px-3 mt-2 mb-0 small';
            decodeBox.innerHTML = (explanation ? explanation + '<br>' : '') + 'No matching location found in database.';
        }
    }

    function useRealGPS() {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(pos => {
            const userGpsCoords = [pos.coords.latitude, pos.coords.longitude];
            const originSelect = document.getElementById('originSelect');
            const gpsVal = `${userGpsCoords[0]},${userGpsCoords[1]}`;
            originSelect.innerHTML = `<option value="${gpsVal}" selected>My Live GPS (${userGpsCoords[0].toFixed(4)}, ${userGpsCoords[1].toFixed(4)})</option>` + originSelect.innerHTML;
            if (userMarker) userMarker.setLatLng(userGpsCoords);
            calculateRoute(true, true);
        });
    }

    function quickRouteTo(buildingId, lat, lng, label = '', roomInstruction = '') {
        activeRoomInstruction = roomInstruction;
        isEmergencyRoute = false;
        const destSelect = document.getElementById('destSelect');
        if (buildingId > 0) {
            for (let i = 0; i < destSelect.options.length; i++) {
                if (destSelect.options[i].value.startsWith(buildingId + '|')) {
                    destSelect.selectedIndex = i;
                    break;
                }
            }
            if (markers['b_' + buildingId]) markers['b_' + buildingId].openPopup();
        } else if (label) {
            for (let i = 0; i < destSelect.options.length; i++) {
                if (destSelect.options[i].text.toLowerCase().includes(label.toLowerCase())) {
                    destSelect.selectedIndex = i;
                    break;
                }
            }
        }
        calculateRoute(true, true);
    }

    function clearRoute() {
        activeRoomInstruction = '';
        isEmergencyRoute = false;
        lastRouteState = null;
        if (activeRouteLayer) {
            map.removeLayer(activeRouteLayer);
            activeRouteLayer = null;
        }
        document.getElementById('routeSummary').classList.add('d-none');
        document.getElementById('routeSteps').classList.add('d-none');
        document.getElementById('roomDecodeBox').classList.add('d-none');
    }
</script>
</body>
</html>