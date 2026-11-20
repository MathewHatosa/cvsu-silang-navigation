<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('map');
});

Route::get('/admin', function () {
    $buildings = DB::table('buildings')->get();
    $rooms = DB::table('rooms')
        ->join('buildings', 'rooms.building_id', '=', 'buildings.building_id')
        ->select('rooms.*', 'buildings.building_name')
        ->get()
        ->map(function ($r) {
            $r->building = (object) ['building_name' => $r->building_name];
            return $r;
        });
    $departments = DB::table('departments')
        ->join('buildings', 'departments.building_id', '=', 'buildings.building_id')
        ->select('departments.*', 'buildings.building_name')
        ->get();
    $facilities = DB::table('facilities')->get();
    $history = DB::table('navigation_history')->orderBy('date', 'desc')->limit(15)->get();

    return view('admin', compact('buildings', 'rooms', 'departments', 'facilities', 'history'));
});

Route::get('/api/campus-data', function () {
    $buildings = DB::table('buildings')->get()->map(function ($b) {
        $b->rooms = DB::table('rooms')->where('building_id', $b->building_id)->get();
        $b->departments = DB::table('departments')->where('building_id', $b->building_id)->get();
        return $b;
    });

    $facilities = DB::table('facilities')->get();

    return response()->json([
        'buildings' => $buildings,
        'facilities' => $facilities
    ]);
});

Route::post('/api/navigation-history', function (Request $request) {
    DB::table('navigation_history')->insert([
        'user_id' => null,
        'destination_id' => $request->destination_id,
        'date' => now()
    ]);
    return response()->json(['status' => 'logged']);
});

Route::post('/admin/buildings', function (Request $request) {
    DB::table('buildings')->insert([
        'building_name' => $request->building_name,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'description' => $request->description
    ]);
    return redirect('/admin')->with('success', 'Building added successfully!');
});

Route::delete('/admin/buildings/{id}', function ($id) {
    DB::table('rooms')->where('building_id', $id)->delete();
    DB::table('departments')->where('building_id', $id)->delete();
    DB::table('navigation_history')->where('destination_id', $id)->delete();
    DB::table('buildings')->where('building_id', $id)->delete();
    return redirect('/admin')->with('success', 'Building deleted!');
});

Route::post('/admin/rooms', function (Request $request) {
    DB::table('rooms')->insert([
        'building_id' => $request->building_id,
        'room_number' => $request->room_number,
        'room_name' => $request->room_name
    ]);
    return redirect('/admin')->with('success', 'Room added successfully!');
});

Route::delete('/admin/rooms/{id}', function ($id) {
    DB::table('rooms')->where('room_id', $id)->delete();
    return redirect('/admin')->with('success', 'Room deleted!');
});

Route::post('/admin/facilities', function (Request $request) {
    DB::table('facilities')->insert([
        'facility_name' => $request->facility_name,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude
    ]);
    return redirect('/admin')->with('success', 'Facility added successfully!');
});