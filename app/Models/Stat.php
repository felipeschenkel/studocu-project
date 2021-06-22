<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Stat extends Model
{
    public $timestamps = false;

    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'question_id',
        'correctly_answered'
    ];

    protected $casts = [
        'correctly_answered' => 'boolean'
    ];

}
