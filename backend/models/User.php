<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    
    // 1. Added 'profile_picture' here so your core create/update methods permit it
    protected array $fillable = ['name', 'email', 'password', 'role', 'profile_picture'];

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allSafe(): array
    {
        // 2. Added profile_picture to the selection list for your frontend dashboards
        $stmt = $this->db->query("SELECT id, name, email, role, profile_picture, created_at FROM {$this->table} ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}