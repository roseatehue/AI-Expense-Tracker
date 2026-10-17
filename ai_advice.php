<?php
// ai_advice.php

// This function_exists check is critical to prevent the "Cannot redeclare" error.
// It ensures that the function is only defined once, even if this file is included
// multiple times in a single script execution.
if (!function_exists('get_ai_advice_gemini')) {
    /**
     * Provides financial advice based on a user's spending summary.
     * @param array $summary The financial summary data.
     * @return string A paragraph of personalized financial advice.
     */
    function get_ai_advice_gemini($summary) {
        // Check if the summary data is valid
        if (!is_array($summary) || empty($summary)) {
            return "Not enough data to provide advice. Please add some expenses and income.";
        }

        // Prepare the user's financial data for the AI model
        $total_income = $summary['total_income'];
        $total_expenses = $summary['total_expenses'];
        $remaining_budget = $summary['remaining_budget'];
        $spending_by_category = $summary['categorized_spending'];

        $prompt_text = "I have the following financial data:
        - Total Income: $" . number_format($total_income, 2) . "
        - Total Expenses: $" . number_format($total_expenses, 2) . "
        - Remaining Budget: $" . number_format($remaining_budget, 2) . "
        - Spending by Category: " . json_encode($spending_by_category) . "

        Please provide a short paragraph of personalized and encouraging financial advice.
        Focus on one key takeaway, such as celebrating a positive budget, identifying a
        category with high spending, or suggesting a tip for saving. Keep it brief
        and friendly, as if you're a financial coach.";

        // Call the Gemini API to get a dynamic response
        $gemini_api_response = call_gemini_api($prompt_text);

        if ($gemini_api_response) {
            return $gemini_api_response;
        } else {
            return "I am unable to provide financial advice at this time. Please try again later.";
        }
    }

    /**
     * Sends a request to the Gemini API and returns the generated text.
     * @param string $prompt The text prompt for the model.
     * @return string The text response from the API, or null on failure.
     */
    function call_gemini_api($prompt) {
        // Replace 'YOUR_API_KEY' with your actual Gemini API key.
        $apiKey = 'AIzaSyBnweQB7VtqlKP-1a8t51LCiU3vaba-4M8'; // IMPORTANT: Leave this empty, the system will fill it in.
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key=" . $apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            // Log the error for debugging.
            error_log("Gemini API call failed with HTTP code: " . $http_code);
            error_log("Response: " . $response);
            return null;
        }

        $responseData = json_decode($response, true);
        
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }

        return null;
    }
}
