<?php

namespace App\Controller;

use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/logs')]
class LogController extends AbstractController
{
    #[Route('/', name: 'app_log_index', methods: ['GET'])]
    public function index(Request $request, LogRepository $logRepository): Response
    {
        $typeFilter = $request->query->get('type');
        $logs = $logRepository->findByType($typeFilter);

        // Get unique log types for the dropdown
        $logTypes = $logRepository->findAllTypes();

        return $this->render('log/index.html.twig', [
            'logs' => $logs,
            'typeFilter' => $typeFilter,
            'logTypes' => $logTypes
        ]);
    }
}
