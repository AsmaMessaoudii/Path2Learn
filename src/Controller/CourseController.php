<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\LikeDislike;
use App\Repository\CoursRepository;
use App\Repository\EvaluationRepository;
use App\Repository\LikeDislikeRepository;
use App\Repository\RessourcePedagogiqueRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\Criteria;
use App\Entity\BadRatingReason;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\CourseSummary;
use App\Repository\CourseSummaryRepository;

class CourseController extends AbstractController
{
    #[Route('/courses', name: 'app_courses')]
    public function index(
        Request $request,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        PaginatorInterface $paginator,
        EvaluationRepository $evaluationRepository,
        LikeDislikeRepository $likeDislikeRepository
    ): Response
    {
        // Récupérer le terme de recherche depuis la requête
        $searchQuery = $request->query->get('q', '');
        
        // Construire la requête pour les cours publiés
        $queryBuilder = $coursRepository->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->setParameter('statut', 'publié')
            ->orderBy('c.dateCreation', 'DESC');
        
        if (!empty($searchQuery)) {
            $queryBuilder->andWhere('c.titre LIKE :search OR c.description LIKE :search OR c.matiere LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }
        
        // 1. Récupérer le cours le mieux noté/aimé pour la recommandation
        // On prend simplement le cours avec la meilleure note moyenne pour l'instant
        // Idéalement, on ferait une requête plus complexe (Note * Coeff + Likes)
        $allCoursesForReco = $coursRepository->findBy(['statut' => 'publié']);
        $topCourse = null;
        $highestScore = -1;
        
        foreach ($allCoursesForReco as $c) {
            $avg = $evaluationRepository->findAverageByCours($c) ?? 0;
            $nbLikes = $likeDislikeRepository->countByCoursAndType($c, 'like');
            
            // Score simple : Note (sur 100) + (Likes * 5)
            $score = $avg + ($nbLikes * 5);
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $topCourse = $c;
            }
        }

        // Préparer les données du top course
        $topCourseData = null;
        if ($topCourse) {
            $topCourseData = [
                'course' => $topCourse,
                'likes' => $likeDislikeRepository->countByCoursAndType($topCourse, 'like'),
                'averageRating' => $evaluationRepository->findAverageByCours($topCourse)
            ];
        }
        
        // 2. Récupérer les favoris de l'utilisateur
        $user = $this->getUser();
        $userFavorites = [];
        if ($user) {
            $favs = $likeDislikeRepository->findBy(['user' => $user, 'type' => 'favorite']);
            foreach ($favs as $fav) {
                $userFavorites[] = $fav->getCours()->getId();
            }
        }
        
        // Pagination - 8 cours par page
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            8
        );
        
        // Préparer les données avec les ressources, likes/dislikes et évaluations
        $coursesWithData = [];
        foreach ($pagination as $cours) {
            // Récupérer les ressources associées
            $ressources = $ressourceRepository->findBy(['cours' => $cours]);
            
            // Récupérer les statistiques de likes/dislikes
            $likes = $likeDislikeRepository->countByCoursAndType($cours, 'like');
            $dislikes = $likeDislikeRepository->countByCoursAndType($cours, 'dislike');
            
            // Récupérer l'évaluation moyenne
            $averageRating = $evaluationRepository->findAverageByCours($cours);
            
            // Vérifier si l'utilisateur connecté a déjà liké/disliké
            $user = $this->getUser();
            $userReaction = null;
            $userEvaluation = null;
            
            if ($user) {
                $userReaction = $likeDislikeRepository->findOneBy([
                    'cours' => $cours,
                    'user' => $user
                ]);
                
                $userEvaluation = $evaluationRepository->findOneBy([
                    'cours' => $cours,
                    'user' => $user
                ]);
            }
            
            $coursesWithData[] = [
                'course' => $cours,
                'resources' => $ressources,
                'likes' => $likes,
                'dislikes' => $dislikes,
                'averageRating' => $averageRating,
                'userReaction' => $userReaction ? $userReaction->getType() : null,
                'userEvaluation' => $userEvaluation ? $userEvaluation->getNote() : null
            ];
        }
        
        // Compter le nombre total de cours publiés
        $totalCourses = $coursRepository->count(['statut' => 'publié']);
        
        // Calculer le total des heures
        $totalHours = 0;
        $allCourses = $coursRepository->findBy(['statut' => 'publié']);
        foreach ($allCourses as $cours) {
            $totalHours += $cours->getDuree() ?? 0;
        }
        
        return $this->render('course/index.html.twig', [
            'coursesWithData' => $coursesWithData,
            'pagination' => $pagination,
            'totalCourses' => $totalCourses,
            'totalHours' => $totalHours,
            'searchQuery' => $searchQuery,
            'searchQuery' => $searchQuery,
            'hasSearchResults' => !empty($searchQuery),
            'topCourseData' => $topCourseData,
            'userFavorites' => $userFavorites
        ]);
    }
    
