<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\WarehouseRequest;
use App\Models\User;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function store(WarehouseRequest $request)
    {
        $warehouse = Warehouse::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'contact_person' => $request->input('contact_person'),
            "status" => $request->input('status', true),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouse,
        ]);
    }

    public function index(Request $request)
    {
        $warehouses = Warehouse::query();

        if ($request->has('name')) {
            $warehouses->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('address')) {
            $warehouses->where('address', 'like', '%' . $request->input('address') . '%');
        }

        if ($request->has('phone')) {
            $warehouses->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        if ($request->has('email')) {
            $warehouses->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->has('contact_person')) {
            $warehouses->where('contact_person', 'like', '%' . $request->input('contact_person') . '%');
        }

        if ($request->has("status")) {
            $warehouses->where("status", $request->input("status"));
        }
        if ($request->has("search")) {
            $warehouses->where(function ($query) use ($request) {
                $query->where("name", "like", "%" . $request->input("search") . "%")
                    ->orWhere("address", "like", "%" . $request->input("search") . "%")
                    ->orWhere("phone", "like", "%" . $request->input("search") . "%")
                    ->orWhere("email", "like", "%" . $request->input("search") . "%")
                    ->orWhere("contact_person", "like", "%" . $request->input("search") . "%");
            });
        }

        if (auth()->user()->role !== RoleEnum::SUPERADMIN->value) {
            $userWarehouse = UserWarehouse::where("user_id", auth()->user()->id)
                ->pluck("warehouse_id");
            $warehouses->whereIn("id", $userWarehouse);
        }

        if ($request->has("pagination") && $request->input("pagination") == "false") {
            $warehouses = $warehouses->get();
        } else {
            $warehouses = $warehouses->paginate(10);
        }
        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouses,
        ]);
    }

    public function show($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Warehouse retrieved successfully',
            'data' => $warehouse,
        ]);
    }

    public function update(WarehouseRequest $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'contact_person' => $request->input('contact_person'),
            'status' => (int) $request->input('status', true),
        ]);

        // Update the user associated with the warehouse
        User::where('warehouse_id', $id)->update([
            'status' => (int) $request->input('status', true),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
            'data' => $warehouse,
        ]);
    }
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        //In active all user
        User::where("warehouse_id", $id)->update([
            "status" => 0,
            "deleted_at" => now(),
        ]);
        //Delete all user
        $warehouse->delete();
        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
        ]);
    }
}
