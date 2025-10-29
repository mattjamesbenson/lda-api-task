<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $table = 'vacancies';
    public $timestamps = true;

    protected $fillable = [
        'vacancy_reference',
        'title',
        'employer_name',
        'postcode',
        'latitude',
        'longitude',
        'description',
    ];
}
