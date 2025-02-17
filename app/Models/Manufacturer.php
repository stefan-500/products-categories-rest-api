<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['id', 'created_at', 'updated_at'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
