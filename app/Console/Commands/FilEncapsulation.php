<?php

namespace App\Console\Commands;

use App\Models\FilOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FilEncapsulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FilEncapsulation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $newTime = Carbon::now()->toDateString();
        $list = FilOrder::query()->where(['pay_states' => 1, 'encapsulation' => 2,'pledge_status'=>1])->get()->toArray();
        //循环判断购买时间大于30天的数据
        foreach ($list as $k => $v) {
            $time = Carbon::parse($v['updated_at'])->addDays(30)->toDateString();
            if ($time < $newTime) {
                FilOrder::query()->where('id', $v['id'])->update(['encapsulation' => 1]);
            }
        }
        return success(1);
    }
}
