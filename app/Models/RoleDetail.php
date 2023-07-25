<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleDetail extends Model
{
    use HasFactory;
    protected $table = 'role_details';
    protected $fillable = [
        'role_name', 'status', 'created_at', 'updated_at'
    ];
}
