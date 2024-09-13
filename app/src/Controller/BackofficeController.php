<?php
// src/Controller/BackofficeController.php
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Slot;
use App\Entity\User;
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
    )
    {
    }

//    #[Route('/backoffice', name: 'backoffice_index')]
//    public function index(): Response
//    {
//        return $this->render('backoffice/index.html.twig');
//    }

    #[Route('/backoffice', name: 'backoffice_index')]
    public function schedule(Request $request, EntityManagerInterface $em): Response
    {
        // Prikaz svih termina
        $slots = $em->getRepository(Slot::class)->findAll();

        // Dodavanje novog termina
        if ($request->isMethod('POST')) {
            $slot = new Slot();
            $slot->setDate(new \DateTime($request->get('date')));
            $slot->setStartTime(new \DateTime($request->get('start_time')));
            $slot->setEndTime(new \DateTime($request->get('end_time')));
            $slot->setIsAvailable(true);
            $slot->setCreatedBy($this->getUser()); // Admin koji je kreirao

            $em->persist($slot);
            $em->flush();

            return $this->redirectToRoute('backoffice_index');
        }

        return $this->render('backoffice/schedule.html.twig', [
            'slots' => $slots,
        ]);
    }

    // Dodajemo nove rute za dodavanje, uređivanje i brisanje slotova
    #[Route('/backoffice/save-slot', name: 'save_slot', methods: ['POST'])]
    public function saveSlot(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $slotId = $request->get('id');
        $slot = $slotId ? $em->getRepository(Slot::class)->find($slotId) : new Slot();

        $slot->setDate(new \DateTime($request->get('date')));
        $slot->setStartTime(new \DateTime($request->get('start_time')));
        $slot->setEndTime(new \DateTime($request->get('end_time')));
//        $slot->setStatus($request->get('status'));
        $slot->setIsAvailable($request->get('status') === 'confirmed');

        if (!$slotId) {
            $slot->setCreatedBy($this->getUser()); // Admin koji kreira
            $em->persist($slot);
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/backoffice/get-events', name: 'get_events')]
    public function getEvents(EntityManagerInterface $em): JsonResponse
    {
        $slots = $em->getRepository(Slot::class)->findAll();
        $events = [];

        foreach ($slots as $slot) {
            // Proveravamo da li slot ima povezanu rezervaciju
            $reservation = $slot->getReservation();
            if ($reservation) {
                // Setovanje boje na osnovu statusa
                $color = '';
                switch ($reservation->getStatus()) {
                    case 'pending':
                        $color = 'yellow';
                        break;
                    case 'approved':
                        $color = 'green';
                        break;
                    case 'rejected':
                        $color = 'red';
                        break;
                }

                $events[] = [
                    'title' => 'Rezervisan: ' . $reservation->getUser()->getEmail(), // Prikaz korisničkog email-a
                    'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i:s'),
                    'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i:s'),
                    'color' => $color, // Dodajemo boju na osnovu statusa
                    'allDay' => false,
                ];
            } else {
                // Prikazujemo slobodne slotove
                $events[] = [
                    'title' => 'Slobodan slot',
                    'start' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getStartTime()->format('H:i:s'),
                    'end' => $slot->getDate()->format('Y-m-d') . 'T' . $slot->getEndTime()->format('H:i:s'),
                    'color' => 'blue', // Plava boja za slobodne slotove
                    'allDay' => false,
                ];
            }
        }

        return new JsonResponse($events);
    }


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
