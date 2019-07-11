//复制config文件为ding.php到配置文件

//在统一日志处理处加上
$ding=new Ding();
$ding->send('测试');
$ding->at(['18866668888'])->send('测试');
