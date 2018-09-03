<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TranscribeController
{
    /**
     * @return JsonResponse
     * @Route("/transcribe", name="transcribe_index")
     */
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
}