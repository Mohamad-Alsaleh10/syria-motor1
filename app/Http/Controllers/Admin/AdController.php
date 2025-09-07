<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\Car; // تأكد من استيراد نموذج السيارة
use App\Models\User; // تأكد من استيراد نموذج المستخدم
use Illuminate\Validation\Rule;

class AdController extends Controller
{
    /**
     * عرض قائمة بجميع إعلانات البيع.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // جلب الإعلانات مع السيارة المرتبطة بها والمستخدم (البائع)
        // استخدام with() لتجنب مشكلة N+1 query
        $ads = Ad::with(['car', 'user'])->paginate(10);
        return view('admin.ads.index', compact('ads'));
    }

    /**
     * عرض تفاصيل إعلان بيع محدد.
     *
     * @param  \App\Models\Ad  $ad
     * @return \Illuminate\View\View
     */
    public function show(Ad $ad)
    {
        // تحميل العلاقات اللازمة للعرض التفصيلي
        $ad->load('car.user', 'user'); // تحميل السيارة ومالكها، وبائع الإعلان
        return view('admin.ads.show', compact('ad'));
    }

    /**
     * عرض نموذج تعديل إعلان بيع محدد.
     *
     * @param  \App\Models\Ad  $ad
     * @return \Illuminate\View\View
     */
    public function edit(Ad $ad)
    {
        // تعريف الحالات المحتملة للإعلان
        $statuses = ['pending', 'active', 'sold', 'rejected'];
        return view('admin.ads.edit', compact('ad', 'statuses'));
    }

    /**
     * تحديث إعلان بيع محدد في قاعدة البيانات.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ad  $ad
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Ad $ad)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'sold', 'rejected'])], // التحقق من حالة الإعلان
            'price' => 'required|numeric|min:0', // السعر يجب أن يكون رقمياً وغير سالب
            'published_at' => 'nullable|date', // تاريخ النشر (يمكن أن يكون فارغاً)
            'expires_at' => 'nullable|date|after_or_equal:published_at', // تاريخ الانتهاء (يجب أن يكون بعد أو يساوي تاريخ النشر)
        ]);

        $ad->update([
            'status' => $request->status,
            'price' => $request->price,
            'published_at' => $request->published_at,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.ads.index')->with('success', 'تم تحديث الإعلان بنجاح.');
    }

    /**
     * حذف إعلان بيع محدد من قاعدة البيانات.
     *
     * @param  \App\Models\Ad  $ad
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ad $ad)
    {
        $ad->delete();
        return redirect()->route('admin.ads.index')->with('success', 'تم حذف الإعلان بنجاح.');
    }

    // يمكنك إضافة طرق create/store هنا إذا كان المدراء يمكنهم إنشاء الإعلانات مباشرة.
    // حالياً، نفترض أن الإعلانات يتم إنشاؤها بواسطة المستخدمين وأن المدراء يقومون بإدارتها فقط.
}
