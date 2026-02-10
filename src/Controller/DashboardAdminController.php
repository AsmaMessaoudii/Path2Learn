<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardAdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        // Vérifier que l'utilisateur est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer tous les utilisateurs
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'ASC');
        $users = $userRepository->searchAndSort($search, $sort);
        
        // Calculer les statistiques
        $totalUsers = count($users);
        $activeUsers = 0;
        $adminCount = 0;
        $teacherCount = 0;
        $studentCount = 0;
        
        foreach ($users as $user) {
            // Compter les utilisateurs actifs
            if ($user->getStatus() === UserStatus::ENABLE) {
                $activeUsers++;
            }
            
            // Compter par rôle
            if ($user->getRole() === UserRole::ADMIN) {
                $adminCount++;
            } elseif ($user->getRole() === UserRole::TEACHER) {
                $teacherCount++;
            } elseif ($user->getRole() === UserRole::STUDENT) {
                $studentCount++;
            }
        }
        
        return $this->render('dashboard_admin/index.html.twig', [
            'users' => $users,
            'stats' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'admins' => $adminCount,
                'teachers' => $teacherCount,
                'students' => $studentCount,
            ],
        ]);
    }
}