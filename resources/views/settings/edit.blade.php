@extends('layouts.admin')

@section('title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Company Information</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                           value="{{ old('company_name', $settings->company_name) }}" required>
                    @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" name="company_logo" accept="image/*" class="form-control @error('company_logo') is-invalid @enderror">
                    @error('company_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if ($settings->logoUrl())
                        <img src="{{ $settings->logoUrl() }}" class="mt-2 rounded" style="height:48px">
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="company_phone" class="form-control @error('company_phone') is-invalid @enderror"
                           value="{{ old('company_phone', $settings->company_phone) }}">
                    @error('company_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="company_email" class="form-control @error('company_email') is-invalid @enderror"
                           value="{{ old('company_email', $settings->company_email) }}">
                    @error('company_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="company_address" rows="2" class="form-control @error('company_address') is-invalid @enderror">{{ old('company_address', $settings->company_address) }}</textarea>
                    @error('company_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Attendance</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Office Start Time <span class="text-danger">*</span></label>
                    <input type="time" name="attendance_office_start_time" class="form-control @error('attendance_office_start_time') is-invalid @enderror"
                           value="{{ old('attendance_office_start_time', $settings->attendance_office_start_time) }}" required>
                    @error('attendance_office_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Office End Time <span class="text-danger">*</span></label>
                    <input type="time" name="attendance_office_end_time" class="form-control @error('attendance_office_end_time') is-invalid @enderror"
                           value="{{ old('attendance_office_end_time', $settings->attendance_office_end_time) }}" required>
                    @error('attendance_office_end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Late Grace Period (minutes) <span class="text-danger">*</span></label>
                    <input type="number" name="attendance_late_grace_minutes" class="form-control @error('attendance_late_grace_minutes') is-invalid @enderror"
                           value="{{ old('attendance_late_grace_minutes', $settings->attendance_late_grace_minutes) }}" required>
                    <div class="form-text">Check-ins after start time + this grace period are marked Late.</div>
                    @error('attendance_late_grace_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Weekend / Off Day</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach (\App\Enums\DayOfWeek::cases() as $day)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="weekend-{{ $day->value }}" name="attendance_weekend_days[]" value="{{ $day->value }}"
                                       @checked(collect(old('attendance_weekend_days', $settings->attendance_weekend_days))->contains($day->value))>
                                <label class="form-check-label" for="weekend-{{ $day->value }}">{{ $day->value }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Days marked here are treated as non-working days for attendance.</div>
                    @error('attendance_weekend_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Visits</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">GPS Verification Radius (meters) <span class="text-danger">*</span></label>
                    <input type="number" name="visit_gps_radius_meters" class="form-control @error('visit_gps_radius_meters') is-invalid @enderror"
                           value="{{ old('visit_gps_radius_meters', $settings->visit_gps_radius_meters) }}" required>
                    <div class="form-text">A check-in farther than this from the dealer's pin is flagged unverified.</div>
                    @error('visit_gps_radius_meters') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Orders &amp; Collections</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Max Discount (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="order_max_discount_percent" class="form-control @error('order_max_discount_percent') is-invalid @enderror"
                           value="{{ old('order_max_discount_percent', $settings->order_max_discount_percent) }}" required>
                    @error('order_max_discount_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Collection Overpayment Tolerance (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="collection_overpayment_tolerance_percent" class="form-control @error('collection_overpayment_tolerance_percent') is-invalid @enderror"
                           value="{{ old('collection_overpayment_tolerance_percent', $settings->collection_overpayment_tolerance_percent) }}" required>
                    @error('collection_overpayment_tolerance_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Target Grading &amp; Notifications</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label">Grade A Min (%) <span class="text-danger">*</span></label>
                    <input type="number" name="target_grade_a_min" class="form-control @error('target_grade_a_min') is-invalid @enderror"
                           value="{{ old('target_grade_a_min', $settings->target_grade_a_min) }}" required>
                    @error('target_grade_a_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grade B Min (%) <span class="text-danger">*</span></label>
                    <input type="number" name="target_grade_b_min" class="form-control @error('target_grade_b_min') is-invalid @enderror"
                           value="{{ old('target_grade_b_min', $settings->target_grade_b_min) }}" required>
                    @error('target_grade_b_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grade C Min (%) <span class="text-danger">*</span></label>
                    <input type="number" name="target_grade_c_min" class="form-control @error('target_grade_c_min') is-invalid @enderror"
                           value="{{ old('target_grade_c_min', $settings->target_grade_c_min) }}" required>
                    @error('target_grade_c_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grade D Min (%) <span class="text-danger">*</span></label>
                    <input type="number" name="target_grade_d_min" class="form-control @error('target_grade_d_min') is-invalid @enderror"
                           value="{{ old('target_grade_d_min', $settings->target_grade_d_min) }}" required>
                    @error('target_grade_d_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Low Performance Grades (trigger a notification)</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach (\App\Enums\PerformanceGrade::cases() as $grade)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="grade-{{ $grade->value }}" name="low_performance_grades[]" value="{{ $grade->value }}"
                                       @checked(collect(old('low_performance_grades', $settings->low_performance_grades))->contains($grade->value))>
                                <label class="form-check-label" for="grade-{{ $grade->value }}">{{ $grade->value }} — {{ $grade->label() }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('low_performance_grades') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Target Reminder Window (days before month end) <span class="text-danger">*</span></label>
                    <input type="number" name="target_reminder_days_before_month_end" class="form-control @error('target_reminder_days_before_month_end') is-invalid @enderror"
                           value="{{ old('target_reminder_days_before_month_end', $settings->target_reminder_days_before_month_end) }}" required>
                    @error('target_reminder_days_before_month_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Target Reminder Minimum Pace (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="target_reminder_min_pct" class="form-control @error('target_reminder_min_pct') is-invalid @enderror"
                           value="{{ old('target_reminder_min_pct', $settings->target_reminder_min_pct) }}" required>
                    @error('target_reminder_min_pct') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Live GPS Dashboard</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Stale After (minutes) <span class="text-danger">*</span></label>
                    <input type="number" name="live_gps_stale_after_minutes" class="form-control @error('live_gps_stale_after_minutes') is-invalid @enderror"
                           value="{{ old('live_gps_stale_after_minutes', $settings->live_gps_stale_after_minutes) }}" required>
                    <div class="form-text">A ping older than this shows as "Last Known" instead of "Online".</div>
                    @error('live_gps_stale_after_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Save Settings</button>
    </form>
@endsection
