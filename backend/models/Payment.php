<?php
namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class Payment extends Model
{
    protected string $table = 'payment';
    protected array $fillable = ['total', 'method', 'status', 'commande_id'];

    /**
     * Validates the payment data.
     * 
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        if (isset($data['total']) && (float)$data['total'] < 0) {
            throw new InvalidArgumentException("Payment total cannot be below zero.");
        }
    }
}