<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailnotSent extends Model
{
    use HasFactory;
    protected $table = 'mailnot_sents';
    protected $fillable = [
        'employee_id', 'emp_email', 'mail_response', 'mail_date', 'created_at', 'updated_at'
    ];
}
