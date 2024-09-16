<?php
// src/Controller/BookingController.php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Reservation;
use App\Entity\Slot;
use App\Slot\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends BaseController
{
    public function __construct(
        RequestStack $requestStack,
        private readonly ReservationService $reservationService,
    )
    {
        parent::__construct($requestStack);
    }

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

    #[Route('/booking/product/{id}', name: 'book_product')]
    public function bookProduct(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $product = $em->getRepository(Product::class)->find($id);

        // Get all slots for this product
        $slots = $em->getRepository(Slot::class)->findBy(['product' => $product]);

        // Prepare array to store slot data
        $slotData = [];
        foreach ($slots as $slot) {
            $isReservedByUser = $user && $slot->getUser() === $user;
            $slotData[] = [
                'id' => $slot->getId(),
                'title' => $isReservedByUser ? 'Reserved by You' : 'Available',
                'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i'),
                'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i'),
                'backgroundColor' => $isReservedByUser ? '#dc3545' : '#28a745',
                'borderColor' => $isReservedByUser ? '#dc3545' : '#28a745'
            ];
        }

        return $this->renderTemplate('booking/booking.html.twig', [
            'product' => $product,
            'slots' => $slotData,
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

        // Set the slot as reserved
        $slot->setUser($this->getUser());
        $slot->setStatus('booked');
        $em->flush();

        $this->reservationService->make($slot);

        return new Response('Reservation made', Response::HTTP_OK);
    }
}



