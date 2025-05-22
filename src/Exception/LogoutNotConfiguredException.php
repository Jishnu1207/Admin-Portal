<?php

namespace App\Exception;

class LogoutNotConfiguredException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Don\'t forget to activate logout in security.yaml');
    }
}
