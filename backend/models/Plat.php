<?php
namespace App\Models;

use App\Core\Model;

class Plat extends Model
{
    protected string $table = 'plat';
    protected array $fillable = ['name', 'description', 'price', 'category_id'];

    public function allWithCategory(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, c.name AS category_name
               FROM {$this->table} p
               LEFT JOIN category c ON c.id = p.category_id
              ORDER BY p.id DESC"
        );
        return $stmt->fetchAll();
    }
}
