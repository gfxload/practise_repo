<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\CookieJar;
use DOMDocument;
use DOMXPath;

class UrlValidationController extends Controller
{
    private $client;
    private $defaultHeaders;

    public function __construct()
    {
        $this->defaultHeaders = [
            'sec-ch-ua' => '"Not A(Brand";v="8", "Chromium";v="132", "Microsoft Edge";v="132"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36 Edg/132.0.0.0',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'sec-fetch-site' => 'none',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-user' => '?1',
            'sec-fetch-dest' => 'document',
            'accept-encoding' => 'gzip, deflate, br, zstd',
            'accept-language' => 'en-GB,en;q=0.9,en-US;q=0.8',
            'priority' => 'u=0, i'
        ];

        $this->client = new Client([
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 30,
            'allow_redirects' => true,
            'headers' => $this->defaultHeaders,
            'cookies' => true,
            'decode_content' => true,
            'http_errors' => false
        ]);
    }

    private function getCustomHeaders($url)
    {
        $headers = $this->defaultHeaders;
        $parsedUrl = parse_url($url);
        $domain = $parsedUrl['host'];

        // Add Referer and Origin headers based on the domain
        $headers['Referer'] = "{$parsedUrl['scheme']}://{$domain}/";
        $headers['Origin'] = "{$parsedUrl['scheme']}://{$domain}";
        
        // Update sec-fetch-site for same-origin requests
        $headers['sec-fetch-site'] = 'same-origin';

        // Add specific cookies for different services
        switch (true) {
            case str_contains($domain, 'freepik.com'):
                $headers['Origin'] = 'https://www.freepik.com';
                $headers['Cookie'] = '_cs_c=0; OptanonAlertBoxClosed=2024-12-12T12:08:06.785Z; OptanonConsent=isGpcEnabled=0&datestamp=Wed+Feb+05+2025+13%3A19%3A14+GMT%2B0200+(Eastern+European+Standard+Time)&version=202411.2.0&browserGpcFlag=0&isIABGlobal=false&hosts=&consentId=0cdadda5-b45d-4fe1-bf6e-4d2375fa1f1d&interactionCount=1&isAnonUser=1&landingPath=NotLandingPage&groups=C0001%3A1%2CC0002%3A1%2CC0003%3A1%2CC0004%3A1%2CC0005%3A1&intType=1&geolocation=EG%3BGZ&AwaitingReconsent=false';
                break;
            case str_contains($domain, 'shutterstock.com'):
                $headers['Origin'] = 'https://www.shutterstock.com';
                break;
            // Add more cases for other services as needed
        }

        return $headers;
    }

