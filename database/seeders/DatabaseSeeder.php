<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(8)->create();

        $users->push(User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]));

        $faker = \Faker\Factory::create('es_CO');

        $now = Carbon::now();

        $customerRows = [];
        $segments = ['SMB', 'Mid-Market', 'Enterprise'];
        $countries = ['CO', 'MX', 'CL', 'AR', 'PE'];
        for ($i = 0; $i < 40; $i++) {
            $fullName = trim($faker->firstName().' '.$faker->lastName().' '.$faker->lastName());
            $customerRows[] = [
                'name' => $fullName,
                'email' => $faker->unique()->companyEmail(),
                'segment' => $segments[array_rand($segments)],
                'country' => $countries[array_rand($countries)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('customers')->insert($customerRows);
        $customerIds = DB::table('customers')->pluck('id')->all();

        $productRows = [];
        $categories = ['Suscripción', 'Servicios', 'Add-ons', 'Soporte', 'Capacitación'];
        for ($i = 0; $i < 28; $i++) {
            $priceCents = random_int(990000, 19990000);
            $productRows[] = [
                'sku' => $faker->unique()->bothify('SKU-??-#####'),
                'name' => ucfirst($faker->words(random_int(2, 4), true)),
                'category' => $categories[array_rand($categories)],
                'price_cents' => $priceCents,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('products')->insert($productRows);
        $productIds = DB::table('products')->pluck('id')->all();
        $productPrices = DB::table('products')->pluck('price_cents', 'id')->all();

        $channels = ['web', 'api', 'phone', 'email', 'whatsapp'];

        for ($i = 0; $i < 260; $i++) {
            $soldAt = $now->copy()->subDays(random_int(0, 29))->subMinutes(random_int(0, 1439));
            $statusRoll = random_int(1, 100);
            $status = match (true) {
                $statusRoll <= 70 => 'paid',
                $statusRoll <= 90 => 'pending',
                default => 'cancelled',
            };

            $itemsCount = random_int(1, 5);
            $picked = [];
            $subtotalCents = 0;
            $items = [];

            for ($j = 0; $j < $itemsCount; $j++) {
                $productId = $productIds[array_rand($productIds)];
                if (isset($picked[$productId])) {
                    continue;
                }
                $picked[$productId] = true;

                $qty = random_int(1, 4);
                $unitPriceCents = $status === 'paid' ? (int) ($productPrices[$productId] ?? 0) : 0;
                $lineTotalCents = $qty * $unitPriceCents;

                $subtotalCents += $lineTotalCents;
                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price_cents' => $unitPriceCents,
                    'line_total_cents' => $lineTotalCents,
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ];
            }

            $taxCents = $status === 'paid' ? (int) round($subtotalCents * 0.19) : 0;
            $totalCents = $status === 'paid' ? ($subtotalCents + $taxCents) : 0;

            $saleId = DB::table('sales')->insertGetId([
                'customer_id' => $customerIds[array_rand($customerIds)],
                'status' => $status,
                'channel' => $channels[array_rand($channels)],
                'sold_at' => $soldAt,
                'subtotal_cents' => $subtotalCents,
                'tax_cents' => $taxCents,
                'total_cents' => $totalCents,
                'currency' => 'COP',
                'created_at' => $soldAt,
                'updated_at' => $soldAt,
            ]);

            foreach ($items as &$item) {
                $item['sale_id'] = $saleId;
            }
            unset($item);

            if (count($items) > 0) {
                DB::table('sale_items')->insert($items);
            }
        }
    }
}
