@extends('layouts.admin')

@section('title', 'Message Log (Global)')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Reporting</a>
        <span class="separator">/</span>
        <span>Message Log (Global)</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3a5f; font-weight: 600;">Global Message Log</h4>
            <p class="text-muted mb-0">Search and view all messages across all client accounts</p>
        </div>
        <button class="btn" style="background: #4a90d9; color: #fff;">
            <i class="fas fa-download me-2"></i>Export
        </button>
    </div>

    <div class="admin-filter-bar">
        <div class="filter-group">
            <label>Date Range</label>
            <input type="date" class="form-control form-control-sm">
        </div>
        <div class="filter-group">
            <label>To</label>
            <input type="date" class="form-control form-control-sm">
        </div>
        <div class="filter-group">
            <label>Account</label>
            <select class="form-select form-select-sm">
                <option>All Accounts</option>
                <option>ACC-1234 - Acme Corp</option>
                <option>ACC-5678 - Finance Ltd</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm">
                <option>All</option>
                <option>Delivered</option>
                <option>Failed</option>
                <option>Pending</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Channel</label>
            <select class="form-select form-select-sm">
                <option>All</option>
                <option>SMS</option>
                <option>RCS</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Phone, Message ID...">
        </div>
        <button class="btn admin-btn-apply">Apply</button>
    </div>

    <div class="admin-card">
        <div class="card-body p-0">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Account</th>
                        <th>Recipient</th>
                        <th>Content</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Sender ID</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Revenue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-size: 0.8rem;">2026-01-19 14:32:15</td>
                        <td>ACC-1234</td>
                        <td><span class="masked-value" id="phone-1" data-masked="+44****89" data-unmasked="+447700900189">+44****89</span>
                            <button class="reveal-btn" data-target="phone-1" data-type="Phone" data-record-id="msg-001">Reveal</button>
                        </td>
                        <td><span class="masked-value">[Content masked]</span></td>
                        <td>SMS</td>
                        <td><span class="admin-status-badge live">Delivered</span></td>
                        <td>ACME</td>
                        <td class="text-end">£0.032</td>
                        <td class="text-end">£0.045</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm">Detail</button>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.8rem;">2026-01-19 14:31:58</td>
                        <td>ACC-5678</td>
                        <td><span class="masked-value">+44****23</span></td>
                        <td><span class="masked-value">[Content masked]</span></td>
                        <td>RCS</td>
                        <td><span class="admin-status-badge live">Delivered</span></td>
                        <td>FINBANK</td>
                        <td class="text-end">£0.028</td>
                        <td class="text-end">£0.042</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm">Detail</button>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.8rem;">2026-01-19 14:31:42</td>
                        <td>ACC-1234</td>
                        <td><span class="masked-value">+44****56</span></td>
                        <td><span class="masked-value">[Content masked]</span></td>
                        <td>SMS</td>
                        <td><span class="admin-status-badge suspended">Failed</span></td>
                        <td>ACME</td>
                        <td class="text-end">£0.000</td>
                        <td class="text-end">£0.000</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm">Detail</button>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.8rem;">2026-01-19 14:31:20</td>
                        <td>ACC-9012</td>
                        <td><span class="masked-value">+44****78</span></td>
                        <td><span class="masked-value">[Content masked]</span></td>
                        <td>SMS</td>
                        <td><span class="admin-status-badge pending">Pending</span></td>
                        <td>RETAIL</td>
                        <td class="text-end">£0.032</td>
                        <td class="text-end">£0.048</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm">Detail</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted" style="font-size: 0.85rem;">Showing 1-4 of 1,247,832 messages</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection
