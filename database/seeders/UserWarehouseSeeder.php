<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.P
     */
    public function run(): void
    {
        $users = DB::table("users")->select("id", "warehouse_id")->get();
        foreach ($users as $user) {
            $existing = DB::table("user_warehouses")
                ->where("user_id", $user->id)
                ->where("warehouse_id", $user->warehouse_id)
                ->first();
            if ($existing) {
                $this->command->info("UserWarehouse for user ID {$user->id} and warehouse ID {$user->warehouse_id} already exists. Skipping.");
                continue;
            }
            if (!$user->warehouse_id) {
                $this->command->warn("User ID {$user->id} does not have a warehouse ID. Skipping.");
                continue;
            }
            DB::table("user_warehouses")->insert([
                "user_id" => $user->id,
                "warehouse_id" => $user->warehouse_id,
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ]);
        }
        $this->command->info("UserWarehouseSeeder completed successfully.");
    }
}
