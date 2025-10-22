<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class SecurityHelper
{
    /**
     * Check if content contains JavaScript patterns
     *
     * @param string $content
     * @return bool
     */
    public static function containsJavaScript($content)
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',  // <script> tags
            '/javascript:/i',                   // javascript: protocol
            '/onclick\s*=/i',                   // onclick attributes
            '/onload\s*=/i',                    // onload attributes
            '/onerror\s*=/i',                   // onerror attributes
            '/onmouseover\s*=/i',               // onmouseover attributes
            '/onmouseout\s*=/i',                // onmouseout attributes
            '/onkeydown\s*=/i',                 // onkeydown attributes
            '/onkeyup\s*=/i',                   // onkeyup attributes
            '/onkeypress\s*=/i',                // onkeypress attributes
            '/onchange\s*=/i',                  // onchange attributes
            '/onsubmit\s*=/i',                  // onsubmit attributes
            '/onfocus\s*=/i',                   // onfocus attributes
            '/onblur\s*=/i',                    // onblur attributes
            '/eval\s*\(/i',                     // eval() function
            '/Function\s*\(/i',                 // Function constructor
            '/setTimeout\s*\(/i',               // setTimeout
            '/setInterval\s*\(/i',              // setInterval
            '/document\.cookie/i',              // document.cookie
            '/document\.write/i',               // document.write
            '/document\.writeln/i',             // document.writeln
            '/innerHTML\s*\+?=/i',              // innerHTML assignments
            '/outerHTML\s*\+?=/i',              // outerHTML assignments
            '/insertAdjacentHTML/i',            // insertAdjacentHTML
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('JavaScript pattern detected in content', [
                    'pattern' => $pattern,
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return true;
            }
        }

        return false;
    }
}
