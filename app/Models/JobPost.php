<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'budget',
        'status',
        'location'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
