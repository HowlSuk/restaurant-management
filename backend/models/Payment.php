<?php
namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payment';
    protected array $fillable = ['total', 'method', 'status', 'commande_id'];
}
