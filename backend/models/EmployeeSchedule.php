<?php
namespace App\Models;

use App\Core\Model;

class EmployeeSchedule extends Model
{
    protected string $table = 'employee_schedule';
    protected array $fillable = ['employee_id', 'working_date', 'shift_start', 'shift_end', 'role_task'];

    public function allForEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE employee_id = :eid ORDER BY working_date ASC, shift_start ASC"
        );
        $stmt->execute([':eid' => $employeeId]);
        return $stmt->fetchAll();
    }

    public function allWithEmployeeName(): array
    {
        $stmt = $this->db->query(
            "SELECT es.*, u.name AS employee_name, u.email AS employee_email
               FROM {$this->table} es
               JOIN users u ON u.id = es.employee_id
              ORDER BY es.working_date ASC, es.shift_start ASC"
        );
        return $stmt->fetchAll();
    }
}
