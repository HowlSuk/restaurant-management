<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Reclamation;

class ReclamationController extends Controller
{
    public function index(array $params, array $ctx): void
    {
        $user  = $ctx['user'] ?? null;
        $model = new Reclamation();
        if ($user && $user['role'] === 'admin') {
            Response::success($model->all());
        }
        Response::success($model->where('user_id', (int) ($user['sub'] ?? 0)));
    }

    public function show(array $params): void
    {
        $row = (new Reclamation())->find((int) $params['id']);
        if (!$row) Response::error('Reclamation not found', 404);
        Response::success($row);
    }

    public function store(array $params, array $ctx): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, ['content' => 'required']);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $data['user_id'] = (int) ($ctx['user']['sub'] ?? 0);
        $data['status']  = $data['status'] ?? 'open';
        $id = (new Reclamation())->create($data);
        Response::success(['id' => $id], 'Reclamation created', 201);
    }

    public function update(array $params): void
    {
        (new Reclamation())->update((int) $params['id'], $this->input());
        Response::success(null, 'Reclamation updated');
    }

    public function destroy(array $params): void
    {
        (new Reclamation())->delete((int) $params['id']);
        Response::success(null, 'Reclamation deleted');
    }
}
