<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\ChefSchedule;

class ChefScheduleController extends Controller
{
    public function index(array $params, array $ctx): void
    {
        $user = $ctx['user'] ?? null;
        $model = new ChefSchedule();

        if ($user && $user['role'] === 'admin') {
            Response::success($model->allWithChefName());
        }

        Response::success($model->allForChef((int) ($user['sub'] ?? 0)));
    }

    public function show(array $params): void
    {
        $row = (new ChefSchedule())->find((int) $params['id']);
        if (!$row) Response::error('Schedule not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'chef_id'      => 'required|integer',
            'working_date' => 'required',
            'shift_start'  => 'required',
            'shift_end'    => 'required',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);

        $id = (new ChefSchedule())->create($data);
        Response::success(['id' => $id], 'Chef schedule created', 201);
    }

    public function update(array $params): void
    {
        (new ChefSchedule())->update((int) $params['id'], $this->input());
        Response::success(null, 'Chef schedule updated');
    }

    public function destroy(array $params): void
    {
        (new ChefSchedule())->delete((int) $params['id']);
        Response::success(null, 'Chef schedule deleted');
    }
}
