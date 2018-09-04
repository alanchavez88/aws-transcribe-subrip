<?php
namespace App\Controller;

use App\Service\TranscribeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TranscribeController
{
    public function index()
    {
        return new JsonResponse([
            'upload' => [
                'path' => '/transcribe/upload',
                'method' => 'POST',
                'body' => 'aws-transcribe-output.json'
            ]
        ], Response::HTTP_OK);
    }

    public function upload(Request $request)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('transcribe');
        $data = file_get_contents($file->getRealPath());
        $transcription = json_decode($data);

        $transcribeService = new TranscribeService($transcription);
        $subtitles = $transcribeService->getSubtitles();
        $subRip = $transcribeService->getSubtitleFile($subtitles, 'subrip');

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'plain/text');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getBasename('.json') . '.srt";');
        $response->headers->set('Content-length', strlen($subRip));
        $response->setStatusCode(Response::HTTP_OK);
        $response->sendHeaders();
        $response->setContent($subRip);

        return $response;
    }
}