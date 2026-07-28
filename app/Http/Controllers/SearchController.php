<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\WorkRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $isHR = $user->isHR();

        if (mb_strlen($query) < 2) {
            return response()->json(['groups' => []]);
        }

        $groups = [];

        if ($isAdmin || $isHR) {
            $employees = Employee::query()
                ->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$query}%"])
                ->orWhere('employee_id', 'like', "%{$query}%")
                ->orWhere('position', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn (Employee $employee) => [
                    'title' => $employee->full_name,
                    'subtitle' => $employee->position,
                    'icon' => 'tabler--id',
                    'url' => route('employees.show', $employee),
                ])
                ->values();

            if ($employees->isNotEmpty()) {
                $groups[] = ['label' => 'Employees', 'items' => $employees];
            }

            $workRequests = WorkRequest::query()
                ->where('reason', 'like', "%{$query}%")
                ->orWhereHas('employee', fn ($q) => $q->whereRaw(
                    "CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?",
                    ["%{$query}%"]
                ))
                ->with('employee')
                ->limit(5)
                ->get()
                ->map(fn (WorkRequest $workRequest) => [
                    'title' => 'Work Request #' . $workRequest->id,
                    'subtitle' => $workRequest->employee->full_name ?? ucfirst($workRequest->status),
                    'icon' => 'tabler--notes',
                    'url' => route('work-requests.show', $workRequest),
                ])
                ->values();

            if ($workRequests->isNotEmpty()) {
                $groups[] = ['label' => 'Work Requests', 'items' => $workRequests];
            }

            // Payroll — no separate payroll model to search; payroll is viewed
            // per-employee (payroll.show takes {employee}), so this just
            // re-searches employees and points at their payroll page instead.
            $payrollEmployees = Employee::query()
                ->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$query}%"])
                ->orWhere('employee_id', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn (Employee $employee) => [
                    'title' => $employee->full_name,
                    'subtitle' => 'View payroll',
                    'icon' => 'tabler--cash',
                    'url' => route('payroll.show', $employee),
                ])
                ->values();

            if ($payrollEmployees->isNotEmpty()) {
                $groups[] = ['label' => 'Payroll', 'items' => $payrollEmployees];
            }

            // Contributions — same story: government-contributions.show is
            // per-employee, so match on name or any of the gov't ID numbers.
            $contributionEmployees = Employee::query()
                ->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$query}%"])
                ->orWhere('sss_number', 'like', "%{$query}%")
                ->orWhere('philhealth_number', 'like', "%{$query}%")
                ->orWhere('pagibig_number', 'like', "%{$query}%")
                ->orWhere('tin_number', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn (Employee $employee) => [
                    'title' => $employee->full_name,
                    'subtitle' => 'View contributions',
                    'icon' => 'tabler--id-badge',
                    'url' => route('government-contributions.show', $employee),
                ])
                ->values();

            if ($contributionEmployees->isNotEmpty()) {
                $groups[] = ['label' => 'Contributions', 'items' => $contributionEmployees];
            }
        }

        // Users — admin only, mirrors the sidebar (HR doesn't see Users/Roles/
        // Permissions/Audit Logs). No per-user show route exists, so results
        // link back to the index rather than a specific user page.
        if ($isAdmin) {
            $users = User::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn (User $u) => [
                    'title' => $u->name,
                    'subtitle' => $u->email,
                    'icon' => 'tabler--users',
                    'url' => route('users.index'),
                ])
                ->values();

            if ($users->isNotEmpty()) {
                $groups[] = ['label' => 'Users', 'items' => $users];
            }
        }

        return response()->json(['groups' => $groups]);
    }
}