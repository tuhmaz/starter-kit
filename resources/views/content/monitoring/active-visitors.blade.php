@extends('layouts.contentNavbarLayout')

@section('title', 'Active Visitors')
@push('meta')
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Referrer-Policy" content="same-origin">
@endpush

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss'
])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Active Visitors</h5>
        <div>
          <span class="badge bg-success" id="visitors-count">0</span>
          <span>Active Visitor</span>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover" id="visitors-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Current Page</th>
                <th>Referrer</th>
                <th>IP</th>
                <th>First Seen</th>
                <th>Last Activity</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  function initVisitorTable() {
    const table = $('#visitors-table').DataTable({
        ajax: {
            url: '/dashboard/monitoring/active-visitors/data',
            dataSrc: 'data',
            error: function(xhr) {
                $('#error-message').text(xhr.responseJSON.message).removeClass('d-none');
            }
        },
        columns: [
            { data: 'ip' },
            { data: 'page' },
            { data: 'last_active' },
            { data: 'device' }
        ],
        autoUpdate: {
            interval: 5000
        }
    });

    setInterval(() => table.ajax.reload(null, false), 5000);
}
function updateVisitorsTable() {
    fetch('/dashboard/monitoring/active-visitors/data')
        .then(response => response.json())
        .then(data => {
            document.getElementById('visitors-count').textContent = data.count;

            const tbody = document.querySelector('#visitors-table tbody');
            tbody.innerHTML = '';

            data.visitors.forEach((visitor, index) => {
                const row = document.createElement('tr');
                const firstSeen = new Date(visitor.first_seen);
                const lastActivity = new Date(visitor.last_activity);

                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${visitor.url}</td>
                    <td>${visitor.referrer || '-'}</td>
                    <td>${visitor.ip}</td>
                    <td>${firstSeen.toLocaleTimeString()}</td>
                    <td>${lastActivity.toLocaleTimeString()}</td>
                `;

                tbody.appendChild(row);
            });
        });
}

// Update data every 5 seconds
setInterval(updateVisitorsTable, 5000);

// Update data on page load
document.addEventListener('DOMContentLoaded', updateVisitorsTable);
</script>
@endsection
