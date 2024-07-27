<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Mine\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Mine\Mine;
use Mine\MineCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * Class InstallProjectCommand.
 */
#[Command]
class InstallProjectCommand extends MineCommand
{
    protected const CONSOLE_GREEN_BEGIN = "\033[32;5;1m";

    protected const CONSOLE_RED_BEGIN = "\033[31;5;1m";

    protected const CONSOLE_END = "\033[0m";

    /**
     * 安装命令.
     */
    protected ?string $name = 'mine:install';

    protected array $database = [];

    protected array $redis = [];

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php mine:install" install MineAdmin system');
        $this->setDescription('MineAdmin system install command');
    }

    public function handle(): void
    {
        $step = $this->installLocalModule();
        $step && $this->setOthers();
        $step && $this->finish();
    }

    protected function welcome(): void
    {
        $this->line('-----------------------------------------------------------', 'comment');
        $this->line('Hello, welcome use HeavyAdmin system.', 'comment');
        $this->line('The installation is about to start, just a few steps', 'comment');
        $this->line('-----------------------------------------------------------', 'comment');
    }

    /**
     * install modules.
     */
    protected function installLocalModule(): bool
    {
        /* @var Mine $mine */
        $tenantNo = $this->input->getArgument('tenant_no');
        if (empty( $tenantNo)) {
            $this->error('tenant number cannot be empty');
            return false;
        }
        $this->line("Installation of Tenant $tenantNo local modules is about to begin...\n", 'comment');
        $mine = make(Mine::class);
        $modules = $mine->getModuleInfo();

        foreach ($modules as $name => $info) {
            $this->call('mine:migrate-run', ['name' => $name, '--force' => 'true', '--database' => $tenantNo]);
            if ($name === 'System') {
                // TODO 交由业务系统调用rpc进行处理
                $this->call('init:systemUser', ['tenant_no' => $tenantNo]);
            }
            $this->call('mine:seeder-run', ['name' => $name, '--force' => 'true', '--database' => $tenantNo]);
            $this->line($this->getGreenText(sprintf('"%s" Tenant ' .$tenantNo. ' module install successfully', $name)));
        }

        return true;
    }

    protected function setOthers(): void
    {
        $this->line(PHP_EOL . ' MineAdmin set others items...' . PHP_EOL, 'comment');
        $this->call('mine:update');

        if (! file_exists(BASE_PATH . '/config/autoload/mineadmin.php')) {
            $this->call('vendor:publish', ['package' => 'nahuomall/mine-core']);
        }
    }

    protected function finish(): void
    {
        $this->output->write(PHP_EOL . $this->getGreenText('The installation is almost complete'), false);
        $this->line(PHP_EOL . '数据已初始化成功，请执行脚本进行管理员用户初始化');
    }

    protected function getArguments()
    {
        return [
            ['tenant_no', InputArgument::REQUIRED, 'is the tenant to be run init'],
        ];
    }
}
