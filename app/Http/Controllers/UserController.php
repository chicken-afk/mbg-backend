<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserWarehouse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('warehouses:id,name')
            ->where('role', '!=', 0);
        if ($search = $request->input('search')) {
            $search = strtolower($search);
            $users->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        }
        if (auth()->user()->role !== RoleEnum::SUPERADMIN->value) {
            $users->withWarehouseId($request->input('warehouse_id', auth()->user()->warehouses->pluck('id')->toArray()));
        }
        $users = $users->orderBy('created_at', 'desc')->paginate(10);
        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            "role" => 'required|in:1,2,3',
            "status" => 'required|in:1,2',
            "client_id" => 'required|array',
            "client_id.*" => 'exists:warehouses,id,deleted_at,NULL',
        ]);

        $clientId = $validated['client_id'];

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            "role" => $validated['role'],
            "status" => $validated['status']
        ]);

        foreach ($clientId as $id) {
            $user->warehouses()->attach($id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }
    public function show($id)
    {
        $user = \App\Models\User::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $user,
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            "role" => 'sometimes|required|in:1,2,3',
            "status" => 'sometimes|required',
            "client_id" => "required|array",
            "client_id.*" => 'exists:warehouses,id,deleted_at,NULL',
        ]);

        if ($request->has('name')) {
            $user->name = $validated['name'];
        }
        if ($request->has('email')) {
            $user->email = $validated['email'];
        }
        if ($request->has('password') && $request->input('password') !== null) {
            // Only update password if it is provided
            $user->password = bcrypt($validated['password']);
        }

        if ($request->has('role')) {
            $user->role = $validated['role'];
        }
        if ($request->has('status')) {
            $user->status = $validated['status'];
        }
        // Detach all warehouses first
        $user->warehouses()->detach();
        foreach ($request->input('client_id', []) as $id) {
            if (!$user->warehouses()->where('warehouse_id', $id)->exists()) {
                $user->warehouses()->attach($id);
            }
        }


        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user,
        ], 200);
    }
    public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ], 200);
    }
    public function me()
    {
        $user = auth()->user();
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $user,
        ], 200);
    }
}
