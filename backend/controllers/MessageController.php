<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Message;

class MessageController extends Controller
{
    public function index(array $params, array $ctx): void
    {
        $user  = $ctx['user'] ?? null;
        $model = new Message();
        if ($user && $user['role'] === 'admin') {
            Response::success($model->all());
        }
        Response::success($model->where('user_id', (int) ($user['sub'] ?? 0)));
    }

    public function show(array $params): void
    {
        $row = (new Message())->find((int) $params['id']);
        if (!$row) Response::error('Message not found', 404);
        Response::success($row);
    }

    public function store(array $params, array $ctx): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, ['content' => 'required']);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $data['user_id'] = (int) ($ctx['user']['sub'] ?? 0);
        $id = (new Message())->create($data);
        Response::success(['id' => $id], 'Message created', 201);
    }

    public function update(array $params): void
    {
        (new Message())->update((int) $params['id'], $this->input());
        Response::success(null, 'Message updated');
    }

    public function destroy(array $params): void
    {
        (new Message())->delete((int) $params['id']);
        Response::success(null, 'Message deleted');
    }
}
  