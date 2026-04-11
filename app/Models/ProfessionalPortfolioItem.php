<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalPortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_id',
        'job_id',
        'image_path',
        'description',
    ];

    public function professional()
    {
        return $this->belongsTo(Professional::class, 'professional_id');
    }

    public function job()
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }
}
