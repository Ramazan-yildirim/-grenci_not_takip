<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ders extends Model
{
    use HasFactory;

    protected $table = 'dersler';

    protected $fillable = [
        'ad',
        'egitmen_id',
    ];

    public function egitmen()
    {
        return $this->belongsTo(Egitmen::class, 'egitmen_id');
    }

    public function notlar()
    {
        return $this->hasMany(Not::class, 'ders_id');
    }
}
