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

    return Processer::detail($table, $item);
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

    return Processer::detail($table, $item);
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

    return Processer::detail($table, $item);
}

function get_tags($id)
{
    $table = 'cms_tags';
    $dbNameSpace = Processer::getDbNamespace();
    $scope = Table::defaultScope($table);
    $item = $dbNameSpace::name($table)
        ->where('id', $id)
        ->where($scope)
        ->cache('cms_tags_' . $id, 3600 * 24 * 7, $table)
        ->find();

    return Processer::detail($table, $item);
}

function channel_url($item)
{
    if (is_numeric($item)) {
        $item = get_channel($item);
    }

    return $item['url'];
}

function content_url($item)
{
    if (is_numeric($item)) {
        $item = get_content($item);
    }

    return $item['url'];
}

function banner_url($item)
{
    if (is_numeric($item)) {
        $item = get_banner($item);
    }

    return $item['url'];
}

function tag_url($item)
{
    if (is_numeric($item)) {
        $item = get_tags($item);
    }

    return $item['url'];
}

function more($str, $len = 100, $more = '...')
{
    if (mb_strlen($str, 'utf-8') > $len) {
        return mb_substr($str, 0, $len, 'utf-8') . $more;
    } else {
        return $str;
    }
}
