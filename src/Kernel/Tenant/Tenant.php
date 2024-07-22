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

namespace Mine\Kernel\Tenant;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Support\Traits\StaticInstance;
use Psr\Container\ContainerInterface;

class Tenant
{
    use StaticInstance;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected string $id = 'default';

    public function __construct()
    {
        $this->container = ApplicationContext::getContainer();
    }

    public function init($id = null): void
    {
        if (empty($id)) {
            $request = $this->container->get(RequestInterface::class);
            $id = $request->header('X-TENANT-ID');
        }
        // 将tenant_no 储存到当前协程上下文中
        $id && $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
