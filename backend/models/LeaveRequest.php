<?php
namespace App\Models;

use App\Core\Model;

class LeaveRequest extends Model
{
    protected string $table = 'leave_requests';
    protected array $fillable = ['staff_id', 'start_date', 'end_date', 'reason', 'status'];

    public function allForStaff(int $staffId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE staff_id = :sid ORDER BY created_at DESC"
        );
        $stmt->execute([':sid' => $staffId]);
        return $stmt->fetchAll();
    }

    public function allWithStaffName(): array
    {
        $stmt = $this->db->query(
            "SELECT lr.*, u.name AS staff_name, u.email AS staff_email, u.role AS staff_role
               FROM {$this->table} lr
               JOIN users u ON u.id = lr.staff_id
              ORDER BY lr.created_at DESC"
        );
        return $stmt->fetchAll();
    }
}
