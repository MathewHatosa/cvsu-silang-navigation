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
            width: 380px; max-height: 92vh; overflow-y: auto;
            background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(8px);
            border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.18);
        }
        .room-badge { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .step-list { max-height: 140px; overflow-y: auto; font-size: 12px; }
        .calib-dot {
            background: #dc2626; border: 2px solid #ffffff; border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.8); cursor: grab;
        }
    </style>
</head>
<body>

<div class="control-panel p-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h5 class="fw-bold mb-0 text-success">CvSU Silang Navigator</h5>
        <a href="/admin" class="btn btn-sm btn-outline-dark">Admin Dashboard</a>
    </div>
    <p class="text-muted small mb-2">GPS, Digital Map & Room Directory</p>

    <!-- Smart Room / Building Search -->
    <div class="mb-3">
        <label class="form-label small fw-bold">Smart Search (Try "324", "Tourism", or "Canteen")</label>
        <div class="input-group">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Enter Room #, Building, or Facility...">
            <button class="btn btn-success btn-sm" onclick="handleSmartSearch()">Locate</button>
        </div>
        <div id="roomDecodeBox" class="alert alert-info py-2 px-3 mt-2 mb-0 small d-none"></div>
    </div>

    <!-- Origin / Destination Route Builder -->
    <div class="card bg-light border-0 mb-3">
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
            <div class="d-grid gap-2 d-md-flex">
                <button class="btn btn-primary btn-sm flex-grow-1 fw-semibold" onclick="calculateRoute()">Start Route</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearRoute()">Clear</button>
            </div>
            <div id="routeSummary" class="mt-2 small fw-bold text-success d-none"></div>
            <ol id="routeSteps" class="step-list mt-2 mb-0 ps-3 d-none"></ol>
        </div>
    </div>

    <!-- Directory Accordion -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold small text-uppercase text-secondary mb-0">Campus Directory</h6>
        <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="resetCampusView()">Reset View</button>
    </div>
    <div class="accordion accordion-flush mb-2" id="directoryAccordion"></div>

    <!-- Live Dotted-Line Alignment Helper -->
    <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="calibSwitch" onchange="toggleCalibration(this.checked)">
            <label class="form-check-label small text-muted" for="calibSwitch">Align / Drag Corner Dots</label>
        </div>
        <button id="resetCalibBtn" class="btn btn-outline-danger btn-sm py-0 px-2 d-none" style="font-size:11px" onclick="resetCalibration()">Reset</button>
    </div>
</div>

<div id="map"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const cvsuBounds = L.latLngBounds(
        [14.2403, 120.9780],
        [14.2421, 120.9800]
    );

    const map = L.map('map', {
        zoomControl: false,
        center: [14.24125, 120.97895],
        zoom: 19,
        minZoom: 19,
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

    // =========================================================================
    // EXACT BLACK-LINE PATHWAY NETWORK (Straight Bldg 1-2 Corridor + Normal West Path to Bldg 3)
    // =========================================================================
    const defaultCampusNodes = {
        // 1. West Main Pathway Spine (Black line from Guard House up to North-West corner of Gym)
        w0:          { lat: 14.240890, lng: 120.978745, name: "Guard House / Main Gate" },
        w_b1_jct:    { lat: 14.241042, lng: 120.978742, name: "West Walkway (First Building Junction)" },
        w_gym_n:     { lat: 14.241428, lng: 120.978822, name: "West Walkway (Upper Gym Corner)" },
        w_north:     { lat: 14.241715, lng: 120.978882, name: "North-West Corner (Above Third Building)" },

        // 2. Straight Black-Line Shortcut Inside/Along Building 1 & Building 2
        b1_pin:      { lat: 14.241010, lng: 120.978915, name: "Building 1 - Administration" },
        clinic_door: { lat: 14.240982, lng: 120.979065, name: "University Clinic" },
        s_gap:       { lat: 14.240972, lng: 120.979122, name: "Central Walkway Intersection (Between Bldg 1 & 2)" },
        b2_pin:      { lat: 14.240938, lng: 120.979310, name: "Building 2 - General Academic" },
        b2_east:     { lat: 14.240905, lng: 120.979485, name: "Second Building East End" },

        // 3. Normal Upper Pathway to Building 3 (Black line above Gym turning straight into Third Bldg)
        b3_turn:     { lat: 14.241382, lng: 120.979092, name: "Third Building Approach Corner" },
        b3_pin:      { lat: 14.241525, lng: 120.979118, name: "Building 3 - IT & Computer Science" },

        // 4. Central Gymnasium Shortcut (For Building 2 -> Gym / North Campus)
        gym_c:       { lat: 14.241215, lng: 120.979168, name: "Campus Gymnasium / Covered Court" },
        n_gap:       { lat: 14.241365, lng: 120.979198, name: "Gymnasium North Exit" },

        // 5. East Walkway to HM/Tourism & North Canteen
        e_alley_s:   { lat: 14.241345, lng: 120.979310, name: "East Alley Intersection" },
        hm_alley_s:  { lat: 14.241328, lng: 120.979405, name: "HM & Tourism Walkway" },
        hm_pin:      { lat: 14.241465, lng: 120.979430, name: "Hostel for HM & Tourism Building" },
        n_east:      { lat: 14.241635, lng: 120.979360, name: "North-East Corner (Student Park)" },
        canteen_jct: { lat: 14.241670, lng: 120.979175, name: "North Walkway to Canteen" },
        canteen_pin: { lat: 14.241785, lng: 120.979205, name: "Student Canteen" }
    };

    const STORAGE_KEY = 'cvsu_silang_paths_v6';
    let campusNodes = JSON.parse(localStorage.getItem(STORAGE_KEY)) || JSON.parse(JSON.stringify(defaultCampusNodes));

    // Connections matching your black line drawing
    const campusEdges = [
        // 1. West Spine (Guard House -> Bldg 1 Junction -> Upper Gym Corner -> North-West)
        ['w0', 'w_b1_jct'],
        ['w_b1_jct', 'w_gym_n'],
        ['w_gym_n', 'w_north'],

        // 2. Straight Black-Line Corridor Through Building 1 & Building 2
        ['w_b1_jct', 'b1_pin'],
        ['b1_pin', 'clinic_door'],
        ['clinic_door', 's_gap'],
        ['s_gap', 'b2_pin'],
        ['b2_pin', 'b2_east'],

        // 3. Normal Pathway to Building 3 (Upper Gym Corner -> Turn -> Building 3)
        ['w_gym_n', 'b3_turn'],
        ['b3_turn', 'b3_pin'],

        // 4. Central Gymnasium Shortcut (Connected for Building 2 & Gym routing)
        ['s_gap', 'gym_c'],
        ['gym_c', 'n_gap'],
        ['b3_turn', 'n_gap'],

        // 5. HM/Tourism & North Canteen Connections
        ['n_gap', 'e_alley_s'],
        ['e_alley_s', 'hm_alley_s'],
        ['hm_alley_s', 'hm_pin'],
        ['e_alley_s', 'n_east'],
        ['w_north', 'canteen_jct'],
        ['canteen_jct', 'n_east'],
        ['canteen_jct', 'canteen_pin']
    ];

    let campusData = { buildings: [], facilities: [] };
    let markers = {};
    let facilityLayerGroup = L.layerGroup().addTo(map);
    let activeRouteLayer = null;
    let calibLayer = null;
    let userMarker = null;

    fetch('/api/campus-data')
        .then(res => res.json())
        .then(data => {
            campusData = data;
            renderCampusData();
        });

    function resetCampusView() {
        map.setView([14.24125, 120.97895], 19);
    }

    function distMeters(lat1, lng1, lat2, lng2) {
        return map.distance([lat1, lng1], [lat2, lng2]);
    }

    function toggleCalibration(enabled) {
        document.getElementById('resetCalibBtn').classList.toggle('d-none', !enabled);
        if (calibLayer) {
            map.removeLayer(calibLayer);
            calibLayer = null;
        }
        if (!enabled) return;

        calibLayer = L.layerGroup().addTo(map);
        drawCalibNetwork();
    }

    function drawCalibNetwork() {
        if (!calibLayer) return;
        calibLayer.clearLayers();

        campusEdges.forEach(([a, b]) => {
            L.polyline(
                [[campusNodes[a].lat, campusNodes[a].lng], [campusNodes[b].lat, campusNodes[b].lng]],
                { color: '#111827', weight: 3, opacity: 0.85 }
            ).addTo(calibLayer);
        });

        for (const [key, node] of Object.entries(campusNodes)) {
            const dotIcon = L.divIcon({ className: 'calib-dot', iconSize: [12, 12], iconAnchor: [6, 6] });
            const m = L.marker([node.lat, node.lng], { icon: dotIcon, draggable: true })
                .bindTooltip(`<b>${key}</b>: ${node.name}`, { direction: 'top' })
                .addTo(calibLayer);

            m.on('drag', function(e) {
                const pos = e.target.getLatLng();
                campusNodes[key].lat = parseFloat(pos.lat.toFixed(6));
                campusNodes[key].lng = parseFloat(pos.lng.toFixed(6));
                calculateRoute();
            });

            m.on('dragend', function() {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(campusNodes));
                drawCalibNetwork();
                renderCampusData();
                calculateRoute();
            });
        }
    }

    function resetCalibration() {
        localStorage.removeItem(STORAGE_KEY);
        campusNodes = JSON.parse(JSON.stringify(defaultCampusNodes));
        drawCalibNetwork();
        renderCampusData();
        calculateRoute();
    }

    function resolveGraphNode(id, lat, lng, label = '') {
        const bId = parseInt(id);
        if (bId === 1) return 'b1_pin';
        if (bId === 2) return 'b2_pin';
        if (bId === 3) return 'b3_pin';
        if (bId === 4) return 'hm_pin';

        const text = label.toLowerCase();
        if (text.includes('guard') || text.includes('gate')) return 'w0';
        if (text.includes('gym')) return 'gym_c';
        if (text.includes('clinic')) return 'clinic_door';
        if (text.includes('canteen')) return 'canteen_pin';
        if (text.includes('building 1')) return 'b1_pin';
        if (text.includes('building 2')) return 'b2_pin';
        if (text.includes('building 3')) return 'b3_pin';
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

                let weight = distMeters(campusNodes[u].lat, campusNodes[u].lng, campusNodes[v].lat, campusNodes[v].lng);

                // Keep Building 1 -> Building 3 on the normal West Pathway (black line)
                // while allowing Building 2 -> Building 3 / Gym to use the central Gym shortcut
                if (startNode === 'b1_pin' && endNode === 'b3_pin' && ((u === 's_gap' && v === 'gym_c') || (u === 'gym_c' && v === 's_gap'))) {
                    weight += 50;
                }

                const alt = dists[u] + weight;
                if (alt < dists[v]) {
                    dists[v] = alt;
                    prev[v] = u;
                }
            });
        }

        const pathKeys = [];
        let curr = endNode;
        while (curr) {
            pathKeys.unshift(curr);
            curr = prev[curr];
        }
        return pathKeys;
    }

    function calculateRoute() {
        const originSelect = document.getElementById('originSelect');
        const destSelect = document.getElementById('destSelect');
        const originVal = originSelect.value;
        const destVal = destSelect.value;

        if (!originVal || !destVal) {
            if (originVal && !destVal && destSelect.options.length > 1) {
                destSelect.selectedIndex = 1;
                return calculateRoute();
            }
            return;
        }

        const originLabel = originSelect.options[originSelect.selectedIndex].text;
        const destLabel = destSelect.options[destSelect.selectedIndex].text;

        const [oLat, oLng] = originVal.split(',').map(Number);
        const [destId, destCoords] = destVal.split('|');
        const [dLat, dLng] = destCoords.split(',').map(Number);

        if (activeRouteLayer) map.removeLayer(activeRouteLayer);

        const startNode = resolveGraphNode(0, oLat, oLng, originLabel);
        const endNode = resolveGraphNode(destId, dLat, dLng, destLabel);

        const nodeKeys = findCampusPath(startNode, endNode);
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

        const endPt = latlngs[latlngs.length - 1];
        activeRouteLayer = L.featureGroup([
            L.polyline(latlngs, { color: '#0c4a6e', weight: 8, opacity: 0.85, lineJoin: 'miter' }),
            L.polyline(latlngs, { color: '#38bdf8', weight: 4, opacity: 1, lineJoin: 'miter' }),
            L.circleMarker(endPt, { radius: 6, color: '#ffffff', weight: 2, fillColor: '#0284c7', fillOpacity: 1 })
        ]).addTo(map);

        const roundedDist = Math.max(5, Math.round(totalMeters));
        const estMinutes = Math.max(1, Math.ceil(roundedDist / 75));

        const sumBox = document.getElementById('routeSummary');
        sumBox.classList.remove('d-none');
        sumBox.innerHTML = `Fastest Route: ${roundedDist} m &bull; Est. Walk: ~${estMinutes} min`;

        const stepList = document.getElementById('routeSteps');
        stepList.classList.remove('d-none');
        stepList.innerHTML = stepsHtml.join('');

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
            4: [campusNodes.hm_pin.lat, campusNodes.hm_pin.lng]
        };

        campusData.buildings.forEach((b, index) => {
            const coords = pinOverrides[b.building_id] || [parseFloat(b.latitude), parseFloat(b.longitude)];
            const roomListHtml = b.rooms.map(r => `<span class="badge room-badge me-1 mb-1">Rm ${r.room_number}: ${r.room_name}</span>`).join('');

            const marker = L.marker(coords).addTo(map).bindPopup(`
                <div style="min-width:200px">
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
                        <div class="accordion-body p-2 small">
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
            if (nameLower.includes('guard')) return;

            let fCoords = [parseFloat(f.latitude), parseFloat(f.longitude)];
            if (nameLower.includes('gym')) fCoords = [campusNodes.gym_c.lat, campusNodes.gym_c.lng];
            if (nameLower.includes('clinic')) fCoords = [campusNodes.clinic_door.lat, campusNodes.clinic_door.lng];
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

    function handleSmartSearch() {
        const query = document.getElementById('searchInput').value.trim();
        const decodeBox = document.getElementById('roomDecodeBox');
        if (!query) return;

        let matchedTarget = null;
        let explanation = '';

        if (/^\d{3}$/.test(query)) {
            const bNum = parseInt(query[0]);
            const floorNum = parseInt(query[1]);
            const rNum = query === '324' ? 3 : parseInt(query[2]);

            explanation = `<strong>Room ${query} Decoded:</strong> Building ${bNum} &bull; Floor ${floorNum} &bull; Room #${rNum}`;

            for (const b of campusData.buildings) {
                const foundRoom = b.rooms.find(r => r.room_number === query);
                if (foundRoom) {
                    matchedTarget = { id: b.building_id, lat: b.latitude, lng: b.longitude, name: b.building_name };
                    explanation += `<br><span class="text-dark">Database Match: ${foundRoom.room_name} (${b.building_name})</span>`;
                    break;
                }
            }
            if (!matchedTarget) {
                const bMatch = campusData.buildings.find(b => b.building_id == bNum || b.building_name.includes(`Building ${bNum}`));
                if (bMatch) matchedTarget = { id: bMatch.building_id, lat: bMatch.latitude, lng: bMatch.longitude, name: bMatch.building_name };
            }
        } else {
            const qLower = query.toLowerCase();
            for (const b of campusData.buildings) {
                const roomHit = b.rooms.find(r => r.room_name.toLowerCase().includes(qLower) || r.room_number.toLowerCase().includes(qLower));
                if (b.building_name.toLowerCase().includes(qLower) || (b.description && b.description.toLowerCase().includes(qLower)) || roomHit) {
                    matchedTarget = { id: b.building_id, lat: b.latitude, lng: b.longitude, name: b.building_name };
                    explanation = roomHit
                        ? `<strong>Found Room ${roomHit.room_number}:</strong> ${roomHit.room_name} inside <b>${b.building_name}</b>`
                        : `<strong>Found Building:</strong> ${b.building_name}`;
                    break;
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
            quickRouteTo(matchedTarget.id, matchedTarget.lat, matchedTarget.lng, matchedTarget.name);
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
            calculateRoute();
        });
    }

    function quickRouteTo(buildingId, lat, lng, label = '') {
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
        calculateRoute();
    }

    function clearRoute() {
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