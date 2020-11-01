<?php

namespace yxmingy\crawler;

require_once "Scheduler.php";
  echo "请输入艺人id：";
  $id = trim(fgets(STDIN));
  $scheduler = new Scheduler($id);
  
  $scheduler->start();