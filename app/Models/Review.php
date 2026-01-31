<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'username',
        'review',
        'label',
        'case_folding',
        'cleansing',
        'normalisasi',
        'tokenizing',
        'stopword',
        'stemming'
    ];
}
