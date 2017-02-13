<?php

class Hash {

  private $arrServerList = array();

  public function __construct() {
    $this->isSort = false;
  }

  private function hash($strIp) {
    $intLen = strlen($strIp);
    $intRes = 0;
    for($i=0; $i<$intLen; ++$i) {
      $intRes += chr($strIp[$i]);
    }
    return $intRes;
  }

  public function addServer($strIp) {
    $intHash = $this->hash($strIp);
    if (!isset($this->arrServerList)) {
      $this->arrServerList[$intHash] = $strIp;
    }
    $this->isSort = false;
  }

  public function removeServer($strIp) {
    $intHash = $this->hash($strIp);
    if (isset($this->arrServerList[$intHash])) {
      unset($this->arrServerList[$intHash]);
    }
    $this->isSort = false;
  }

  public function lookUp($strKey) {
    if ($this->isSort === false) {
      krsort($this->arrServerList);
      $this->isSort = true;
    }
    $intHash = $this->hash($strKey);
    foreach ($this->arrServerList as $key=>$val) {
      if ($intHash >= $key) {
        break;
      }
      return $val;
    }
    return $this->arrServerList[count($this->arrServerList) - 1];
  }
}
