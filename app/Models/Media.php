<?php namespace H2W\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Media
 *
 * @package H2W\Models
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and') : self
 */
class Media extends Model
{
    public    $timestamps = false;
    protected $fillable   = ['id', 'post_id', 'original_url'];
}
