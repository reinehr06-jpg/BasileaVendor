<?php

namespace Tests\Unit;

use App\Services\AsaasService;
use App\Services\Asaas\CustomerService;
use App\Services\Asaas\PaymentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsaasServiceTest extends TestCase
{
    /** Service delega para CustomerService. */
    public function test_delega_para_customer_service(): void
    {
        Http::fake([
            'api-sandbox.asaas.com/v3/customers*' => Http::response([
                'data' => [['id' => 'cus_123', 'name' => 'Test']],
            ], 200),
        ]);
        
        $service = new AsaasService();
        $resultado = $service->findCustomerByCpfCnpj('12345678900');
        
        $this->assertNotNull($resultado);
        $this->assertEquals('cus_123', $resultado['id']);
    }

    /** Service delega para PaymentService. */
    public function test_delega_para_payment_service(): void
    {
        Http::fake([
            'api-sandbox.asaas.com/v3/payments' => Http::response([
                'id' => 'pay_123',
                'status' => 'PENDING',
            ], 200),
        ]);
        
        $service = new AsaasService();
        $resultado = $service->createPayment(
            'cus_123',
            100.00,
            '2026-12-31',
            'PIX',
            'Test'
        );
        
        $this->assertEquals('pay_123', $resultado['id']);
    }

    /** mapStatus converte status corretamente. */
    public function test_map_status(): void
    {
        $this->assertEquals('Pago', AsaasService::mapStatus('RECEIVED'));
        $this->assertEquals('Pago', AsaasService::mapStatus('CONFIRMED'));
        $this->assertEquals('Pendente', AsaasService::mapStatus('PENDING'));
        $this->assertEquals('Cancelado', AsaasService::mapStatus('CANCELED'));
    }
}
