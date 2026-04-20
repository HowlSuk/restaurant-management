<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\RestaurantTable;

class RestaurantTableController extends Controller
{
    public function index(): void
    {
        Response::success((new RestaurantTable())->all());
    }

    public function show(array $params): void
    {
        $row = (new RestaurantTable())->find((int) $params['id']);
        if (!$row) Response::error('Table not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'number'   => 'required|integer',
            'capacity' => 'required|integer',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $id = (new RestaurantTable())->create($data);
        Response::success(['id' => $id], 'Table created', 201);
    }

    public function update(array $params): void
    {
        (new RestaurantTable())->update((int) $params['id'], $this->input());
        Response::success(null, 'Table updated');
    }

    public function destroy(array $params): void
    {
        (new RestaurantTable())->delete((int) $params['id']);
        Response::success(null, 'Table deleted');
    }
}
