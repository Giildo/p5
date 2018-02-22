<?php

namespace Core\Entity;

use DateTime;
use DateTimeZone;

class Entity
{
    /**
     * Génère les dates avec l'objet DateTime
     *
     * @param array $dates
     */
    protected function date(array $dates): void
    {
        $timeZone = new DateTimeZone('Europe/Paris');

        foreach ($dates as $date) {
            $this->$date = new DateTime($this->$date);
            $this->$date->setTimeZone($timeZone);
        }
    }

    /**
     * Renvoie le format de date demandé
     *
     * @param DateTime $date
     * @param bool|null $datetime
     * @param null|string $returnFormat
     * @return DateTime|string
     */
    protected function getDate(DateTime $date, ?bool $datetime = true, ?string $returnFormat)
    {
        if ($datetime) {
            return $date;
        } else {
            return $this->dateString($date, $returnFormat);
        }
    }

    /**
     * Renvoie un array avec les différentes parties de la date
     *
     *
     * @var DateTime $datetime
     * @param string $returnFormat
     * @return string
     */
    protected function dateString(DateTime $dateTime, string $returnFormat): string
    {
        setlocale(LC_TIME, "fr_FR.utf8");
        return strftime($returnFormat, $dateTime->getTimestamp());
    }
}
