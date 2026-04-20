<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Plat;

class PlatController extends Controller
{
    public function index(): void
    {
        Response::success((new Plat())->allWithCategory());
    }

    public function show(array $params): void
    {
        $row = (new Plat())->find((int) $params['id']);
        if (!$row) Response::error('Plat not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'name'  => 'required',
            'price' => 'required|numeric',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $id = (new Plat())->create($data);
        Response::success(['id' => $id], 'Plat created', 201);
    }

    public function update(array $params): void
    {
        (new Plat())->update((int) $params['id'], $this->input());
        Response::success(null, 'Plat updated');
    }

    public function destroy(array $params): void
    {
        (new Plat())->delete((int) $params['id']);
        Response::success(null, 'Plat deleted');
    }
}
