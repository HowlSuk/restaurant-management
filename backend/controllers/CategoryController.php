<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        Response::success((new Category())->all());
    }

    public function show(array $params): void
    {
        $row = (new Category())->find((int) $params['id']);
        if (!$row) Response::error('Category not found', 404);
        Response::success($row);
    }

    public function store(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, ['name' => 'required']);
        if ($errors) Response::error('Validation failed', 422, $errors);
        $id = (new Category())->create($data);
        Response::success(['id' => $id], 'Category created', 201);
    }

    public function update(array $params): void
    {
        (new Category())->update((int) $params['id'], $this->input());
        Response::success(null, 'Category updated');
    }

    public function destroy(array $params): void
    {
        (new Category())->delete((int) $params['id']);
        Response::success(null, 'Category deleted');
    }
}
