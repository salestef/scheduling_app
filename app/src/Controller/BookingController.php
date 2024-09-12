<?php
// src/Controller/BookingController.php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
//    #[Route(path: '/booking', name: 'booking_index', methods: ['GET'])]
//    public function index(): Response
//    {
//        return $this->render('booking/index.html.twig');
//    }

    #[Route('/booking', name: 'booking_index')]
    public function booking(EntityManagerInterface $em): Response
    {
        $availableSlots = $em->getRepository(Slot::class)->findBy(['isAvailable' => true]);

        return $this->render('booking/booking.html.twig', [
            'slots' => $availableSlots,
        ]);
    }

    #[Route('/booking/{slot}', name: 'booking_reserve')]
    public function reserve(Slot $slot, EntityManagerInterface $em): Response
    {
        $reservation = new Reservation();
        $reservation->setUser($this->getUser());
        $reservation->setSlot($slot);
        $reservation->setStatus('pending');

        $em->persist($reservation);
        $em->flush();

        return $this->redirectToRoute('booking_index');
    }
}
