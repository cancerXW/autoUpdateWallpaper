<?php
/**
 * 利用Windows 自动任务更换桌面壁纸
 */

//保存路径 TODO: 绝对路径,因为是自动任务执行的所以得绝对路径
$savePath="D:\\wallpaper";
//下载地址 抓取www.wallpaperup.com的地址
$downloadUrl = "https://www.wallpaperup.com/wallpaper/download/";
//记录文件 用于存储上一次的抓取节点
$recordFile = "record.data";
//抓取数量
$number = 5;
//默认开始记号
$start=1000;

//检查是否有网络
if (get_img($downloadUrl)===false){
    exit("网络异常,任务终止");
}
if (!is_dir($savePath)) {
    mkdir($savePath);
}

if (is_file($savePath."\\".$recordFile)){
    $start=(int)file_get_contents($savePath."\\".$recordFile);
}

//特殊文件不删除
$exclude_arr=[".","..","autoUpdateWallpaper.php",$recordFile];

//删除之前的图片
foreach (scandir($savePath) as $item){
    if (!in_array($item,$exclude_arr)){
        unlink($savePath . "\\" . $item);
    }
}

//抓取
for ($i=0;$i<$number;){
    echo '正在抓取第'.($i+1)."张...\n";
    $r = get_img($downloadUrl.$start++);
    if ($r === false) {
        file_put_contents($savePath."\\".$recordFile,$start);
        exit("网络异常,任务终止");
    }
    echo "抓取成功,正在读取数据...\n";
    $gdr = getimagesizefromstring($r);
    echo "数据读取成功...\n";
    if ($gdr) {
        echo "数据检验中...\n";
        if ($gdr[0]>=1000&&$gdr[1]>=1000)
        {
            echo "检验完成,正在写入数据...\n";
            if (file_put_contents($savePath.'\\'.uniqid().'_'.$gdr[0].'x'.$gdr[1] . '.jpg', $r)!==false){
                echo "写入成功,开始下一张...\n";
                $i++;
            }
        }else{
            echo "检验不合尺寸,长: {$gdr[0]} 宽: {$gdr[1]}\n";
        }

    }
}

file_put_contents($savePath."\\".$recordFile,$start);

//获取图片
function get_img($url){
    $curl=curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $result=curl_exec($curl);
    curl_close($curl);
    return $result;
}
