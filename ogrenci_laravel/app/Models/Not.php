<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Not extends Model
{
    use HasFactory;

    protected $table = 'notlar';

    protected $fillable = [
        'ogrenci_id',
        'ders_id',
        'not',
    ];

    public function ogrenci()
    {
        return $this->belongsTo(Ogrenci::class, 'ogrenci_id');
    }

    public function ders()
    {
        return $this->belongsTo(Ders::class, 'ders_id');
    }
}
