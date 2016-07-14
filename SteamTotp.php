<?php
class SteamTotp {
    const CHARSET = '23456789BCDFGHJKMNPQRTVWXY';
    const CODE_LENGTH = 5;

    /**
     * Generate a Steam-style TOTP authentication code.
     * @param string $shared_secret   Your TOTP shared_secret, as a base64 string, hex string, or binary string
     * @param int $time_offset        If you know how far off your clock is from the Steam servers, put the offset here in seconds
     * @return string
     */
    public static function getAuthCode($shared_secret, $time_offset = 0) {
        $hmac = hash_hmac('sha1', pack('J', floor((time() + $time_offset) / 30)), self::bufferizeSecret($shared_secret), true);
        $start = unpack('c19trash/Cstart', $hmac);
        $start = $start['start'] & 0x0F;

        $fullcode = unpack('c' . $start . 'trash/Nfullcode', $hmac);
        $fullcode = $fullcode['fullcode'] & 0x7FFFFFFF;

        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= substr(self::CHARSET, $fullcode % strlen(self::CHARSET), 1);
            $fullcode /= strlen(self::CHARSET);
        }

        return $code;
    }

    /**
     * Generate a base64 confirmation key for use with mobile trade confirmations. The key can only be used once.
     * @param string $identity_secret   The identity_secret that you received when enabling two-factor authentication, as a base64 string, hex string, or binary string
     * @param int $time                 The Unix time for which you are generating this secret. Generally should be the current time.
     * @param string $tag               The tag which identifies what this request (and therefore key) will be for. "conf" to load the confirmations page, "details" to load details about a trade, "allow" to confirm a trade, "cancel" to cancel it.
     * @return string
     */
    public static function getConfirmationKey($identity_secret, $time, $tag) {
        if (empty($tag)) {
            $buf = pack('J', $time);
        } else {
            $buf = pack('Ja*', $time, $tag);
        }

        $hmac = hash_hmac('sha1', $buf, self::bufferizeSecret($identity_secret), true);
        return base64_encode($hmac);
    }

    public static function getTimeOffset() {
        // TODO
    }

    /**
     * Get a standardized device ID based on your SteamID.
     * @param string|int $steamid Your SteamID in 64-bit format (as a string or integer)
     * @return string
     */
    public static function getDeviceID($steamid) {
        return 'android:' . preg_replace('/^([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12}).*$/', '$1-$2-$3-$4-$5', sha1($steamid));
    }

    private static function bufferizeSecret($secret) {
        if (preg_match('/[0-9a-fA-F]{40}/', $secret)) {
            return pack('H*', $secret);
        }

        if (preg_match('/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/', $secret)) {
            return base64_decode($secret);
        }

        return $secret;
    }
}
