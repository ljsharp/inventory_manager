<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::select('id', 'name', 'location', 'contact_info', 'capacity')->get();
        // return response()->json($warehouses);
        return Inertia::render('admin/Warehouse/Index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return Inertia::render('admin/Warehouse/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        info($request->all());
        $warehouse = Warehouse::create($request->validated());

        // return response()->json([
        //     'message' => 'Warehouse created successfully!',
        //     'warehouse' => $warehouse
        // ], 201);

        return to_route('admin.warehouses.index')->with('success', 'Warehouse created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        return response()->json($warehouse);
        // return Inertia::render('admin/Warehouse/Show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return response()->json($warehouse);
        // return Inertia::render('admin/Warehouse/Show', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        // return response()->json([
        //     'message' => 'Warehouse updated successfully!',
        //     'warehouse' => $warehouse
        // ]);

        return to_route('admin.warehouses.index')->with('success', 'Warehouse updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        // return response()->json(['message' => 'Warehouse deleted successfully!']);
        return to_route('admin.warehouses.index')->with('success', 'Warehouse deleted successfully!');
    }
}
