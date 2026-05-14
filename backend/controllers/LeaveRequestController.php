<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\LeaveRequest;

class LeaveRequestController extends Controller
{
    public function index(array $params, array $ctx): void
    {
        $user = $ctx['user'] ?? null;
        $model = new LeaveRequest();

        if ($user && $user['role'] === 'admin') {
            Response::success($model->allWithStaffName());
        }

        Response::success($model->allForStaff((int) ($user['sub'] ?? 0)));
    }

    public function show(array $params): void
    {
        $row = (new LeaveRequest())->find((int) $params['id']);
        if (!$row) Response::error('Leave request not found', 404);
        Response::success($row);
    }

    public function store(array $params, array $ctx): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'start_date' => 'required',
            'end_date'   => 'required',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);

        $data['staff_id'] = (int) ($ctx['user']['sub'] ?? 0);
        $data['status']   = 'pending';

        $id = (new LeaveRequest())->create($data);
        Response::success(['id' => $id], 'Leave request submitted', 201);
    }

    public function update(array $params): void
    {
        (new LeaveRequest())->update((int) $params['id'], $this->input());
        Response::success(null, 'Leave request updated');
    }

    public function destroy(array $params): void
    {
        (new LeaveRequest())->delete((int) $params['id']);
        Response::success(null, 'Leave request deleted');
    }
}
