<?php
use Bitrix\Main\Loader;

Loader::registerAutoloadClasses(
    'mattweb.redirectmap',
    array(
        'RedirectmapActions' => 'classes/general/redirectmap.php',
        'RedirectmapEvent' => 'classes/general/redirectmap_event.php',
    )
);

