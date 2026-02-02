<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\PendingKycReminderMail;
use Illuminate\Support\Facades\Mail;
use DB;

class SendKycReminders extends Command
{
    protected $signature = 'kyc:send-reminders';
    protected $description = 'Send reminder emails to BMs with pending KYC updates';

    public function handle()
    {
        $bms = DB::table('kyc_forms as k')
    ->join('users as u', 'u.id', '=', 'k.responsible_staff_id')
    ->select(
        'u.email',
        'u.name as staff_name',
        DB::raw('COUNT(k.id) as pending_count'),
        DB::raw("
            STUFF((
                SELECT DISTINCT ', ' + TRIM(k2.buname)
                FROM kyc_forms k2
                WHERE k2.responsible_staff_id = u.id
                  AND k2.bm_status = 'pending'
                  AND k2.business_type IS NOT NULL
                FOR XML PATH(''), TYPE
            ).value('.', 'NVARCHAR(MAX)'), 1, 2, '') as business_units
        ")
    )
    ->whereNotNull('k.responsible_staff_id')
    ->whereNotNull('k.business_type')
    ->where('k.bm_status', 'pending')
    ->where('u.role', 'bm')  // Ensures only BMs
    ->groupBy('u.id', 'u.email', 'u.name')
    ->havingRaw('COUNT(k.id) > 0')
    ->get();

        if ($bms->isEmpty()) {
            $this->info('No pending KYC updates found. No emails sent.');
            return;
        }

        $this->info("Sending reminders to {$bms->count()} Branch Manager(s)...");

        foreach ($bms as $bm) {
            Mail::to($bm->email)->send(
                new PendingKycReminderMail($bm->staff_name, $bm->pending_count, $bm->business_units)
            );

            $this->info("Reminder sent to {$bm->staff_name} ({$bm->email}) - {$bm->pending_count} pending");
        }

        $this->info('All reminder emails sent successfully!');
    }
}