<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Otp;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile.index');
    }

    public function update(Request $request)
    {
        if($request->name == 'name'){
            try {
                $this->validate($request, [
                    'value' => 'required'
                ]);
            }catch (\Exception $e){
                return response()->json([
                    'message' => $e->getMessage()
                ], 422);
            }

            auth()->user()->update([
                'name' => $request->value
            ]);
        }elseif($request->name == 'identity_card'){
            try {
                $this->validate($request, [
                    'value' => 'required|numeric|unique:admins,identity_card,'.auth()->user()->id.',id,deleted_at,NULL'
                ]);
            }catch (\Exception $e){
                return response()->json([
                    'message' => $e->getMessage()
                ], 422);
            }

            auth()->user()->update([
                'identity_card' => $request->value
            ]);
        }elseif($request->name == 'phone'){
            try {
                $this->validate($request, [
                    'value' => 'nullable|numeric'
                ]);
            }catch (\Exception $e){
                return response()->json([
                    'message' => $e->getMessage()
                ], 422);
            }

            auth()->user()->update([
                'phone' => $request->value
            ]);
        }elseif($request->name == 'email'){
            try {
                $this->validate($request, [
                    'value' => 'required|email|unique:admins,email,'.auth()->user()->id.',id,deleted_at,NULL'
                ]);
            }catch (\Exception $e){
                return response()->json([
                    'message' => $e->getMessage()
                ], 422);
            }

            auth()->user()->update([
                'email' => $request->value,
                'email_verified_at' => null
            ]);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => [
                'required', 'string', 'confirmed',
                Password::min(8)->letters()->numbers()->mixedCase()->symbols()
            ]
        ]);

        if(!Hash::check($request->old_password, auth()->user()->password)){
            $request->session()->flash('error', trans('Old password does not match with current password'));

            return redirect()->route('admin.profile.index');
        }

        if(Hash::check($request->new_password, auth()->user()->password)){
            $request->session()->flash('error', trans('New password cannot same as current password'));

            return redirect()->route('admin.profile.index');
        }

        auth()->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        $request->session()->flash('success', trans('Password updated'));

        return redirect()->route('admin.profile.index');
    }

    public function sendVerifyEmail()
    {
        if(auth()->user()->email_verified_at){
            return response()->json([
                'message' => 'Your email is already verified.'
            ], 422);
        }

        $otp = Otp::setSensitive(true)->setLength(8)->setFormat('string')->generate('AdminEmailVerificationOTP'.auth()->id());

        $to_name = auth()->user()->name;
        $to_email = auth()->user()->email;
        $data = array('name'=>$to_name, 'otp' => $otp);

        Mail::to($to_email)->send(new EmailVerificationOtp($data));

        return response()->json([
            'success' => true
        ]);
    }

    public function verifyEmail(Request $request)
    {
        if(count($request->otps) < 8){
            $request->session()->flash('error', 'Please fill in otp');

            return redirect()->route('admin.profile.index');
        }

        $otp = implode('', $request->otps);

        $validate = Otp::setSensitive(true)->validate('AdminEmailVerificationOTP'.auth()->id(), $otp);

        if(!$validate->status){
            $request->session()->flash('error', trans('messages.verify.'.$validate->error));

            return redirect()->route('admin.profile.index');
        }

        auth()->user()->update([
            'email_verified_at' => now()
        ]);

        $request->session()->flash('success', trans('messages.verify.success'));

        return redirect()->route('admin.profile.index');
    }
}
