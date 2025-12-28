<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailySalesReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $salesData = Order::getTodaySalesStats();
        
        // Get admin user
        $admin = User::where('is_admin', true)->first();
        
        if ($admin) {
            Log::info('Sending daily sales report', [
                'date' => $salesData['date'],
                'total_orders' => $salesData['total_orders'],
                'total_revenue' => $salesData['total_revenue'],
                'admin_email' => $admin->email,
            ]);

            Mail::to($admin->email)
                ->send(new DailySalesReport($salesData));
        } else {
            Log::warning('Daily sales report not sent: No admin user found');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Daily sales report failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

