<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Answer extends Model
{
    use HasFactory, Notifiable;
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'answer',
        'is_correct',
    ];
}
