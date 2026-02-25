<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieLibrary extends Model
{
    protected $fillable = [
        'img_url',
        'rate',
        'info_url',
        'info_title',
        'category',
        'content',
    ];

     public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }
}
