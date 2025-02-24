'use strict';

document.addEventListener('DOMContentLoaded', function () {
  // تهيئة الخريطة
  const initMap = () => {
    const mapElement = document.getElementById('visitor-map');
    if (!mapElement) return;

    const map = L.map('visitor-map', {
      center: [0, 0],
      zoom: 2,
      layers: [
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors'
        })
      ]
    });
    window.visitorMap = map;
  };

  // تهيئة الرسم البياني
  const initChart = () => {
    const chartElement = document.getElementById('visitor-chart');
    if (!chartElement) return;

    const visitorChart = new ApexCharts(chartElement, {
      chart: {
        type: 'line',
        height: 300,
        toolbar: { show: false },
        zoom: { enabled: false }
      },
      series: [{ name: 'Visitors', data: [] }],
      xaxis: { type: 'datetime' },
      stroke: { curve: 'smooth', width: 2 },
      colors: ['#696cff']
    });
    visitorChart.render();
    window.visitorChart = visitorChart;
  };

  // تحديث جدول المستخدمين النشطين
  const updateActiveUsersTable = users => {
    const tableBody = document.querySelector('#active-users-table tbody');
    if (!tableBody) return;

    tableBody.innerHTML = users
      .map(
        user => `
            <tr>
                <td>${user.user_id || 'Guest'}</td>
                <td>${user.ip_address || 'Unknown'}</td>
                <td>${new Date(user.last_activity).toLocaleString()}</td>
                <td>${user.url || '-'}</td>
                <td>${user.browser || '-'}</td>
                <td>${user.os || '-'}</td>
            </tr>
        `
      )
      .join('');
  };

  // تحديث إحصائيات الطلبات
  const updateRequestStats = stats => {
    const totalElement = document.getElementById('total-requests');
    const onlineElement = document.getElementById('online-requests');
    const offlineElement = document.getElementById('offline-requests');

    if (totalElement) totalElement.textContent = stats.total;
    if (onlineElement) onlineElement.textContent = stats.online;
    if (offlineElement) offlineElement.textContent = stats.offline;
  };

  // تحديث الإحصائيات العامة
  const updateStats = async () => {
    try {
      const response = await fetch('/dashboard/monitoring/stats', {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          Accept: 'application/json'
        }
      });
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const data = await response.json();

      console.log('Received stats:', data); // للتحقق من البيانات

      // تحديث إحصائيات الطلبات
      if (data.data?.requestStats) {
        updateRequestStats(data.data.requestStats);
      }

      // تحديث جدول المستخدمين النشطين
      if (data.activeUsers) {
        updateActiveUsersTable(data.activeUsers);
      }

      // تحديث الرسم البياني
      if (window.visitorChart && data.visitorStats?.history) {
        window.visitorChart.updateSeries([
          {
            name: 'Visitors',
            data: data.visitorStats.history.map(item => ({
              x: item.timestamp,
              y: item.count
            }))
          }
        ]);
      }

      // تحديث وقت آخر تحديث
      const lastUpdatedSpan = document.getElementById('last-updated');
      if (lastUpdatedSpan) lastUpdatedSpan.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;

      // تحديث عدد المستخدمين النشطين
      const totalUsersBadge = document.getElementById('total-users');
      if (totalUsersBadge && Array.isArray(data.activeUsers)) totalUsersBadge.textContent = data.activeUsers.length;
    } catch (error) {
      console.error('Error updating stats:', error);
    }
  };

  // تهيئة الخريطة والرسم البياني
  initMap();
  initChart();

  // التحديث الأولي للإحصائيات
  updateStats();

  // تحديث الإحصائيات كل 5 ثواني
  setInterval(updateStats, 5000);

  // وظائف إدارة الأخطاء
  const errorLogTableBody = document.querySelector('#error-log-table tbody');
  const clearErrorsButton = document.getElementById('clear-errors');

  // وظيفة لجلب وتحديث الأخطاء
  const updateErrorLogs = async () => {
    try {
      const response = await fetch('/dashboard/monitoring/error-logs', {
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          Accept: 'application/json'
        }
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const result = await response.json();
      if (result.status === 'success' && result.data) {
        const errors = result.data.recent;
        errorLogTableBody.innerHTML = errors
          .map(
            error => `
                    <tr>
                        <td>${error.timestamp}</td>
                        <td>${error.type}</td>
                        <td>${error.message}</td>
                        <td>${error.file}</td>
                        <td>${error.line}</td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-error" data-error-id="${error.id}">
                              Delete
                            </button>
                        </td>
                    </tr>
                `
          )
          .join('');
      }
    } catch (error) {
      console.error('Error fetching error logs:', error);
    }
  };

  // وظيفة لحذف خطأ
  const deleteError = async errorId => {
    try {
      const response = await fetch('/dashboard/monitoring/delete-error', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          Accept: 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ errorId })
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const result = await response.json();
      if (result.status === 'success') {
        alert('Error deleted successfully');
        updateErrorLogs(); // تحديث الجدول بعد الحذف
      }
    } catch (error) {
      console.error('Error deleting error:', error);
    }
  };

  // تحديث الأخطاء كل 5 ثواني
  updateErrorLogs();
  setInterval(updateErrorLogs, 5000);

  // إضافة حدث لحذف خطأ
  if (errorLogTableBody) {
    errorLogTableBody.addEventListener('click', function (event) {
      if (event.target.classList.contains('delete-error')) {
        const errorId = event.target.getAttribute('data-error-id');
        if (confirm('Are you sure you want to delete this error?')) {
          deleteError(errorId);
        }
      }
    });
  }

  // إضافة حدث لمسح السجل بالكامل
  if (clearErrorsButton) {
    clearErrorsButton.addEventListener('click', function () {
      if (confirm('Are you sure you want to clear the entire error log?')) {
        fetch('/dashboard/monitoring/clear-error-logs', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            Accept: 'application/json'
          }
        })
          .then(response => {
            if (response.ok) {
              alert('Error log cleared successfully');
              updateErrorLogs(); // تحديث الجدول بعد المسح
            }
          })
          .catch(error => {
            console.error('Error clearing error log:', error);
          });
      }
    });
  }
});
