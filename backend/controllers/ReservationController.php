<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index(array $params, array $ctx): void
{
    $user  = $ctx['user'] ?? null;
    $model = new Reservation();

    if ($user && $user['role'] === 'admin') {
        Response::success($model->allWithCustomer());
        return;
    }

    Response::success($model->allForUser((int) ($user['sub'] ?? 0)));
}

    public function show(array $params): void
    {
        $row = (new Reservation())->find((int) $params['id']);
        if (!$row) Response::error('Reservation not found', 404);
        Response::success($row);
    }

    public function store(array $params, array $ctx): void
{
    $data   = $this->input();

    $errors = $this->validate($data, [
        'date'             => 'required',
        'time'             => 'required',
        'number_of_people' => 'required|integer',
    ]);

    if ($errors) {
        Response::error('Validation failed', 422, $errors);
    }

    
    $reservationDateTime = new \DateTime($data['date'] . ' ' . $data['time']);
    $now = new \DateTime();

    if ($reservationDateTime < $now) {
        Response::error("You cannot book a past date/time", 400);
    }

    $data['user_id'] = (int) ($ctx['user']['sub'] ?? 0);
    $data['status']  = $data['status'] ?? 'pending';

    $id = (new Reservation())->create($data);

    Response::success(['id' => $id], 'Reservation created', 201);
}

    public function update(array $params): void
    {
        (new Reservation())->update((int) $params['id'], $this->input());
        Response::success(null, 'Reservation updated');
    }

    public function destroy(array $params): void
    {
        (new Reservation())->delete((int) $params['id']);
        Response::success(null, 'Reservation deleted');
    }
}
