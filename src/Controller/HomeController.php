<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/instructor', name: 'instructor')]
    public function instructor(): Response
    {
        return $this->render('dashboard_instructor/index.html.twig');
    }

    #[Route('/student/dashboard', name: 'student_dashboard')]
    public function studentDashboard(): Response
    {
        return $this->render('dashboard_student/index.html.twig');
    }
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
public function adminDashboard(): Response
{
    return $this->render('dashboard_admin/index.html.twig');
}



<<<<<<< HEAD
<<<<<<< HEAD
=======

>>>>>>> 5863369a9829258019d3ee98bf198f1ba6905b37
=======
>>>>>>> gestionquiz
}

