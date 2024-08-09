<?php

namespace App\Console\Commands;

use App\Services\StarSender;
use Illuminate\Console\Command;
use Intervention\Image\ImageManager as Image;

class testingWa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testing-wa';

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
        $pesan = 'testing pesan';
        $tujuan = '085208303087';
        $file = asset('storage/invoices/invoice-3.pdf');

        $req = new StarSender( $tujuan,$pesan, $file);
        $req->sendGroup();
    }
}
