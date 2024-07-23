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

namespace Mine\Kernel\Auth;

use App\JsonRpc\RpcActionLoginInterface;
use Mine\Exception\TokenException;
use Mine\MineRequest;
use function Hyperf\Support\env;

class LoginUser
{
    protected MineRequest $request;

    protected array $userInfo = [];

    public function __construct(array $userInfo = [])
    {
        $this->userInfo = $userInfo;

        $this->request = container()->get(MineRequest::class);
    }

    /**
     * 获取当前登录用户信息.
     */
    public function getUserInfo(string $token = ''): array
    {
        if (empty($this->userInfo) && ! empty($token)) {
            $this->getRpcUserInfo($token);
        }
        return $this->userInfo;
    }

    /**
     * 获取当前登录用户ID.
     */
    public function getId(): int
    {
        if (empty($this->userInfo)) {
            $this->getRpcUserInfo();
        }
        return $this->userInfo['id'];
    }

    /**
     * 获取当前登录用户名.
     */
    public function getUsername(): string
    {
        if (empty($this->userInfo)) {
            $this->getRpcUserInfo();
        }
        return $this->userInfo['username'];
    }

    /**
     * 是否为超级管理员（创始人），用户禁用对创始人没用.
     */
    public function isSuperAdmin(): bool
    {
        return env('SUPER_ADMIN') == $this->getId();
    }

    /**
     * 验证登录.
     */
    public function check(string $token = ''): bool
    {
        if (empty($token)) {
            $token = $this->request->getHeaderLine('Authorization');
        }

        // TODO 去登陆中心查询
        return container()->get(RpcActionLoginInterface::class)->checkLogin($token);
    }

    /**
     * 获取用户信息.
     * @return void
     */
    private function getRpcUserInfo(string $token = '')
    {
        if (empty($this->userInfo)) {
            try {
                $token = !empty($token) ? $token : $this->request->getHeaderLine('Authorization');
                $this->userInfo = container()->get(RpcActionLoginInterface::class)->getLoginInfo(str_replace('Bearer ', '', $token));

                if (empty($this->userInfo)) {
                    throw new TokenException('登录信息失效');
                }
            }catch (\Exception $e){
                throw new TokenException('登录信息失效');
            }
        }
    }
}
