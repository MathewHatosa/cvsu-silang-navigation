<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CvSU Silang - Administrator Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background-color: #edf3ef;
            font-family: system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }
        .cvsu-navbar {
            background: linear-gradient(135deg, #14532d 0%, #15803d 100%);
            box-shadow: 0 4px 16px rgba(20, 83, 45, 0.22);
        }
        .dashboard-wrap {
            max-width: 1480px;
            margin: 0 auto;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #dbe5df;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
            padding: 1rem 1.25rem;
        }
        .stat-label {
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
        }
        .stat-value {
            font-size: 1.65rem;
            font-weight: 800;
            color: #14532d;
            line-height: 1.2;
        }
        .card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid #dbe5df;
            border-radius: 14px;
            box-shadow: 0 6px 22px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .card-header-cvsu {
            background: #15803d;
            color: #ffffff;
            font-weight: 600;
            padding: 0.85rem 1.2rem;
            border-bottom: none;
        }
        .card-header-slate {
            background: #1e293b;
            color: #ffffff;
            font-weight: 600;
            padding: 0.85rem 1.2rem;
            border-bottom: none;
        }
        .card-header-light {
            background: #ffffff;
            color: #14532d;
            font-weight: 700;
            padding: 0.9rem 1.2rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .sticky-forms {
            position: sticky;
            top: 16px;
        }
        .btn-cvsu {
            background-color: #15803d;
            border-color: #15803d;
            color: #ffffff;
            font-weight: 600;
        }
        .btn-cvsu:hover {
            background-color: #166534;
            border-color: #166534;
            color: #ffffff;
        }
        .btn-slate {
            background-color: #1e293b;
            border-color: #1e293b;
            color: #ffffff;
            font-weight: 600;
        }
        .btn-slate:hover {
            background-color: #0f172a;
            border-color: #0f172a;
            color: #ffffff;
        }
        #pickerMap {
            height: 260px;
            width: 100%;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            z-index: 1;
        }
        .table-scroll-rooms {
            max-height: 480px;
            overflow-y: auto;
        }
        .table-scroll-rooms thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .table thead th {
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .table tbody td {
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .coord-pill {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-family: ui-monospace, SFMono-Regular, monospace;
            font-size: 0.76rem;
            padding: 0.22rem 0.5rem;
            border-radius: 6px;
            display: inline-block;
            white-space: nowrap;
        }
        .room-pill {
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            display: inline-block;
        }
        .nav-pills .nav-link {
            color: #14532d;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
        }
        .nav-pills .nav-link.active {
            background-color: #15803d;
            color: #ffffff;
        }
        .form-control:focus, .form-select:focus {
            border-color: #15803d;
            box-shadow: 0 0 0 0.2rem rgba(21, 128, 61, 0.15);
        }
    </style>
</head>
<body>

<nav class="navbar cvsu-navbar px-4 py-3 mb-4">
    <div class="container-fluid dashboard-wrap px-0 d-flex justify-content-between align-items-center">
        <div>
            <span class="navbar-brand text-white fw-bold mb-0 h5 d-block">CvSU Silang Navigator — Admin Dashboard</span>
            <small class="text-white-50">Campus Buildings, Coordinates, Room Directory & Facilities</small>
        </div>
        <a href="/" class="btn btn-light btn-sm fw-semibold text-success px-3 shadow-sm">View Live Campus Map</a>
    </div>
</nav>

<div class="container-fluid dashboard-wrap px-4 pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Campus Buildings</div>
                <div class="stat-value">{{ count($buildings) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Rooms & Labs</div>
                <div class="stat-value">{{ count($rooms) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Campus Facilities</div>
                <div class="stat-value">{{ count($facilities ?? []) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-label">Recent Navigations</div>
                <div class="stat-value">{{ count($history ?? []) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-xl-4 col-lg-5">
            <div class="sticky-forms">
                <div class="card mb-4">
                    <div class="card-header-cvsu d-flex justify-content-between align-items-center">
                        <span>Add New Campus Building</span>
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size: 11px;" onclick="resetPickerView()">Reset Map</button>
                    </div>
                    <div class="card-body p-3">
                        <p class="small text-muted mb-2">Click or drag the pin inside CvSU Silang to auto-fill GPS coordinates:</p>
                        <div id="pickerMap" class="mb-3"></div>

                        <form action="/admin/buildings" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Building Name</label>
                                <input type="text" name="building_name" class="form-control form-control-sm" placeholder="e.g., Building 6 - Science Wing" required>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Latitude</label>
                                    <input type="text" id="latInput" name="latitude" class="form-control form-control-sm" value="14.24125000" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Longitude</label>
                                    <input type="text" id="lngInput" name="longitude" class="form-control form-control-sm" value="120.97910000" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold mb-1">Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Departments, offices, or labs inside..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-cvsu btn-sm w-100 py-2">Save Building to Database</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header-slate">
                        Add Room / Laboratory
                    </div>
                    <div class="card-body p-3">
                        <form action="/admin/rooms" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Select Building</label>
                                <select name="building_id" class="form-select form-select-sm" required>
                                    @foreach($buildings as $b)
                                        <option value="{{ $b->building_id }}">{{ $b->building_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <label class="form-label small fw-semibold mb-1">Room #</label>
                                    <input type="text" name="room_number" class="form-control form-control-sm" placeholder="325" required>
                                </div>
                                <div class="col-8">
                                    <label class="form-label small fw-semibold mb-1">Room Name</label>
                                    <input type="text" name="room_name" class="form-control form-control-sm" placeholder="Networking Lab" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-slate btn-sm w-100 py-2">Save Room to Database</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header-light d-flex justify-content-between align-items-center">
                    <span>Campus Buildings ({{ count($buildings) }})</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">CvSU Silang</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Building Name</th>
                                <th>Coordinates</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buildings as $b)
                                <tr>
                                    <td class="ps-3 fw-semibold text-muted">{{ $b->building_id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $b->building_name }}</div>
                                        <div class="small text-muted">{{ $b->description }}</div>
                                    </td>
                                    <td>
                                        <span class="coord-pill">{{ $b->latitude }}, {{ $b->longitude }}</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="/admin/buildings/{{ $b->building_id }}" method="POST" onsubmit="return confirm('Delete this building and all its rooms?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No buildings registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span>Rooms & Laboratories (<span id="roomVisibleCount">{{ count($rooms) }}</span> / {{ count($rooms) }})</span>
                        <div class="d-flex gap-2">
                            <select id="filterBuilding" class="form-select form-select-sm" style="width: 190px;" onchange="filterRooms()">
                                <option value="">All Buildings</option>
                                @foreach($buildings as $b)
                                    <option value="{{ strtolower($b->building_name) }}">{{ $b->building_name }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="filterRoomSearch" class="form-control form-control-sm" style="width: 180px;" placeholder="Search room # or name..." oninput="filterRooms()">
                        </div>
                    </div>
                </div>
                <div class="table-responsive table-scroll-rooms">
                    <table class="table table-hover mb-0" id="roomsTable">
                        <thead>
                            <tr>
                                <th class="ps-3">Room #</th>
                                <th>Room Name</th>
                                <th>Building</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rooms as $r)
                                <tr class="room-row" data-building="{{ strtolower($r->building->building_name ?? $r->building_name ?? '') }}" data-text="{{ strtolower($r->room_number . ' ' . $r->room_name) }}">
                                    <td class="ps-3">
                                        <span class="room-pill">{{ $r->room_number }}</span>
                                    </td>
                                    <td class="fw-medium">{{ $r->room_name }}</td>
                                    <td class="small text-muted">{{ $r->building->building_name ?? $r->building_name ?? 'Unassigned' }}</td>
                                    <td class="text-end pe-3">
                                        <form action="/admin/rooms/{{ $r->room_id }}" method="POST" onsubmit="return confirm('Delete this room?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No rooms registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header-light d-flex justify-content-between align-items-center">
                    <span>Campus Facilities ({{ count($facilities ?? []) }})</span>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Map Landmarks</span>
                </div>
                <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Facility Name</th>
                                <th class="text-end pe-3">Coordinates</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facilities ?? [] as $f)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">{{ $f->facility_id }}</td>
                                    <td class="fw-medium">{{ $f->facility_name }}</td>
                                    <td class="text-end pe-3">
                                        <span class="coord-pill">{{ $f->latitude }}, {{ $f->longitude }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No facilities registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const cvsuBounds = L.latLngBounds(
        [14.2397, 120.9778],
        [14.2421, 120.9800]
    );

    const pickerMap = L.map('pickerMap', {
        center: [14.24120, 120.97905],
        zoom: 18,
        minZoom: 18,
        maxZoom: 21,
        maxBounds: cvsuBounds,
        maxBoundsViscosity: 1.0
    });

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

    osmLayer.addTo(pickerMap);
    L.control.layers({ "OSM": osmLayer, "Satellite": esriSat }, null, { position: 'topright' }).addTo(pickerMap);

    const existingBuildings = @json($buildings);
    existingBuildings.forEach(b => {
        if (b.latitude && b.longitude) {
            L.circleMarker([parseFloat(b.latitude), parseFloat(b.longitude)], {
                radius: 6,
                color: '#ffffff',
                weight: 2,
                fillColor: '#15803d',
                fillOpacity: 0.9
            }).addTo(pickerMap).bindTooltip(b.building_name, { direction: 'top' });
        }
    });

    let selectedMarker = L.marker([14.24125, 120.97910], { draggable: true }).addTo(pickerMap);

    function updateInputs(lat, lng) {
        document.getElementById('latInput').value = lat.toFixed(8);
        document.getElementById('lngInput').value = lng.toFixed(8);
    }

    pickerMap.on('click', function(e) {
        selectedMarker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    selectedMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateInputs(pos.lat, pos.lng);
    });

    function resetPickerView() {
        pickerMap.setView([14.24120, 120.97905], 18);
    }

    function filterRooms() {
        const bFilter = document.getElementById('filterBuilding').value;
        const qFilter = document.getElementById('filterRoomSearch').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.room-row');
        let visible = 0;

        rows.forEach(row => {
            const matchB = !bFilter || row.dataset.building === bFilter;
            const matchQ = !qFilter || row.dataset.text.includes(qFilter);
            const show = matchB && matchQ;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('roomVisibleCount').textContent = visible;
    }
</script>
</body>
</html>