<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserWarehouseController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->user()->id;
        $warehouses = \App\Models\UserWarehouse::withUserWarehouseId($userId);

        if ($search = $request->input('search')) {
            $search = strtolower($search);
            $warehouses->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(warehouses.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(users.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(users.email) LIKE ?', ["%{$search}%"]);
            });
        }

        $warehouses = $warehouses->get();

        return response()->json([
            'status' => 'success',
            'message' => 'User warehouses retrieved successfully',
            'data' => $warehouses,
        ], 200);
    }
}
