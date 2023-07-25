<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalDetails extends Model
{
    use HasFactory;
    protected $table = 'portal_details';
    protected $fillable = [
        'portal_name', 'status', 'created_at', 'updated_at'
    ];
}
