<?php

namespace App\Security;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class DiscordAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router,
        private readonly UsersRepository $userRepository,
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $parameterBag
    ) {}

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse($this->router->generate("auth_discord_start"), Response::HTTP_TEMPORARY_REDIRECT);
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get("_route") === "auth_discord_login";
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient("discord");
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var DiscordResourceOwner $discordUser */
                $discordUser = $client->fetchUserFromToken($accessToken);

                $user = $this->userRepository->findOneBy(["DiscordId" => $discordUser->getId()]);

                if (null === $user) {


                    $this->httpClient->request(
                        'PUT',
                        'https://discord.com/api/v10/guilds/'.$this->parameterBag->get('discord_guild').'/members/'.$discordUser->getId().'', 
                        [
                            'headers' => [
                                'Authorization' => 'Bot '.$this->parameterBag->get('discord_token').'',
                                'Content-Type' => 'application/json'
                            ],
                            'json' => [
                                'access_token' => $accessToken->getToken(),
                            ]
                        ]
                    );


                    $avatar = 'https://cdn.discordapp.com/avatars/'.$discordUser->getId().'/'.$discordUser->getAvatarHash().'.png';
                    $user = new Users();
                    $user->setEmail($discordUser->getEmail());
                    $user->setUsername($discordUser->getUsername());
                    $user->setRole('ROLE_USER');
                    if ($discordUser->getAvatarHash()) {
                        $user->setAvatar($avatar);
                    }
                    $user->setPassword($accessToken->getToken());
                    $user->setDiscordId($discordUser->getId());

                    $this->em->persist($user);
                }

                $this->em->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_application_index');


        return new RedirectResponse($targetUrl);
    
        // or, on success, let the request continue to be handled by the controller
        //return null;
        // redirect to user to your post authentication page (e.g. dashboard, home)
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
