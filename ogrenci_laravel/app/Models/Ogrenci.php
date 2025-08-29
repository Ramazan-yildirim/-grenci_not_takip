<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ogrenci extends Model
{
    use HasFactory;

    protected $table = 'ogrenciler';

    protected $fillable = [
        'ad_soyad',
    ];

    public function notlar()
    {
        return $this->hasMany(Not::class, 'ogrenci_id');
    }
}
