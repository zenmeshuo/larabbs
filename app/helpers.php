<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function category_nav_active($category_id)
{
    return active_class(if_route('categories.show') && if_route_param('category', $category_id));
}

function make_excerpt($value, $lenght = 200)
{
    // preg_replace 正则替换 strip_tags 去除 html 等标签
    $excerpt = trim(preg_replace('/\r\n|\r|\n+/', ' ', strip_tags($value)));

    // 按给定的长度截断给定的字符串
    return Str::limit($excerpt, $lenght);
}
