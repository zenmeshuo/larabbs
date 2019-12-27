<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cache;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 'description'
    ];

    public $cache_key = 'larabbs_categories';

    protected $cache_expire_in_seconds = 1440 * 60;

    public function categories()
    {
        return Cache::remember($this->cache_key, $this->cache_expire_in_seconds, function(){
            return $this->all();
        });
    }
}
