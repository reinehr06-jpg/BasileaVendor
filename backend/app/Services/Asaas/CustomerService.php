<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function findCustomerByCpfCnpj(string $cpfCnpj): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/customers", [
                    'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data']) && count($data['data']) > 0) {
                    return $data['data'][0];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar cliente por CPF/CNPJ', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function createCustomer(string $name, string $cpfCnpj, ?string $phone = null, ?string $email = null): array
    {
        $existing = $this->findCustomerByCpfCnpj($cpfCnpj);
        if ($existing) {
            if (isset($existing['name']) && $existing['name'] !== $name) {
                Log::info('Asaas: cliente existe com nome diferente, atualizando', [
                    'old_name' => $existing['name'],
                    'new_name' => $name,
                ]);
                try {
                    $updatePayload = ['name' => $name];
                    if ($email) $updatePayload['email'] = $email;
                    if ($phone) $updatePayload['phone'] = preg_replace('/\D/', '', $phone);

                    $response = Http::withHeaders($this->headers())
                        ->put("{$this->baseUrl}/customers/{$existing['id']}", $updatePayload);

                    if ($response->successful()) {
                        return $response->json();
                    }
                } catch (\Exception $e) {
                    Log::warning('Asaas: falha ao atualizar nome do cliente', ['error' => $e->getMessage()]);
                }
            }
            return $existing;
        }

        $payload = [
            'name'    => $name,
            'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
        ];

        if ($phone) $payload['phone'] = preg_replace('/\D/', '', $phone);
        if ($email) $payload['email'] = $email;

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/customers", $payload);

        if ($response->successful()) {
            Log::info('Asaas: cliente criado', ['id' => $response->json()['id'] ?? null, 'name' => $name]);
            return $response->json();
        }

        Log::error('Asaas: erro ao criar cliente', [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception('Falha ao registrar cliente no Asaas: ' . $response->body());
    }
}
