<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Avis;

class AvisController extends Controller
{
    public function index(): void
    {
        Response::success((new Avis())->allWithUser());
    }

    public function show(array $params): void
    {
        $row = (new Avis())->find((int) $params['id']);
        if (!$row) Response::error('Avis not found', 404);
        Response::success($row);
    }

    public function store(array $params, array $ctx): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'note' => 'required|integer',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);

        $note = (int) $data['note'];
        if ($note < 1 || $note > 5) Response::error('note must be between 1 and 5', 422);

        $data['user_id'] = (int) ($ctx['user']['sub'] ?? 0);
        $id = (new Avis())->create($data);
        Response::success(['id' => $id], 'Avis created', 201);
    }

    public function update(array $params): void
    {
        (new Avis())->update((int) $params['id'], $this->input());
        Response::success(null, 'Avis updated');
    }

    public function destroy(array $params): void
    {
        (new Avis())->delete((int) $params['id']);
        Response::success(null, 'Avis deleted');
    }
}
