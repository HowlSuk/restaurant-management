<?php
namespace App\Models;

use App\Core\Model;

class Avis extends Model
{
    protected string $table = 'avis';
    protected array $fillable = ['note', 'comment', 'user_id'];

    public function allWithUser(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.name AS user_name
               FROM {$this->table} a
               JOIN users u ON u.id = a.user_id
              ORDER BY a.id DESC"
        );
        return $stmt->fetchAll();
    }
}
