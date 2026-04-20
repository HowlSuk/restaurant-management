<?php
namespace App\Models;

use App\Core\Model;

class OrderItem extends Model
{
    protected string $table = 'order_items';
    protected array $fillable = ['quantity', 'price', 'commande_id', 'plat_id'];

    public function allForCommande(int $commandeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE commande_id = :id");
        $stmt->execute([':id' => $commandeId]);
        return $stmt->fetchAll();
    }
}
