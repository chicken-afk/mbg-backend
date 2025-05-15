<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ["id", "created_at", "updated_at"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Decode the additional_data column
    public function getAdditionalDataAttribute($value)
    {
        return json_decode($value, true);
    }

    //cast date
    protected $casts = [
        'transaction_at' => 'datetime',
    ];
}
