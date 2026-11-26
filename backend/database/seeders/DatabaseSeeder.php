<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $policy = PrivacyPolicy::updateOrCreate(
            ['version' => '1.0'],
            [
                'title' => 'Atualização da Política de Privacidade',
                'content' => "Atualizamos nossa Política de Privacidade para garantir ainda mais transparência sobre como coletamos, armazenamos e protegemos seus dados pessoais em conformidade com a LGPD (Lei nº 13.709/2018).\n\nPrincipais pontos:\n1. Uso exclusivo dos dados para operação da conta e melhoria dos serviços.\n2. Direito de acesso, correção e exclusão de dados a qualquer momento.\n3. Registro seguro do seu consentimento por versão de política.\n\nAo clicar em \"Li e aceito os termos\", você confirma estar ciente das atualizações vigentes.",
                'is_current' => true,
                'is_enabled' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@souenergy.com.br'],
            [
                'name' => 'Administrador Sou Energy',
                'password' => 'admin123',
                'role' => 'admin',
            ]
        );

        $customers = [
            ['name' => 'Ana Souza', 'email' => 'ana@cliente.com', 'accepted' => true],
            ['name' => 'Bruno Lima', 'email' => 'bruno@cliente.com', 'accepted' => true],
            ['name' => 'Carla Mendes', 'email' => 'carla@cliente.com', 'accepted' => false],
            ['name' => 'Diego Rocha', 'email' => 'diego@cliente.com', 'accepted' => false],
            ['name' => 'Elena Costa', 'email' => 'elena@cliente.com', 'accepted' => false],
        ];

        foreach ($customers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'cliente123',
                    'role' => 'customer',
                ]
            );

            if ($data['accepted']) {
                PrivacyPolicyAcceptance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'privacy_policy_id' => $policy->id,
                    ],
                    [
                        'accepted_at' => now()->subDays(rand(1, 5)),
                        'ip_address' => '127.0.0.1',
                    ]
                );
            }
        }
    }
}
