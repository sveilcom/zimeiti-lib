<?php

// +----------------------------------------------------------------------
// | Library for sveil/zimeiti-cms
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2020 KuangJia Inc.
// +----------------------------------------------------------------------
// | Website: https://sveil.com
// +----------------------------------------------------------------------
// | License ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee：https://gitee.com/sveil/zimeiti-lib
// | github：https://github.com/sveil/zimeiti-lib
// +----------------------------------------------------------------------

namespace sveil\rep\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * SVN version instructions
 *
 * Class Subversion
 * @author Richard <richard@sveil.com>
 * @package sveil\rep\command
 */
class Subversion extends Command
{
    /**
     * Account authorization file location
     * @var string
     */
    protected $authzFile = 'php://output';

    /**
     * Account management file location
     * @var string
     */
    protected $passwdFile = 'php://output';

    /**
     * Configuration instruction configuration
     */
    protected function configure()
    {
        $this->setName('xsubversion:config')->setDescription('从数据库的配置同步到SVN配置文件');
    }

    /**
     * Business instruction execution
     *
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    protected function execute(Input $input, Output $output)
    {

        $paths = ['/' => [0]];
        $where = ['status' => '1', 'is_deleted' => '0'];

        // Get available user accounts
        $users   = Db::name('CompanyUser')->field('svn_username,svn_password,svn_authorize')->where($where)->select();
        $authids = array_unique(explode(',', join(',', array_column($users, 'svn_authorize'))));

        // Get available permissions configuration
        $userAuths = Db::name('CompanyUserAuth')->field('id,path')->where($where)->whereIn('id', $authids)->order('sort desc,id desc')->select();

        foreach ($userAuths as $item) {
            foreach (explode("\n", preg_replace('/\s+/i', "\n", trim($item['path']))) as $path) {
                $paths[$path][] = $item['id'];
            }
        }

        $this->writeAuth($users, $paths);
    }

    /**
     * Write SVN configuration file
     *
     * @param array $users
     * @param array $paths
     */
    protected function writeAuth($users, $paths)
    {
        $output = [];
        // Passwd user account processing
        foreach ($users as $user) {
            $output[] = "{$user['svn_username']}={$user['svn_password']}";
        }

        file_put_contents($this->passwdFile, join(PHP_EOL, $output));
        // Authz authorization configuration processing
        $groups = ['_0' => []];
        foreach ($users as $user) {
            $ids = array_unique(explode(',', $user['svn_authorize']));
            foreach ($ids as $id) {
                $groups["_{$id}"][] = $user['svn_username'];
            }

        }
        $output   = [];
        $output[] = '[groups]';
        foreach ($groups as $key => $group) {
            $output[] = "group{$key}=" . join(',', $group);
        }

        $output[] = '';
        foreach ($paths as $path => $ids) {
            $output[] = "[{$path}]";
            $output[] = "* =";
            $output[] = '@group_0 = rw';
            foreach ($ids as $id) {
                if ($id > 0) {
                    $output[] = "@group_{$id} = rw";
                }
            }

            $output[] = '';
        }
        file_put_contents($this->authzFile, join(PHP_EOL, $output));
    }
}
