<?php

return [
    'company' => getenv('SIMPLYBOOK_COMPANY') ?: '',
    'login' => getenv('SIMPLYBOOK_LOGIN') ?: '',
    'password' => getenv('SIMPLYBOOK_PASSWORD') ?: '',
];
