# php
## 通用多进程类使用方法：    
···php    
<?php
function echoData($strLoc) {
  echo $strLoc;
}
$obj = new MultiProcessing();
$obj->setTypeAndLoc('file', '/home/work/v2');
$obj->setWorkerProcess(4);
$obj->setDataHandler('echoData');
$obj->execute
```
