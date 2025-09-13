<?php
//20110713 COPY
//20111219 update

if (strpos ($_SERVER['PHP_SELF'], 'lib_configuration.php') !== false) {
    die ('This file can not be used on its own.');
}


// +---------------------------------------------------------------------------+
// | configバックアップファイル作成（なければ作成、あれば上書き）
// | 書式 LIB_Backupconfig($pi_name)
// +---------------------------------------------------------------------------+
// | 引数 $pi_name:plugin name 'databox' 'userbox' 'formbox' 'assist' ''
// +---------------------------------------------------------------------------+
// | 戻値 nomal:finish message
// +---------------------------------------------------------------------------+
function LIB_Backupconfig(
	$pi_name= ''
	,$mode=""
)
{
	COM_errorLog("[".strtoupper($pi_name)."] configuration backup");
	
	global $_CONF;
    $display="";
    
	if ($pi_name==""){
		$xconf_name="_CONF";
	}else{
		$xconf_name="_".strtoupper($pi_name)."_CONF";
		global $$xconf_name;
	}
	
	$xconf=$$xconf_name;
	
	$w="";
    foreach( $xconf as $nm => $value ){
        if  (is_array($xconf[$nm])){
			$wk='$'.$xconf_name.'_bak[\''.$nm.'\']= array(';
            $wkary=$xconf[$nm];
			foreach( $wkary as $nm2 => $value2 ){
				if (is_array($value2)){
					$vl=LB."    ";
					$vl.="'".$nm2."' =>  array(";
					$wk.=$vl;
					$wkary3=$value2;
					foreach( $wkary3 as $nm3 => $value3 ){
						$vl=LB."        ";
						$vl.="'".$nm3."' => ";
						$vl.="'".$value3."'";
						$wk.=$vl.",";
					}
					$wk=rtrim($wk,",");
					$wk.=');';
				}else{
					$vl=LB."    ";
					$vl.="'".$nm2."' => ";
					$vl.="'".$value2."'";
					$wk.=$vl.",";
				}
			}
            $wk=rtrim($wk,",");
			$wk.=');'.LB;
		 }else{
			$vl=$xconf[$nm];
			$vl=str_replace("'","\'",$vl);
			$wk='$'.$xconf_name.'_bak[\''.$nm.'\']=\''.$vl.'\';'.LB;
         }
         $display.=$wk."<br>";
         $w.=$wk;
    }


	//file output open
	if ($mode=="update"){
		$outfile = $_CONF['path_log'] .$pi_name."config_bak.php";
	}else{
		$outfile = $_CONF["path_data"].$pi_name."config_bak.php";
	}
	$file = @fopen( $outfile, 'w' );
    if ( $file === false ) {
        $display .= "ERR! ".$outfile ." is not writable!<br />" . LB;
        return $display;
    }
    fwrite($file,'<?php'.LB);
    fwrite($file,$w);
    fwrite($file,'?>'.LB);
    fclose($file);

    $display.=".......... ".$pi_name." Config Backup finished!"."<br>";
    return $display;
}
// +---------------------------------------------------------------------------+
// | configリストア
// | 書式 fnc_Restoreconfig($pi_name,$config,$name_ary)
// +---------------------------------------------------------------------------+
// | 引数 $pi_name:plugin name 'databox' 'userbox' 'formbox' 'assist'
// | 引数 $config:
// +---------------------------------------------------------------------------+
// | 戻値 nomal:finish message
// +---------------------------------------------------------------------------+
// 20111219
function LIB_Restoreconfig(
	$pi_name
    ,$config
	,$mode=""
)
{
    $display="";
	COM_errorLog("[".strtoupper($pi_name)."] configuration restore");
	
    global $_CONF;
	if ($pi_name==""){
		$xconf_name="_CONF";
	}else{
		$xconf_name="_".strtoupper($pi_name)."_CONF";
		global $$xconf_name;
	}
	$xconf=$$xconf_name;

	
	//
    $display="";
	
	if ($mode=="update"){
		$outfile = $_CONF['path_log'] .$pi_name."config_bak.php";
	}else{
		$outfile = $_CONF["path_data"].$pi_name."config_bak.php";
	}
	
    if (file_exists($outfile)) {
        require_once( $outfile );

        $group=$pi_name;
		$box_conf_bak="_".strtoupper($pi_name)."_CONF_bak";
		$box_conf_bak=$$box_conf_bak;
		
	foreach( $box_conf_bak as $nm => $value ){
	    if (($nm === "version") || (substr($nm,0,3) === "fs_")
	        || ($pi_name == "assist" && $nm === "cron_schedule_interval")) {
	        continue;
	    } else {
	        $vl = $box_conf_bak[$nm];
	        // 配列やオブジェクトを文字列化
	        if (is_array($vl) || is_object($vl)) {
	            $vl_display = json_encode($vl);
	        } else {
	            $vl_display = $vl;
	        }
	        $display .= $nm . "=" . $vl_display . "<br>";
	        $config->set($nm, $vl, $group);
	    }
	}

	$display .= "..........{$pi_name} Config Restore finished!<br>";



    }
    return $display;
}
// +---------------------------------------------------------------------------+
// | config削除
// | 書式 fnc_Deleteconfig($pi_name,$config)
// +---------------------------------------------------------------------------+
// | 引数 $pi_name:plugin name 'databox' 'userbox' 'formbox' 'assist'
// | 引数 $config:
// +---------------------------------------------------------------------------+
// | 戻値 nomal:finish message
// +---------------------------------------------------------------------------+
function LIB_Deleteconfig($pi_name, $config)
{
    COM_errorLog("[" . strtoupper($pi_name) . "] configuration delete");

    global $_TABLES;

    $display = '';  // ← 初期化

    // 設定グループ名
    $group = $pi_name;

    // 設定配列名（例: $_DATABOX_CONF）
    $box_conf = "_" . strtoupper($pi_name) . "_CONF";

    // グローバル配列を取り出す
    global $$box_conf;
    $ary = $$box_conf;

    // $config のチェック
    if (!is_object($config)) {
        COM_errorLog("[$pi_name] config object is null or invalid in LIB_Deleteconfig");
        return "Config object is null or invalid.";
    }

	foreach ($ary as $nm => $value) {
	    // 配列またはオブジェクトが値の場合に文字列化
	    if (is_array($value) || is_object($value)) {
	        $value = json_encode($value);  // 配列をJSON形式に変換
	    }
	    
	    $display .= "del: " . $nm . "=" . $value . "<br>";
	    $config->del($nm, $group);
	}

    // 設定配列を空に
    $$box_conf = array();

    // 設定グループ全体の削除（必要であれば）
    // ※ これが意味するのは、「グループ全体の削除」
    $config->del(null, $group);

    // DB 上の設定値を削除
    DB_delete($_TABLES['conf_values'], 'group_name', $group);

    $display .= "..........{$pi_name} Config Delete<br>";

    return $display;
}
// +---------------------------------------------------------------------------+
// | config初期化（インストール直後の内容にもどす）
// | 書式 fnc_Initializeconfig($pi_name)
// +---------------------------------------------------------------------------+
// | 引数 $pi_name:plugin name 'databox' 'userbox' 'formbox' 'assist'
// +---------------------------------------------------------------------------+
// | 戻値 nomal:finish message
// +---------------------------------------------------------------------------+
function LIB_Initializeconfig(
	$pi_name
)
{
	COM_errorLog("[".strtoupper($pi_name)."] configuration initialize");
	
    global $_CONF;
    $display="";
    //require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $_CONF['path'] . 'plugins/'.$pi_name.'/install_defaults.php';

    $function="plugin_initconfig_".$pi_name;
    $rt=$function(1);

    $display.="..........{$pi_name} Config Initialization finished!!!"."<br>";

    return $display;

}


function LIB_Disply(
    $pi_name= ''
)
{
	$title=$pi_name." configuration backup";
    $retval = "";
    $retval .= "<!DOCTYPE    HTML PUBLIC '-//W3C//DTD HTML   4.01 Transitional//EN'>".LB;
    $retval .= "<html>".LB;
    $retval .= "<head>".LB;
    $retval .= "    <title>{$title}</title>".LB;
    $retval .= "</head>".LB;
    $retval .= "<body   bgcolor='#ffffff'>".LB;
    $retval .= "<h1>{$title}</h1>".LB;
    $retval .= "<form action="."'".THIS_SCRIPT."'"."method='post'>".LB;
    $retval .= "    <input type='submit' name='action' value='submit'>".LB;
    $retval .= "    <input type='submit' name='action' value='cancel'>".LB;
    $retval .= "</form>".LB;
    $retval .= "</body>".LB;
    $retval .= "</html>".LB;

    return $retval ;

}

?>