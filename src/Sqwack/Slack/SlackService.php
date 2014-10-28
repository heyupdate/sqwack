<?php

namespace Sqwack\Slack;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJarInterface;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;

class SlackService
{
    protected $client;

    const PATH_SIGNIN = '/';
    const PATH_PHOTO_UPLOAD = '/account/photo';

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function login($email, $password)
    {
        $response = $this->client->get(self::PATH_SIGNIN);
        $crumbValue = $this->findCrumb((string) $response->getBody());

        $response = $this->client->post(self::PATH_SIGNIN, [
            'body' => [
                'signin' => '1',
                'crumb' => $crumbValue,
                'email' => $email,
                'password' => $password,
                'remember' => 'on'
            ]
        ]);
    }

    public function uploadPhoto($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('Unable to open "%s" photo file.', $file));
        }

        // Initial request to fetch the crumb value
        $response = $this->client->get(self::PATH_PHOTO_UPLOAD);
        $crumbValue = $this->findCrumb((string) $response->getBody());

        // Upload the photo
        $response = $this->client->post(self::PATH_PHOTO_UPLOAD, [
            'body' => [
                'upload' => '1',
                'crumb' => $crumbValue,
                'img' => fopen($file, 'r'),

            ]
        ]);

        $redirectLocation = $response->getHeader('location');
        if (!$redirectLocation) {
            throw new \RuntimeException('Unable to upload photo');
        }

        parse_str(parse_url($redirectLocation, PHP_URL_QUERY), $query);
        if (!isset($query['id'])) {
            throw new \RuntimeException('Unable to find photo for cropping');
        }

        $photoId = $query['id'];

        // Calculate crop box for a square image
        $size = getimagesize($file);
        $width = $size[0];
        $height = $size[1];
        $cropbox = floor(($width-$height)/2) . ',0,' . $height;

        $response = $this->client->post(self::PATH_PHOTO_UPLOAD, [
            'body' => [
                'crop' => '1',
                'crumb' => $crumbValue,
                'id' => $photoId,
                'cropbox' => $cropbox
            ]
        ]);

        // Done!
    }

    public function hasSession()
    {
        $jar = $this->client->getDefaultOption('cookies');
        if (!$jar instanceof CookieJarInterface) {
            return false;
        }

        foreach ($jar->toArray() as $cookie) {
            if ($cookie['Name'] === 'a' && trim($cookie['Value']) !== '') {
                return true;
            }
        }

        return false;
    }

    private function findCrumb($response)
    {
        $crawler = new Crawler($response);

        return $crawler->filterXPath(CssSelector::toXPath('input[name=crumb]'))->attr('value');
    }
}
