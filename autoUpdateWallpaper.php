<?php
/**
 * 抓取壁纸
 */

//使用交互模式
//define('STDIN', fopen("php://stdin", "r"));
//保存路径 TODO: 绝对路径,因为是自动任务执行的所以得绝对路径
$savePath = 'E:\\51397\\Pictures\\wallpaper';
//下载地址 抓取www.wallpaperup.com的地址
$downloadUrl = 'https://www.wallpaperup.com/wallpaper/download/';
//记录文件 用于存储上一次的抓取节点
$recordFile = 'record.data';
//抓取数量
//echo '请输入数量(默认10): ';
//$number = (int)fgets(STDIN) ?: 10;
$number = 10;
//默认开始记号
$start = 1000;
//echo '是否删除之前的图片(y/n[默认不删除]): ';
//$isDelete = trim(fgets(STDIN));


if (!is_dir($savePath)) {
    mkdir($savePath);
}

if (is_file($savePath . '\\' . $recordFile)) {
    $start = (int)file_get_contents($savePath . '\\' . $recordFile);
}

//特殊文件不删除
$exclude_arr = ['.', '..', 'autoUpdateWallpaper.php', $recordFile];

$oldImgPath = [];

//如果要删除就记录之前的图片
//if (strtolower($isDelete) == 'y') {
foreach (scandir($savePath) as $item) {
    if (!in_array($item, $exclude_arr)) {
        $oldImgPath[] = $savePath . '\\' . $item;
    }
}
//}

//抓取
for ($i = 1; $i <= $number;) {
    echo '正在抓取第' . $i . "张...\n";
    $r = get_http_img($downloadUrl . $start++);
    if ($r === false) {
        file_put_contents($savePath . '\\' . $recordFile, $start);
        exit('网络异常,任务终止');
    }
    echo "抓取成功,正在读取数据...\n";
    $gdr = getimagesizefromstring($r);
    echo "数据读取成功...\n";
    if ($gdr) {
        echo "数据检验中...\n";
        if ($gdr[0] >= 1000 && $gdr[1] >= 1000) {
            echo "检验完成,正在写入数据...\n";
            if (file_put_contents($savePath . '\\' . uniqid() . '_' . $gdr[0] . 'x' . $gdr[1] . '.jpg', $r) !== false) {
                file_put_contents($savePath . '\\' . $recordFile, $start);
                echo "写入成功...\n";
                $i++;
            }
        } else {
            echo "检验不合尺寸,长: {$gdr[0]} 宽: {$gdr[1]}\n";
        }

    }
}

//删除之前的图片
foreach ($oldImgPath as $value) {
    unlink($value);
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
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
