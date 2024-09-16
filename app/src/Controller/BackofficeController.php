<?php
// src/Controller/BackofficeController.php
namespace App\Controller;

use App\Entity\Product;
use App\Entity\Reservation;
use App\Entity\Slot;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackofficeController extends AbstractController
{
    public function __construct(
        public readonly UserRepository $userRepository,
        public readonly SlotRepository $slotRepository,
        public readonly ReservationRepository $reservationRepository,
        public readonly EntityManagerInterface $em,
    )
    {
    }

    #[Route('/backoffice', name: 'backoffice_index')]
    public function schedule(Request $request, EntityManagerInterface $em): Response
    {
        // Dohvati sve proizvode za dropdown
        $products = $em->getRepository(Product::class)->findAll();

        return $this->render('backoffice/schedule.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/backoffice/get-slots', name: 'get_slots', methods: ['POST'])]
    public function getSlots(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Prikupimo ID proizvoda iz requesta
        $productId = $request->get('product_id');
        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        // Dohvati slotove povezane sa odabranim proizvodom
        $slots = $em->getRepository(Slot::class)->findBy(['product' => $product]);
        $events = [];

        foreach ($slots as $slot) {
            $color = match ($slot->getStatus()) {
                'open' => 'green',
                'booked' => 'yellow',
                'approved' => 'blue',
                'paid' => 'purple',
                default => 'gray',
            };

            $events[] = [
                'id' => $slot->getId(),  // Dodaj ID slota
                'title' => 'Slot: ' . $slot->getStatus(),
                'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i:s'),
                'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i:s'),
                'color' => $color,
                'extendedProps' => [  // Dodaj proširena svojstva
                    'status' => $slot->getStatus(),
                    'productId' => $slot->getProduct()->getId(),
                ],
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/backoffice/save-slot', name: 'save_slot', methods: ['POST'])]
    public function saveSlot(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $slotId = $request->get('id');
        $slot = $slotId ? $em->getRepository(Slot::class)->find($slotId) : new Slot();

        $startTime = new \DateTime($request->get('start_time'));
        $endTime = new \DateTime($request->get('end_time'));

        // Proveri da li slotovi počinju i završavaju u celim satima
        if ((int) $startTime->format('i') !== 0 || (int) $endTime->format('i') !== 0) {
            return new JsonResponse(['error' => 'Slots must start and end on the hour.'], Response::HTTP_BAD_REQUEST);
        }

        // Preuzmi proizvod na osnovu product_id iz zahteva
        $product = $em->getRepository(Product::class)->find($request->get('product_id'));

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found.'], Response::HTTP_NOT_FOUND);
        }

        // Proveri da li postoji slot u tom terminu za isti proizvod
//        $existingSlot = $em->getRepository(Slot::class)->findOneBy([
//            'product' => $product,
//            'date' => new \DateTime($request->get('date')),
//            'startTime' => $startTime,
//            'endTime' => $endTime,
//        ]);
//
//        if ($existingSlot && $existingSlot->getId() !== $slotId) {
//            return new JsonResponse(['error' => 'Slot already exists for this product.'], Response::HTTP_CONFLICT);
//        }

        // Postavi vrednosti slota
        $slot->setDate(new \DateTime($request->get('date')));
        $slot->setStartTime($startTime);
        $slot->setEndTime($endTime);
        $slot->setStatus($request->get('status'));
        $slot->setProduct($product);

        // Ako slot postane rezervisan (nije "open"), kreiraj rezervaciju
        if ($slot->getStatus() !== 'open') {

            if($slot->getReservations()->count() > 0){
                $reservation = $slot->getReservations()->first();
            }else{
                $reservation = new Reservation();
                $reservation->setSlot($slot);
                $reservation->setUser($slot->getUser());
            }

            $reservation
                ->setPrice($product->getPrice())
                ->setStatus($slot->getStatus()); // Status rezervacije

            $em->persist($reservation);
        }

        if (!$slotId) {
            $slot->setCreatedBy($this->getUser());
            $em->persist($slot);
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }


    #[Route('/backoffice/delete-slot', name: 'delete_slot', methods: ['POST'])]
    public function deleteSlot(Request $request): JsonResponse
    {
        $slotId = $request->request->get('id');
        if (!$slotId) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid slot ID.']);
        }

        // Find and delete all reservations associated with the slot
        $reservations = $this->reservationRepository->findBy(['slot' => $slotId]);

        foreach ($reservations as $reservation) {
            $this->em->remove($reservation);
        }

        // Find and delete the slot
        $slot = $this->slotRepository->find($slotId);
        if ($slot) {
            $this->em->remove($slot);
        }

        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }


//    #[Route('/backoffice', name: 'backoffice_index')]
//    public function index(): Response
//    {
//        return $this->render('backoffice/index.html.twig');
//    }

//    #[Route('/backoffice', name: 'backoffice_index')]
//    public function schedule(Request $request, EntityManagerInterface $em): Response
//    {
//        // Prikaz svih termina
//        $slots = $em->getRepository(Slot::class)->findAll();
//
//        // Dodavanje novog termina
//        if ($request->isMethod('POST')) {
//            $slot = new Slot();
//            $slot->setDate(new \DateTime($request->get('date')));
//            $slot->setStartTime(new \DateTime($request->get('start_time')));
//            $slot->setEndTime(new \DateTime($request->get('end_time')));
//            $slot->setIsAvailable(true);
//            $slot->setCreatedBy($this->getUser()); // Admin koji je kreirao
//
//            $em->persist($slot);
//            $em->flush();
//
//            return $this->redirectToRoute('backoffice_index');
//        }
//
//        return $this->render('backoffice/schedule.html.twig', [
//            'slots' => $slots,
//        ]);
//    }
//
//    // Dodajemo nove rute za dodavanje, uređivanje i brisanje slotova
//    #[Route('/backoffice/save-slot', name: 'save_slot', methods: ['POST'])]
//    public function saveSlot(Request $request, EntityManagerInterface $em): JsonResponse
//    {
//        $slotId = $request->get('id');
//        $slot = $slotId ? $em->getRepository(Slot::class)->find($slotId) : new Slot();
//
//        $slot->setDate(new \DateTime($request->get('date')));
//        $slot->setStartTime(new \DateTime($request->get('start_time')));
//        $slot->setEndTime(new \DateTime($request->get('end_time')));
////        $slot->setStatus($request->get('status'));
//        $slot->setIsAvailable($request->get('status') === 'confirmed');
//
//        if (!$slotId) {
//            $slot->setCreatedBy($this->getUser()); // Admin koji kreira
//            $em->persist($slot);
//        }
//
//        $em->flush();
//
//        return new JsonResponse(['success' => true]);
//    }
//
//    #[Route('/backoffice/get-events', name: 'get_events')]
//    public function getEvents(EntityManagerInterface $em): JsonResponse
//    {
//        $slots = $em->getRepository(Slot::class)->findAll();
//        $events = [];
//
//        foreach ($slots as $slot) {
//            // Proveravamo da li slot ima povezanu rezervaciju
//            $reservation = $slot->getReservation();
//            if ($reservation) {
//                // Setovanje boje na osnovu statusa
//                $color = '';
//                switch ($reservation->getStatus()) {
//                    case 'pending':
//                        $color = 'yellow';
//                        break;
//                    case 'approved':
//                        $color = 'green';
//                        break;
//                    case 'rejected':
//                        $color = 'red';
//                        break;
//                }
//
//                $events[] = [
//                    'title' => 'Rezervisan: ' . $reservation->getUser()->getEmail(), // Prikaz korisničkog email-a
//                    'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i:s'),
//                    'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i:s'),
//                    'color' => $color, // Dodajemo boju na osnovu statusa
//                    'allDay' => false,
//                ];
//            } else {
//                // Prikazujemo slobodne slotove
//                $events[] = [
//                    'title' => 'Slobodan slot',
//                    'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i:s'),
//                    'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i:s'),
//                    'color' => 'blue', // Plava boja za slobodne slotove
//                    'allDay' => false,
//                ];
//            }
//        }
//
//        return new JsonResponse($events);
//    }


    #[Route('/backoffice/users', name: 'backoffice_users')]
    public function users(): Response
    {
        // Dobavljanje svih korisnika sa rolom ROLE_USER
        $users = $this->userRepository->findByRole('ROLE_USER');

        return $this->render('backoffice/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/backoffice/users/{id}/edit', name: 'user_edit')]
    public function edit(User $user, Request $request): Response
    {
        dd('UNDER CONSTRUCTION');
        // logika za editovanje korisnika
    }

    #[Route('/backoffice/users/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('backoffice_users');
    }


    #[Route('/backoffice/price-list', name: 'backoffice_price_list')]
    public function priceList(): Response
    {
        return $this->render('backoffice/price_list.html.twig');
    }

    #[Route('/backoffice/reservations', name: 'backoffice_reservations')]
    public function reservations(EntityManagerInterface $em): Response
    {
        $reservations = $em->getRepository(Reservation::class)->findAll();

        return $this->render('backoffice/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/backoffice/reservations/{id}/approve', name: 'reservation_approve')]
    public function approve(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservation->setStatus('approved');
        $reservation->getSlot()->setIsAvailable(false);

        $em->flush();

        return $this->redirectToRoute('backoffice_reservations');
    }
}
