<?php

namespace App\Console\Commands;

use App\Notifications\LeadVertexNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data_array['to'] = 'webshippy';
        $data_array['msg'] = "This is just test";
        Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data_array));

        return Command::SUCCESS;
    }
}
