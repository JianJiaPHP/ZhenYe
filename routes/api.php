<?php
$files = glob(base_path('routes/api/*.php'));
foreach($files as $file){
    require  $file;
}
