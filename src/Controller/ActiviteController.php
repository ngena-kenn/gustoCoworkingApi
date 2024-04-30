<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActiviteController extends AbstractController
{
    #[Route('/activite/actuviteUser', name: 'activite_user', methods:["POST"])]
    public function getActivite(Request $request):void
    {
        // $userAgent = $request->get('User-Agent');
        // $ipAddress = $request->get('X-Forwarded-For');
        
        // $ipAddresses = explode(',', $ipAddress);
        // $clientIp = trim($ipAddresses[0]);
    }
}