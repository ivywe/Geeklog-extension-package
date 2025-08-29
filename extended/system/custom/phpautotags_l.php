<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// |自動タグ[l:]を使い多言語サイト用コンテンツ表示を実装するカスタム関数
// +---------------------------------------------------------------------------+
// | phpautotags_l.php
// |
// | Copyright (C) 2017 by the following authors:
// |
// | Authors: hiroron         - hiroron AT hiroron DOT com
// |
// | Version: 1.0.0 (2017-07-25)
// | Update:2024/10/25 by hiroron - autotag1.1.4向けに調整
// |
// +---------------------------------------------------------------------------+

if (strpos(strtolower($_SERVER['PHP_SELF']), 'phpautotags_l.php') !== false) {
    die('This file can not be used on its own!');
}

/**
* 自動タグ [l:<LanguageId>]LanguageIdの言語用コンテンツ[/l]
*     引数  $p1=パラメーター１ 例 [l:ja] のja
*           $p2=パラメーター２
*           $p0=フルタグ(タグ付き) 例 [l:ja]にほんご[/l]
*           $p3=タイプ(使われているプラグイン？) 例 staticpage
*           $p4=id(対象プラグインでのid) 例 test_ja
* ※p3,p4はTOPページやcoreなどからだと空文字が入っている（ここに値が入ってくるプラグインはGeeklog2.2.2以降の自動タグ向けに改良されたプラグインからのみか？）
**/
function phpautotags_l($p1, $p2, $p0, $p3, $p4) {

    $lang_id = "";
    
    // return "sp1:".$sp1. "sp2:".$sp2. "sp0:".$sp0. "sp3:".$sp3;

    if (empty($p1)) { return $p0; }
    
    if (empty($p4)) {
        $url_current = COM_getCurrentURL();
    } else {
        $url_current = $p4;
    }
    

    $lang_id = COM_getLanguageId();
    
    if (strcmp($p1, $lang_id) === 0) {
        $ret  = str_replace('[l:'.$p1.']', '', $p0);
        return str_replace('[/l]', '', $ret);
    }
    return '';
}
