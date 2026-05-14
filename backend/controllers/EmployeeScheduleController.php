<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\EmployeeSchedule;

class EmployeeScheduleController extends Controller
{
    public function index(array $params, array $ctx): void
    {
        $user = $ctx['user'] ?? null;
        $model = new EmployeeSchedule();

        if ($user && $user['role'] === 'admin') {
            Response::success($model->allWithEmployeeName());
        }

        Response::success($model->allForEmployee((int) ($user['sub'] ?? 0)));
    }

    public function show(array $params): void
    {
        $row = (new EmployeeSchedule())->find((int) $params['id']);
        if (!$row) Response::error('Schedule not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'employee_id'  => 'required|integer',
            'working_date' => 'required',
            'shift_start'  => 'required',
            'shift_end'    => 'required',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);

        $id = (new EmployeeSchedule())->create($data);
        Response::success(['id' => $id], 'Employee schedule created', 201);
    }

    public function update(array $params): void
    {
        (new EmployeeSchedule())->update((int) $params['id'], $this->input());
        Response::success(null, 'Employee schedule updated');
    }

    public function destroy(array $params): void
    {
        (new EmployeeSchedule())->delete((int) $params['id']);
        Response::success(null, 'Employee schedule deleted');
    }
}
