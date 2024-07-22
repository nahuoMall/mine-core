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

use Mine\Kernel\Tenant\Tenant;
use Hyperf\Guzzle\ClientFactory;

class JsonRpcHttpTransporter extends \Hyperf\JsonRpc\JsonRpcHttpTransporter
{

    public function __construct(ClientFactory $clientFactory, array $config = [])
    {
        // $token = container(RequestInterface::class)->getHeaderLine('Authorization');
        $config['headers'] = ['Authorization' => $token ?? '', 'TenantId' => Tenant::instance()->getId()];
        parent::__construct($clientFactory, $config);
    }

}
