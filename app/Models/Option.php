<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'option',
        'correct',
        'question_id',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
