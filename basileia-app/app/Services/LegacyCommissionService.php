<?php

namespace App\Services;

use App\Models\LegacyCommission;
use App\Models\LegacyCustomerImport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegacyCommissionService
{
    public function getPendingCommissions(): Collection
    {
        return LegacyCommission::with(['vendedor', 'gestor', 'cliente', 'legacyImport'])
            ->where('status', 'GENERATED')
            ->orderBy('generated_at', 'desc')
            ->get();
    }

    public function getPendingByVendedor(int $vendedorId): Collection
    {
        return LegacyCommission::with(['cliente', 'legacyImport'])
            ->where('vendedor_id', $vendedorId)
            ->where('status', 'GENERATED')
            ->orderBy('generated_at', 'desc')
            ->get();
    }

    public function getPendingByGestor(int $gestorId): Collection
    {
        return LegacyCommission::with(['vendedor', 'cliente', 'legacyImport'])
            ->where('gestor_id', $gestorId)
            ->where('status', 'GENERATED')
            ->orderBy('generated_at', 'desc')
            ->get();
    }

    public function markAsPaid(int $commissionId, ?string $notes = null): LegacyCommission
    {
        $commission = LegacyCommission::findOrFail($commissionId);

        $commission->update([
            'status' => 'PAID',
            'released_at' => now(),
            'notes' => $notes ?? ('Pago em '.now()->format('d/m/Y H:i')),
        ]);

        Log::info('[LegacyCommission] Comissão marcada como paga', [
            'commission_id' => $commissionId,
            'vendedor_id' => $commission->vendedor_id,
            'gestor_id' => $commission->gestor_id,
            'amount' => $commission->seller_commission_amount + $commission->gestor_commission_amount,
        ]);

        return $commission;
    }

    public function markMultipleAsPaid(array $commissionIds, ?string $notes = null): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
        ];

        foreach ($commissionIds as $commissionId) {
            try {
                $this->markAsPaid($commissionId, $notes);
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "ID {$commissionId}: ".$e->getMessage();
            }
        }

        return $results;
    }

    public function blockCommission(int $commissionId, string $reason): LegacyCommission
    {
        $commission = LegacyCommission::findOrFail($commissionId);

        $commission->update([
            'status' => 'BLOCKED',
            'notes' => $reason,
        ]);

        Log::info('[LegacyCommission] Comissão bloqueada', [
            'commission_id' => $commissionId,
            'reason' => $reason,
        ]);

        return $commission;
    }

    public function generateRecurringForAll(?int $vendedorId = null, ?string $month = null): array
    {
        $query = LegacyCustomerImport::where('import_status', 'IMPORTED')
            ->where('generate_recurring_commission', true)
            ->whereIn('subscription_status', ['ACTIVE', 'OVERDUE']);

        if ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        $imports = $query->get();

        $stats = [
            'processed' => 0,
            'generated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $importService = new LegacyImportService;

        foreach ($imports as $import) {
            $stats['processed']++;
            try {
                $result = $importService->generateCommissions($import, $month);
                $stats['generated'] += $result['old_sale'] + $result['recurring'];
                if (empty($result['old_sale']) && empty($result['recurring'])) {
                    $stats['skipped']++;
                }
            } catch (\Exception $e) {
                $stats['errors'][] = "Import {$import->id}: ".$e->getMessage();
                Log::error('[LegacyCommission] Erro ao gerar comissão', [
                    'import_id' => $import->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    public function getSummary(): array
    {
        $pending = LegacyCommission::where('status', 'GENERATED')->sum(
            DB::raw('COALESCE(seller_commission_amount, 0) + COALESCE(gestor_commission_amount, 0)')
        );

        $paidThisMonth = LegacyCommission::where('status', 'PAID')
            ->whereYear('released_at', now()->year)
            ->whereMonth('released_at', now()->month)
            ->sum(
                DB::raw('COALESCE(seller_commission_amount, 0) + COALESCE(gestor_commission_amount, 0)')
            );

        $totalPending = LegacyCommission::where('status', 'GENERATED')->count();
        $totalVendedores = LegacyCommission::where('status', 'GENERATED')
            ->distinct('vendedor_id')
            ->count('vendedor_id');

        return [
            'pending_amount' => $pending,
            'paid_this_month' => $paidThisMonth,
            'total_pending_commissions' => $totalPending,
            'total_vendedores_with_pending' => $totalVendedores,
        ];
    }

    public function getCommissionsByMonth(string $month): Collection
    {
        return LegacyCommission::with(['vendedor', 'gestor', 'cliente', 'legacyImport'])
            ->where('reference_month', $month)
            ->orderBy('generated_at', 'desc')
            ->get();
    }
}
