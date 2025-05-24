<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('Client:id,name')
            ->where('role', '!=', 0);
        if ($search = $request->input('search')) {
            $search = strtolower($search);
            $users->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        }
        if (auth()->user()->role !== RoleEnum::SUPERADMIN->value) {
            $users->where('warehouse_id', auth()->user()->warehouse_id);
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
            "role" => 'required|in:1,2',
            "status" => 'required|in:1,2',
            "client_id" => 'nullable|exists:warehouses,id,deleted_at,NULL',
        ]);

        $clientId = auth()->user()->role === RoleEnum::SUPERADMIN->value ? $validated['client_id'] : auth()->user()->warehouse_id;

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            "role" => $validated['role'],
            "status" => $validated['status'],
            "warehouse_id" => $clientId,
        ]);

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
            'password' => 'sometimes|required|string|min:8|confirmed',
            "role" => 'sometimes|required|in:1,2,3',
            "status" => 'sometimes|required',
            "client_id" => 'nullable|exists:warehouses,id,deleted_at,NULL',
        ]);

        if ($request->has('name')) {
            $user->name = $validated['name'];
        }
        if ($request->has('email')) {
            $user->email = $validated['email'];
        }
        if ($request->has('password')) {
            $user->password = bcrypt($validated['password']);
        }

        if ($request->has('role')) {
            $user->role = $validated['role'];
        }
        if ($request->has('status')) {
            $user->status = $validated['status'];
        }
        if ($request->has('client_id')) {
            $user->client_id = $validated['client_id'];
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
