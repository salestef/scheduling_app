<?php

namespace App\Slot;

use App\Entity\Reservation;
use App\Entity\Slot;
use App\Enum\StatusEnum;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    public function __construct(
        public readonly EntityManagerInterface $em,
    )
    {
    }

    public function make(Slot $slot): void
    {
        if($slot->getStatus() !== StatusEnum::OPEN->value){
            $product = $slot->getProduct();

            $reservation = new Reservation();

            $reservation
                ->setSlot($slot)
                ->setUser($slot->getUser())
                ->setPrice($product->getPrice())
                ->setStatus($slot->getStatus()); // Status rezervacije

            $this->em->persist($reservation);

            $this->em->flush();
        }
    }
}