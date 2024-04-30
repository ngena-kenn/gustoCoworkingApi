<?php

namespace App\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AllServices
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator ;
    }

    public function generateCode(int $length = 6): string
    {   
        $routeName = 'user_active_account';
        $additionalPath = '/user/active';
        $url = $this->urlGenerator->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL);

        
        // $characters = '0123456789';
        // $code = '';
            
        // $charactersLength = strlen($characters);
        // for ($i = 0; $i < $length; $i++) {
        //     $code .= $characters[rand(0, $charactersLength - 1)];
        // }
        
        // dump($url."/".$code) ; die ;
        // return $url."/".$code;
        return $url ;
    }

}
