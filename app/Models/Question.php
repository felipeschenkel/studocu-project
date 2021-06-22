<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Question extends Model
{
    public $timestamps = false;

    use HasFactory, Notifiable;

    protected $fillable = [
        'question'
    ];
}
