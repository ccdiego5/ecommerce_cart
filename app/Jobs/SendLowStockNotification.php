<?php

namespace App\Jobs;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendLowStockNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get admin user
        $admin = User::where('is_admin', true)->first();
        
        if ($admin && $this->product->isLowStock()) {
            Log::info('Sending low stock notification', [
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'stock_quantity' => $this->product->stock_quantity,
                'threshold' => $this->product->low_stock_threshold,
                'admin_email' => $admin->email,
            ]);

            Mail::to($admin->email)
                ->send(new LowStockAlert($this->product));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Low stock notification failed', [
            'product_id' => $this->product->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

