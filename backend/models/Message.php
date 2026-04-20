<?php
namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    protected string $table = 'message';
    protected array $fillable = ['content', 'user_id', 'date'];
}
