<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\AdminNotificationEvent;

class SendTestNotification extends Command
{
    protected $signature = 'notify:test 
    {type=neworder} 
    {--id=} 
    {--customer=} 
    {--total=} 
    {--name=} 
    {--email=}';


    protected $description = 'Send a test notification event to admin.notifications channel';

    public function handle(): void
    {
        $type = $this->argument('type');
$data = [];

switch ($type) {
    case 'neworder':
        $data = [
            'id' => $this->option('id'),
            'customer' => $this->option('customer'),
            'total' => $this->option('total'),
        ];
        break;

    case 'newclient':
        $data = [
            'name' => $this->option('name'),
            'email' => $this->option('email'),
        ];
        break;
}

event(new AdminNotificationEvent($type, $data));

$this->info("✅ Event yuborildi: type={$type}, data=" . json_encode($data));

    }
}
