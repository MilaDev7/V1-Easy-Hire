<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


    class Report extends Model
{
    protected $fillable = [
        'contract_id',
        'reporter_id',
        'reported_id',
        'reason'
    ];
}