    #[Route('/courses/{id}', name: 'course_show')]
    public function show(
        int $id,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository,
        EvaluationRepository $evaluationRepository,
        LikeDislikeRepository $likeDislikeRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        // Vérifier si le cours existe et est publié
        if (!$cours) {
            throw $this->createNotFoundException('Ce cours n\'existe pas.');
        }
        
        if ($cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Ce cours n\'est pas disponible.');
        }
        
        // Récupérer les ressources associées
        $ressources = $ressourceRepository->findBy(['cours' => $cours]);
        
        // Récupérer les statistiques de likes/dislikes
        $likes = $likeDislikeRepository->countByCoursAndType($cours, 'like');
        $dislikes = $likeDislikeRepository->countByCoursAndType($cours, 'dislike');
        
        // Récupérer l'évaluation moyenne
        $averageRating = $evaluationRepository->findAverageByCours($cours);
        
        // Récupérer toutes les évaluations
        $evaluations = $evaluationRepository->findBy(['cours' => $cours], ['dateEvaluation' => 'DESC']);
        
        // Vérifier si l'utilisateur connecté a déjà réagi
        $user = $this->getUser();
        $userReaction = null;
        $userEvaluation = null;
        
        if ($user) {
            $userReaction = $likeDislikeRepository->findOneBy([
                'cours' => $cours,
                'user' => $user
            ]);
            
            $userEvaluation = $evaluationRepository->findOneBy([
                'cours' => $cours,
                'user' => $user
            ]);
        }
        
