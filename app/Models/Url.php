<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $table = 'urls';

    protected $fillable = ['ruta'];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'id_url');
    }
}
