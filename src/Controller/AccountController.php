<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/account')]
class AccountController extends AbstractController
{
    #[Route('/profile', name: 'admin_account_profile')]
    public function profile(): Response
    {
        return $this->render('admin/account/profile.html.twig');
    }

    #[Route('/security', name: 'admin_account_security')]
    public function security(): Response
    {
        return $this->render('admin/account/security.html.twig');
    }
}
