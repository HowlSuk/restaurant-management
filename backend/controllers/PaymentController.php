<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(): void
    {
        Response::success((new Payment())->all());
    }

    public function show(array $params): void
    {
        $row = (new Payment())->find((int) $params['id']);
        if (!$row) Response::error('Payment not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'total'       => 'required|numeric',
            'method'      => 'required',
            'commande_id' => 'required|integer',
        ]);

        // 1. Check if the total is below zero during creation
        if (isset($data['total']) && (float)$data['total'] < 0) {
            $errors['total'] = ['Payment total cannot be below zero.'];
        }

        if ($errors) Response::error('Validation failed', 422, $errors);
        
        $data['status'] = $data['status'] ?? 'pending';
        $id = (new Payment())->create($data);
        Response::success(['id' => $id], 'Payment created', 201);
    }

    public function update(array $params): void
    {
        (new Payment())->update((int) $params['id'], $this->input());
        Response::success(null, 'Payment updated');
    }

    public function destroy(array $params): void
    {
        (new Payment())->delete((int) $params['id']);
        Response::success(null, 'Payment deleted');
    }
}
