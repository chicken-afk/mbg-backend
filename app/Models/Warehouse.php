<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ["id", "created_at", "updated_at"];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'status'
    ];
    protected $hidden = [
        'deleted_at',
    ];
}
