<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vocabulary;
use Illuminate\Support\Facades\Http;

class VocabularyController extends Controller
{
    /**
     * Display the vocabulary input form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'word' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id'
        ]);

        // DeepSeek APIから意味を取得 (簡易実装)
        $meaning = $this->getMeaningFromDeepSeek($request->word);

        $vocabulary = Vocabulary::create([
            'word' => $request->word,
            'meaning' => $meaning,
            'user_id' => $request->user_id,
            'notification_date' => now()->addWeek(),
            'test_notification_date' => now()->addMinute() // テスト用に1分後に通知
        ]);

        return response()->json($vocabulary, 201);
    }

    private function getMeaningFromDeepSeek(string $word): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('DEEPSEEK_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Explain the meaning of the English word '{$word}' in Japanese"
                    ]
                ],
                'max_tokens' => 1000
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            return "Failed to get meaning for {$word}";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
