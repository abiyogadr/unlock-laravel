<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Upanel extends Model
{
    protected $fillable = ['user_id', 'username', 'name'];
    
    protected static function boot()
    {
        parent::boot();
        
        // Auto-sync saat create
        static::creating(function ($upanel) {
            $user = User::find($upanel->user_id);
            if ($user) {
                $upanel->username = $user->username;
                $upanel->name = $user->name;
            }
        });
    }
}
