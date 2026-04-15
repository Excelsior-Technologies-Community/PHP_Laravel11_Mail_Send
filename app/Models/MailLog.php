<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes; 
    // Enables soft delete functionality (adds deleted_at column support)

    /**
     * Mass assignable fields
     * These fields can be filled using create() or update() methods
     */
    protected $fillable = [
        'email',        // Recipient email address
        'subject',      // Email subject
        'message',      // Email message content
        'created_by',   // User ID who created the mail log
        'updated_by',   // User ID who last updated the mail log
        'status'        // Status of the mail log: 1=Active, 0=Inactive
    ];
}


