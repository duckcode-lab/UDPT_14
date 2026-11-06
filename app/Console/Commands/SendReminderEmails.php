<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-reminder-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $target = Carbon::now()->addHour();

        $bookings = \App\Models\Booking::with('user', 'showtime.movie')
            ->whereHas('showtime', function ($q) use ($target) {
                $q->whereBetween('time', [
                    $target->copy()->subMinutes(10),
                    $target->copy()->addMinutes(10)
                ]);
            })
            ->where('reminder_sent', false)
            ->get();

        foreach ($bookings as $booking) {

        if (!$booking->user || !$booking->user->email) continue;
            Mail::to($booking->user->email)
                ->send(new \App\Mail\ReminderMail($booking));

            $booking->update(['reminder_sent' => true]);
        }
        $this->info('Found bookings: ' . $bookings->count());
    }
}