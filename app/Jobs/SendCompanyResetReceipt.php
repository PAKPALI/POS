<?php

namespace App\Jobs;

use App\Mail\CompanyResetMail;
use App\Models\CompanyResetRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCompanyResetReceipt implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 5;
    public int $backoff = 60;
    public int $uniqueFor = 600;

    public function __construct(public string $resetId) {}
    public function uniqueId(): string { return $this->resetId; }

    public function handle(): void
    {
        $reset = CompanyResetRequest::find($this->resetId);
        if (!$reset?->completed_at || $reset->notification_sent_at) return;
        Mail::to($reset->email)->send(new CompanyResetMail($reset));
        $reset->update(['notification_sent_at' => now()]);
    }
}
