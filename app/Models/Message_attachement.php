<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message_attachement extends Model
{
    use HasFactory;

    protected $fillabele = [
        'message_id',
        'name',
        'path',
        'mime',
        'size'
    ];
}
