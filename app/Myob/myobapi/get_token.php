<?php

//$this_comp = unserialize('a:2:{i:0;s:13:"Administrator";i:1;s:10:"dmw1999iew";}');

//echo $this_comp;

//$this_comp = 'Administrator:dmw1999iew';

$this_comp = 'Administrator:morgan';

//$this_comp = 'Administrator:';

//$this_comp = 'Administrator:';

$cftoken = base64_encode($this_comp);

echo $cftoken;