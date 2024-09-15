<?php
// src/Controller/BookingController.php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends BaseController
{
    #[Route('/booking', name: 'booking_index')]
    public function booking(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Get all available slots
        $availableSlots = $em->getRepository(Slot::class)->findBy(['isAvailable' => true]);

        // Get all reservations for the current user
        $userReservations = $em->getRepository(Reservation::class)->findBy(['user' => $user]);

        // Get all slots reserved by the current user
        $reservedSlots = array_map(function($reservation) {
            return $reservation->getSlot();
        }, $userReservations);

        return $this->renderTemplate('booking/booking.html.twig', [
            'availableSlots' => $availableSlots,
            'userReservations' => $userReservations,
            'reservedSlots' => $reservedSlots,
        ]);
    }

    #[Route('/booking/reserve', name: 'booking_reserve', methods: ['POST'])]
    public function reserve(Request $request, EntityManagerInterface $em): Response
    {
        $slotId = $request->query->get('slot');
        $slot = $em->getRepository(Slot::class)->find($slotId);

        if (!$slot || !$this->getUser()) {
            return new Response('Invalid request', Response::HTTP_BAD_REQUEST);
        }

        $reservation = new Reservation();
        $reservation->setUser($this->getUser());
        $reservation->setSlot($slot);
        $reservation->setStatus('pending');
//        $reservation->setCreatedAt(new \D()); // Ensure createdAt is set

        $em->persist($reservation);

        // Mark the slot as unavailable
        $slot->setIsAvailable(false);

        $em->flush();

        return new Response('Reservation made', Response::HTTP_OK);
    }
}



