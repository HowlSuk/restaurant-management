<?php
namespace App\Models;

use App\Core\Model;

class RestaurantTable extends Model
{
    protected string $table = 'restaurant_table';
    protected array $fillable = ['number', 'capacity', 'status'];
}
