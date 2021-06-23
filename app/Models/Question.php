<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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

    public function getQuestions(): Collection
    {
        return Question::all();
    }

    public function getQuestion(int $questionId): object
    {
        return Question::find($questionId);
    }

    public function answer()
    {
        return $this->hasMany(Answer::class);
    }
}
