<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\OtpCode;
use App\Models\SystemSetting;
use App\Services\NotificationService;

class AccountSettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user()->load(['employee','roles']);
        return view('modules.account.settings', compact('user'));
    }

    public function servePhoto($filename)
    {
        // Try multiple paths to find the photo
        $paths = [
            storage_path('app/public/photos/' . $filename),
            storage_path('app/private/public/photos/' . $filename),
            public_path('storage/photos/' . $filename),
        ];
        
        $path = null;
        foreach ($paths as $testPath) {
            if (file_exists($testPath)) {
                $path = $testPath;
                break;
            }
        }
        
        if (!$path || !file_exists($path)) {
            \Log::error('Photo not found', [
                'filename' => $filename,
                'tried_paths' => $paths,
                'storage_public_exists' => file_exists(storage_path('app/public/photos')),
                'storage_public_photos_exists' => file_exists(storage_path('app/public/photos/' . $filename))
            ]);
            abort(404, 'Photo not found: ' . $filename);
        }
        
        // Security: Only allow image files
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            abort(403, 'Invalid file type');
        }
        
        $file = file_get_contents($path);
        $mimeType = mime_content_type($path);
        
        if (!$mimeType) {
            // Fallback MIME type based on extension
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
        }
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'public, max-age=31536000')
            ->header('Pragma', 'public');
    }

    public function updatePhoto(Request $request)
    {
        // Handle photo deletion
        if ($request->has('delete') && $request->delete == '1') {
            try {
                $user = Auth::user();
                
                // Delete old photo if exists
                if ($user->photo && Storage::exists('public/photos/' . $user->photo)) {
                    Storage::delete('public/photos/' . $user->photo);
                }
                
                $user->update(['photo' => null]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile photo removed successfully.',
                    'photo_url' => null
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error removing photo: ' . $e->getMessage()
                ], 500);
            }
        }

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Ensure storage directory exists - use absolute path
            $photosDir = storage_path('app/public/photos');
            if (!file_exists($photosDir)) {
                if (!mkdir($photosDir, 0755, true)) {
                    throw new \Exception('Failed to create photos directory');
                }
            }
            
            // Also ensure via Storage facade
            if (!Storage::exists('public/photos')) {
                Storage::makeDirectory('public/photos', 0755, true);
            }

            // Delete old photo if exists
            if ($user->photo && Storage::exists('public/photos/' . $user->photo)) {
                Storage::delete('public/photos/' . $user->photo);
            }

            // Store new photo with unique name
            $photoName = time() . '_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $photoPath = $request->file('photo')->storeAs('public/photos', $photoName);

            // Verify file was stored - check both ways
            $absolutePath = storage_path('app/public/photos/' . $photoName);
            if (!Storage::exists('public/photos/' . $photoName) && !file_exists($absolutePath)) {
                \Log::error('Photo upload failed', [
                    'user_id' => $user->id,
                    'photo_name' => $photoName,
                    'storage_exists' => Storage::exists('public/photos/' . $photoName),
                    'file_exists' => file_exists($absolutePath),
                    'photos_dir' => $photosDir,
                    'photos_dir_exists' => file_exists($photosDir)
                ]);
                throw new \Exception('File was not stored correctly. Path: ' . $absolutePath);
            }

            $user->update(['photo' => $photoName]);

            // Get the photo URL - Use route if symlink doesn't work
            $photoUrl = Storage::url('photos/' . $photoName);
            
            // Also provide route-based URL as primary (more reliable)
            $photoUrlRoute = route('storage.photos', ['filename' => $photoName]);
            
            // Fallback to asset URL
            $photoUrlAlt = asset('storage/photos/' . $photoName);

            // Verify file actually exists and get file size
            $fileSize = file_exists($absolutePath) ? filesize($absolutePath) : 0;
            
            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully.',
                'photo_url' => $photoUrlRoute, // Use route-based URL as primary
                'photo_url_storage' => $photoUrl, // Storage URL as fallback
                'photo_url_alt' => $photoUrlAlt, // Asset URL as alternative
                'photo' => $photoName,
                'photo_path' => $photoPath,
                'absolute_path' => $absolutePath,
                'file_exists' => file_exists($absolutePath),
                'file_size' => $fileSize,
                'debug_info' => [
                    'storage_exists' => Storage::exists('public/photos/' . $photoName),
                    'file_exists' => file_exists($absolutePath),
                    'photos_dir' => $photosDir,
                    'photos_dir_exists' => is_dir($photosDir),
                    'photos_dir_writable' => is_writable($photosDir)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendPasswordOtp(Request $request, NotificationService $notifier)
    {
        $user = Auth::user();
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return response()->json(['success'=>false,'message'=>'No phone number on file. Update your phone first.'], 422);
        }
        // Get OTP timeout from settings
        $otpTimeout = SystemSetting::getValue('otp_timeout_minutes', 10);
        // Invalidate previous OTPs for password_change
        OtpCode::where('user_id', $user->id)->where('purpose','password_change')->where('used',false)->update(['used'=>true,'used_at'=>now()]);
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'user_id' => $user->id,
            'otp_code' => $code,
            'expires_at' => now()->addMinutes($otpTimeout),
            'purpose' => 'password_change',
            'ip_address' => $request->ip(),
        ]);
        // Notify via SMS/email
        $notifier->sendSMS($phone, "Your OfisiLink OTP is: {$code}. Valid for {$otpTimeout} minutes.");
        return response()->json(['success'=>true,'message'=>"OTP sent. Valid for {$otpTimeout} minutes."]);
    }

    public function verifyPasswordOtp(Request $request)
    {
        $request->validate(['otp_code'=>'required|string|size:6']);
        $user = Auth::user();
        $otp = OtpCode::where('user_id',$user->id)
            ->where('purpose','password_change')
            ->where('otp_code',$request->otp_code)
            ->where('used',false)
            ->where('expires_at','>',now())
            ->latest()->first();
        if(!$otp){
            return response()->json(['success'=>false,'message'=>'Invalid or expired OTP.'], 422);
        }
        $otp->update(['used'=>true,'used_at'=>now()]);
        session(['password_change_verified'=>true,'password_change_verified_at'=>now()]);
        return response()->json(['success'=>true]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        $user = Auth::user();
        if (!session('password_change_verified') || now()->diffInMinutes(session('password_change_verified_at')) > 10) {
            return response()->json(['success'=>false,'message'=>'OTP verification expired. Verify again.'], 422);
        }
        if(!Hash::check($request->current_password, $user->password)){
            return response()->json(['success'=>false,'message'=>'Current password is incorrect.'], 422);
        }
        $user->forceFill(['password'=>Hash::make($request->new_password)])->save();
        session()->forget(['password_change_verified','password_change_verified_at']);
        return response()->json(['success'=>true,'message'=>'Password updated successfully.']);
    }

    public function sendPhoneOtp(Request $request, NotificationService $notifier)
    {
        $user = Auth::user();
        $phone = $user->mobile ?? $user->phone;
        if (!$phone) {
            return response()->json(['success'=>false,'message'=>'No phone number on file. Please contact administrator.'], 422);
        }
        // Get OTP timeout from settings
        $otpTimeout = SystemSetting::getValue('otp_timeout_minutes', 10);
        // Invalidate previous OTPs for phone_change
        OtpCode::where('user_id', $user->id)->where('purpose','phone_change')->where('used',false)->update(['used'=>true,'used_at'=>now()]);
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpCode::create([
            'user_id' => $user->id,
            'otp_code' => $code,
            'expires_at' => now()->addMinutes($otpTimeout),
            'purpose' => 'phone_change',
            'ip_address' => $request->ip(),
        ]);
        // Notify via SMS/email
        $notifier->sendSMS($phone, "Your OfisiLink OTP for phone change is: {$code}. Valid for {$otpTimeout} minutes.");
        return response()->json(['success'=>true,'message'=>"OTP sent to your current phone number. Valid for {$otpTimeout} minutes."]);
    }

    public function verifyPhoneOtp(Request $request)
    {
        $request->validate(['otp_code'=>'required|string|size:6']);
        $user = Auth::user();
        $otp = OtpCode::where('user_id',$user->id)
            ->where('purpose','phone_change')
            ->where('otp_code',$request->otp_code)
            ->where('used',false)
            ->where('expires_at','>',now())
            ->latest()->first();
        if(!$otp){
            return response()->json(['success'=>false,'message'=>'Invalid or expired OTP.'], 422);
        }
        $otp->update(['used'=>true,'used_at'=>now()]);
        session(['phone_change_verified'=>true,'phone_change_verified_at'=>now()]);
        return response()->json(['success'=>true,'message'=>'OTP verified successfully.']);
    }

    public function updatePhone(Request $request, NotificationService $notifier)
    {
        // Check OTP verification
        if (!session('phone_change_verified') || now()->diffInMinutes(session('phone_change_verified_at')) > 10) {
            return response()->json(['success'=>false,'message'=>'OTP verification expired. Please verify OTP again.'], 422);
        }

        // Accept various formats: 0712345678, 255712345678, +255712345678, etc.
        // The model mutator will format it to 255XXXXXXXXX
        $request->validate([
            'mobile' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Remove all non-numeric characters for validation
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    
                    // Must have at least 9 digits (local number) or 12 digits (with country code)
                    if (strlen($cleaned) < 9 || strlen($cleaned) > 15) {
                        $fail('The mobile number must be a valid Tanzania phone number (e.g., 0712345678 or 255712345678).');
                    }
                    
                    // Check if it's a valid Tanzania number pattern
                    $cleaned = ltrim($cleaned, '0');
                    if (!str_starts_with($cleaned, '255')) {
                        $cleaned = '255' . $cleaned;
                    }
                    
                    // Final format should be 255 followed by 9 digits
                    if (!preg_match('/^255[0-9]{9}$/', $cleaned)) {
                        $fail('The mobile number must be a valid Tanzania phone number (e.g., 0712345678 or 255712345678).');
                    }
                }
            ]
        ]);
        
        $user = Auth::user();
        $old = $user->mobile ?? $user->phone;
        
        // The setMobileAttribute mutator will automatically format the number
        $user->mobile = $request->mobile;
        $user->save();
        
        // Clear verification session
        session()->forget(['phone_change_verified','phone_change_verified_at']);
        
        if($old){ 
            $notifier->sendSMS($old, "Your OfisiLink number was changed to {$user->mobile}. If this wasn't you, contact admin immediately."); 
        }
        $notifier->sendSMS($user->mobile, "Your OfisiLink phone number has been updated successfully.");
        
        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully. Format: ' . $user->mobile
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email,'.Auth::id(),
            'marital_status'=>'nullable|in:single,married,divorced,widowed'
        ]);
        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('marital_status')) {
            $user->marital_status = $request->marital_status;
        }
        $user->save();
        return response()->json(['success'=>true,'message'=>'Profile updated successfully.']);
    }
}


