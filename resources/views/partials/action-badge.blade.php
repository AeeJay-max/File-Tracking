@php
$map = [
    'created'     => ['badge-created',     'Created'],
    'transferred' => ['badge-transferred', 'Transferred'],
    'delivered'   => ['badge-delivered',   'Delivered'],
];
$entry = $map[$action ?? ''] ?? ['badge-draft', ucfirst($action ?? 'Unknown')];
@endphp
<span class="badge-status {{ $entry[0] }}">{{ $entry[1] }}</span>
