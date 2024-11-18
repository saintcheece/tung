<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImageToTextController
 * 
 * This class handles the request to convert an image to text.
 * It uses the API-Ninjas API to perform the conversion.
 */
class ImageToTextController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'toText' => 'required|file|mimes:jpg',
        ]);

        // Store the image in the 'requests' directory
        $files = scandir(public_path('requests'));
        $files = array_filter($files, function($file) {
            return strpos($file, 'request') === 0;
        });
        $files = array_map(function($file) {
            return (int) substr($file, 7, 4);
        }, $files);

        if (!empty($files)) {
            $max = max($files) + 1;
            $fileName = 'request' . str_pad($max, 4, '0', STR_PAD_LEFT) . '.jpg';
        } else {
            $fileName = 'request0001.jpg';
        }

        $imagePath = $request->file('toText')->storeAs('requests', $fileName, 'public');

        // Generate public URL
        $publicUrl = Storage::url($imagePath);
        
        // Create a new Guzzle client
        $client = new Client();

        $languages = [
            'eng', 'fre', 'ger', 'gre', 'ita', 'por', 'spa', 'swe'
        ];

        $translation = "";

        try {
            // Post the image to the API
            $readText = "";
            foreach ($languages as $language) {
                $response = $client->request('POST', 'https://api.ocr.space/parse/image', [
                    'headers' => [
                        'apikey' => 'K85301401588957',
                    ],
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => fopen(storage_path('app/public/' . $imagePath), 'r'),
                        ],
                        [
                            'name'     => 'language',
                            'contents' => $language, // Specify the language
                        ],
                        [
                            'name'     => 'filetype',
                            'contents' => 'jpg', // Specify the file type
                        ],
                        [
                            'name'     => 'isOverlayRequired',
                            'contents' => 'false', // Specify additional options
                        ],
                        [
                            'name'     => 'iscreatesearchablepdf',
                            'contents' => 'false', // Disable PDF creation
                        ],
                        [
                            'name'     => 'issearchablepdfhidetextlayer',
                            'contents' => 'false', // Disable hidden text layer
                        ],
                    ],
                ]);
                $data = json_decode($response->getBody()->getContents(), true);
                if (!empty($data['ParsedResults'][0]['ParsedText'])) {
                    $readText = $data['ParsedResults'][0]['ParsedText'];
                }

                return $this->explain($readText, $toLang = $request->input('toLang'));
            }

        } catch (Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 400);
        }

        return $translation;
    }

    public function explain($readText, $language)
    {
        // Create a new Guzzle client
        $client = new Client();

        try {
            // TRANSLATE
            $response = $client->post('https://api.apilayer.com/language_translation/translate?target='.$language, [
                'headers' => [
                    'Content-Type' => 'text/plain',
                    'apikey' => 'yF9GYCbZJxcGrKUvZ5tsyRvDKEk3R617',
                ],
                'body' => rawurldecode($readText), // URL-encoded text
            ]);
            
            $translation= json_decode($response->getBody()->getContents(), true)['translations'][0]['translation'];

            return $translation;

        } catch (Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 400);
        }
    }

    public function retranslate(Request $request)
    {
        return $this->explain($request->input('readText'), $request->input('language'));
    }
}

