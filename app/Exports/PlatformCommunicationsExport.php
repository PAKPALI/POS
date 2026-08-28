<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlatformCommunicationsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private $query) {}
    public function query() { return $this->query; }
    public function headings(): array { return ['Date', 'Entreprise', 'Canal', 'Catégorie', 'Événement', 'Statut', 'Tentatives', 'Destinataire', 'Erreur']; }
    public function map($delivery): array
    {
        return [$delivery->created_at?->format('Y-m-d H:i:s'), $delivery->company?->name, strtoupper($delivery->channel),
            $delivery->category, $delivery->event_type.' #'.$delivery->event_key, $delivery->status,
            $delivery->attempts, $delivery->user?->email ?: $delivery->user?->phone, $delivery->last_error];
    }
}
