<?php
/**
 * Created by PhpStorm.
 * User: yangjingquan
 * Date: 2019/3/8
 * Time: 5:31 PM
 */

namespace app\command\daemon;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class TestCron extends Command
{
    protected function configure()
    {
        $this->setName('TestCron')
            ->setDescription('计划任务TestCron');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->doCron();
        $output->writeln("已经执行计划任务TestCron");
    }

    public function doCron()
    {
        $data = array(
            'a'   => rand(10000,99999),
            'b1'   => rand(10000,99999),
            'b2'   => rand(10000,99999),
            'c1'   => rand(10000,99999),
            'c2'   => rand(10000,99999),
            'c3'   => rand(10000,99999),
            'c4'   => rand(10000,99999),
            'rec_id'   => rand(10000,99999),
            'create_time'   => date('Y-m-d H:i:s')
        );

        DB::table('store_teams')->insert($data);
    }

}