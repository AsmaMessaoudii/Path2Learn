<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Service\OpenAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'ASC');
        $users = $userRepository->searchAndSort($search, $sort);

        $totalUsers = count($users);
        $activeUsers = 0;
        $adminCount = 0;
        $teacherCount = 0;
        $studentCount = 0;

        foreach ($users as $user) {
            if ($user->getStatus() === UserStatus::ENABLE) {
                $activeUsers++;
            }

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
            'role' => $this->getUser()->getRole(),
        ]);
    }

    #[Route('/admin/predict-stats', name: 'admin_predict_stats', methods: ['POST'])]
    public function predictStats(UserRepository $userRepository, OpenAIService $openAIService): JsonResponse
    {
        // Log that the endpoint was hit (for debugging)
        error_log('=== PREDICT STATS ENDPOINT HIT ===');
        
        try {
            // Get fresh stats directly from repository
            $stats = [
                'total'    => $userRepository->count([]),
                'active'   => $userRepository->count(['status' => UserStatus::ENABLE]),
                'admins'   => $userRepository->count(['role' => UserRole::ADMIN]),
                'teachers' => $userRepository->count(['role' => UserRole::TEACHER]),
                'students' => $userRepository->count(['role' => UserRole::STUDENT]),
            ];
            
            error_log('Stats calculated: ' . json_encode($stats));

            // Prompt for OpenAI/Groq
            $userPrompt = <<<EOT
Analyse ces stats Path2Learn :
Total users: {$stats['total']}
Actifs: {$stats['active']}
Admins: {$stats['admins']}
Teachers: {$stats['teachers']}
Students: {$stats['students']}

Prédiction 30-90 jours: croissance, churn, point faible.

4-6 conseils prioritaires concrets.

*STRICT* : Réponds UNIQUEMENT avec ce JSON valide, RIEN d'autre (pas de texte avant/après) :
{"prediction":"2-4 phrases","tips":["conseil1","conseil2","conseil3","conseil4","conseil5"]}
EOT;

            try {
                // Try to get AI response
                $history = [
                    ['role' => 'system', 'content' => 'Tu es un analyste. Réponds EXCLUSIVEMENT en JSON valide strict, sans aucun mot avant ou après le JSON.'],
                    ['role' => 'user', 'content' => $userPrompt]
                ];

                $aiResponse = $openAIService->askWithHistory($history);
                
                error_log('AI Response received: ' . $aiResponse);

                // Clean the response (remove any potential markdown or extra text)
                $aiResponse = trim($aiResponse);
                // Try to extract JSON if there's any text around it
                if (preg_match('/\{.*\}/s', $aiResponse, $matches)) {
                    $aiResponse = $matches[0];
                }
                
                $result = json_decode($aiResponse, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($result) && !empty($result['prediction']) && !empty($result['tips']) && is_array($result['tips'])) {
                    return $this->json([
                        'success' => true,
                        'prediction' => trim($result['prediction']),
                        'tips' => array_map('trim', $result['tips'])
                    ]);
                } else {
                    throw new \Exception('Invalid JSON response from AI: ' . json_last_error_msg());
                }
                
            } catch (\Exception $e) {
                error_log('AI Service error: ' . $e->getMessage());
                
                // Fallback response with calculated stats
                $activityRate = round(($stats['active'] / max(1, $stats['total'])) * 100);
                $inactive = $stats['total'] - $stats['active'];
                
                // Generate intelligent fallback based on actual stats
                $prediction = "Basé sur les données actuelles: {$stats['total']} utilisateurs dont {$stats['active']} actifs ({$activityRate}% d'activité). ";
                
                if ($stats['students'] > $stats['teachers'] * 10) {
                    $prediction .= "Déséquilibre élèves/enseignants détecté, risque de manque d'encadrement.";
                } elseif ($activityRate < 50) {
                    $prediction .= "Taux d'activité faible, risque de churn élevé dans les 30 prochains jours.";
                } else {
                    $prediction .= "Croissance stable prévue si le taux d'activité se maintient.";
                }
                
                $tips = [
                    "{$stats['students']} étudiants - cibler les inactifs pour réactivation",
                    "{$stats['teachers']} enseignants - recruter pour améliorer ratio élèves/prof",
                    "{$inactive} utilisateurs inactifs - campagne de notification urgente",
                    "Analyser les comptes inactifs > 30 jours",
                    "{$stats['admins']} administrateurs - vérifier charge administrative"
                ];
                
                return $this->json([
                    'success' => true,
                    'prediction' => $prediction,
                    'tips' => $tips
                ]);
            }
            
        } catch (\Exception $e) {
            error_log('Critical error in predictStats: ' . $e->getMessage());
            error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}