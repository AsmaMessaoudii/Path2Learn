<?php

namespace App\Controller;

use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QRCodeController extends AbstractController
{
    #[Route('/cours/qrcode/{id}', name: 'course_qrcode')]
    public function generateCourseQR(int $id, CoursRepository $coursRepository): Response
    {
        $course = $coursRepository->find($id);
        
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé');
        }
        
        $courseUrl = $this->generateUrl('course_show', ['id' => $id], true);
        
        // UNIQUEMENT QR SERVER - PAS DE GOOGLE
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($courseUrl);
        
        // Redirection directe vers QR Server (solution la plus simple)
        return $this->redirect($qrUrl);
    }
    
    #[Route('/cours/qrcode/{id}/telecharger', name: 'course_qrcode_download')]
    public function downloadCourseQR(int $id, CoursRepository $coursRepository): Response
    {
        $course = $coursRepository->find($id);
        
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé');
        }
        
        $courseUrl = $this->generateUrl('course_show', ['id' => $id], true);
        
        // QR Server pour téléchargement
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($courseUrl);
        
        // Redirection directe
        return $this->redirect($qrUrl);
    }
}