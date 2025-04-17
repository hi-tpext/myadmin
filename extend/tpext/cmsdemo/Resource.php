<?php

namespace tpext\cmsdemo;

use tpext\common\Resource as baseResource;
use tpext\cms\common\model\CmsContent;
use tpext\cms\common\model\CmsChannel;

class Resource extends baseResource
{
    protected $version = '1.0.1';

    protected $name = 'tpext.cms.demo';

    protected $title = 'cms演示数据';

    protected $description = '提供[tpext.cms]演示数据';

    protected $root = __DIR__ . '/';

    public function install()
    {
        $dbNameSpace = class_exists(\think\facade\Db::class) ? '\think\facade\Db' : '\think\Db';
        $data = $dbNameSpace::query("select count(*) as c from information_schema.tables where table_name like '%cms_content%'");
        if ($data[0]['c'] == 0) {
            $this->errors[] = new \Exception('<p>请先安装[tpext.cms]扩展！</p>');

            return false;
        }

        $success = parent::install();
        if ($success) {
            $this->insertData();
        }

        return $success;
    }

    public function uninstall($runSql = true)
    {
        $success = parent::uninstall();

        $dbNameSpace = class_exists(\think\facade\Db::class) ? '\think\facade\Db' : '\think\Db';
        $data = $dbNameSpace::query("select count(*) as c from information_schema.tables where table_name like '%cms_content%'");

        if ($success && $data[0]['c'] > 0) {
            $this->clearData();
        }

        return $success;
    }

    protected function insertData()
    {
        $files = [
            'http://www.people.com.cn/rss/world.xml',
            'http://www.people.com.cn/rss/game.xml',
            'http://www.people.com.cn/rss/finance.xml',
            'http://www.people.com.cn/rss/sports.xml',
            'http://www.people.com.cn/rss/house.xml',
            'http://www.people.com.cn/rss/shipin.xml',
            'http://www.people.com.cn/rss/auto.xml',
            'http://www.people.com.cn/rss/scitech.xml',
            'http://www.people.com.cn/rss/health.xml',
        ];

        $time = 60;

        foreach ($files as $i => $file) {
            $xml = simplexml_load_file($file);
            if (!$xml) {
                continue;
            }
            foreach ($xml as $key => $channel) {
                if ($key == 'channel') {
                    $hasChannel = CmsChannel::where('name', $channel->title)->find();
                    $channelId = 0;
                    if ($hasChannel) {
                        $channelId = $hasChannel['id'];
                    } else {
                        $hasChannel = new CmsChannel;
                        $resC = $hasChannel->save([
                            'name' => trim($channel->title),
                            'full_name' => trim($channel->title),
                            'is_show' => 1,
                            'type' => 3,
                            'sort' => ($i + 1) * 5,
                            'parent_id' => 0,
                            'logo' => $channel->image ? $channel->image->url : 'http://www.people.cn/img/2014peoplelogo/rmw_logo.gif'
                        ]);
                        if (!$resC) {
                            continue;
                        }
                        $channelId = $channelId = $hasChannel['id'];
                    }
                    $j = 0;
                    foreach ($channel as $k => $item) {
                        if ($k != 'item') {
                            continue;
                        }
                        $j += 5;
                        $time += 13;
                        $hasContent = CmsContent::where('title', $item->title)->find();
                        if (!$hasContent) {
                            $hasContent = new CmsContent;
                            $tag = mt_rand(1, 4);
                            $hasContent->save([
                                'channel_id' => $channelId,
                                'admin_id' => session('admin_id') ?: 0,
                                'title' => mb_substr(trim($item->title), 0, 55),
                                'is_show' => 1,
                                'author' => $item->author,
                                'source' => 'www.people.cn',
                                'sort' => $j,
                                'tags' => $tag < 5 ? $tag : '',
                                'publish_time' => date('Y-m-d H:i:s', strtotime($item->pubDate) + $time),
                                'content' => preg_replace('/(<img\s+[^>]*?src=[\'\"])(.+?\.(jpeg|jpg|png|gif|svg|ico|webp)[\'\"])/is', '$1http://www.people.com.cn$2', $item->description) . '<p>内容来源网络(www.people.cn)，仅做CMS功能演示用，请演示完毕后删除。</p>',
                            ]);
                        }
                    }
                    break;
                }
            }
        }
    }

    protected function clearData()
    {
        $resultSet = CmsContent::where('source', 'www.people.cn')->select();
        foreach ($resultSet as $result) {
            $result->delete();
        }
    }
}
