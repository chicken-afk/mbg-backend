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
        'options' => 'array',
        'required' => 'boolean',
    ];
    protected $attributes = [
        'options' => '[]',
        'required' => false,
    ];
    public function getOptionsAttribute($value)
    {
        return json_decode($value, true);
    }
}
