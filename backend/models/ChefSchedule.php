<?php
namespace App\Models;

use App\Core\Model;

class ChefSchedule extends Model
{
    protected string $table = 'chef_schedule';
    protected array $fillable = ['chef_id', 'working_date', 'shift_start', 'shift_end'];

    public function allForChef(int $chefId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE chef_id = :cid ORDER BY working_date ASC, shift_start ASC"
        );
        $stmt->execute([':cid' => $chefId]);
        return $stmt->fetchAll();
    }

    public function allWithChefName(): array
    {
        $stmt = $this->db->query(
            "SELECT cs.*, u.name AS chef_name, u.email AS chef_email
               FROM {$this->table} cs
               JOIN users u ON u.id = cs.chef_id
              ORDER BY cs.working_date ASC, cs.shift_start ASC"
        );
        return $stmt->fetchAll();
    }
}
