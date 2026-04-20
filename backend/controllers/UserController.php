<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

class UserController extends Controller
{
    public function index(): void
    {
        Response::success((new User())->allSafe());
    }

    public function show(array $params): void
    {
        $user = (new User())->find((int) $params['id']);
        if (!$user) Response::error('User not found', 404);
        unset($user['password']);
        Response::success($user);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'role'     => 'required',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);

        $users = new User();
        if ($users->findByEmail($data['email'])) {
            Response::error('Email already exists', 409);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $id = $users->create($data);
        Response::success(['id' => $id], 'User created', 201);
    }

    public function update(array $params): void
    {
        $id   = (int) $params['id'];
        $data = $this->input();
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        $ok = (new User())->update($id, $data);
        if (!$ok) Response::error('Nothing to update', 400);
        Response::success(null, 'User updated');
    }

    public function destroy(array $params): void
    {
        (new User())->delete((int) $params['id']);
        Response::success(null, 'User deleted');
    }
}
