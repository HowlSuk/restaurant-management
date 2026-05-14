<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

class StaffController extends Controller
{
    public function index(): void
    {
        Response::success($this->getStaffList());
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

        if (!in_array($data['role'], ['chef', 'employee'], true)) {
            Response::error('Role must be chef or employee', 422);
        }

        $users = new User();
        if ($users->findByEmail($data['email'])) {
            Response::error('Email already exists', 409);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $id = $users->create($data);
        Response::success(['id' => $id], 'Staff member created', 201);
    }

    public function show(array $params): void
    {
        $user = (new User())->find((int) $params['id']);
        if (!$user) Response::error('Staff member not found', 404);
        unset($user['password']);
        Response::success($user);
    }

    public function update(array $params): void
    {
        $id   = (int) $params['id'];
        $data = $this->input();

        if (isset($data['role']) && !in_array($data['role'], ['chef', 'employee'], true)) {
            Response::error('Role must be chef or employee', 422);
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        $ok = (new User())->update($id, $data);
        if (!$ok) Response::error('Nothing to update', 400);
        Response::success(null, 'Staff member updated');
    }

    public function destroy(array $params): void
    {
        (new User())->delete((int) $params['id']);
        Response::success(null, 'Staff member deleted');
    }

    private function getStaffList(): array
    {
        $db = \App\Config\Database::connect();
        $stmt = $db->query(
            "SELECT id, name, email, role, created_at FROM users WHERE role IN ('chef','employee') ORDER BY id DESC"
        );
        return $stmt->fetchAll();
    }
}
