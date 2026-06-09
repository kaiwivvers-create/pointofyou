<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'description', 'show_in_pos'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
