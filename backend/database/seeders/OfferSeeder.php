<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'slug' => 'plano-igreja-basico',
                'name' => 'Plano Igreja Básico',
                'description' => 'Ideal para igrejas pequenas que estão começando',
                'benefits' => json_encode([
                    'Até 100 membros',
                    'Suporte por email',
                    'Relatórios básicos',
                ]),
                'features' => json_encode([
                    'Acesso imediato ao sistema',
                    'Suporte por email',
                    'Relatórios básicos',
                    'Atualizações gratuitas',
                ]),
                'price_brl' => 97.00,
                'price_usd' => 19.00,
                'price_eur' => 18.00,
                'discount_percent' => 0,
                'installments_max' => 12,
                'installment_value_brl' => 9.70,
                'guarantee_text' => '7 dias',
                'is_active' => true,
            ],
            [
                'slug' => 'plano-igreja-profissional',
                'name' => 'Plano Igreja Profissional',
                'description' => 'Para igrejas em crescimento que precisam de mais recursos',
                'benefits' => json_encode([
                    'Até 500 membros',
                    'Suporte prioritário',
                    'Relatórios avançados',
                    'Integração com sistemas',
                ]),
                'features' => json_encode([
                    'Acesso imediato ao sistema',
                    'Suporte prioritário 24/7',
                    'Relatórios avançados',
                    'Integração com sistemas',
                    'Treinamento da equipe',
                    'Atualizações prioritárias',
                ]),
                'price_brl' => 197.00,
                'price_usd' => 39.00,
                'price_eur' => 36.00,
                'discount_percent' => 20,
                'installments_max' => 12,
                'installment_value_brl' => 19.70,
                'guarantee_text' => '14 dias',
                'is_active' => true,
            ],
            [
                'slug' => 'plano-igreja-enterprise',
                'name' => 'Plano Igreja Enterprise',
                'description' => 'Solução completa para redes de igrejas e mega igrejas',
                'benefits' => json_encode([
                    'Membros ilimitados',
                    'Suporte dedicado',
                    'API personalizada',
                    'Treinamento incluso',
                ]),
                'features' => json_encode([
                    'Membros ilimitados',
                    'Suporte dedicado com gerente',
                    'API personalizada',
                    'Treinamento completo',
                    'Onboarding assistido',
                    'SLA garantido',
                    'White-label disponível',
                ]),
                'price_brl' => 497.00,
                'price_usd' => 97.00,
                'price_eur' => 89.00,
                'discount_percent' => 15,
                'installments_max' => 12,
                'installment_value_brl' => 49.70,
                'guarantee_text' => '30 dias',
                'is_active' => true,
            ],
        ];

        foreach ($offers as $offer) {
            Offer::updateOrCreate(
                ['slug' => $offer['slug']],
                $offer
            );
        }

        $this->command->info('Ofertas criadas com sucesso!');
    }
}
