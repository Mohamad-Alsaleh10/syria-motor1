<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Workshop;
use App\Models\Rating; // لاستخدام نموذج التقييم
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    /**
     * عرض جميع طلبات الخدمة للمستخدم المصادق عليه.
     * (أو جميع الطلبات للمدير)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ServiceRequest::with('user', 'workshop');

        // إذا كان المستخدم ليس مديراً، اعرض طلباته فقط أو طلبات الورشة التي يملكها
        if ($user->accountType->name !== 'admin') {
            if ($user->accountType->name === 'workshop' && $user->workshop) {
                $query->where('workshop_id', $user->workshop->id);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        // تصفية حسب الحالة
        if ($request->has('status') && in_array($request->status, ['pending', 'accepted', 'rejected', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        $serviceRequests = $query->paginate(10);

        return response()->json([
            'message' => 'تم جلب طلبات الخدمة بنجاح.',
            'service_requests' => $serviceRequests,
        ]);
    }

    /**
     * عرض تفاصيل طلب خدمة محدد.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::with('user', 'workshop')->find($id);

        if (!$serviceRequest) {
            return response()->json(['message' => 'طلب الخدمة غير موجود.'], 404);
        }

        // التحقق من الصلاحية: المستخدم صاحب الطلب، أو الورشة المعنية، أو المدير
        if ($serviceRequest->user_id !== $user->id &&
            !($user->accountType->name === 'workshop' && $user->workshop && $serviceRequest->workshop_id === $user->workshop->id) &&
            $user->accountType->name !== 'admin') {
            return response()->json(['message' => 'ليس لديك صلاحية لعرض طلب الخدمة هذا.'], 403);
        }

        return response()->json([
            'message' => 'تم جلب تفاصيل طلب الخدمة بنجاح.',
            'service_request' => $serviceRequest,
        ]);
    }

    /**
     * تقديم طلب خدمة جديد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // فقط المستخدمون الأفراد يمكنهم تقديم طلبات خدمة
        if ($user->accountType->name !== 'individual') {
            return response()->json(['message' => 'ليس لديك الصلاحية لتقديم طلبات خدمة.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'workshop_id' => 'nullable|exists:workshops,id', // يمكن للمستخدم اختيار ورشة أو تركها للنظام
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'workshop_id' => $request->workshop_id, // إذا تم تحديدها
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending', // افتراضياً معلق
        ]);

        // يمكن هنا إضافة منطق لترشيح الورشة الأقرب إذا لم يتم تحديد workshop_id
        // أو إرسال إشعار للورشة المختارة

        return response()->json([
            'message' => 'تم تقديم طلب الخدمة بنجاح. سيتم مراجعته.',
            'service_request' => $serviceRequest,
        ], 201);
    }

    /**
     * تحديث حالة طلب خدمة (للورشة أو المدير).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::find($id);

        if (!$serviceRequest) {
            return response()->json(['message' => 'طلب الخدمة غير موجود.'], 404);
        }

        // التحقق من الصلاحية: الورشة المعنية أو المدير
        if (!($user->accountType->name === 'workshop' && $user->workshop && $serviceRequest->workshop_id === $user->workshop->id) &&
            $user->accountType->name !== 'admin') {
            return response()->json(['message' => 'ليس لديك صلاحية لتحديث حالة طلب الخدمة هذا.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,accepted,rejected,completed,cancelled',
            'estimated_cost' => 'nullable|numeric|min:0', // يمكن للورشة إضافة تكلفة تقديرية
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // منطق خاص لتغيير الحالة
        if ($request->has('status')) {
            $serviceRequest->status = $request->status;
        }
        if ($request->has('estimated_cost')) {
            $serviceRequest->estimated_cost = $request->estimated_cost;
        }
        $serviceRequest->save();

        return response()->json([
            'message' => 'تم تحديث حالة طلب الخدمة بنجاح.',
            'service_request' => $serviceRequest,
        ]);
    }

    /**
     * تقييم ورشة بعد إتمام الخدمة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $serviceRequestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rateWorkshop(Request $request, $serviceRequestId)
    {
        $user = $request->user();
        $serviceRequest = ServiceRequest::where('id', $serviceRequestId)
                                        ->where('user_id', $user->id)
                                        ->where('status', 'completed') // يمكن التقييم فقط بعد إتمام الخدمة
                                        ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'طلب الخدمة غير موجود، أو لم يتم إكماله، أو ليس لديك صلاحية لتقييمه.'], 404);
        }

        if (!$serviceRequest->workshop_id) {
             return response()->json(['message' => 'لا يمكن تقييم ورشة غير محددة لهذا الطلب.'], 400);
        }

        // التحقق مما إذا كان المستخدم قد قام بالتقييم بالفعل
        if (Rating::where('user_id', $user->id)
                  ->where('rateable_id', $serviceRequest->workshop_id)
                  ->where('rateable_type', Workshop::class)
                  ->exists()) {
            return response()->json(['message' => 'لقد قمت بتقييم هذه الورشة لهذا الطلب بالفعل.'], 400);
        }


        $validator = Validator::make($request->all(), [
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rating = Rating::create([
            'user_id' => $user->id,
            'rateable_id' => $serviceRequest->workshop_id,
            'rateable_type' => Workshop::class,
            'stars' => $request->stars,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'تم تقييم الورشة بنجاح.',
            'rating' => $rating,
        ], 201);
    }
}
