<?php
namespace App\Models;

use App\Core\Model;

class Contact extends Model
{
    protected string $table = 'contact';
    protected array $fillable = ['message', 'date'];
}
