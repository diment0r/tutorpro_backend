<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paraphrase extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'topic',
        'paraphrase',
        'user_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
