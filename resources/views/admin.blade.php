<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Campus Navigation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #pickerMap { height: 260px; width: 100%; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4 mb-4">
    <span class="navbar-brand fw-bold">Campus Navigation — Administrator Dashboard</span>
    <a href="/" class="btn btn-outline-light btn-sm">View Live Campus Map</a>
</nav>

<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <!-- Add Building + Map Coordinate Picker -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white fw-bold">Add New Campus Building</div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Click anywhere on the mini-map below to auto-fill GPS coordinates:</p>
                    <div id="pickerMap" class="mb-3 border"></div>

                    <form action="/admin/buildings" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Building Name</label>
                            <input type="text" name="building_name" class="form-control form-control-sm" placeholder="e.g., Building 4 - Science Wing" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Latitude</label>
                                <input type="text" id="latInput" name="latitude" class="form-control form-control-sm" value="14.22380000" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Longitude</label>
                                <input type="text" id="lngInput" name="longitude" class="form-control form-control-sm" value="120.97450000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Departments, offices, or labs inside..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Save Building to Database</button>
                    </form>
                </div>
            </div>

            <!-- Add Room to Building -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold">Add Room / Laboratory</div>
                <div class="card-body">
                    <form action="/admin/rooms" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Select Building</label>
                            <select name="building_id" class="form-select form-select-sm" required>
                                @foreach($buildings as $b)
                                    <option value="{{ $b->building_id }}">{{ $b->building_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label small fw-bold">Room #</label>
                                <input type="text" name="room_number" class="form-control form-control-sm" placeholder="325" required>
                            </div>
                            <div class="col-8">
                                <label class="form-label small fw-bold">Room Name</label>
                                <input type="text" name="room_name" class="form-control form-control-sm" placeholder="Networking Lab" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-sm w-100 fw-bold">Add Room</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Database Records Tables -->
        <div class="col-lg-7">
            <!-- Buildings List -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Campus Buildings ({{ count($buildings) }})</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0 small align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Building Name</th>
                                <th>Coordinates</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buildings as $b)
                            <tr>
                                <td>{{ $b->building_id }}</td>
                                <td>
                                    <strong>{{ $b->building_name }}</strong><br>
                                    <span class="text-muted">{{ $b->description }}</span>
                                </td>
                                <td>{{ $b->latitude }}, {{ $b->longitude }}</td>
                                <td>
                                    <form action="/admin/buildings/{{ $b->building_id }}/delete" method="POST">
                                        @csrf
                                        <button class="btn btn-danger btn-sm py-0 px-2">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rooms List -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Rooms & Laboratories ({{ count($rooms) }})</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0 small align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Room #</th>
                                <th>Room Name</th>
                                <th>Building</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $r)
                            <tr>
                                <td class="fw-bold">{{ $r->room_number }}</td>
                                <td>{{ $r->room_name }}</td>
                                <td>{{ $r->building_name }}</td>
                                <td>
                                    <form action="/admin/rooms/{{ $r->room_id }}/delete" method="POST">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm py-0 px-2">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Navigation History Logs -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Recent Navigation History Logs</div>
                <div class="card-body p-0">
                    <table class="table mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Log ID</th>
                                <th>Destination Building ID</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $h)
                            <tr>
                                <td>#{{ $h->history_id }}</td>
                                <td>Building ID: {{ $h->destination_id }}</td>
                                <td>{{ $h->date }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted text-center">No routes calculated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const pickerMap = L.map('pickerMap').setView([14.2233, 120.9744], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickerMap);
    let pickMarker = L.marker([14.2238, 120.9745]).addTo(pickerMap);

    pickerMap.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);
        document.getElementById('latInput').value = lat;
        document.getElementById('lngInput').value = lng;
        pickMarker.setLatLng(e.latlng);
    });
</script>
</body>
</html>