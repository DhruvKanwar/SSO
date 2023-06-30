<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;
    protected $table = 'user_details';
    protected $fillable = [
        'user_id', 'portal_id', 'role_id', 'assign_date', 'remove_date', 'remarks', 'updated_by', 'updated_id',
        'created_at', 'updated_at'
    ];
}
