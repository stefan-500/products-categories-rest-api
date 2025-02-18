<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['id', 'manufacturer_id', 'created_at', 'updated_at']; // JSON rezultat ne treba da prikazuje id

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }
}
