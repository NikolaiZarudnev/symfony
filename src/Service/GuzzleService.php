<?php

namespace App\Service;

use App\DTO\UserDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Utils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;


class GuzzleService
{
    public function __construct(
        private readonly SerializerInterface   $serializer,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
        $this->client = new Client([
            'base_uri' => $_ENV['BASE_URI'],
        ]);
        $response = $this->client->request('POST', '/api/login/', ['json' => [
            'username' => $_ENV['ADMIN_EMAIL'],
            'password' => $_ENV['ADMIN_PASSWORD']
        ]]);
        $this->token = json_decode($response->getBody(), true)['token'];
    }

    private $client;
    private $token;

    public function create(UserDTO $userDTO)
    {
        $userData = 'userJson=' . $this->serializer->serialize($userDTO, 'json');

        if ($userDTO->getId()) {
            $path = $this->urlGenerator->generate('api_user_edit', ['id' => $userDTO->getid()]);
        } else {
            $path = $this->urlGenerator->generate('api_user_create');
        }
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-type' => 'application/x-www-form-urlencoded',
        ];

        return $this->sendRequest(new Request(
            method: 'POST',
            uri: $path,
            headers: $headers,
            body: $userData
        ));
    }

    private function sendRequest(Request $request)
    {
        try {

            $response = $this->client->send($request, ['timeout' => 3]);

        } catch (ClientException $e) {

            throw new BadResponseException($e->getMessage(), $e->getRequest(), $e->getResponse());

        } catch (ServerException $e) {

            $errorsJson = $e->getResponse()->getBody()->getContents();
            $errors = json_decode($errorsJson, true);

            $message = '';
            if ($errors) {
                foreach ($errors as $error) {
                    $message .= $error['propertyPath'] . ": " . $error['message'] . ";\n";
                }
            }
            throw new BadResponseException($message, $e->getRequest(), $e->getResponse());

        } catch (GuzzleException $e) {

            throw new TransferException($e->getMessage());

        }

        return Utils::jsonDecode($response->getBody()->getContents(), true);
    }
}