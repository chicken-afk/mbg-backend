<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWarehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'warehouse_id',
    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function scopeWithWarehouse($query)
    {
        return $query->with('warehouse:id,name');
    }
    public function scopeWithUser($query)
    {
        return $query->with('user:id,name,email');
    }
    public function scopeWithUserWarehouse($query)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name']);
    }
    public function scopeWithUserWarehouseId($query, $userId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('user_id', $userId);
    }
    public function scopeWithWarehouseId($query, $warehouseId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('warehouse_id', $warehouseId);
    }
    public function scopeWithUserWarehouseIds($query, $userId, $warehouseId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('user_id', $userId)
            ->where('warehouse_id', $warehouseId);
    }
    public function scopeWithUserWarehouseIdsOrWarehouseId($query, $userId, $warehouseId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('user_id', $userId)
            ->orWhere('warehouse_id', $warehouseId);
    }
    public function scopeWithUserWarehouseIdsOrWarehouseIdAndUserId($query, $userId, $warehouseId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('user_id', $userId)
            ->orWhere('warehouse_id', $warehouseId)
            ->where('user_id', '!=', $userId);
    }
    public function scopeWithUserWarehouseIdsAndWarehouseId($query, $userId, $warehouseId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('user_id', $userId)
            ->where('warehouse_id', $warehouseId);
    }
    public function scopeWithUserWarehouseIdsAndWarehouseIdOrUserId($query, $userId, $warehouseId)
    {
        return $query->with(['user:id,name,email', 'warehouse:id,name'])
            ->where('user_id', $userId)
            ->where('warehouse_id', $warehouseId)
            ->orWhere('user_id', $userId);
    }
}
