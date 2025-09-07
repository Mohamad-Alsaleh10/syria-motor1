<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RentalAd;
use App\Models\Car; // لاستخدام نموذج السيارة
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // لاستخدام تخزين الملفات

class RentalAdController extends Controller
{
    /**
     * عرض جميع إعلانات التأجير مع إمكانية البحث والتصفية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = RentalAd::with('car.user'); // تحميل بيانات السيارة والمستخدم المرتبطين بالإعلان

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

        // تصفية حسب الموقع (location)
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // تصفية حسب السعر اليومي (min_daily_price, max_daily_price)
        if ($request->has('min_daily_price')) {
            $query->where('daily_price', '>=', $request->min_daily_price);
        }
        if ($request->has('max_daily_price')) {
            $query->where('daily_price', '<=', $request->max_daily_price);
        }

        // تصفية حسب السعر الشهري (min_monthly_price, max_monthly_price)
        if ($request->has('min_monthly_price')) {
            $query->where('monthly_price', '>=', $request->min_monthly_price);
        }
        if ($request->has('max_monthly_price')) {
            $query->where('monthly_price', '<=', $request->max_monthly_price);
        }

        // تصفية حسب الحالة (status) - للمدير أو لعرض الإعلانات النشطة فقط
        if ($request->has('status') && in_array($request->status, ['pending', 'active', 'rented', 'rejected'])) {
            $query->where('status', $request->status);
        } else {
            // افتراضياً، عرض الإعلانات النشطة فقط للمستخدمين العاديين
            $query->where('status', 'active');
        }

        $rentalAds = $query->paginate(10);

        return response()->json([
            'message' => 'تم جلب إعلانات التأجير بنجاح.',
            'rental_ads' => $rentalAds,
        ]);
    }

    /**
     * عرض تفاصيل إعلان تأجير محدد.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $rentalAd = RentalAd::with('car.user')->find($id);

        if (!$rentalAd || $rentalAd->status !== 'active') { // عرض الإعلانات النشطة فقط
            return response()->json(['message' => 'إعلان التأجير غير موجود أو غير نشط.'], 404);
        }

        return response()->json([
            'message' => 'تم جلب تفاصيل إعلان التأجير بنجاح.',
            'rental_ad' => $rentalAd,
        ]);
    }

    /**
     * إضافة إعلان تأجير جديد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // التحقق من أن المستخدم لديه نوع حساب يسمح له بإضافة إعلانات تأجير (فردي، شركة)
        if (!in_array($user->accountType->name, ['individual', 'company'])) {
            return response()->json(['message' => 'ليس لديك الصلاحية لإضافة إعلانات تأجير.'], 403);
        }

        // التحقق من وجود اشتراك نشط
        if (!$user->hasActiveSubscription()) {
            return response()->json(['message' => 'يجب أن يكون لديك اشتراك نشط لإضافة إعلانات تأجير. يرجى الاشتراك أولاً.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'condition' => 'required|string|in:new,used',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'daily_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'rental_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // يجب أن يكون أحد السعرين على الأقل موجوداً
        if (is_null($request->daily_price) && is_null($request->monthly_price)) {
            return response()->json(['message' => 'يجب تحديد سعر يومي أو شهري على الأقل.'], 422);
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

        // إنشاء إعلان التأجير
        $rentalAd = RentalAd::create([
            'car_id' => $car->id,
            'user_id' => $user->id,
            'daily_price' => $request->daily_price,
            'monthly_price' => $request->monthly_price,
            'rental_conditions' => $request->rental_conditions,
            'location' => $request->location,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم إضافة إعلان التأجير بنجاح. سيتم مراجعته قبل النشر.',
            'rental_ad' => $rentalAd->load('car'),
        ], 201);
    }

    /**
     * تحديث إعلان تأجير موجود.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $rentalAd = RentalAd::where('id', $id)->where('user_id', $user->id)->first();

        if (!$rentalAd) {
            return response()->json(['message' => 'إعلان التأجير غير موجود أو ليس لديك صلاحية لتعديله.'], 404);
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
            'daily_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'rental_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // يجب أن يكون أحد السعرين على الأقل موجوداً إذا تم تحديثهما
        if ($request->hasAny(['daily_price', 'monthly_price']) && is_null($request->daily_price) && is_null($request->monthly_price)) {
            return response()->json(['message' => 'يجب تحديد سعر يومي أو شهري على الأقل.'], 422);
        }

        // تحديث بيانات السيارة
        $car = $rentalAd->car;
        if ($request->has('make')) $car->make = $request->make;
        if ($request->has('model')) $car->model = $request->model;
        if ($request->has('year')) $car->year = $request->year;
        if ($request->has('condition')) $car->condition = $request->condition;
        if ($request->has('description')) $car->description = $request->description;
        if ($request->has('location')) $car->location = $request->location;

        if ($request->hasFile('images')) {
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

        // تحديث بيانات إعلان التأجير
        if ($request->has('daily_price')) $rentalAd->daily_price = $request->daily_price;
        if ($request->has('monthly_price')) $rentalAd->monthly_price = $request->monthly_price;
        if ($request->has('rental_conditions')) $rentalAd->rental_conditions = $request->rental_conditions;
        if ($request->has('location')) $rentalAd->location = $request->location;
        $rentalAd->save();

        return response()->json([
            'message' => 'تم تحديث إعلان التأجير بنجاح.',
            'rental_ad' => $rentalAd->load('car'),
        ]);
    }

    /**
     * حذف إعلان تأجير.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $rentalAd = RentalAd::where('id', $id)->where('user_id', $user->id)->first();

        if (!$rentalAd) {
            return response()->json(['message' => 'إعلان التأجير غير موجود أو ليس لديك صلاحية لحذفه.'], 404);
        }

        // حذف الصور المرتبطة بالسيارة قبل حذف السيارة والإعلان
        $car = $rentalAd->car;
        if ($car && $car->images) {
            foreach ($car->images as $imagePath) {
                Storage::delete(str_replace('/storage/', 'public/', $imagePath));
            }
        }

        $rentalAd->delete();
        $car->delete(); // سيتم حذف السيارة أيضاً بسبب علاقة cascade في الهجرة

        return response()->json(['message' => 'تم حذف إعلان التأجير بنجاح.'], 200);
    }
}
