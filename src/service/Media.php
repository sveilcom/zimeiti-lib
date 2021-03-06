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

namespace sveil\service;

use sveil\common\File;
use sveil\common\MyCurlFile;
use sveil\exception\InvalidResponseException;
use sveil\exception\LocalCacheException;
use sveil\Service;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;

/**
 * WeChat material management
 *
 * Class Media
 * @author Richard <richard@sveil.com>
 * @package sveil\service
 */
class Media extends Service
{

    /**
     * Read image information by image ID
     *
     * @param integer $id Local image ID
     * @param array $where Additional search conditions
     * @return array
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public static function news($id, $where = [])
    {

        $data                                = Db::name('WechatNews')->where(['id' => $id])->where($where)->find();
        list($data['articles'], $articleIds) = [[], explode(',', $data['article_id'])];
        $articles                            = Db::name('WechatNewsArticle')->whereIn('id', $articleIds)->select();

        foreach ($articleIds as $article_id) {
            foreach ($articles as $article) {
                if (intval($article['id']) === intval($article_id)) {
                    array_push($data['articles'], $article);
                }

                unset($article['create_by'], $article['create_at']);
            }
        }

        return $data;
    }

    /**
     * Upload image permanent material，return media_id
     *
     * @param string $url File URL
     * @param string $type File type
     * @param array $videoInfo Video information
     * @return string|null
     * @throws InvalidResponseException
     * @throws LocalCacheException
     * @throws Exception
     * @throws PDOException
     */
    public static function upload($url, $type = 'image', $videoInfo = [])
    {

        $where = ['md5' => md5($url), 'appid' => WechatService::getAppid()];

        if (($mediaId = Db::name('WechatMedia')->where($where)->value('media_id'))) {
            return $mediaId;
        }

        $result = WechatService::WeChatMedia()->addMaterial(self::getServerPath($url), $type, $videoInfo);
        data_save('WechatMedia', [
            'local_url' => $url, 'md5'                                             => $where['md5'], 'appid' => WechatService::getAppid(), 'type' => $type,
            'media_url' => isset($result['url']) ? $result['url'] : '', 'media_id' => $result['media_id'],
        ], 'type', $where);

        return $result['media_id'];
    }

    /**
     * File location handling
     *
     * @param string $local
     * @return string
     * @throws LocalCacheException
     */
    private static function getServerPath($local)
    {

        if (file_exists($local)) {
            return new MyCurlFile($local);
        } else {
            return new MyCurlFile(File::down($local)['file']);
        }

    }

}
