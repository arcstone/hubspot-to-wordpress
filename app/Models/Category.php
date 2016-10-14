<?php

namespace H2W\Models;

class Category
{
    public function getWordPressAttributes()
    {
        return [
            'description',
            'name',
            'slug',
            'parent',
        ];
    }
}
