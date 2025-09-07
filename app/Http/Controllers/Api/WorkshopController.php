<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Models\ServiceRequest; // لاستخدام نموذج طلبات الخدمة
use App\Models\User; // لاستخدام نموذج المستخدم
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class WorkshopController extends Controller
{
    /**
     * عرض جميع الورش مع إمكانية البحث والتصفية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Workshop::with('user', 'ratings'); // تحميل المستخدم والتقييمات المرتبطة بالورشة

        // تصفية حسب الموقع
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // تصفية حسب الخدمات المقدمة
        if ($request->has('service')) {
            $query->whereJsonContains('services', $request->service);
        }

        // ترتيب حسب التقييم (يمكن تحسين هذا لاحقاً لحساب متوسط التقييم)
        // $query->withAvg('ratings', 'stars')->orderByDesc('ratings_avg_stars');

        $workshops = $query->paginate(10);

        return response()->json([
            'message' => 'تم جلب الورش بنجاح.',
            'workshops' => $workshops,
        ]);
    }

    /**
     * عرض تفاصيل ورشة محددة.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $workshop = Workshop::with('user', 'ratings.user')->find($id);

        if (!$workshop) {
            return response()->json(['message' => 'الورشة غير موجودة.'], 404);
        }

        return response()->json([
            'message' => 'تم جلب تفاصيل الورشة بنجاح.',
            'workshop' => $workshop,
        ]);
    }

    /**
     * تسجيل ورشة جديدة (يجب أن يكون المستخدم من نوع 'workshop').
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // التحقق من أن المستخدم من نوع 'workshop'
        if ($user->accountType->name !== 'workshop') {
            return response()->json(['message' => 'ليس لديك الصلاحية لتسجيل ورشة.'], 403);
        }

        // التحقق مما إذا كانت هذه الورشة قد تم تسجيلها بالفعل من قبل هذا المستخدم
        if ($user->workshop) {
            return response()->json(['message' => 'لقد قمت بالفعل بتسجيل ورشة.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:workshops,name',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'services' => 'required|array', // مصفوفة من الخدمات
            'services.*' => 'string|max:255', // كل خدمة يجب أن تكون نصاً
            'images' => 'nullable|array|max:5', // يمكن تحميل ما يصل إلى 5 صور
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // كل صورة يجب أن تكون ملف صورة
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $workshop = Workshop::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'services' => $request->services,
        ]);

        // معالجة تحميل الصور
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/workshop_images'); // حفظ الصور في مجلد public/workshop_images
                $imagePaths[] = Storage::url($path); // الحصول على المسار العام للصور
            }
        }
        $workshop->images = $imagePaths;
        $workshop->save();

        return response()->json([
            'message' => 'تم تسجيل الورشة بنجاح.',
            'workshop' => $workshop,
        ], 201);
    }

    /**
     * تحديث معلومات ورشة موجودة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $workshop = Workshop::where('id', $id)->where('user_id', $user->id)->first();

        if (!$workshop) {
            return response()->json(['message' => 'الورشة غير موجودة أو ليس لديك صلاحية لتعديلها.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:workshops,name,' . $workshop->id,
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'services' => 'sometimes|required|array',
            'services.*' => 'string|max:255',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('name')) $workshop->name = $request->name;
        if ($request->has('description')) $workshop->description = $request->description;
        if ($request->has('location')) $workshop->location = $request->location;
        if ($request->has('services')) $workshop->services = $request->services;

        if ($request->hasFile('images')) {
            foreach ($workshop->images as $oldImage) {
                Storage::delete(str_replace('/storage/', 'public/', $oldImage));
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/workshop_images');
                $imagePaths[] = Storage::url($path);
            }
            $workshop->images = $imagePaths;
        }
        $workshop->save();

        return response()->json([
            'message' => 'تم تحديث معلومات الورشة بنجاح.',
            'workshop' => $workshop,
        ]);
    }

    /**
     * حذف ورشة.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $workshop = Workshop::where('id', $id)->where('user_id', $user->id)->first();

        if (!$workshop) {
            return response()->json(['message' => 'الورشة غير موجودة أو ليس لديك صلاحية لحذفها.'], 404);
        }

        // حذف الصور المرتبطة بالورشة
        if ($workshop->images) {
            foreach ($workshop->images as $imagePath) {
                Storage::delete(str_replace('/storage/', 'public/', $imagePath));
            }
        }

        $workshop->delete();

        return response()->json(['message' => 'تم حذف الورشة بنجاح.'], 200);
    }
}
