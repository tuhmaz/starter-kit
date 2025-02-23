<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ErrorLogService
{
   public function getRecentErrors()
{
    try {
        // قراءة الأخطاء الفعلية من ملف السجل
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            return [
                'count' => 0,
                'trend' => 0,
                'recent' => []
            ];
        }

        // قراءة آخر 1000 سطر من ملف السجل
        $logs = array_slice(file($logFile), -1000);
        $errors = [];
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(ERROR|WARNING|NOTICE|DEPRECATED).*?: (.*?) in (.*?):(\d+)/i';

        foreach ($logs as $log) {
            if (preg_match($pattern, $log, $matches)) {
                $timestamp = $matches[1];
                // تجاهل الأخطاء القديمة (أكثر من 24 ساعة)
                if (strtotime($timestamp) < strtotime('-24 hours')) {
                    continue;
                }

                $errors[] = [
                    'id' => md5($matches[1] . $matches[3] . $matches[4] . $matches[5]),
                    'timestamp' => $timestamp,
                    'type' => ucfirst(strtolower($matches[2])),
                    'message' => $matches[3],
                    'file' => $matches[4],
                    'line' => $matches[5]
                ];
            }
        }

        // ترتيب الأخطاء حسب الوقت (الأحدث أولاً)
        usort($errors, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // حساب الإحصائيات
        $todayErrors = array_filter($errors, function($error) {
            return strtotime($error['timestamp']) >= strtotime('today');
        });

        $yesterdayErrors = array_filter($errors, function($error) {
            return strtotime($error['timestamp']) >= strtotime('yesterday') &&
                   strtotime($error['timestamp']) < strtotime('today');
        });

        $todayCount = count($todayErrors);
        $yesterdayCount = count($yesterdayErrors);

        $trend = $yesterdayCount > 0 ? 
                round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100) : 
                0;

        return [
            'count' => $todayCount,
            'trend' => $trend,
            'recent' => array_slice($errors, 0, 10) // عرض آخر 10 أخطاء
        ];

    } catch (\Exception $e) {
        Log::error('Error reading error logs: ' . $e->getMessage());
        return [
            'count' => 0,
            'trend' => 0,
            'recent' => []
        ];
    }
}

  public function deleteError($errorId)
{
    try {
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            return false;
        }

        $logs = file($logFile);
        $newLogs = [];
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(ERROR|WARNING|NOTICE|DEPRECATED).*?: (.*?) in (.*?):(\d+)/i';

        foreach ($logs as $log) {
            if (preg_match($pattern, $log, $matches)) {
                $currentId = md5($matches[1] . $matches[3] . $matches[4] . $matches[5]);
                if ($currentId !== $errorId) {
                    $newLogs[] = $log;
                }
            } else {
                $newLogs[] = $log;
            }
        }

        File::put($logFile, implode('', $newLogs));
        return true;

    } catch (\Exception $e) {
        Log::error('Error deleting error log: ' . $e->getMessage());
        return false;
    }
}
}
