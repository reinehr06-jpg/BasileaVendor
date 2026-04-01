<?php

namespace App\Console\Commands;

use App\Services\Checkout\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscriptionRenewalCommand extends Command
{
    protected $signature = 'subscription:check-renewals';
    protected $description = 'Check and process subscription renewals that are due';

    public function handle(): int
    {
        $this->info('Checking subscription renewals...');

        $service = new SubscriptionService();
        $subscriptions = $service->getSubscriptionsNeedingRenewal();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions need renewal.');
            return self::SUCCESS;
        }

        $this->info("Found {$subscriptions->count()} subscriptions needing renewal.");

        $processed = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            $this->line("Processing subscription #{$subscription->id} for lead #{$subscription->lead_id}");

            $invoice = $service->processRenewal($subscription);

            if ($invoice) {
                $processed++;
                $this->info("  ✓ Invoice #{$invoice->id} created");
            } else {
                $failed++;
                $this->error("  ✗ Failed to process renewal");
            }
        }

        $this->info("\nDone! Processed: {$processed}, Failed: {$failed}");

        Log::info('SubscriptionRenewalCommand completed', [
            'total' => $subscriptions->count(),
            'processed' => $processed,
            'failed' => $failed,
        ]);

        return self::SUCCESS;
    }
}
