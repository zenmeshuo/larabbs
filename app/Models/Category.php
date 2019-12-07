<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 'description'
    ];

    public function categories()
    {
        if (is_null(cache('categories'))) {
            cache(['categories' => $this->all()], 480);
        }
        return cache('categories');
    }
}
