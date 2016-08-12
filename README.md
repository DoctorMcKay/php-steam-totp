# Steam TOTP PHP

This lightweight class generates Steam-style 5-digit alphanumeric two-factor authentication codes given a shared secret.

Usage is simple:

```php
SteamTotp::getAuthCode('cnOgv/KdpLoP6Nbh0GMkXkPXALQ=');
```

## getAuthCode(secret[, timeOffset])
- `secret` - Your `shared_secret`, as a `Buffer`, hex string, or base64 string
- `timeOffset` - Optional. If you know your clock's offset from the Steam servers, you can provide it here. This number of seconds will be added to the current time to produce the final time. Default 0.

Returns your current 5-character alphanumeric TOTP code as a string (if no callback is provided) or queries the current
time from the Steam servers and returns the code in the callback (if the callback was provided).

## getConfirmationKey(identitySecret, time, tag)
- `identitySecret` - Your `identity_secret`, as a `Buffer`, hex string, or base64 string
- `time` - The Unix time for which you are generating this secret. Generally should be the current time.
- `tag` - The tag which identifies what this request (and therefore key) will be for. "conf" to load the confirmations page, "details" to load details about a trade, "allow" to confirm a trade, "cancel" to cancel it.

Returns a string containing your base64 confirmation key for use with the mobile confirmations web page.
