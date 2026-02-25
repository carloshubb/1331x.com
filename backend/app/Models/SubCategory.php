<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    //
    protected $fillable = ['category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function popularTorrents()
    {
        return $this->hasMany(PopularTorrent::class);
    }
    public function movieLibraries()
    {
        return $this->hasMany(MovieLibrary::class, 'subcategory_id');
    }
}
