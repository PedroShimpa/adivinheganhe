<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class SecurityHelper
{
    /**
     * Check if the given content contains any JavaScript elements
     *
     * @param string $content
     * @return bool
     */
    public static function containsJavaScript(string $content): bool
    {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',  // <script> tags
            '/javascript:/i',                   // javascript: protocol
            '/on\w+\s*=/i',                     // Event handlers like onclick, onload, etc.
            '/vbscript:/i',                     // vbscript: protocol
            '/data:text\/html/i',               // data:text/html
            '/expression\s*\(/i',               // CSS expressions
            '/eval\s*\(/i',                     // eval() function
            '/Function\s*\(/i',                 // Function constructor
            '/setTimeout\s*\(/i',               // setTimeout
            '/setInterval\s*\(/i',              // setInterval
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('JavaScript content detected in user input', [
                    'pattern' => $pattern,
                    'user_id' => auth()->id(),
                    'content_preview' => substr($content, 0, 100) . (strlen($content) > 100 ? '...' : ''),
                ]);
                return true;
            }
        }

        return false;
    }
}
