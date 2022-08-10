<?php

namespace App\Models;

use App\Http\Resources\Admin\InvoiceResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['full_address'];

    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    public function students()
    {
        return $this->hasMany(\App\Models\Student::class);
    }

    public function branch_students()
    {
        return $this->belongsToMany(Student::class, 'branch_student')->withTimestamps();
    }

    public function subjects()
    {
        return $this->hasMany(\App\Models\Subject::class);
    }

    public function branch_subject()
    {
        return $this->belongsToMany(Subject::class, 'branch_subject')->withPivot('is_individual', 'tuition_fee', 'material_fee', 'misc_fee')->withTimestamps();
    }

    public function teachers()
    {
        return $this->hasMany(\App\Models\Teacher::class);
    }

    public function staffs()
    {
        return $this->hasMany(Staff::class);
    }

    public function guardians()
    {
        return $this->hasMany(\App\Models\Guardian::class);
    }

    public function courses()
    {
        return $this->hasMany(\App\Models\Course::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function banners()
    {
        return $this->hasMany(\App\Models\Banner::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function holidays()
    {
        return $this->hasMany(\App\Models\Holiday::class);
    }

    public function fee_settings()
    {
        return $this->hasMany(FeeSetting::class);
    }

    public function payment_terms()
    {
        return $this->hasMany(\App\Models\PaymentTerm::class);
    }

    public function getFullAddressAttribute()
    {
        $full_address = $this->address1;

        if($this->address2) {
            $full_address .= ', ' . $this->address2;
        }

        $full_address .= ', '.$this->postal_code.' '.$this->city->name.', '.$this->city->state->name;

        return $full_address;
    }

    public function scopeLocalSearch($query)
    {
        $query->when(request()->has('name') && filled(request('name')), function ($q) {
            $q->where('name', 'LIKE', '%' . request('name') . '%');
        });
        $query->when(request()->has('phone') && filled(request('phone')), function ($q) {
            $q->where('phone', 'LIKE', '%' . request('phone') . '%');
        });
        $query->when(request()->has('email') && filled(request('email')), function ($q) {
            $q->where('email', 'LIKE', '%' . request('email') . '%');
        });
        $query->when(request()->has('status') && filled(request('status')), function ($q) {
            $q->where('status', request('status'));
        });
        return $query;
    }

    public function generateBranchSubscriptionFee()
    {
        $invoice = $this->invoices()->create([
            'title' => 'Branch Subscription Fee for '.now()->format('m/Y'),
            'remark' => '',
            'amount' => $this->package->price,
            'amount_paid' => 0,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'is_paid' => 0
        ]);

        $invoice->update(['invoice_number' => 'INV'.now()->format('YmdHis').$invoice->id]);

        return $invoice;
    }

    public function generateInvoice()
    {
        $this->students()->each(function ($student) {
            $student_course_count = 0;
            foreach($student->course_student as $course){
                if($course->pivot->status == 1 && !$course->is_short_course && ($course->subject->branch_subject()->where('id',$course->branch_id)->exists() && !$course->subject->branch_subject()->where('id',$course->branch_id)->first()->pivot->is_individual)){
                    $student_course_count++;
                }
            }
            $invoice_items = [];
            foreach($student->fee_setting_student as $fee_setting){
                if(!$fee_setting->pivot->next_generate_date && $student->branch->invoice_generate_date == now()->format('d') || $fee_setting->pivot->next_generate_date == now()->format('Y-m-d')) {
                    if($fee_setting->fee_type_id == 1) {
                        $invoice_items[$student->id][$fee_setting->fee_type_id] = [
                            'price' => $fee_setting->price[$student_course_count - 1],
                            'payment_term' => $fee_setting->pivot->payment_term_id,
                            'fee_setting' => $fee_setting->id,
                            'frequency' => $fee_setting->pivot->frequency,
                        ];

                        $material_fee_setting = FeeSetting::where('branch_id', $student->branch_id)->where('fee_type_id', 7)->where('parent_id', $fee_setting->id)->first();
                        $misc_fee_setting = FeeSetting::where('branch_id', $student->branch_id)->where('fee_type_id', 8)->where('parent_id', $fee_setting->id)->first();

                        $invoice_items[$student->id][$material_fee_setting->fee_type_id] = [
                            'price' => $material_fee_setting->price[$student_course_count - 1],
                            'payment_term' => $fee_setting->pivot->payment_term_id,
                            'fee_setting' => $fee_setting->id,
                            'frequency' => $fee_setting->pivot->frequency,
                        ];

                        $invoice_items[$student->id][$misc_fee_setting->fee_type_id] = [
                            'price' => $misc_fee_setting->price[$student_course_count - 1],
                            'payment_term' => $fee_setting->pivot->payment_term_id,
                            'fee_setting' => $fee_setting->id,
                            'frequency' => $fee_setting->pivot->frequency,
                        ];
                    } else {
                        if($fee_setting->pivot->frequency > 0){
                            $invoice_items[$student->id][$fee_setting->fee_type_id] = [
                                'price' => $fee_setting->price[0],
                                'payment_term' => $fee_setting->pivot->payment_term_id,
                                'fee_setting' => $fee_setting->id,
                                'frequency' => $fee_setting->pivot->frequency,
                            ];
                        }
                    }
                }
            }

            if(count($invoice_items) > 0) {
                $payment_term = PaymentTerm::find($student->fee_setting_student[0]->pivot->payment_term_id);

                $invoice = $student->invoices()->create([
                    'invoiceable_type' => Guardian::class,
                    'invoiceable_id' => $student->guardian_id,
                    'title' => $payment_term->term == 1 ? 'Invoice for ' . $student->name . ' for ' . now()->format('m/Y') : 'Invoice for ' . $student->name . ' for ' . now()->format('m/Y').' to '.now()->addMonthsNoOverflow($payment_term->term-1)->format('m/Y'),
                    'amount' => 0.00
                ]);

                foreach ($invoice_items as $student_id => $items) {
                    foreach ($items as $fee_type_id => $item) {
                        $fee_type = FeeType::find($fee_type_id);
                        $payment_term = PaymentTerm::find($item['payment_term']);

                        $invoice->items()->create([
                            'fee_type_id' => $fee_type_id,
                            'name' => $fee_type->name,
                            'price' => $fee_type_id != 5 ? $item['price'] * $payment_term->term * (1-($payment_term->discount/100)) : $item['price'] * $payment_term->term,
                        ]);

                        if($item['frequency'] != null) {
                            $student->fee_setting_student()->updateExistingPivot($item['fee_setting'], [
                                'next_generate_date' => now()->addMonthsNoOverflow($payment_term->term),
                                'frequency' => $item['frequency']-1,
                            ]);
                        }else{
                            $student->fee_setting_student()->updateExistingPivot($item['fee_setting'], [
                                'next_generate_date' => now()->addMonthsNoOverflow($payment_term->term)
                            ]);
                        }
                    }
                }

                foreach($student->course_student as $course){
                    if($course->subject->branch_subject()->where('id',$course->branch_id)->exists() && $course->subject->branch_subject()->where('id',$course->branch_id)->first()->pivot->is_individual){
                        $invoice->items()->create([
                            'fee_type_id' => 1,
                            'name' => 'Tuition Fee for '.$course->subject->name,
                            'price' => ($course->subject->branch_subject()->where('id',$course->branch_id)->first()->pivot->tuition_fee + $course->subject->branch_subject()->where('id',$course->branch_id)->first()->pivot->material_fee + $course->subject->branch_subject()->where('id',$course->branch_id)->first()->pivot->misc_fee) * $payment_term->term,
                        ]);
                    }
                }

                $discount = $invoice->items()->where('fee_type_id',5)->first();

                $invoice->update([
                    'invoice_number' => 'INV'.now()->format('YmdHis').$invoice->id,
                    'amount' => $discount ? $invoice->items()->where('fee_type_id','!=',5)->sum('price') - $discount->price : $invoice->items()->where('fee_type_id','!=',5)->sum('price')
                ]);
            }
        });
    }
}
