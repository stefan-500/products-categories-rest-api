<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $hidden = ['id', 'created_at', 'updated_at', 'pivot']; // JSON rezultat prikazuje samo naziv

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }
}
