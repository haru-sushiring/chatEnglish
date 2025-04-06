<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendVocabularyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vocabulary:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send vocabulary notifications via LINE';

    protected $lineNotification;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->lineNotification = app('line-notification');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');

        $vocabularies = \App\Models\Vocabulary::where(function ($query) use ($today) {
            $query->whereDate('notification_date', $today)
                ->orWhereDate('test_notification_date', $today);
        })
            ->whereNull('notified_at')
            ->with('user')
            ->get();

        foreach ($vocabularies as $vocabulary) {
            $message = "【単語復習】\n{$vocabulary->word}: {$vocabulary->meaning}";

            if ($this->lineNotification->sendNotification($vocabulary->user->line_id, $message)) {
                $vocabulary->update(['notified_at' => now()]);
                $this->info("Sent notification for: {$vocabulary->word}");
            } else {
                $this->error("Failed to send notification for: {$vocabulary->word}");
            }
        }

        return 0;
    }
}
