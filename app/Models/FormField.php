<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'required' => 'boolean',
        // 'options' => 'array',
    ];
    protected $attributes = [
        'required' => false,
    ];

    public function getOptionsAttribute($value)
    {
        if (is_string($value) && strlen($value) > 1 && $value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }
        // if (is_null($value)) {
        //     return [];
        // }
        return json_decode($value, true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
