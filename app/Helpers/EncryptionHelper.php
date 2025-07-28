<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class EncryptionHelper
{
    /**
     * Generate an encrypted token for a given ID that's valid for the current session
     */
    public static function generateEncryptedId($id)
    {
        $sessionId = Session::getId();
        $timestamp = time();
        
        // Create a payload with the ID, session ID, and timestamp
        $payload = [
            'id' => $id,
            'session' => $sessionId,
            'timestamp' => $timestamp
        ];
        
        return Crypt::encrypt($payload);
    }
    
    /**
     * Decrypt and validate an encrypted ID token
     */
    public static function decryptId($encryptedId, $validateSession = false)
    {
        try {
            $payload = Crypt::decrypt($encryptedId);
            
            // Validate the payload structure
            if (!is_array($payload) || !isset($payload['id'], $payload['session'], $payload['timestamp'])) {
                return null;
            }
            
            // Optional session validation (disabled by default for staff profile links)
            if ($validateSession && $payload['session'] !== Session::getId()) {
                return null;
            }
            
            // Validate timestamp (token expires after 24 hours)
            if (time() - $payload['timestamp'] > 86400) {
                return null;
            }
            
            return $payload['id'];
        } catch (\Exception $e) {
            return null;
        }
    }
}