<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\SeniorCitizen;
use App\Services\SemaphoreSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAnnouncementSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Announcement $announcement)
    {
    }

    public function handle(SemaphoreSmsService $sms): void
    {
        $this->announcement->update(['status' => 'sending']);

        // Only the barangays picked on the announcement.
        // No barangays picked = every eligible senior citizen.
        $codes = $this->announcement->barangays()
            ->pluck('barangay_code')
            ->all();

        $numbers = SeniorCitizen::eligible()
            ->whereNotNull('contact_number')
            ->when(
                count($codes) > 0,
                fn ($q) => $q->whereIn('barangay_code', $codes)
            )
            ->pluck('contact_number')
            ->unique()
            ->values()
            ->toArray();

        $body = "{$this->announcement->title}\n\n{$this->announcement->message}";

        if ($this->announcement->distribution_date) {
            $body .= "\nDate: {$this->announcement->distribution_date->format('M d, Y')}";
        }

        if ($this->announcement->distribution_location) {
            $body .= "\nVenue: {$this->announcement->distribution_location}";
        }

        $result = $sms->sendBulk($numbers, $body);

        $this->announcement->update([
            'status'           => ($result['failed'] > 0 && $result['sent'] === 0) ? 'failed' : 'sent',
            'recipients_count' => count($numbers),
            'sent_count'       => $result['sent'],
            'sent_at'          => now(),
        ]);
    }
}