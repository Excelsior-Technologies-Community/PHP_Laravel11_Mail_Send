<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'subject',
        'message',
        'created_by',
        'updated_by',
        'status'
    ];
}
