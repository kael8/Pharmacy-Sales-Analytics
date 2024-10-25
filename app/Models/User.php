<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sale;
use App\Models\Report;
use App\Models\Analytic;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fname',
        'lname',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function analytics()
    {
        return $this->hasMany(Analytic::class);
    }

    public function image()
    {
        return $this->hasOne(Image::class);
    }

}
