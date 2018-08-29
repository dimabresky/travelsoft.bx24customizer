<?php

$classes = [
    "travelsoft\\bx24customizer\\EventsHandlers" => "lib/EventsHandlers.php",
    "travelsoft\\bx24customizer\\Tools" => "lib/Tools.php",
    "travelsoft\\bx24customizer\\Cache" => "lib/Cache.php",
    "travelsoft\\bx24customizer\\mastertour\\Gateway" => "lib/mastertour/Gateway.php"
];
CModule::AddAutoloadClasses("travelsoft.bx24customizer", $classes);
