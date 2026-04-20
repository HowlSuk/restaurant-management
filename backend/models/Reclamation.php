<?php
namespace App\Models;

use App\Core\Model;

class Reclamation extends Model
{
    protected string $table = 'reclamation';
    protected array $fillable = ['content', 'status', 'user_id'];
}
