<?php
namespace App\Models;

use App\Core\Model;

class Reservation extends Model
{
    protected string $table = 'reservation';
    protected array $fillable = ['date', 'time', 'number_of_people', 'status', 'user_id', 'table_id'];

    public function allForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function allWithCustomer(): array
    {
        $stmt = $this->db->query(
            "SELECT r.*, u.name AS customer_name, u.email AS customer_email
               FROM {$this->table} r
               JOIN users u ON u.id = r.user_id
              ORDER BY r.id DESC"
        );
        return $stmt->fetchAll();
    }
}
