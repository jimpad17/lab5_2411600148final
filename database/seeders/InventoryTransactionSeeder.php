<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryTransactionSeeder extends Seeder
{
    /**
     * Give each seeded product an initial "Stock In" movement.
     */
    public function run(): void
    {
        $userId = User::where('email', 'admin@jimtrack.local')->value('id');

        Product::chunkById(100, function ($products) use ($userId) {
            foreach ($products as $product) {
                InventoryTransaction::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'reference' => 'SEED-INITIAL',
                    ],
                    [
                        'type' => 'in',
                        'quantity' => $product->quantity,
                        'user_id' => $userId,
                    ]
                );
            }
        });
    }
}
