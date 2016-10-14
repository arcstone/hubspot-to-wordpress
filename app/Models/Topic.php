<?php

namespace H2W\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected static $unguarded = true;

    public function getWordPressAttributes()
    {
        return array_only($this->getAttributes(), [
            'description',
            'name',
            'slug',
        ]);
    }
}
