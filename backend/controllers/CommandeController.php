<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Commande;
use App\Models\OrderItem;
use App\Models\Plat;

class CommandeController extends Controller
{
    public function index(array $params, array $ctx): void
    {
        $user  = $ctx['user'] ?? null;
        $model = new Commande();
        if ($user && $user['role'] === 'admin') {
            Response::success($model->allWithCustomer());
            return;  // Y fix
        }
        Response::success($model->allForUser((int) ($user['sub'] ?? 0)));
    }

    public function show(array $params): void
    {
        $row = (new Commande())->withItems((int) $params['id']);
        if (!$row) Response::error('Commande not found', 404);
        Response::success($row);
    }

    /**
     * Body:
     * {
     *   "items": [ { "plat_id": 1, "quantity": 2 }, ... ]
     * }
     */
    public function store(array $params, array $ctx): void
    {
        $data = $this->input();
        if (empty($data['items']) || !is_array($data['items'])) {
            Response::error('items array is required', 422);
        }

        $commandes = new Commande();
        $itemsM    = new OrderItem();
        $platM     = new Plat();

        $commandeId = $commandes->create([
        'date'    => date('Y-m-d H:i:s'),
        'status'  => 'pending',
        'user_id' => (int) ($ctx['user']['sub'] ?? 0),
        ]);

        foreach ($data['items'] as $it) {
            $plat = $platM->find((int) ($it['plat_id'] ?? 0));
            if (!$plat) continue;
            $itemsM->create([
                'commande_id' => $commandeId,
                'plat_id'     => (int) $plat['id'],
                'quantity'    => (int) ($it['quantity'] ?? 1),
                'price'       => (float) $plat['price'],
            ]);
        }

        Response::success(['id' => $commandeId], 'Commande created', 201);
    }

    public function update(array $params): void
    {
        (new Commande())->update((int) $params['id'], $this->input());
        Response::success(null, 'Commande updated');
    }

    public function destroy(array $params): void
    {
        (new Commande())->delete((int) $params['id']);
        Response::success(null, 'Commande deleted');
    }
}