    private function findImageInPage($html, $fileId)
    {
        try {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Try different XPath queries to find images
            $queries = [
                "//img[contains(@src, '{$fileId}')]", // Direct file ID in src
                "//meta[@property='og:image']/@content", // OpenGraph image
                "//meta[@name='twitter:image']/@content", // Twitter image
                "//link[@rel='image_src']/@href", // Image source link
                "//img[contains(@class, 'preview') or contains(@class, 'thumbnail')]", // Common image classes
                "//img[contains(@alt, '{$fileId}')]", // Alt text containing file ID
            ];
            
            foreach ($queries as $query) {
                $nodes = $xpath->query($query);
                if ($nodes && $nodes->length > 0) {
                    $node = $nodes->item(0);
                    return $node instanceof \DOMAttr ? $node->value : $node->getAttribute('src');
                }
            }

            // If no specific image found, try to find the main/largest image
            $images = $xpath->query("//img");
            if ($images->length > 0) {
                // Try to find the largest image by checking attributes
                $maxArea = 0;
                $bestImage = null;
                
                foreach ($images as $img) {
                    $width = (int) $img->getAttribute('width');
                    $height = (int) $img->getAttribute('height');
                    $area = $width * $height;
                    
                    if ($area > $maxArea) {
                        $maxArea = $area;
                        $bestImage = $img;
                    }
                }
                
                if ($bestImage) {
                    return $bestImage->getAttribute('src');
                }
                
                // If no sized images found, return the first image
                return $images->item(0)->getAttribute('src');
            }
        } catch (\Exception $e) {
            Log::error('Error parsing HTML for images', [
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    private function getImageUrl($url, $fileId)
    {
        try {
            $cookieJar = new CookieJar();
            $headers = $this->getCustomHeaders($url);
            
            // First try to get the page normally
            try {
                $response = $this->client->get($url, [
                    'headers' => $headers,
                    'cookies' => $cookieJar
                ]);
                
                $html = $response->getBody()->getContents();
                
                // تسجيل محتوى الصفحة في السجل
                Log::info('Page content fetched', [
                    'url' => $url,
                    'status_code' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'content_length' => strlen($html)
                ]);

                $imageUrl = $this->findImageInPage($html, $fileId);

                if ($imageUrl) {
                    $absoluteUrl = $this->makeAbsoluteUrl($imageUrl, $url);
                    if ($this->validateImageUrl($absoluteUrl, $headers, $cookieJar)) {
                        return $absoluteUrl;
                    } else {
                        Log::warning('Found image URL but failed to validate it', [
                            'url' => $url,
                            'image_url' => $absoluteUrl,
                            'file_id' => $fileId
                        ]);
                    }
                } else {
                    Log::info('No image found in page content', [
                        'url' => $url,
                        'file_id' => $fileId,
                        'html_length' => strlen($html)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch page content', [
                    'url' => $url,
                    'file_id' => $fileId,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e)
                ]);
            }

            // Define patterns with regex
            $patterns = [
                'shutterstock.com' => [
                    'patterns' => [
                        "/https?:\/\/www\.shutterstock\.com\/image-[a-z]+\/[^\/]+-600w-{$fileId}\.jpg/i"
                    ],
                    'fallback' => "https://image.shutterstock.com/image-vector/-250nw-{$fileId}.jpg"
                ],
                'stock.adobe.com' => [
                    'patterns' => [
                        "/https?:\/\/stock\.adobe\.com\/[a-z]+\/download-preview\/{$fileId}/i"
                    ],
                    'fallback' => "https://stock.adobe.com/preview/{$fileId}"
                ],
                'freepik.com' => [
                    'patterns' => [
                        "/https?:\/\/img\.freepik\.com\/[a-z-]+\/[a-z-]+\/{$fileId}\.[a-z]+/i"
                    ],
                    'fallback' => "https://i2.pngimg.me/stock/freepik/{$fileId}.jpg"
                ]
            ];

            $parsedUrl = parse_url($url);
            $matchFound = false;
            
            foreach ($patterns as $domain => $config) {
                if (str_contains($parsedUrl['host'], $domain)) {
                    // First try to find a matching pattern in the HTML content
                    foreach ($config['patterns'] as $pattern) {
                        if (preg_match($pattern, $html, $matches)) {
                            $matchFound = true;
                            $foundUrl = $matches[0];
                            // Validate the found URL
                            if ($this->validateImageUrl($foundUrl, $headers, $cookieJar)) {
                                return $foundUrl;
                            } else {
                                Log::warning('Pattern matched but URL validation failed', [
                                    'url' => $url,
                                    'found_url' => $foundUrl,
                                    'pattern' => $pattern,
                                    'file_id' => $fileId
                                ]);
                            }
                        }
                    }

                    if (!$matchFound) {
                        Log::info('No regex patterns matched for domain', [
                            'url' => $url,
                            'domain' => $domain,
                            'file_id' => $fileId
                        ]);
                    }

                    // Try fallback URL
                    if ($this->validateImageUrl($config['fallback'], $headers, $cookieJar)) {
                        return $config['fallback'];
                    } else {
                        Log::warning('Fallback URL failed validation', [
                            'url' => $url,
                            'fallback_url' => $config['fallback'],
                            'file_id' => $fileId
                        ]);
                    }
                }
            }

            Log::error('All attempts to get image URL failed', [
                'url' => $url,
                'file_id' => $fileId,
                'attempts' => [
                    'page_content' => isset($html),
                    'regex_patterns' => $matchFound,
                    'fallback_url' => isset($config['fallback'])
                ]
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected error while getting image URL', [
                'url' => $url,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function validateImageUrl($url, $headers, $cookieJar)
    {
        try {
            $response = $this->client->get($url, [
                'headers' => $headers,
                'cookies' => $cookieJar
            ]);

            // تسجيل كامل تفاصيل الطلب والرد
            Log::info('Image URL validation attempt', [
                'url' => $url,
                'request' => [
                    'headers' => $headers,
                    'method' => 'GET'
                ],
                'response' => [
                    'status_code' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'content_type' => $response->getHeaderLine('Content-Type'),
                    'content_length' => $response->getHeaderLine('Content-Length'),
                    'body_length' => strlen($response->getBody()->getContents())
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('Image URL validation failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'request' => [
                    'headers' => $headers,
                    'method' => 'GET'
                ],
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function makeAbsoluteUrl($imageUrl, $baseUrl)
    {
        if (strpos($imageUrl, 'http') !== 0) {
            $parsedUrl = parse_url($baseUrl);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            return $baseUrl . ($imageUrl[0] === '/' ? '' : '/') . $imageUrl;
        }
        return $imageUrl;
    }

    private function extractAdobeStockFileUrl($url, $fileId)
    {
        try {
            $cookieJar = new CookieJar();
            $headers = $this->getCustomHeaders($url);

            $response = $this->client->get($url, [
                'headers' => $headers,
                'cookies' => $cookieJar
            ]);

            $html = $response->getBody()->getContents();

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Find the <a> element with the data-content-id attribute matching the fileId
            $query = "//a[.//*/@data-content-id='{$fileId}'] | //a[@data-content-id='{$fileId}']";
            $nodes = $xpath->query($query);
            if ($nodes && $nodes->length > 0) {
                $node = $nodes->item(0);
                $fileUrl = $node->getAttribute('href');

                // Make the URL absolute
                $absoluteFileUrl = $this->makeAbsoluteUrl($fileUrl, $url);

                Log::info('Adobe Stock search page - File URL extracted', [
                    'url' => $url,
                    'file_id' => $fileId,
                    'extracted_file_url' => $absoluteFileUrl
                ]);

                return $absoluteFileUrl;
            } else {
                Log::warning('Adobe Stock search page - File URL not found', [
                    'url' => $url,
                    'file_id' => $fileId,
                    'html_length' => strlen($html)
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error extracting Adobe Stock file URL', [
                'url' => $url,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function validateUrl(Request $request)
    {
        try {
            Log::info('URL validation request received', [
                'url' => $request->input('url'),
                'method' => $request->method(),
                'headers' => $request->headers->all()
            ]);

            $request->validate([
                'url' => ['required', 'url']
            ]);

            $url = $request->input('url');

            // **NEW: Check if it's an Adobe Stock search page URL and extract the correct file URL (BEFORE Service Detection)**
            $adobeStockRegex = '/^https:\/\/stock\.adobe\.com\/[a-z]{2}\/search\/?\??.*&asset_id=\d+$/';
            if (preg_match($adobeStockRegex, $url)) {
                // Try to extract asset ID from URL parameters
                $parsedUrl = parse_url($url);
                parse_str($parsedUrl['query'] ?? '', $queryParams);
                if (isset($queryParams['asset_id'])) {
                    $assetId = $queryParams['asset_id'];
                    $correctedUrl = $this->extractAdobeStockFileUrl($url, $assetId);

                    if ($correctedUrl) {
                        // Update the URL with the corrected values
                        $url = $correctedUrl;

                        Log::info('Adobe Stock search URL corrected (before service detection)', [
                            'original_url' => $request->input('url'),
                            'corrected_url' => $url,
                            'asset_id' => $assetId
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Could not extract file URL from Adobe Stock search page.',
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not extract asset ID from Adobe Stock search URL. Please check the URL.',
                    ], 400);
                }
            }

            // Now, find the service and extract the file ID (using the potentially corrected URL)
            $result = Service::findServiceAndExtractId($url);

            if (!$result) {
                Log::warning('URL validation failed: Unsupported URL', [
                    'url' => $url
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, this URL is not supported. Please make sure it is a valid URL from supported services.',
                ], 400);
            }

            if ($result['file_id'] === null) {
                Log::warning('URL validation failed: Could not extract file ID', [
                    'url' => $url,
                    'service' => $result['service']->name
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Could not extract file ID from URL. Please check the URL.',
                ], 400);
            }

            // Get image URL using our new method
            $imageUrl = $this->getImageUrl($url, $result['file_id']);

            // Load video options if this is a video service
            if ($result['service']->is_video) {
                $videoOptions = $result['service']->videoOptions()->get()->map(function($option) {
                    return [
                        'id' => $option->id,
                        'display_name' => $option->display_name,
                        'parameter_name' => $option->parameter_name,
                        'parameter_value' => $option->parameter_value,
                        'points_cost' => (int)$option->points_cost,
                    ];
                });
                
                // تسجيل معلومات خيارات الفيديو المتاحة
                Log::info('Video options available for service', [
                    'service_id' => $result['service']->id,
                    'service_name' => $result['service']->name,
                    'options_count' => $videoOptions->count(),
                    'options' => $videoOptions->toArray()
                ]);
                
                $result['video_options'] = $videoOptions->toArray();
            } else {
                $result['video_options'] = [];
            }

            // Log successful validation
            Log::info('URL validated successfully', [
                'url' => $url,
                'service' => $result['service']->name,
                'file_id' => $result['file_id'],
                'image_url' => $imageUrl
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'URL validated successfully',
                'data' => [
                    'service' => $result['service'],
                    'file_id' => $result['file_id'],
                    'image_url' => $imageUrl,
                    'video_options' => $result['video_options'] ?? [],
                    'corrected_url' => $url !== $request->input('url') ? $url : null, // إرجاع الرابط المصحح إذا كان مختلفًا عن الرابط الأصلي
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error during URL validation', [
                'url' => $request->input('url'),
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during URL validation.'
            ], 500);
        }
    }
}
