<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    public function index(): void
    {
        Response::success((new OrderItem())->all());
    }

    public function show(array $params): void
    {
        $row = (new OrderItem())->find((int) $params['id']);
        if (!$row) Response::error('Order item not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'quantity'    => 'required|integer',
            'price'       => 'required|numeric',
            'commande_id' => 'required|integer',
            'plat_id'     => 'required|integer',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $id = (new OrderItem())->create($data);
        Response::success(['id' => $id], 'Order item created', 201);
    }

    public function update(array $params): void
    {
        (new OrderItem())->update((int) $params['id'], $this->input());
        Response::success(null, 'Order item updated');
    }

    public function destroy(array $params): void
    {
        (new OrderItem())->delete((int) $params['id']);
        Response::success(null, 'Order item deleted');
    }
}
