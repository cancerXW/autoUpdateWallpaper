<?php
/**
 * 获取壁纸
 */

//使用交互模式
//define('STDIN', fopen("php://stdin", "r"));
//保存路径 TODO: 绝对路径,因为是自动任务执行的所以得绝对路径
$savePath = '/mnt/c/Users/王/Pictures/wallpaper';
//下载地址 获取www.wallpaperup.com的图片
$downloadUrl = 'https://www.wallpaperup.com/wallpaper/download/';
//记录文件 用于存储之前的抓取节点
$recordFile = 'record.data';
//获取数量
echo '请输入数量(默认10): ';
$number = (int)fgets(STDIN) ?: 10;
//$number = 30;
//默认开始记号
$start = 1000;
echo '是否保留之前的图片(y/n[默认删除]): ';
$isDelete = trim(fgets(STDIN));


if (!is_dir($savePath)) {
    mkdir($savePath);
}

if (is_file($savePath . '/' . $recordFile)) {
    $start = (int)file_get_contents($savePath . '/' . $recordFile);
}


//特殊文件不删除
$exclude_arr = ['.', '..', 'autoUpdateWallpaper.php', $recordFile];

$oldFullImgPath = [];

//如果要删除就记录之前的图片
if (strtolower($isDelete) != 'y') {
    foreach (scandir($savePath) as $item) {
        if (!in_array($item, $exclude_arr)) {
            $oldFullImgPath[] = $savePath . '/' . $item;
        }
    }
}

//抓取
for ($i = 1; $i <= $number;) {
    echo '正在获取第' . $i . "张...\n\n";
    $r = get_http_img($downloadUrl . $start++);
    if ($r === false) {
        file_put_contents($savePath . '/' . $recordFile, $start);
        exit('网络异常,任务终止');
    }
    echo "获取成功,正在读取数据...\n\n";
    $gdr = getimagesizefromstring($r);
    echo "数据读取成功...\n\n";
    if ($gdr) {
        echo "数据检验中...\n\n";
        if ($gdr[0] >= 1000 && $gdr[1] >= 1000) {
            echo "检验完成,正在写入数据...\n\n";
            $file_extension = image_type_to_extension($gdr[2]);
            $file_name = $savePath . '/' . $start . '_' . uniqid() . '_' . $gdr[0] . 'x' . $gdr[1] . ($file_extension ? $file_extension : '.jpg');
            if (file_put_contents($file_name, $r) !== false) {
                file_put_contents($savePath . '/' . $recordFile, $start);
                echo "写入成功...\n\n";
                $i++;
            }
            if (isset($oldFullImgPath[$i - 1])) {
                $oldImgPath = $oldFullImgPath[$i - 1];
                echo "删除旧图片...\n\n";
                unlink($oldImgPath);
                echo "图片 {$oldImgPath} 已删除...\n\n";
                //从数组中移除
                unset($oldFullImgPath[$i - 1]);

            }
        } else {
            echo "检验不合尺寸,长: {$gdr[0]} 宽: {$gdr[1]}\n\n";
        }

    }
}

//删除之前未删除的图片(让数量保持一致)
foreach ($oldFullImgPath as $value) {
    unlink($value);
    echo "未移除图片 {$value} 已删除...\n\n";
}

/**
 * 获取网络图片
 * @param $url
 * @return mixed  图片文件流
 */
function get_http_img($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //设置请求头
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
    //设置超时时间 300秒
//    curl_setopt($curl, CURLOPT_TIMEOUT, 300);
    echo "请求url为: {$url}\n\n";
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
