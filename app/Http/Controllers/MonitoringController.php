<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ErrorLogService;
use App\Services\MonitoringService;
use App\Models\VisitorTracking;
use DeviceDetector\DeviceDetector;
use GeoIp2\Database\Reader;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

/**
 * Class MonitoringController
 * @package App\Http\Controllers
 */
class MonitoringController extends Controller
{
    protected $monitoringService;
    protected $errorLogService;

    public function __construct(MonitoringService $monitoringService, ErrorLogService $errorLogService)
    {
        $this->monitoringService = $monitoringService;
        $this->errorLogService = $errorLogService;
    }

    public function index()
    {
        return redirect()->route('dashboard.monitoring.monitorboard');
    }

    public function monitorboard()
    {
        try {
            $data = [
                'activeUsers' => $this->getActiveUsers(),
                'visitorStats' => $this->getVisitorStats(),
                'requestStats' => $this->getRequestStats(),
                'responseTimes' => $this->getResponseTimes() // استدعاء الدالة هنا
            ];

            return view('content.dashboard.monitoring.index', $data);
        } catch (\Exception $e) {
            Log::error('Error in monitorboard: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل لوحة المراقبة');
        }
    }

    public function getMonitoringData()
    {
        try {
            $requestStats = [
                'total' => VisitorTracking::count(),
                'online' => VisitorTracking::join('users', 'visitors_tracking.user_id', '=', 'users.id')
                    ->where('users.status', 'online')
                    ->distinct('visitors_tracking.user_id') // Add this line
                    ->count(),
                'offline' => VisitorTracking::join('users', 'visitors_tracking.user_id', '=', 'users.id')
                    ->where(function($query) {
                        $query->whereNull('users.status')
                              ->orWhere('users.status', '!=', 'online');
                    })
                    ->distinct('visitors_tracking.user_id') // Add this line
                    ->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'requestStats' => $requestStats
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getMonitoringData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getActiveUsers()
    {
        try {
            $fiveMinutesAgo = now()->subMinutes(5);

            return User::query()
                ->select([
                    'users.id as user_id',
                    'users.name as user_name',
                    'users.status',
                    'visitors_tracking.ip_address',
                    'visitors_tracking.url',
                    'visitors_tracking.country',
                    'visitors_tracking.city',
                    'visitors_tracking.browser',
                    'visitors_tracking.os',
                    'visitors_tracking.last_activity',
                    'visitors_tracking.user_agent'
                ])
                ->join('visitors_tracking', 'users.id', '=', 'visitors_tracking.user_id')
                ->where(function ($query) use ($fiveMinutesAgo) {
                    $query->where('users.status', '=', 'online')
                          ->where('visitors_tracking.last_activity', '>=', $fiveMinutesAgo);
                })
                ->orWhere(function ($query) use ($fiveMinutesAgo) {
                    $query->whereNull('users.status')
                          ->where('visitors_tracking.last_activity', '>=', $fiveMinutesAgo);
                })
                ->orderBy('visitors_tracking.last_activity', 'desc')
                ->get()
                ->map(function ($user) {
                    return [
                        'user_id' => $user->user_id,
                        'user_name' => $user->user_name,
                        'ip_address' => $user->ip_address,
                        'url' => $user->url,
                        'country' => $user->country ?? 'Unknown',
                        'city' => $user->city ?? 'Unknown',
                        'browser' => $user->browser,
                        'os' => $user->os,
                        'last_activity' => $user->last_activity,
                        'user_agent' => $user->user_agent,
                        'status' => $user->status ?? 'offline'
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting active users: ' . $e->getMessage());
            return collect([]);
        }
    }

    private function getVisitorStats()
    {
        try {
            // عدد الزوار الحاليين (آخر 5 دقائق)
            $current = DB::table('visitors_tracking')
                ->where('last_activity', '>=', now()->subMinutes(5))
                ->count();

            // الزوار في الساعة السابقة
            $previousHour = DB::table('visitors_tracking')
                ->whereBetween('last_activity', [now()->subHours(2), now()->subHour()])
                ->count();

            // الزوار في الساعة الحالية
            $currentHour = DB::table('visitors_tracking')
                ->where('last_activity', '>=', now()->subHour())
                ->count();

            // النسبة المئوية للتغير
            $change = $previousHour > 0
                ? (($currentHour - $previousHour) / $previousHour) * 100
                : 0;

            // بناء الـ history لآخر 24 ساعة (لكل ساعة)
            $history = [];
            for ($i = 23; $i >= 0; $i--) {
                $start = now()->subHours($i);
                $end   = $start->copy()->addHour();

                $count = DB::table('visitors_tracking')
                    ->whereBetween('last_activity', [$start, $end])
                    ->count();

                $history[] = [
                    'timestamp' => $start->timestamp * 1000, // بالـ milliseconds للرسوم
                    'count'     => $count
                ];
            }

            return [
                'current' => $current,
                'change'  => round($change, 1),
                'history' => $history
            ];
        } catch (\Exception $e) {
            Log::error('Error getting visitor stats: ' . $e->getMessage());
            return [
                'current' => 0,
                'change'  => 0,
                'history' => []
            ];
        }
    }
    private function getRequestStats()
    {
        try {
            // إحصائيات الطلبات
            $stats = DB::table('visitors_tracking')
                ->selectRaw('
                COUNT(*) as total_requests,
                COUNT(DISTINCT ip_address) as unique_visitors,
                COUNT(DISTINCT url) as unique_pages
            ')
                ->where('created_at', '>=', now()->subDay())
                ->first();

            return [
                'total_requests' => $stats->total_requests ?? 0,
                'unique_visitors' => $stats->unique_visitors ?? 0,
                'unique_pages' => $stats->unique_pages ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Error getting request stats: ' . $e->getMessage());
            return [
                'total_requests' => 0,
                'unique_visitors' => 0,
                'unique_pages' => 0
            ];
        }
    }
    private function getResponseTimes()
    {
        try {
            $stats = DB::table('visitors_tracking')
                ->selectRaw('
                AVG(response_time) as avg_response,
                MAX(response_time) as max_response,
                MIN(response_time) as min_response
            ')
                ->where('created_at', '>=', now()->subHour())
                ->first();


            return [
                'average' => round($stats->avg_response ?? 0, 2),
                'maximum' => round($stats->max_response ?? 0, 2),
                'minimum' => round($stats->min_response ?? 0, 2)
            ];
        } catch (\Exception $e) {
            Log::error('Error getting response times: ' . $e->getMessage());
            return [
                'average' => 0,
                'maximum' => 0,
                'minimum' => 0
            ];
        }
    }

    // وظيفة لجلب بيانات الأخطاء
    public function getErrorLogs()
    {
        try {
            $errors = $this->errorLogService->getRecentErrors();
            return response()->json([
                'status' => 'success',
                'data' => $errors
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching error logs: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // وظيفة لحذف خطأ معين
    public function deleteErrorLog(Request $request)
    {
        try {
            $errorId = $request->input('errorId');
            $success = $this->errorLogService->deleteError($errorId);
            return response()->json([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Error deleted successfully' : 'Failed to delete error'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting error log: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function clearErrorLogs()
    {
        $logFilePath = storage_path('logs/laravel.log');
        if (file_exists($logFilePath)) {
            file_put_contents($logFilePath, ''); // إفراغ محتوى الملف
            return true;
        }
        return false;
    }

    /**
     * عرض صفحة الزوار النشطين
     */
    public function activeVisitors()
    {
        return view('content.monitoring.active-visitors');
    }

    /**
     * جلب بيانات الزوار النشطين
     */
     

    /**
     * تسجيل زائر جديد
     */
    public function trackVisitor(Request $request)
{
    $visitorId = $request->session()->getId();
    $key = "visitor:{$visitorId}";

    Redis::hmset($key, [
        'url' => $request->input('url'),
        'referrer' => $request->input('referrer') ?? 'Direct',
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'last_activity' => now()->timestamp
    ]);

    Redis::expire($key, 300); // 5 دقائق
}

public function getActiveVisitorsData()
{
    $keys = Redis::keys('visitor:*');
    $visitors = collect($keys)->map(function($key) {
        return Redis::hgetall(str_replace(config('database.redis.options.prefix'), '', $key));
    })->filter();

    return response()->json([
        'count' => $visitors->count(),
        'visitors' => $visitors->values()
    ]);
}

    /**
     * تحديث نشاط الزائر
     */
    public function updateVisitorActivity(Request $request)
    {
        $visitorId = $request->session()->getId();
        $activeVisitors = Cache::get('active_visitors', []);

        if (isset($activeVisitors[$visitorId])) {
            $activeVisitors[$visitorId]['last_activity'] = now();
            $activeVisitors[$visitorId]['url'] = $request->input('url');

            Cache::put('active_visitors', $activeVisitors, now()->addHours(24));
        }

        return response()->json(['status' => 'success']);
    }
}
