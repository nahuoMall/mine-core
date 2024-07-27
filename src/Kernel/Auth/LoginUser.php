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

use Hyperf\Context\Context;
use Mine\MineRequest;

class LoginUser
{
    protected MineRequest $request;

    protected array $userInfo = [];

    public function __construct(array $userInfo = [])
    {
        $this->userInfo = $userInfo ?: Context::get('admin_user_info', []);
        $this->request = container()->get(MineRequest::class);
    }

    /**
     * 获取当前登录用户信息.
     */
    public function getUserInfo(string $token = ''): array
    {
        return $this->userInfo;
    }

    /**
     * 获取当前登录用户ID.
     */
    public function getId(): int
    {
        return $this->userInfo['id'];
    }

    /**
     * 获取当前登录用户名.
     */
    public function getUsername(): string
    {
        return $this->userInfo['username'];
    }

    /**
     * 是否为超级管理员（创始人），用户禁用对创始人没用.
     */
    public function isSuperAdmin(): bool
    {
        return (int) $this->userInfo['user_type'] === 100;
    }

    /**
     * 验证登录.
     */
    public function check(): bool
    {
        if (empty($this->userInfo)) {
            return false;
        }
        return true;
    }

}
