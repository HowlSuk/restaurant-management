<?php
namespace App\Models;

use App\Core\Model;

class Commande extends Model
{
    protected string $table = 'commande';
    protected array $fillable = ['date', 'status', 'user_id'];

    public function withItems(int $id): ?array
    {
        $commande = $this->find($id);
        if (!$commande) return null;

        $stmt = $this->db->prepare(
            "SELECT oi.*, p.name AS plat_name
               FROM order_items oi
               JOIN plat p ON p.id = oi.plat_id
              WHERE oi.commande_id = :id"
        );
        $stmt->execute([':id' => $id]);
        $commande['items'] = $stmt->fetchAll();
        return $commande;
    }

    public function allForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function allWithCustomer(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, u.name AS customer_name, u.email AS customer_email
               FROM {$this->table} c
               JOIN users u ON u.id = c.user_id
              ORDER BY c.id DESC"
        );
        return $stmt->fetchAll();
    }
}
