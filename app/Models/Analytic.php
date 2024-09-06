<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'analysis_type',
        'results',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
