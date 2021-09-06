<?php
$files = glob(base_path('routes/admin/*.php'));
foreach($files as $file){
    require  $file;
}
