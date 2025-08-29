<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egitmen extends Model
{
    use HasFactory;

    protected $table = 'egitmenler';

    protected $fillable = [
        'ad_soyad',
    ];

    public function dersler()
    {
        return $this->hasMany(Ders::class, 'egitmen_id');
    }
}
