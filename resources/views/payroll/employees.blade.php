@extends('layouts.staff')

@section('content')
<div class="container py-4">
    <h1>Employees</h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Employees</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Position</th>
                                <th>Hire Date</th>
                                <th>Base Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->phone ?? '-' }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->hire_date->format('Y-m-d') }}</td>
                                <td>{{ $employee->base_salary }}</td>
                                <td>
                                    <span class="badge bg-{{ $employee->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
