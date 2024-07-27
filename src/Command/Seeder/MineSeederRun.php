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

namespace Mine\Command\Seeder;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Concerns\Confirmable;
use Hyperf\Database\Commands\Seeders\BaseCommand;
use Hyperf\Database\Seeders\Seed;
use Mine\Kernel\Tenant\Tenant;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MineSeederRun.
 */
#[Command]
class MineSeederRun extends BaseCommand
{
    use Confirmable;

    /**
     * The console command name.
     */
    protected ?string $name = 'mine:seeder-run';

    /**
     * The console command description.
     */
    protected string $description = 'Seed the database with records';

    /**
     * The seed instance.
     */
    protected Seed $seed;

    protected string $module;

    /**
     * Create a new seed command instance.
     */
    public function __construct(Seed $seed)
    {
        parent::__construct();

        $this->seed = $seed;

        $this->setDescription('The run seeder class of MineAdmin module');
    }

    /**
     * Handle the current command.
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->module = ucfirst(trim($this->input->getArgument('name')));

        $database = $this->input->getOption('database');

        // 获取所有租户
        $tenantIds = Tenant::instance()->getAllTenant();


        foreach ($tenantIds as $tenantId) {
            // 初始化租户
            Tenant::instance()->init($database ?: $tenantId);

            $this->line('seeder run by tenant_no: ' . $database?:$tenantId);

            $this->seed->setOutput($this->output);

            $this->seed->setConnection($database ?: $tenantId);

            $this->seed->run([$this->getSeederPath()]);

            if (!empty($database)) {
                break;
            }
        }
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The run seeder class of the name'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the seeders file stored'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided seeder file paths are pre-resolved absolute paths'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }

    protected function getSeederPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? BASE_PATH . '/' . $targetPath
                : $targetPath;
        }

        return BASE_PATH . '/app/' . $this->module . '/Database/Seeders';
    }
}
