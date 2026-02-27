<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use MrPunyapal\Php2fa\Actions\ConfirmTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\DisableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\EnableTwoFactorAuthentication;
use MrPunyapal\Php2fa\Actions\GenerateRecoveryCodes;
use MrPunyapal\Php2fa\Actions\VerifyTwoFactorCode;
use MrPunyapal\Php2fa\Exceptions\InvalidOtpException;

class TwoFactorController extends Controller
{
    public function enable(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
    {
        $setup = $enable($request->user(), $request->user()->email);

        return response()->json([
            'qr_code_url' => $setup->qrCodeUrl,
            'secret' => $setup->secret,
            'recovery_codes' => $setup->recoveryCodes,
        ]);
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $confirm($request->user(), $request->input('code'));
        } catch (InvalidOtpException) {
            return response()->json(['message' => 'Invalid code. Please try again.'], 422);
        }

        return response()->json(['message' => 'Two-factor authentication confirmed.']);
    }

    public function verify(Request $request, VerifyTwoFactorCode $verify): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        if (! $verify($request->user(), $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code or recovery code.']);
        }

        $request->session()->put('two_factor_verified', true);

        return redirect()->intended('/dashboard');
    }

    public function disable(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
    {
        $disable($request->user());

        return response()->json(['message' => 'Two-factor authentication disabled.']);
    }

    public function regenerateRecoveryCodes(
        Request $request,
        GenerateRecoveryCodes $generate,
    ): JsonResponse {
        $codes = $generate($request->user());

        return response()->json(['recovery_codes' => $codes]);
    }
}
