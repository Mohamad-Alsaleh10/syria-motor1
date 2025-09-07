<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Car; // لاستخدام نموذج السيارة
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // لاستخدام المعاملات (Transactions)
use Illuminate\Support\Facades\Storage; // لاستخدام تخزين الملفات

class AuctionController extends Controller
{
    /**
     * عرض جميع المزادات مع إمكانية البحث والتصفية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Auction::with('car.user', 'winner', 'bids'); // تحميل العلاقات الضرورية

        // تصفية حسب حالة المزاد
        if ($request->has('status') && in_array($request->status, ['pending', 'active', 'closed', 'cancelled'])) {
            $query->where('status', $request->status);
        } else {
            // افتراضياً، عرض المزادات النشطة فقط للمستخدمين العاديين
            $query->where('status', 'active');
        }

        // تصفية حسب نوع السيارة (make)
        if ($request->has('make')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('make', 'like', '%' . $request->make . '%');
            });
        }

        // تصفية حسب موديل السيارة (model)
        if ($request->has('model')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('model', 'like', '%' . $request->model . '%');
            });
        }

        // تصفية حسب الموقع (location)
        if ($request->has('location')) {
            $query->whereHas('car', function ($q) use ($request) {
                $q->where('location', 'like', '%' . $request->location . '%');
            });
        }

        // ترتيب حسب وقت الانتهاء (الأقرب أولاً)
        $query->orderBy('end_time', 'asc');

        $auctions = $query->paginate(10);

        return response()->json([
            'message' => 'تم جلب المزادات بنجاح.',
            'auctions' => $auctions,
        ]);
    }

    /**
     * عرض تفاصيل مزاد محدد.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $auction = Auction::with('car.user', 'winner', 'bids.user')->find($id);

        if (!$auction || $auction->status !== 'active') { // عرض المزادات النشطة فقط
            return response()->json(['message' => 'المزاد غير موجود أو غير نشط.'], 404);
        }

        return response()->json([
            'message' => 'تم جلب تفاصيل المزاد بنجاح.',
            'auction' => $auction,
        ]);
    }

    /**
     * إضافة سيارة للمزاد (إنشاء مزاد جديد).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // التحقق من أن المستخدم لديه نوع حساب يسمح له بإضافة مزادات (فردي، شركة)
        if (!in_array($user->accountType->name, ['individual', 'company'])) {
            return response()->json(['message' => 'ليس لديك الصلاحية لإضافة مزادات.'], 403);
        }

        // التحقق من وجود اشتراك نشط
        if (!$user->hasActiveSubscription()) {
            return response()->json(['message' => 'يجب أن يكون لديك اشتراك نشط لإضافة مزادات. يرجى الاشتراك أولاً.'], 403);
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
            'starting_price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1|max:720',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
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

            // إنشاء المزاد
            $auction = Auction::create([
                'car_id' => $car->id,
                'user_id' => $user->id,
                'starting_price' => $request->starting_price,
                'current_price' => $request->starting_price,
                'start_time' => now(),
                'end_time' => now()->addHours($request->duration_hours),
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم إضافة سيارة للمزاد بنجاح. سيتم مراجعة المزاد قبل النشر.',
                'auction' => $auction->load('car'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ أثناء إنشاء المزاد.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * المزايدة على مزاد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBid(Request $request, $id)
    {
        $user = $request->user();
        $auction = Auction::find($id);

        if (!$auction) {
            return response()->json(['message' => 'المزاد غير موجود.'], 404);
        }

        if ($auction->status !== 'active' || now()->isAfter($auction->end_time)) {
            return response()->json(['message' => 'المزاد غير نشط أو انتهى.'], 400);
        }

        // لا يمكن للبائع المزايدة على مزاده الخاص
        if ($auction->user_id === $user->id) {
            return response()->json(['message' => 'لا يمكنك المزايدة على مزادك الخاص.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:' . ($auction->current_price + 1), // يجب أن تكون المزايدة أعلى من السعر الحالي
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // التحقق من رصيد المستخدم في المحفظة (إذا كان سيتم خصم العربون مباشرة)
        // حالياً، لن نخصم أي شيء، ولكن يمكن إضافة هذا المنطق لاحقاً.
        // if ($user->wallet->balance < $request->amount) {
        //     return response()->json(['message' => 'رصيدك غير كافٍ للمزايدة بهذا المبلغ.'], 400);
        // }

        DB::beginTransaction(); // بدء معاملة قاعدة البيانات

        try {
            // تحديث السعر الحالي للمزاد
            $auction->current_price = $request->amount;
            $auction->save();

            // تسجيل المزايدة
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
            ]);

            DB::commit(); // تأكيد المعاملة

            return response()->json([
                'message' => 'تمت المزايدة بنجاح.',
                'bid' => $bid,
                'auction_current_price' => $auction->current_price,
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // التراجع عن المعاملة
            return response()->json(['message' => 'حدث خطأ أثناء المزايدة.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث مزاد (للمدير أو البائع).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $auction = Auction::find($id);

        if (!$auction) {
            return response()->json(['message' => 'المزاد غير موجود.'], 404);
        }

        // السماح للمدير أو البائع بتعديل المزاد
        if ($user->accountType->name !== 'admin' && $auction->user_id !== $user->id) {
            return response()->json(['message' => 'ليس لديك صلاحية لتعديل هذا المزاد.'], 403);
        }

        // منع التعديل إذا كان المزاد نشطاً أو مغلقاً (باستثناء المدير لتغيير الحالة)
        if ($auction->status === 'active' || $auction->status === 'closed') {
            if ($user->accountType->name !== 'admin' || !$request->has('status')) {
                return response()->json(['message' => 'لا يمكن تعديل المزاد بعد أن أصبح نشطاً أو مغلقاً.'], 403);
            }
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
            'starting_price' => 'sometimes|numeric|min:0',
            'duration_hours' => 'sometimes|integer|min:1|max:720',
            'status' => 'sometimes|string|in:pending,active,closed,cancelled', // يمكن للمدير تغيير الحالة
            'winner_id' => 'nullable|exists:users,id', // يمكن للمدير تعيين الفائز يدوياً
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            // تحديث بيانات السيارة
            $car = $auction->car;
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

            // تحديث بيانات المزاد
            if ($request->has('starting_price')) $auction->starting_price = $request->starting_price;
            if ($request->has('duration_hours')) {
                $auction->end_time = now()->addHours($request->duration_hours);
            }
            if ($request->has('status')) {
                // منطق خاص لتغيير الحالة (خاصة للمدير)
                if ($request->status === 'active' && $auction->status === 'pending') {
                    $auction->published_at = now(); // إذا تم تفعيل المزاد
                }
                $auction->status = $request->status;
            }
            if ($request->has('winner_id')) $auction->winner_id = $request->winner_id;
            $auction->save();

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث المزاد بنجاح.',
                'auction' => $auction->load('car'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ أثناء تحديث المزاد.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * حذف مزاد.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $auction = Auction::find($id);

        if (!$auction) {
            return response()->json(['message' => 'المزاد غير موجود.'], 404);
        }

        // السماح للمدير أو البائع بحذف المزاد إذا لم يكن نشطاً
        if ($user->accountType->name !== 'admin' && $auction->user_id !== $user->id) {
            return response()->json(['message' => 'ليس لديك صلاحية لحذف هذا المزاد.'], 403);
        }

        if ($auction->status === 'active') {
            return response()->json(['message' => 'لا يمكن حذف مزاد نشط. يرجى إلغاؤه أولاً.'], 400);
        }

        DB::beginTransaction();

        try {
            // حذف الصور المرتبطة بالسيارة قبل حذف السيارة والمزاد
            $car = $auction->car;
            if ($car && $car->images) {
                foreach ($car->images as $imagePath) {
                    Storage::delete(str_replace('/storage/', 'public/', $imagePath));
                }
            }

            $auction->delete();
            $car->delete(); // سيتم حذف السيارة أيضاً بسبب علاقة cascade في الهجرة

            DB::commit();

            return response()->json(['message' => 'تم حذف المزاد بنجاح.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ أثناء حذف المزاد.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * وظيفة مساعدة لإنهاء المزادات المنتهية وتحديد الفائز.
     * يمكن استدعاؤها عبر Cron Job أو Event Listener.
     *
     * @return void
     */
    public function closeExpiredAuctions()
    {
        $expiredAuctions = Auction::where('status', 'active')
            ->where('end_time', '<=', now())
            ->get();

        foreach ($expiredAuctions as $auction) {
            DB::beginTransaction();
            try {
                $highestBid = $auction->bids()->orderBy('amount', 'desc')->first();

                if ($highestBid) {
                    $auction->winner_id = $highestBid->user_id;
                    $auction->status = 'closed';
                    // هنا يمكن إضافة منطق لخصم العربون من الفائز أو إرسال إشعار بالدفع
                    // وإشعار للبائع بانتهاء المزاد والفائز
                } else {
                    // لا توجد مزايدات، يمكن إلغاء المزاد أو إعادته
                    $auction->status = 'cancelled';
                }
                $auction->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // تسجيل الخطأ
                \Log::error("Failed to close auction ID {$auction->id}: " . $e->getMessage());
            }
        }
    }
}
