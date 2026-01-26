@extends('layouts.quicksms')

@section('title', 'My Profile')

@push('styles')
<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}
.breadcrumb-item.active {
    font-weight: 500;
}
.profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #886cc0;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 600;
}
.profile-info h2 {
    margin: 0 0 0.25rem 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
}
.profile-info p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}
.profile-card {
    background: #fff;
    border: 1px solid #adb5bd;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.profile-card h5 {
    margin: 0 0 1.25rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e9ecef;
}
.profile-field {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}
.profile-field:last-child {
    border-bottom: none;
}
.profile-field-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    min-width: 140px;
}
.profile-field-value {
    font-size: 0.85rem;
    color: #333;
    text-align: right;
    flex: 1;
}
.profile-field-value .badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.35rem 0.65rem;
}
.btn-edit-profile {
    background: #886cc0;
    border-color: #886cc0;
    color: #fff;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}
.btn-edit-profile:hover {
    background: #7559a8;
    border-color: #7559a8;
    color: #fff;
}
.security-note {
    background: rgba(136, 108, 192, 0.08);
    border: 1px solid rgba(136, 108, 192, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
}
.security-note i {
    color: #886cc0;
    margin-right: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">My Profile</li>
        </ol>
    </div>
    
    @php
        $firstName = 'Sarah';
        $lastName = 'Mitchell';
        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        $email = 'sarah.mitchell@example.com';
        $mobile = '+44 7700 900123';
        $role = 'Account Administrator';
        $department = 'Marketing';
        $lastLogin = '26 Jan 2026, 09:15';
        $accountCreated = '15 Mar 2024';
        $twoFactorEnabled = true;
    @endphp
    
    <div class="profile-header">
        <div class="profile-avatar">{{ $initials }}</div>
        <div class="profile-info">
            <h2>{{ $firstName }} {{ $lastName }}</h2>
            <p>{{ $email }}</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="profile-card">
                <h5><i class="fas fa-user me-2" style="color: #886cc0;"></i>Personal Information</h5>
                <div class="profile-field">
                    <span class="profile-field-label">First Name</span>
                    <span class="profile-field-value">{{ $firstName }}</span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Last Name</span>
                    <span class="profile-field-value">{{ $lastName }}</span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Email Address</span>
                    <span class="profile-field-value">{{ $email }}</span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Mobile Number</span>
                    <span class="profile-field-value">{{ $mobile }}</span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Department</span>
                    <span class="profile-field-value">{{ $department }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="profile-card">
                <h5><i class="fas fa-shield-alt me-2" style="color: #886cc0;"></i>Account & Security</h5>
                <div class="profile-field">
                    <span class="profile-field-label">Role</span>
                    <span class="profile-field-value">
                        <span class="badge" style="background: rgba(136, 108, 192, 0.15); color: #886cc0;">{{ $role }}</span>
                    </span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Two-Factor Auth</span>
                    <span class="profile-field-value">
                        @if($twoFactorEnabled)
                            <span class="badge bg-success">Enabled</span>
                        @else
                            <span class="badge bg-warning text-dark">Disabled</span>
                        @endif
                    </span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Last Login</span>
                    <span class="profile-field-value">{{ $lastLogin }}</span>
                </div>
                <div class="profile-field">
                    <span class="profile-field-label">Account Created</span>
                    <span class="profile-field-value">{{ $accountCreated }}</span>
                </div>
            </div>
            
            <div class="security-note">
                <i class="fas fa-info-circle"></i>
                To update your password or security settings, please contact your account administrator.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[MyProfile] Page loaded');
});
</script>
@endpush
