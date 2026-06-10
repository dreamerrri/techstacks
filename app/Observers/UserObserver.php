<?php

namespace App\Observers;

class UserObserver extends AuditObserver
{
    protected string $module = 'user';
}