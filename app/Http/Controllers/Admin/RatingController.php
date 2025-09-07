<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\User; // تأكد من استيراد نموذج المستخدم
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    /**
     * Display a listing of the ratings.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // جلب التقييمات مع المستخدم الذي قام بالتقييم والكيان الذي تم تقييمه
        // تم تغيير 'rater' إلى 'user' ليتوافق مع العلاقة في نموذج Rating
        $ratings = Rating::with(['user', 'rateable'])->paginate(10);
        return view('admin.ratings.index', compact('ratings'));
    }

    /**
     * Display the specified rating.
     *
     * @param  \App\Models\Rating  $rating
     * @return \Illuminate\View\View
     */
    public function show(Rating $rating)
    {
        // تحميل العلاقات للعرض التفصيلي
        // تم تغيير 'rater' إلى 'user' ليتوافق مع العلاقة في نموذج Rating
        $rating->load('user', 'rateable'); // تحميل المستخدم الذي قام بالتقييم والكيان الذي تم تقييمه
        return view('admin.ratings.show', compact('rating'));
    }

    /**
     * Show the form for editing the specified rating.
     *
     * @param  \App\Models\Rating  $rating
     * @return \Illuminate\View\View
     */
    public function edit(Rating $rating)
    {
        // تعريف الحالات الممكنة للتقييم (مثل: معلق، موافق عليه، مرفوض)
        // ملاحظة: إذا لم يكن لديك عمود 'status' في جدول التقييمات، يجب إضافة هذا العمود إلى الهجرة
        $statuses = ['pending', 'approved', 'rejected'];
        return view('admin.ratings.edit', compact('rating', 'statuses'));
    }

    /**
     * Update the specified rating in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rating  $rating
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Rating $rating)
    {
        $request->validate([
            'stars' => 'required|integer|min:1|max:5', // تم تغيير 'rating' إلى 'stars' ليتوافق مع الهجرة
            'comment' => 'nullable|string',
            // 'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])], // إذا كان عمود 'status' غير موجود، يجب إزالته من هنا أو إضافته للهجرة
        ]);

        $rating->update([
            'stars' => $request->stars, // تم تغيير 'rating' إلى 'stars'
            'comment' => $request->comment,
            // 'status' => $request->status, // إذا كان عمود 'status' غير موجود، يجب إزالته من هنا
        ]);

        return redirect()->route('admin.ratings.index')->with('success', 'تم تحديث التقييم بنجاح.');
    }

    /**
     * Remove the specified rating from storage.
     *
     * @param  \App\Models\Rating  $rating
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Rating $rating)
    {
        $rating->delete();
        return redirect()->route('admin.ratings.index')->with('success', 'تم حذف التقييم بنجاح.');
    }

    // لا يتم عادةً إنشاء التقييمات مباشرة من قبل المديرين، لذا لم يتم تضمين طرق create/store.
}
