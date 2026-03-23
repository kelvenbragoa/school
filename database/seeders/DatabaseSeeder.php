<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuários com diferentes roles
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@stock.com',
            'password' => Hash::make('password'),
        ]);

        $gestor = User::factory()->gestor()->create([
            'name' => 'Gestor User',
            'email' => 'gestor@stock.com',
            'password' => Hash::make('password'),
        ]);

        $armazem = User::factory()->armazem()->create([
            'name' => 'Armazém User',
            'email' => 'armazem@stock.com',
            'password' => Hash::make('password'),
        ]);

        // Criar 5 solicitantes
        $solicitantes = User::factory()->count(5)->create([
            'password' => Hash::make('password'),
        ]);

        // Criar itens de stock
        // $items = Item::factory()->count(30)->create();

        // Criar alguns itens com stock baixo
        // Item::factory()->lowStock()->count(5)->create();

        // Criar pedidos para os solicitantes
        // foreach ($solicitantes as $solicitante) {
        //     // Pedidos solicitados
        //     RequestModel::factory()
        //         ->solicitado()
        //         ->count(2)
        //         ->create([
        //             'requester_id' => $solicitante->id,
        //             'item_id' => $items->random()->id,
        //         ]);

        //     // Pedidos entregues passados
        //     RequestModel::factory()
        //         ->entregue()
        //         ->count(3)
        //         ->create([
        //             'requester_id' => $solicitante->id,
        //             'item_id' => $items->random()->id,
        //         ]);
        // }

        // Criar alguns pedidos em diferentes status
        // RequestModel::factory()->count(3)->create([
        //     'status' => 'preparando',
        //     'requester_id' => $solicitantes->random()->id,
        //     'item_id' => $items->random()->id,
        // ]);

        // RequestModel::factory()->count(2)->create([
        //     'status' => 'enviando',
        //     'requester_id' => $solicitantes->random()->id,
        //     'item_id' => $items->random()->id,
        // ]);

        // RequestModel::factory()->count(2)->create([
        //     'status' => 'recebido',
        //     'requester_id' => $solicitantes->random()->id,
        //     'item_id' => $items->random()->id,
        // ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@eskolare.com / password');
        $this->command->info('Gestor: gestor@eskolare.com / password');
        $this->command->info('Armazém: armazem@eskolare.com / password');
        $this->command->info('Solicitante: test usuários / password');
    }
}
