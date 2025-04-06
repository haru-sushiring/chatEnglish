<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LineWebhookController extends Controller
{
    /**
     * LINEからのウェブフックリクエストを処理する
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        // 署名の検証
        $signature = $request->header('X-Line-Signature');
        $body = $request->getContent();

        // 環境変数からチャネルシークレットを取得
        $channelSecret = config('services.line.channel_secret');

        // 署名の検証
        $hash = hash_hmac('sha256', $body, $channelSecret, true);
        $calculatedSignature = base64_encode($hash);

        // 署名が一致しない場合は400エラーを返す
        if ($signature !== $calculatedSignature) {
            Log::error('LINE Webhook: Invalid signature');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        // リクエストの内容をログに記録
        Log::info('LINE Webhook received', ['body' => json_decode($body, true)]);

        $events = json_decode($body, true)['events'];

        foreach ($events as $event) {
            // イベントタイプによる処理の分岐
            switch ($event['type']) {
                case 'follow':
                    // ユーザーがボットをフォローした場合の処理
                    $this->handleFollowEvent($event);
                    break;

                case 'message':
                    // メッセージを受信した場合の処理
                    $this->handleMessageEvent($event);
                    break;

                default:
                    Log::info('Unhandled event type', ['type' => $event['type']]);
                    break;
            }
        }

        // 成功レスポンスを返す（LINEプラットフォームの要件）
        return response()->json(['message' => 'OK']);
    }

    /**
     * フォローイベントを処理する
     *
     * @param array $event
     * @return void
     */
    private function handleFollowEvent(array $event)
    {
        // ユーザーIDを取得
        $lineUserId = $event['source']['userId'];
        Log::info('User followed the bot', ['lineUserId' => $lineUserId]);

        // ユーザーIDをデータベースに保存するか既存ユーザーに関連付け
        // サンプル実装：ユーザーIDの記録
        User::updateOrCreate(
            ['id' => 1], // 最初のユーザーを更新（実際の実装ではユーザー識別方法を調整）
            ['line_id' => $lineUserId]
        );
    }

    /**
     * メッセージイベントを処理する
     *
     * @param array $event
     * @return void
     */
    private function handleMessageEvent(array $event)
    {
        if ($event['message']['type'] != 'text') {
            Log::info('Non-text message received', ['type' => $event['message']['type']]);
            return;
        }

        $userId = $event['source']['userId'];
        $message = $event['message']['text'];

        Log::info('Message received', [
            'userId' => $userId,
            'message' => $message
        ]);

        // メッセージの内容に応じた処理を実装
        // 例：特定のキーワードに対する応答など
    }
}
