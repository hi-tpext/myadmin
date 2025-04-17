<?php

use tpext\cms\common\taglib\Processer;
use tpext\cms\common\taglib\Table;

function get_channel($id)
{
    $table = 'cms_channel';
    $dbNameSpace = Processer::getDbNamespace();
    $scope = Table::defaultScope($table);
    $item = $dbNameSpace::name($table)->where('id', $id)
        ->where($scope)
        ->cache('cms_channel_' . $id, 3600 * 24 * 7, $table)
        ->find();

    return $item;
}

function get_content($id)
{
    $table = 'cms_content';
    $dbNameSpace = Processer::getDbNamespace();
    $scope = Table::defaultScope($table);
    $item = $dbNameSpace::name($table)
        ->where('id', $id)
        ->where($scope)
        ->cache('cms_content_' . $id, 3600 * 24 * 7, $table)
        ->find();

    return $item;
}

function get_banner($id)
{
    $table = 'cms_banner';
    $dbNameSpace = Processer::getDbNamespace();
    $scope = Table::defaultScope($table);
    $item = $dbNameSpace::name($table)
        ->where('id', $id)
        ->where($scope)
        ->cache('cms_banner_' . $id, 3600 * 24 * 7, $table)
        ->find();

    return $item;
}

function get_tags($id)
{
    $table = 'cms_tags';
    $dbNameSpace = Processer::getDbNamespace();
    $scope = Table::defaultScope($table);
    $item = $dbNameSpace::name($table)
        ->where('id', $id)
        ->where($scope)
        ->find();

    return $item;
}

function channel_url($item)
{
    $url = '';
    if (is_numeric($item)) {
        $item = get_channel($item);
    }

    if (is_array($item) || is_object($item)) {
        $url = Processer::resolveWebPath($item['link']) ?: ($item['channel_path'] == '#' ? '#' : Processer::getPath() . Processer::resolveChannelPath($item) . '.html');
    }

    return $url;
}

function content_url($item)
{
    $url = '';
    if (is_numeric($item)) {
        $item = get_content($item);
    }

    if (is_array($item) || is_object($item)) {
        $channel = get_channel($item);
        if ($channel) {
            $url = Processer::resolveWebPath($item['link']) ?: Processer::getPath() . Processer::resolveContentPath($item, $channel) . '.html';
        }
    }

    return $url;
}

function banner_url($item)
{
    $url = '';
    if (is_numeric($item)) {
        $item = get_banner($item);
    }

    if (is_array($item) || is_object($item)) {
        $url = Processer::resolveWebPath($item['link']);
    }

    return $url;
}

function tag_url($item)
{
    $url = '';
    if (is_numeric($item)) {
        $item = get_tags($item);
    }

    if (is_array($item) || is_object($item)) {
        $url = Processer::getPath() . Processer::resolveTagPath($item);
    }

    return $url;
}
