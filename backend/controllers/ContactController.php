<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index(): void
    {
        Response::success((new Contact())->all());
    }

    public function show(array $params): void
    {
        $row = (new Contact())->find((int) $params['id']);
        if (!$row) Response::error('Contact message not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, ['message' => 'required']);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $id = (new Contact())->create(['message' => $data['message']]);
        Response::success(['id' => $id], 'Contact message sent', 201);
    }

    public function destroy(array $params): void
    {
        (new Contact())->delete((int) $params['id']);
        Response::success(null, 'Contact message deleted');
    }
}
