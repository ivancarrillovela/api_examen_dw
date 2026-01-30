<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column]
    private ?int $maxParticipants = null;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'activity', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $playlist;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'activity', orphanRemoval: true)]
    private Collection $bookings;

    public function __construct()
    {
        $this->playlist = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;

        return $this;
    }

    public function getPlaylist(): Collection
    {
        return $this->playlist;
    }

    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    // Añade una canción a la colección de canciones de la actividad
    public function addPlaylist(Song $playlist): static
    {
        if (!$this->playlist->contains($playlist)) {
            $this->playlist->add($playlist);
            $playlist->setActivity($this);
        }

        return $this;
    }
    // Elimina una canción de la colección de canciones de la actividad
    public function removePlaylist(Song $playlist): static
    {
        if ($this->playlist->removeElement($playlist)) {
            // Si la canción está asociada a la actividad la desvincula
            if ($playlist->getActivity() === $this) {
                $playlist->setActivity(null);
            }
        }

        return $this;
    }

    // Añade una reserva a la colección de reservas de la actividad
    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setActivity($this);
        }

        return $this;
    }

    // Elimina una reserva de la colección de reservas de la actividad
    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // Si la reserva está asociada a la actividad la desvincula
            if ($booking->getActivity() === $this) {
                $booking->setActivity(null);
            }
        }

        return $this;
    }
}