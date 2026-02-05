<?php
require_once 'config.php';

class NutritionAnalyzer {
    private $api_key;
    private $provider;
    
    public function __construct() {
        $this->api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        $this->provider = defined('AI_PROVIDER') ? AI_PROVIDER : 'gemini';
    }
    
    public function analyzeIngredients($ingredients, $servings = 1) {
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'error' => 'AI API key not configured'
            ];
        }
        
        $ingredients_text = $this->formatIngredients($ingredients);
        
        if ($this->provider === 'gemini') {
            return $this->callGeminiAPI($ingredients_text, $servings);
        } else {
            return $this->callDeepSeekAPI($ingredients_text, $servings);
        }
    }
    
    private function formatIngredients($ingredients) {
        $text = "Ingredients list:\n";
        foreach ($ingredients as $ingredient) {
            $text .= "- " . $ingredient['name'] . ": " . $ingredient['amount'] . "\n";
        }
        return $text;
    }
    
    private function callGeminiAPI($ingredients_text, $servings) {
        // Using gemini-2.5-flash - latest and fastest model available in v1 API
        // Previous attempts with gemini-1.5-flash and gemini-pro failed (404 errors)
        $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=' . $this->api_key;

        error_log("[NUTRITION AI - FINAL FIX] === Using gemini-2.5-flash API ===");
        
        $system_prompt = "You are a nutrition data extractor. Analyze the given ingredients and return:
        1. Boolean flags for dietary restrictions (true/false)
        2. Approximate calorie and macronutrient estimates (per serving)

        Return ONLY a JSON object with this structure:
        {
          \"vegetarian\": true/false,
          \"vegan\": true/false,
          \"gluten_free\": true/false,
          \"dairy_free\": true/false,
          \"has_nuts\": true/false,
          \"low_carb\": true/false,
          \"Carbs\": number,
          \"Proteins\": number,
          \"Calories\": number,
          \"Fat\": number,
          \"ingredient_status\": \"complete\" or \"partial_unknown\" or \"all_unknown\",
          \"unknown_ingredients\": [\"list\", \"of\", \"unknown\", \"items\"]
        }

        Guidelines:
        - Vegetarian: true if no meat/fish, but may contain dairy/eggs
        - Vegan: true if no animal products at all
        - Gluten_free: true if no gluten-containing ingredients (wheat, barley, rye)
        - Dairy_free: true if no milk, cheese, butter, yogurt, etc.
        - Has_nuts: true if contains nuts (almonds, walnuts, etc.)
        - Low_carb: true if estimated carbs per serving < 20g
        - Estimate calories and macronutrients based on common ingredients
        - If unsure about an ingredient, mark it in unknown_ingredients
        - If ALL ingredients are unknown, set ingredient_status to 'all_unknown'
        - If SOME ingredients are unknown, set ingredient_status to 'partial_unknown'
        - If NO unknown ingredients, set ingredient_status to 'complete'";

        $user_prompt = $ingredients_text . "\n\nNumber of servings: " . $servings . "\n\nAnalyze and return ONLY valid JSON, no other text:";
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $system_prompt . "\n\n" . $user_prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 500,
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        $json_payload = json_encode($data);
        error_log("[NUTRITION AI] URL: " . $url);
        error_log("[NUTRITION AI] Payload length: " . strlen($json_payload) . " bytes");
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);

        error_log("[NUTRITION AI] HTTP Code: " . $http_code);
        error_log("[NUTRITION AI] cURL Error Number: " . $curl_errno);
        if ($curl_error) {
            error_log("[NUTRITION AI] cURL Error Message: " . $curl_error);
        }
        error_log("[NUTRITION AI] Response: " . substr($response, 0, 500));
        
        curl_close($ch);
        
        if ($curl_errno !== 0) {
            error_log("[NUTRITION AI] Connection Error: " . $curl_error);
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $curl_error
            ];
        }
        
        if ($http_code != 200) {
            $error_response = json_decode($response, true);
            $error_msg = isset($error_response['error']['message']) 
                ? $error_response['error']['message'] 
                : 'Unknown error';
            
            error_log("[NUTRITION AI] API HTTP " . $http_code . " Error: " . $error_msg);
            return [
                'success' => false,
                'error' => 'API Error (HTTP ' . $http_code . '): ' . substr($error_msg, 0, 100)
            ];
        }
        
        error_log("[NUTRITION AI] === GEMINI-2.5-FLASH API REQUEST COMPLETED SUCCESSFULLY ===");
        
        $response_data = json_decode($response, true);
        
        if (!isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
            error_log("[NUTRITION AI] Invalid response structure: " . json_encode($response_data));
            return [
                'success' => false,
                'error' => 'Invalid AI response format'
            ];
        }
        
        $text_response = $response_data['candidates'][0]['content']['parts'][0]['text'];
        error_log("[NUTRITION AI] AI Response: " . substr($text_response, 0, 300));
        
        return $this->parseAIResponse($text_response);
    }
    
    private function parseAIResponse($text) {
        // Extract JSON from response
        $json_pattern = '/\{(?:[^{}]|(?R))*\}/s';
        preg_match($json_pattern, $text, $matches);
        
        if (empty($matches)) {
            error_log("[NUTRITION AI] No JSON found in response: " . $text);
            return [
                'success' => false,
                'error' => 'Could not parse AI response'
            ];
        }
        
        $json_str = $matches[0];
        $data = json_decode($json_str, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[NUTRITION AI] JSON decode error: " . json_last_error_msg());
            return [
                'success' => false,
                'error' => 'Invalid JSON from AI: ' . json_last_error_msg()
            ];
        }
        
        error_log("[NUTRITION AI] Parsed nutrition data: " . json_encode($data));
        
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    private function callDeepSeekAPI($ingredients_text, $servings) {
        $url = 'https://api.deepseek.com/v1/chat/completions';
        
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ],
            [
                'role' => 'user',
                'content' => $ingredients_text . "\n\nNumber of servings: " . $servings
            ]
        ];
        
        $data = [
            'model' => 'deepseek-chat',
            'messages' => $messages,
            'temperature' => 0.1,
            'max_tokens' => 500
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code != 200) {
            error_log("DeepSeek API error: " . $response);
            return [
                'success' => false,
                'error' => 'AI service unavailable'
            ];
        }
        
        $response_data = json_decode($response, true);
        $text_response = $response_data['choices'][0]['message']['content'] ?? '';
        
        return $this->parseAIResponse($text_response);
    }
    
    private function getSystemPrompt() {
        return "You are a nutrition data extractor. Analyze ingredients and return ONLY JSON with this structure:
        {
          \"vegetarian\": true/false/null,
          \"vegan\": true/false/null,
          \"gluten_free\": true/false/null,
          \"dairy_free\": true/false/null,
          \"has_nuts\": true/false/null,
          \"low_carb\": true/false/null,
          \"Carbs\": number/null,
          \"Proteins\": number/null,
          \"Calories\": number/null,
          \"Fat\": number/null,
          \"ingredient_status\": \"complete\" or \"partial_unknown\" or \"all_unknown\",
          \"unknown_ingredients\": []
        }";
    }
}
?>
