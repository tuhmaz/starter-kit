<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use Illuminate\Http\Request;
use App\Models\User;

class ReactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
            'type' => 'required|string',
        ]);

        // الحصول على قاعدة البيانات من الجلسة
        $database = session('database', 'jo');

        // تحقق مما إذا كان المستخدم قد أضاف تفاعلًا بالفعل على هذا التعليق
        $existingReaction = Reaction::where('user_id', auth()->id())
                                    ->where('comment_id', $request->comment_id)
                                    ->first();

        if ($existingReaction) {
            // إذا كان التفاعل موجودًا بالفعل، حدث نوعه
            $existingReaction->update([
                'type' => $request->type,
                'database' => $database
            ]);
        } else {
            // إذا لم يكن التفاعل موجودًا، قم بإنشائه
            Reaction::create([
                'user_id' => auth()->id(),
                'comment_id' => $request->comment_id,
                'type' => $request->type,
                'database' => $database
            ]);
        }

        return redirect()->back()->with('success', 'تم إضافة التفاعل بنجاح!');
    }
}
