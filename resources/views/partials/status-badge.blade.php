@php
$map = [
    'active'             => ['badge-active',      'Active'],
    'archived'           => ['badge-archived',     'Archived'],
    'draft'              => ['badge-draft',        'Draft'],
    'pending_assignment' => ['badge-pending',      'Awaiting Assignment'],
    'completed'          => ['badge-completed',    'Completed'],
];
$entry = $map[$status ?? ''] ?? ['badge-draft', ucfirst(str_replace('_', ' ', $status ?? 'Unknown'))];
@endphp
<span class="badge-status {{ $entry[0] }}">{{ $entry[1] }}</span>
