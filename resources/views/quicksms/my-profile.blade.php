@extends('layouts.quicksms')

@section('title', 'My Profile')

@push('styles')
<style>
.profile-avatar-large {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: #886cc0;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
}
.form-label .text-muted {
    font-weight: 400;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-titles">
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
        $subAccount = 'Marketing Department';
        $accountName = 'Acme Corporation Ltd';
        $lastLogin = '26 Jan 2026, 09:15';
        $accountCreated = '15 Mar 2024';
        $lastPasswordChange = '10 Jan 2026';
        $twoFactorEnabled = true;
        $loginCount = 247;
    @endphp
    
    <div class="row">
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Profile Information</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="profile-avatar-large me-3">{{ $initials }}</div>
                        <div>
                            <h5 class="mb-1">{{ $firstName }} {{ $lastName }}</h5>
                            <span class="text-muted">{{ $email }}</span>
                        </div>
                    </div>
                    
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="{{ $firstName }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="{{ $lastName }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="{{ $email }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" value="{{ $mobile }}">
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-light ms-2">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Security</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-1">Password</h6>
                                <span class="text-muted" style="font-size: 0.85rem;">Last changed: {{ $lastPasswordChange }}</span>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm">Change Password</button>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Two-Factor Authentication</h6>
                            <span class="text-muted" style="font-size: 0.85rem;">Add an extra layer of security to your account</span>
                        </div>
                        <div>
                            @if($twoFactorEnabled)
                                <span class="badge badge-success light">Enabled</span>
                            @else
                                <span class="badge badge-warning light">Disabled</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Account & Permissions</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Account</label>
                        <span>{{ $accountName }}</span>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Sub-Account</label>
                        <span>{{ $subAccount }}</span>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Role</label>
                        <span class="badge badge-primary light">{{ $role }}</span>
                    </div>
                    <div>
                        <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Permissions</label>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-light">Send Messages</span>
                            <span class="badge badge-light">View Reports</span>
                            <span class="badge badge-light">Manage Contacts</span>
                            <span class="badge badge-light">Manage Templates</span>
                            <span class="badge badge-light">View Billing</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Audit & Metadata</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Account Created</label>
                            <span>{{ $accountCreated }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Last Login</label>
                            <span>{{ $lastLogin }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">Total Logins</label>
                            <span>{{ $loginCount }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="text-muted mb-1 d-block" style="font-size: 0.8rem;">User ID</label>
                            <span class="text-muted" style="font-family: monospace; font-size: 0.85rem;">usr_8f4a2b1c</span>
                        </div>
                    </div>
                </div>
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