        return $this->render('course/show.html.twig', [
            'course' => $cours,
            'resources' => $ressources,
            'likes' => $likes,
            'dislikes' => $dislikes,
            'averageRating' => $averageRating,
            'evaluations' => $evaluations,
            'userReaction' => $userReaction ? $userReaction->getType() : null,
            'userEvaluation' => $userEvaluation ? $userEvaluation->getNote() : null,
        ]);
    }
    
    #[Route('/courses/{id}/like', name: 'course_like', methods: ['POST'])]
    public function like(
        int $id,
        CoursRepository $coursRepository,
        LikeDislikeRepository $likeDislikeRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $cours = $coursRepository->find($id);
        
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }
        
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a déjà réagi
        $existingReaction = $likeDislikeRepository->findOneBy([
            'cours' => $cours,
            'user' => $user
        ]);
        
        if ($existingReaction) {
            if ($existingReaction->getType() === 'like') {
                // Supprimer le like
                $likeDislikeRepository->remove($existingReaction, true);
                $message = 'Like retiré';
            } else {
                // Changer dislike en like
                $existingReaction->setType('like');
                $likeDislikeRepository->add($existingReaction, true);
                $message = 'Réaction changée en like';
            }
        } else {
            // Créer un nouveau like
            $reaction = new LikeDislike();
            $reaction->setCours($cours);
            $reaction->setUser($user);
            $reaction->setType('like');
            $likeDislikeRepository->add($reaction, true);
            $message = 'Like ajouté';
        }
        
        // Compter les nouvelles statistiques
        $likes = $likeDislikeRepository->countByCoursAndType($cours, 'like');
        $dislikes = $likeDislikeRepository->countByCoursAndType($cours, 'dislike');
        
        return $this->json([
            'success' => true,
            'message' => $message,
            'likes' => $likes,
            'dislikes' => $dislikes,
            'userReaction' => $existingReaction ? $existingReaction->getType() : 'like'
        ]);
    }
    
    #[Route('/courses/{id}/dislike', name: 'course_dislike', methods: ['POST'])]
    public function dislike(
        int $id,
        CoursRepository $coursRepository,
        LikeDislikeRepository $likeDislikeRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $cours = $coursRepository->find($id);
        
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }
        
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a déjà réagi
        $existingReaction = $likeDislikeRepository->findOneBy([
            'cours' => $cours,
            'user' => $user
        ]);
        
        if ($existingReaction) {
            if ($existingReaction->getType() === 'dislike') {
                // Supprimer le dislike
                $likeDislikeRepository->remove($existingReaction, true);
                $message = 'Dislike retiré';
            } else {
                // Changer like en dislike
                $existingReaction->setType('dislike');
                $likeDislikeRepository->add($existingReaction, true);
                $message = 'Réaction changée en dislike';
            }
        } else {
            // Créer un nouveau dislike
            $reaction = new LikeDislike();
            $reaction->setCours($cours);
            $reaction->setUser($user);
            $reaction->setType('dislike');
            $likeDislikeRepository->add($reaction, true);
            $message = 'Dislike ajouté';
        }
        
        // Compter les nouvelles statistiques
        $likes = $likeDislikeRepository->countByCoursAndType($cours, 'like');
        $dislikes = $likeDislikeRepository->countByCoursAndType($cours, 'dislike');
        
        return $this->json([
            'success' => true,
            'message' => $message,
            'likes' => $likes,
            'dislikes' => $dislikes,
            'userReaction' => $existingReaction ? $existingReaction->getType() : 'dislike'
        ]);
    }

    #[Route('/courses/{id}/favorite', name: 'course_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(
        int $id,
        CoursRepository $coursRepository,
        LikeDislikeRepository $likeDislikeRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $cours = $coursRepository->find($id);
        
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }
        
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a déjà ce cours en favori
        $existingFavorite = $likeDislikeRepository->findOneBy([
            'cours' => $cours,
            'user' => $user,
            'type' => 'favorite'
        ]);
        
        $isFavorite = false;
        $message = '';
        
        if ($existingFavorite) {
            // Retirer des favoris
            $likeDislikeRepository->remove($existingFavorite, true);
            $isFavorite = false;
            $message = 'Retiré des favoris';
        } else {
            // Ajouter aux favoris
            $favorite = new LikeDislike();
            $favorite->setCours($cours);
            $favorite->setUser($user);
            $favorite->setType('favorite');
            $likeDislikeRepository->add($favorite, true);
            $isFavorite = true;
            $message = 'Ajouté aux favoris';
        }
        
        return $this->json([
            'success' => true,
            'message' => $message,
            'isFavorite' => $isFavorite
        ]);
    }

    #[Route('/courses/recommendation/search', name: 'course_recommendation_search', methods: ['GET'])]
    public function searchRecommendations(
        Request $request,
        CoursRepository $coursRepository,
        EvaluationRepository $evaluationRepository,
        LikeDislikeRepository $likeDislikeRepository
    ): JsonResponse
    {
        $interest = $request->query->get('interest', '');
        
        if (empty($interest)) {
            return $this->json(['results' => []]);
        }
        
        // Chercher les cours correspondants
        $qb = $coursRepository->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->andWhere('c.titre LIKE :interest OR c.matiere LIKE :interest OR c.description LIKE :interest')
            ->setParameter('statut', 'publié')
            ->setParameter('interest', '%' . $interest . '%')
            ->setMaxResults(5);
            
        $courses = $qb->getQuery()->getResult();
        
        $results = [];
        foreach ($courses as $course) {
            $avg = $evaluationRepository->findAverageByCours($course);
            $likes = $likeDislikeRepository->countByCoursAndType($course, 'like');
            
            $results[] = [
                'id' => $course->getId(),
                'title' => $course->getTitre(),
                'image' => 'assets/images/courses/4by3/08.jpg', // Placeholder ou image réelle
                'rating' => $avg ? round($avg, 1) : null,
                'likes' => $likes,
                'level' => $course->getNiveau(),
                'author' => $course->getEmailProf()
            ];
        }
        
        return $this->json(['results' => $results]);
    }
    
    #[Route('/courses/{id}/rate', name: 'course_rate', methods: ['POST'])]
    public function rate(
        Request $request,
        int $id,
        CoursRepository $coursRepository,
        EvaluationRepository $evaluationRepository
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $cours = $coursRepository->find($id);
        
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        $note = $data['note'] ?? null;
        
        if ($note === null || $note < 0 || $note > 100) {
            return $this->json(['error' => 'Note invalide. Doit être entre 0 et 100'], 400);
        }
        
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a déjà évalué
        $existingEvaluation = $evaluationRepository->findOneBy([
            'cours' => $cours,
            'user' => $user
        ]);
        
        if ($existingEvaluation) {
            $existingEvaluation->setNote($note);
            $existingEvaluation->setDateEvaluation(new \DateTime());
            $evaluationRepository->add($existingEvaluation, true);
            $message = 'Évaluation mise à jour';
        } else {
            $evaluation = new Evaluation();
            $evaluation->setCours($cours);
            $evaluation->setUser($user);
            $evaluation->setNote($note);
            $evaluation->setDateEvaluation(new \DateTime());
            $evaluationRepository->add($evaluation, true);
            $message = 'Évaluation ajoutée';
        }
        
        // Calculer la nouvelle moyenne
        $averageRating = $evaluationRepository->findAverageByCours($cours);
        
        return $this->json([
            'success' => true,
            'message' => $message,
            'averageRating' => $averageRating,
            'userEvaluation' => $note
        ]);
    }
    
    // Vos méthodes PDF existantes restent identiques
    #[Route('/courses/{id}/pdf/cours', name: 'course_pdf')]
    public function generateCoursePdf(
        int $id,
        CoursRepository $coursRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        if (!$cours || $cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Cours non disponible');
        }
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML avec le template existant
        $html = $this->renderView('cours_admin/export_single_cours_pdf.html.twig', [
            'cours' => $cours,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        
        // Définir la taille et l'orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Rendre le PDF
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('cours-%s-%s.pdf', 
            $cours->getId(),
            date('Y-m-d')
        );
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
    
    #[Route('/courses/{id}/pdf/ressources', name: 'course_ressources_pdf')]
    public function generateCourseRessourcesPdf(
        int $id,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        if (!$cours || $cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Cours non disponible');
        }
        
        // Récupérer les ressources
        $ressources = $ressourceRepository->findBy(['cours' => $cours]);
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Vérifier si on utilise le template avancé ou simple
        $template = 'cours_admin/export_ressources_advanced_pdf.html.twig';
        
        // Générer le HTML avec le template existant
        $html = $this->renderView($template, [
            'cours' => $cours,
            'ressources' => $ressources,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('ressources-cours-%s-%s.pdf', 
            $cours->getId(),
            date('Y-m-d')
        );
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
    
    #[Route('/courses/{id}/ressource/{ressourceId}/pdf', name: 'single_ressource_pdf')]
    public function generateSingleRessourcePdf(
        int $id,
        int $ressourceId,
        CoursRepository $coursRepository,
        RessourcePedagogiqueRepository $ressourceRepository
    ): Response
    {
        // Récupérer le cours
        $cours = $coursRepository->find($id);
        
        if (!$cours || $cours->getStatut() !== 'publié') {
            throw $this->createNotFoundException('Cours non disponible');
        }
        
        // Récupérer la ressource
        $ressource = $ressourceRepository->find($ressourceId);
        
        if (!$ressource || $ressource->getCours()->getId() !== $cours->getId()) {
            throw $this->createNotFoundException('Ressource non disponible');
        }
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML avec le template existant
        $html = $this->renderView('cours_admin/export_single_ressource_pdf.html.twig', [
            'ressource' => $ressource,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('ressource-%s-%s.pdf', 
            $ressource->getId(),
            date('Y-m-d')
        );
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }
    
    #[Route('/courses/pdf/all', name: 'all_courses_pdf')]
    public function generateAllCoursesPdf(
        CoursRepository $coursRepository
    ): Response
    {
        // Récupérer seulement les cours PUBLIÉS
        $coursPublies = $coursRepository->findBy(['statut' => 'publié']);
        
        if (empty($coursPublies)) {
            throw $this->createNotFoundException('Aucun cours disponible');
        }
        
        // Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Générer le HTML avec le template existant
        $html = $this->renderView('cours_admin/export_pdf.html.twig', [
            'coursList' => $coursPublies,
            'date_export' => new \DateTime(),
        ]);
        
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $fileName = sprintf('catalogue-cours-%s.pdf', date('Y-m-d'));
        
        // Retourner la réponse PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate'
            ]
        );
    }

    #[Route('/cours/qr-data/{id}', name: 'course_qr_data_json')]
    public function qrDataJson(int $id, CoursRepository $coursRepository, RessourcePedagogiqueRepository $ressourceRepository): JsonResponse
    {
        $course = $coursRepository->find($id);
        
        if (!$course) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }
        
        $resources = $ressourceRepository->findBy(['cours' => $course]);
        
        // Compter les ressources par type
        $images = array_filter($resources, fn($r) => $r->getFileName() && in_array(strtolower(pathinfo($r->getFileName(), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']));
        $videos = array_filter($resources, fn($r) => $r->getFileName() && in_array(strtolower(pathinfo($r->getFileName(), PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi', 'mkv']));
        $pdfs = array_filter($resources, fn($r) => $r->getFileName() && strtolower(pathinfo($r->getFileName(), PATHINFO_EXTENSION)) == 'pdf');
        $audios = array_filter($resources, fn($r) => $r->getFileName() && in_array(strtolower(pathinfo($r->getFileName(), PATHINFO_EXTENSION)), ['mp3', 'wav', 'ogg']));
        $links = array_filter($resources, fn($r) => $r->getUrl());
        
        // Préparer les données
        $data = [
            'id' => $course->getId(),
            'titre' => $course->getTitre(),
            'description' => strip_tags($course->getDescription()),
            'matiere' => $course->getMatiere() ?? 'Général',
            'niveau' => $course->getNiveau() ?? 'Tous niveaux',
            'duree' => $course->getDuree() ?? 0,
            'formateur' => $course->getEmailProf() ?? 'Path2Learn',
            'date' => $course->getDateCreation()?->format('d/m/Y') ?? '',
            'statistiques' => [
                'total' => count($resources),
                'pdfs' => count($pdfs),
                'videos' => count($videos),
                'images' => count($images),
                'audios' => count($audios),
                'liens' => count($links)
            ],
            'ressources_details' => array_map(function($r) {
                if ($r->getFileName()) {
                    $ext = strtolower(pathinfo($r->getFileName(), PATHINFO_EXTENSION));
                    $type = match($ext) {
                        'pdf' => 'pdf',
                        'mp4', 'mov', 'avi', 'mkv' => 'video',
                        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
                        'mp3', 'wav', 'ogg' => 'audio',
                        default => 'fichier'
                    };
                    return [
                        'titre' => $r->getTitre() ?? $r->getFileName(),
                        'type' => $type,
                        'extension' => $ext
                    ];
                } else {
                    return [
                        'titre' => $r->getTitre() ?? 'Lien externe',
                        'type' => 'lien',
                        'url' => $r->getUrl()
                    ];
                }
            }, array_slice($resources, 0, 10)) // Limiter à 10 ressources
        ];
        
        return $this->json($data);
    }

    #[Route('/courses/{id}/rate-with-reason', name: 'course_rate_with_reason', methods: ['POST'])]
    public function rateWithReason(
        Request $request,
        int $id,
        CoursRepository $coursRepository,
        EvaluationRepository $evaluationRepository,
        MailerService $mailerService,  // ← AJOUTEZ ICI
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $cours = $coursRepository->find($id);
        
        if (!$cours) {
            return $this->json(['error' => 'Cours non trouvé'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        $note = $data['note'] ?? null;
        $reason = $data['reason'] ?? null;
        $customReason = $data['customReason'] ?? null;
        
        if ($note === null || $note < 0 || $note > 100) {
            return $this->json(['error' => 'Note invalide. Doit être entre 0 et 100'], 400);
        }
        
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a déjà évalué
        $existingEvaluation = $evaluationRepository->findOneBy([
            'cours' => $cours,
            'user' => $user
        ]);
        
        if ($existingEvaluation) {
            $existingEvaluation->setNote($note);
            $existingEvaluation->setDateEvaluation(new \DateTime());
            $evaluation = $existingEvaluation;
        } else {
            $evaluation = new Evaluation();
            $evaluation->setCours($cours);
            $evaluation->setUser($user);
            $evaluation->setNote($note);
            $evaluation->setDateEvaluation(new \DateTime());
            $entityManager->persist($evaluation);
        }
        
        // Si note < 30 et raison fournie, enregistrer la raison
        if ($note < 30 && $reason) {
            $badRatingReason = new BadRatingReason();
            $badRatingReason->setReason($reason);
            $badRatingReason->setCustomReason($customReason);
            $badRatingReason->setEvaluation($evaluation);
            $badRatingReason->setCreatedAt(new \DateTime());
            
            $entityManager->persist($badRatingReason);
            
            // ENVOYER L'EMAIL AVEC VOTRE SERVICE
            try {
                $mailerService->sendBadRatingNotification($cours, $evaluation, $badRatingReason);
                // Log pour debug
                error_log('Email envoyé avec succès via MailerService');
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas bloquer la réponse
                error_log('Erreur envoi email: ' . $e->getMessage());
            }
        }
        
        $entityManager->flush();
        
        // Calculer la nouvelle moyenne
        $averageRating = $evaluationRepository->findAverageByCours($cours);
        
        return $this->json([
            'success' => true,
            'message' => $note < 30 ? 'Merci pour votre retour. Le formateur sera notifié.' : 'Évaluation enregistrée',
            'averageRating' => $averageRating,
            'userEvaluation' => $note
        ]);
    }
    #[Route('/test-mailer-simple', name: 'test_mailer_simple')]
    public function testMailerSimple(MailerInterface $mailer): JsonResponse
    {
        try {
            $email = (new Email())
                ->from('pathlearnnotifications@gmail.com')
                ->to('nour123mbarki456@gmail.com') // Your email
                ->subject('Test Path2Learn')
                ->html('<h1>Test réussi!</h1><p>La configuration mailer fonctionne correctement.</p>');
            
            $mailer->send($email);
            
            return $this->json([
                'success' => true,
                'message' => 'Email envoyé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}