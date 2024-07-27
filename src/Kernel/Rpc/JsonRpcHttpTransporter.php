<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Mine\Kernel\Rpc;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Guzzle\ClientFactory;

class JsonRpcHttpTransporter extends \Hyperf\JsonRpc\JsonRpcHttpTransporter
{
    public function __construct(ClientFactory $clientFactory, array $config = [])
    {
        $token = container()->get(RequestInterface::class)->getHeaderLine('Authorization');
        $tenantId = container()->get(RequestInterface::class)->getHeaderLine('X-Tenant-Id');
        $config['headers'] = ['Authorization' => $token ?? '', 'TenantId' => $tenantId];
        parent::__construct($clientFactory, $config);
    }

}
