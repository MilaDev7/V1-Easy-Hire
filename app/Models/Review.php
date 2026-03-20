<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'contract_id',
        'reviewer_id',
        'reviewed_id',
        'rating',
        'comment',
    ];
}