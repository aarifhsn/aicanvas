<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Generation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'provider', 'template_key', 'prompt', 'result', 'latency_ms'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}