<?php
spl_autoload_register(function ($class_name) {
    require_once $class_name . '.php';
});

$leads = ApiWorker::getLeads('/api/v2/leads?');

LeadsDrawer::render($leads);
?>
