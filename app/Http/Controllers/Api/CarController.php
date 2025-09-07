<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // لاستخدام تخزين الملفات
use App\Models\User;
class CarController extends Controller
{
    /**
     * عرض جميع إعلانات البيع مع إمكانية البحث والتصفية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Ad::with('car.user'); // تحميل بيانات السيارة والمستخدم المرتبطين بالإعلان

        // تصفية حسب النوع (make)
        if ($request->has('make')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('make', 'like', '%' . $request->make . '%');
            });
        }

        // تصفية حسب الموديل (model)
        if ($request->has('model')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('model', 'like', '%' . $request->model . '%');
            });
        }

        // تصفية حسب السعر (min_price, max_price)
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // تصفية حسب الحالة (condition)
        if ($request->has('condition')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('condition', $request->condition);
            });
        }

        // تصفية حسب الموقع (location)
        if ($request->has('location')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('location', 'like', '%' . $request->location . '%');
            });
        }

        // تصفية حسب الوكيل أو التاجر (user_id) - إذا كان المستخدم شركة/معرض
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // تصفية حسب الحالة (status) - للمدير أو لعرض الإعلانات النشطة فقط
        if ($request->has('status') && in_array($request->status, ['pending', 'active', 'sold', 'rejected'])) {
            $query->where('status', $request->status);
        } else {
            // افتراضياً، عرض الإعلانات النشطة فقط للمستخدمين العاديين
            $query->where('status', 'active');
        }


        $ads = $query->paginate(10); // تقسيم النتائج إلى صفحات

        return response()->json([
            'message' => 'تم جلب إعلانات البيع بنجاح.',
            'ads' => $ads,
        ]);
    }

    /**
     * عرض تفاصيل إعلان بيع محدد.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $ad = Ad::with('car.user')->find($id);

        if (!$ad || $ad->status !== 'active') { // عرض الإعلانات النشطة فقط
            return response()->json(['message' => 'الإعلان غير موجود أو غير نشط.'], 404);
        }

        return response()->json([
            'message' => 'تم جلب تفاصيل الإعلان بنجاح.',
            'ad' => $ad,
        ]);
    }

    /**
     * إضافة إعلان بيع جديد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // التحقق من أن المستخدم لديه نوع حساب يسمح له بإضافة إعلانات (فردي، شركة)
        if (!in_array($user->accountType->name, ['individual', 'company'])) {
            return response()->json(['message' => 'ليس لديك الصلاحية لإضافة إعلانات بيع.'], 403);
        }

        // التحقق من وجود اشتراك نشط
        if (!$user->hasActiveSubscription()) {
            return response()->json(['message' => 'يجب أن يكون لديك اشتراك نشط لإضافة إعلانات. يرجى الاشتراك أولاً.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'condition' => 'required|string|in:new,used',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'images' => 'nullable|array|max:5', // يمكن تحميل ما يصل إلى 5 صور
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // كل صورة يجب أن تكون ملف صورة
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // حفظ السيارة أولاً
        $car = Car::create([
            'user_id' => $user->id,
            'make' => $request->make,
            'model' => $request->model,
            'year' => $request->year,
            'condition' => $request->condition,
            'description' => $request->description,
            'location' => $request->location,
        ]);

        // معالجة تحميل الصور
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/cars_images');
                $imagePaths[] = Storage::url($path);
            }
        }
        $car->images = $imagePaths;
        $car->save();

        // إنشاء إعلان البيع
        $ad = Ad::create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'price' => $request->price,
            'status' => 'pending', // الإعلان يكون معلقاً للمراجعة
            'published_at' => null,
            'expires_at' => null,
        ]);

        return response()->json([
            'message' => 'تم إضافة إعلان البيع بنجاح. سيتم مراجعته قبل النشر.',
            'ad' => $ad->load('car'),
        ], 201);
    }

    /**
     * تحديث إعلان بيع موجود.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ad = Ad::where('id', $id)->where('user_id', $user->id)->first();

        if (!$ad) {
            return response()->json(['message' => 'الإعلان غير موجود أو ليس لديك صلاحية لتعديله.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'condition' => 'sometimes|required|string|in:new,used',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'sometimes|required|numeric|min:0',
            // لا تسمح للمستخدم بتغيير الحالة مباشرة
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // تحديث بيانات السيارة
        $car = $ad->car;
        if ($request->has('make')) $car->make = $request->make;
        if ($request->has('model')) $car->model = $request->model;
        if ($request->has('year')) $car->year = $request->year;
        if ($request->has('condition')) $car->condition = $request->condition;
        if ($request->has('description')) $car->description = $request->description;
        if ($request->has('location')) $car->location = $request->location;

        // معالجة الصور الجديدة (يمكنك إضافة منطق لحذف الصور القديمة هنا إذا لزم الأمر)
        if ($request->hasFile('images')) {
            // حذف الصور القديمة إذا أردت استبدالها بالكامل
            foreach ($car->images as $oldImage) {
                Storage::delete(str_replace('/storage/', 'public/', $oldImage));
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('public/cars_images');
                $imagePaths[] = Storage::url($path);
            }
            $car->images = $imagePaths;
        }
        $car->save();

        // تحديث بيانات الإعلان
        if ($request->has('price')) $ad->price = $request->price;
        $ad->save();

        return response()->json([
            'message' => 'تم تحديث إعلان البيع بنجاح.',
            'ad' => $ad->load('car'),
        ]);
    }

    /**
     * حذف إعلان بيع.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $ad = Ad::where('id', $id)->where('user_id', $user->id)->first();

        if (!$ad) {
            return response()->json(['message' => 'الإعلان غير موجود أو ليس لديك صلاحية لحذفه.'], 404);
        }

        // حذف الصور المرتبطة بالسيارة قبل حذف السيارة والإعلان
        $car = $ad->car;
        if ($car && $car->images) {
            foreach ($car->images as $imagePath) {
                Storage::delete(str_replace('/storage/', 'public/', $imagePath));
            }
        }

        $ad->delete();
        $car->delete(); // سيتم حذف السيارة أيضاً بسبب علاقة cascade في الهجرة

        return response()->json(['message' => 'تم حذف إعلان البيع بنجاح.'], 200);
    }
}
