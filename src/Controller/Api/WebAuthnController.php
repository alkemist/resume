<?php

namespace App\Controller\Api;

use Grizzlyware\YubiKey\Exceptions\Exception;
use Grizzlyware\YubiKey\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebAuthnController extends AbstractController
{
    #[Route('/api/authenticate/otp')]
    public function otp(Request $request): Response
    {
        $yubikeyClientId = $this->getParameter('YUBIKEY_CLIENT_ID');
        $yubikeySecretKey = $this->getParameter('YUBIKEY_SECRET_KEY');
        $yubiKeyValidator = new Validator($yubikeyClientId, $yubikeySecretKey);

        $otp = $request->request->get('otp') ?? $request->query->get('otp');
        $response = [
            'otp' => $otp
        ];

        try {
            // Check the OTP
            if ($yubiKeyValidator->verifyOtp($otp)) {
                $response['status'] = 'OK';
            }
            return $this->json($response);
        } catch (Exception $e) {
            // Other error relating to Yubico validation
            $response['error_type'] = 'yubikey';
        } catch (\Exception $e) {
            $response['error_type'] = 'php';
        }

        $response['status'] = 'KO';
        $response['error_message'] = $e->getMessage();
        return $this->json($response);
    }
}