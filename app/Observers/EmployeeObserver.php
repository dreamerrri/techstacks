<?php

namespace App\Observers;

class EmployeeObserver extends AuditObserver
{
    protected string $module = 'employee';
}