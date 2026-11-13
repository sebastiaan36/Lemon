<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\VideoStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Bunny Stream calls this when a video's encoding status changes.
 *
 * The payload is not trusted: it only says which video to look at, and the
 * actual status is fetched from the Bunny API with our own key. Unknown
 * videos and other libraries are ignored.
 */
class BunnyStreamWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $guid = $request->input('VideoGuid');
        $libraryId = (string) $request->input('VideoLibraryId');

        if (! is_string($guid) || $guid === '' || $libraryId !== (string) config('services.bunny.stream.library_id')) {
            return response()->json(['ignored' => true]);
        }

        $media = Media::query()->where('custom_properties->bunny_stream->guid', $guid)->first();

        if (! $media) {
            return response()->json(['ignored' => true]);
        }

        try {
            VideoStream::refresh($media);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'refresh failed'], 502);
        }

        return response()->json(['status' => VideoStream::state($media)['status'] ?? null]);
    }
}
