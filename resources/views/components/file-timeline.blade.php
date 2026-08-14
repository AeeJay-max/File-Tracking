{{--
    ┌──────────────────────────────────────────────────────────────────┐
    │  <x-file-timeline>  — Horizontal Workflow Timeline               │
    │  Usage:                                                          │
    │    <x-file-timeline                                              │
    │        :movements="$file->movements"                             │
    │        :current-user-id="$file->current_user_id"                │
    │        :viewer-dept-id="auth()->user()->department_id"           │
    │        :is-super-admin="auth()->user()->role === 'super_admin'"  │
    │    />                                                            │
    │                                                                  │
    │  Department scoping rules:                                       │
    │    - Super Admin sees all movements (no filtering)               │
    │    - Admin sees only movements involving their dept;             │
    │      cross-dept entry/exit shown as a single "Dept Received"     │
    │      or "Transferred to Dept" node                               │
    └──────────────────────────────────────────────────────────────────┘
--}}
@props([
    'movements',
    'currentUserId'  => null,
    'viewerDeptId'   => null,
    'isSuperAdmin'   => false,
])

@php
    $allMoves = $movements->load([
        'fromUser', 'toUser', 'fromDept', 'toDept',
    ])->sortBy('created_at')->values();

    /*
     * ── Department scoping ─────────────────────────────────────────
     * Super Admin   → see everything ($isSuperAdmin = true)
     * Department Admin / User  → see only movements touching their dept,
     *   but collapse cross-dept boundaries into a single summary node.
     *
     * Scoped view rules:
     *  1. Include a movement if from_department OR to_department equals viewer's dept.
     *  2. For the first movement INTO this dept from outside → show as "Received from Dept X".
     *  3. For the first movement OUT of this dept to outside → show as "Transferred to Dept Y".
     *  4. Do NOT show internal movements of any other department.
     */
    if ($isSuperAdmin || !$viewerDeptId) {
        $moves = $allMoves;
    } else {
        // Build a scoped view: only movements where from_dept or to_dept == viewer dept
        $moves = $allMoves->filter(function ($move) use ($viewerDeptId) {
            return (int)($move->from_department ?? 0) === (int)$viewerDeptId
                || (int)($move->to_department   ?? 0) === (int)$viewerDeptId;
        })->values();
    }
@endphp

{{-- ── Empty state ─────────────────────────────────────────── --}}
@if($moves->isEmpty())
<div class="tl-empty">
    <div class="tl-empty-icon"><i class="fa-solid fa-diagram-project"></i></div>
    <p>No movement history recorded yet.</p>
</div>

@else

{{-- ── Horizontal scrollable track ────────────────────────── --}}
<div class="tl-outer" role="region" aria-label="File Journey Timeline">
    <div class="tl-track">

        @foreach($moves as $idx => $move)
        @php
            /* ── Determine who the card represents ── */
            $isCreated  = $move->action === 'created';
            $isDeptMove = $move->fromDept && $move->toDept
                          && (int) $move->fromDept->id !== (int) $move->toDept->id;

            if ($isCreated) {
                $person    = $move->fromUser;
                $deptLabel = $move->fromDept?->name ?? '—';
                $typeKey   = 'created';
            } else {
                $person    = $move->toUser;
                $deptLabel = $move->toDept?->name ?? '—';
                $typeKey   = $isDeptMove ? 'dept' : 'transfer';
            }

            $isDeptCard = !$person && $isDeptMove;   // department-only node

            /* ── Is this the current holder? ── */
            $isCurrent = false;
            if ($currentUserId && $move === $moves->last()) {
                if ($isCreated && $person && (int)$person->id === (int)$currentUserId) {
                    $isCurrent = true;
                } elseif (!$isCreated && $person && (int)$person->id === (int)$currentUserId) {
                    $isCurrent = true;
                }
            }

            /* ── Designation label ── */
            $designation = $person?->designation?->name ?? null;
            $designation = ($designation && $designation !== '—') ? $designation : null;

            /* ── Action label ── */
            $actionLabel = match($move->action) {
                'created'     => 'Created',
                'transferred' => 'Transferred',
                'approved'    => 'Approved',
                'rejected'    => 'Returned',
                default       => ucfirst($move->action),
            };
        @endphp

        {{-- ── Arrow connector (between cards, not before first) ── --}}
        @if($idx > 0)
        <div class="tl-arrow" aria-hidden="true">
            <div class="tl-arrow-line"></div>
            <div class="tl-arrow-head">&#9658;</div>
        </div>
        @endif

        {{-- ── Card ─────────────────────────────────────────────── --}}
        <div class="tl-card tl-card-{{ $typeKey }} {{ $isCurrent ? 'tl-card-current' : '' }}"
             data-idx="{{ $idx }}"
             style="animation-delay: {{ $idx * 80 }}ms">

            {{-- Current Holder badge (top of card) --}}
            @if($isCurrent)
            <div class="tl-current-badge">
                <i class="fa-solid fa-circle-check"></i> Current Holder
            </div>
            @endif

            {{-- Step number --}}
            <div class="tl-step-num">{{ $idx + 1 }}</div>

            {{-- Avatar --}}
            <div class="tl-avatar-wrap">
                @if(!$isDeptCard && $person && $person->photo_url)
                    <img src="{{ $person->photo_url }}"
                         alt="{{ $person->name }}"
                         class="tl-avatar"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="tl-avatar-initials tl-av-{{ $typeKey }}" style="display:none;">
                        {{ $person->initials }}
                    </div>
                @elseif(!$isDeptCard && $person)
                    <div class="tl-avatar-initials tl-av-{{ $typeKey }}">{{ $person->initials }}</div>
                @else
                    <div class="tl-avatar-initials tl-av-dept">
                        <i class="fa-solid fa-building fa-sm"></i>
                    </div>
                @endif
            </div>

            {{-- Name --}}
            <div class="tl-name">
                {{ $person?->name ?? $deptLabel }}
            </div>

            {{-- Designation --}}
            @if($designation)
            <div class="tl-desig">{{ $designation }}</div>
            @endif

            {{-- Department --}}
            <div class="tl-dept">
                <i class="fa-solid fa-building-columns fa-xs"></i>
                {{ $deptLabel }}
            </div>

            {{-- Date + Time --}}
            <div class="tl-datetime">
                <span class="tl-date">
                    <i class="fa-regular fa-calendar fa-xs"></i>
                    {{ $move->created_at->format('d M Y') }}
                </span>
                <span class="tl-time">
                    <i class="fa-regular fa-clock fa-xs"></i>
                    {{ $move->created_at->format('h:i A') }}
                </span>
            </div>

            {{-- Remarks — each card shows its own movement remark, preserved as entered --}}
            <div class="tl-remarks">
                <i class="fa-solid fa-comment-dots fa-xs"></i>
                @if($move->remarks)
                    {!! nl2br(e($move->remarks)) !!}
                @else
                    <span class="text-muted fst-italic">No remarks</span>
                @endif
            </div>

            {{-- Action badge --}}
            <div class="tl-action-badge tl-badge-{{ $typeKey }}">
                @if($move->action === 'created')       <i class="fa-solid fa-file-circle-plus fa-xs me-1"></i>
                @elseif($move->action === 'transferred')<i class="fa-solid fa-paper-plane fa-xs me-1"></i>
                @elseif($move->action === 'approved')   <i class="fa-solid fa-circle-check fa-xs me-1"></i>
                @elseif($move->action === 'rejected')   <i class="fa-solid fa-rotate-left fa-xs me-1"></i>
                @else                                   <i class="fa-solid fa-circle-dot fa-xs me-1"></i>
                @endif
                {{ $actionLabel }}
            </div>

        </div>
        {{-- ── End card ─────────────────────────────────────────── --}}

        @endforeach
    </div>{{-- /.tl-track --}}
</div>{{-- /.tl-outer --}}

{{-- ── Step count label ──────────────────────────────────── --}}
<div class="tl-footer">
    <i class="fa-solid fa-route fa-xs me-1"></i>
    {{ $moves->count() }} {{ Str::plural('movement', $moves->count()) }} recorded
    &nbsp;·&nbsp;
    <span class="text-muted" style="font-size:.75rem;">Scroll horizontally to see full journey</span>
</div>

@endif

{{-- ═══════════════ STYLES (injected once per page) ═══════════════ --}}
@once
@push('styles')
<style>
/* ================================================================
   FILE JOURNEY — Horizontal Workflow Timeline
   Desktop: horizontal linked-list  |  Mobile: vertical stacked
   ================================================================ */

/* ── Outer scroll container ─────────────────────────────────────── */
.tl-outer {
    overflow-x: auto;
    overflow-y: visible;
    padding: 2.5rem 1rem 1.5rem;
    /* custom scrollbar */
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.tl-outer::-webkit-scrollbar { height: 5px; }
.tl-outer::-webkit-scrollbar-track { background: transparent; }
.tl-outer::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

/* ── Flex track — holds cards + arrows ─────────────────────────── */
.tl-track {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 0;
    min-width: max-content;
}

/* ── Arrow connector ────────────────────────────────────────────── */
.tl-arrow {
    display: flex;
    align-items: center;
    align-self: center;   /* vertically centred on the cards */
    flex-shrink: 0;
    padding: 0 2px;
    margin-top: -24px;    /* nudge up to align with card mid-point */
}
.tl-arrow-line {
    width: 48px;
    height: 2px;
    background: linear-gradient(90deg, #94a3b8 0%, #64748b 100%);
    position: relative;
}
/* animated pulse on the line */
.tl-arrow-line::after {
    content: '';
    position: absolute;
    top: -2px; left: 0;
    height: 6px;
    width: 20px;
    background: rgba(255,255,255,.6);
    border-radius: 999px;
    animation: tl-pulse 2.4s ease-in-out infinite;
}
.tl-arrow-head {
    font-size: .85rem;
    color: #64748b;
    margin-left: -2px;
    line-height: 1;
}

@keyframes tl-pulse {
    0%   { left: -20px; opacity: 0; }
    20%  { opacity: .9; }
    80%  { opacity: .6; }
    100% { left: calc(100% + 4px); opacity: 0; }
}

/* ── Card base ──────────────────────────────────────────────────── */
.tl-card {
    width: 200px;
    min-height: 240px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 16px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    box-shadow:
        0 1px 3px rgba(15,23,42,.06),
        0 4px 16px rgba(15,23,42,.04);
    transition:
        transform .22s cubic-bezier(.34,1.56,.64,1),
        box-shadow .22s ease,
        border-color .18s ease;
    /* fade-in on load */
    opacity: 0;
    animation: tl-fadein .45s ease forwards;
    flex-shrink: 0;
}

@keyframes tl-fadein {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.tl-card:hover {
    transform: translateY(-6px) scale(1.025);
    box-shadow:
        0 8px 28px rgba(0,90,43,.14),
        0 2px 8px rgba(0,90,43,.07);
    border-color: #c1d7c7;
    z-index: 2;
}

/* ── Card type colour coding ────────────────────────────────────── */
/* Created — gold top bar */
.tl-card-created {
    border-top: 4px solid #c69214;
}
/* Same-dept transfer — green top bar */
.tl-card-transfer {
    border-top: 4px solid #005a2b;
}
/* Cross-dept transfer — dark green top bar */
.tl-card-dept {
    border-top: 4px solid #00421f;
}
/* Current holder — green border + glow */
.tl-card-current {
    border: 2px solid #22c55e !important;
    border-top: 4px solid #16a34a !important;
    background: linear-gradient(160deg, #f0fdf4 0%, #dcfce7 100%);
    box-shadow:
        0 0 0 3px rgba(34,197,94,.18),
        0 8px 28px rgba(22,163,74,.16);
}
.tl-card-current:hover {
    box-shadow:
        0 0 0 4px rgba(34,197,94,.26),
        0 12px 36px rgba(22,163,74,.22);
}

/* ── Current holder badge ───────────────────────────────────────── */
.tl-current-badge {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
    color: #fff;
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 3px 12px;
    border-radius: 999px;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(22,163,74,.30);
}

/* ── Step number ────────────────────────────────────────────────── */
.tl-step-num {
    position: absolute;
    top: 10px;
    left: 12px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    font-size: .65rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.tl-card-current .tl-step-num {
    background: #16a34a;
    color: #fff;
}

/* ── Avatar ─────────────────────────────────────────────────────── */
.tl-avatar-wrap { margin-bottom: 10px; }
.tl-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    object-fit: cover;
    border: 2.5px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.tl-avatar-initials {
    width: 52px; height: 52px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; font-weight: 800; color: #fff;
    border: 2.5px solid rgba(255,255,255,.25);
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
.tl-av-created  { background: linear-gradient(135deg, #c69214 0%, #9a6f00 100%); }
.tl-av-transfer { background: linear-gradient(135deg, #005a2b 0%, #00421f 100%); }
.tl-av-dept     { background: linear-gradient(135deg, #00421f 0%, #0b120c 100%); }
.tl-card-current .tl-avatar { border-color: #22c55e; }

/* ── Text blocks ────────────────────────────────────────────────── */
.tl-name {
    font-weight: 800;
    font-size: .88rem;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 2px;
    word-break: break-word;
}
.tl-desig {
    font-size: .72rem;
    color: #c69214;
    font-weight: 600;
    margin-bottom: 3px;
}
.tl-dept {
    font-size: .72rem;
    color: #64748b;
    margin-bottom: 6px;
    word-break: break-word;
}
.tl-dept i { color: #94a3b8; }

.tl-datetime {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 8px;
}
.tl-date, .tl-time {
    font-size: .70rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.tl-date i, .tl-time i { color: #cbd5e1; }

.tl-remarks {
    font-size: .71rem;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 5px 8px;
    margin-bottom: 8px;
    width: 100%;
    text-align: left;
    word-break: break-word;
    line-height: 1.4;
}
.tl-card-current .tl-remarks {
    background: rgba(255,255,255,.65);
    border-color: #bbf7d0;
}

/* ── Action badge ───────────────────────────────────────────────── */
.tl-action-badge {
    display: inline-flex;
    align-items: center;
    font-size: .68rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    letter-spacing: .04em;
    margin-top: auto;   /* push to card bottom */
}
.tl-badge-created  { background: #fef9c3; color: #854d0e; }
.tl-badge-transfer { background: #e8f5e9; color: #005a2b; }
.tl-badge-dept     { background: #fef3c7; color: #92400e; }

/* ── Footer label ───────────────────────────────────────────────── */
.tl-footer {
    padding: .5rem 1rem .25rem;
    font-size: .78rem;
    color: #94a3b8;
    border-top: 1px solid #f1f5f9;
    margin-top: .25rem;
}

/* ── Empty state ────────────────────────────────────────────────── */
.tl-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}
.tl-empty-icon {
    font-size: 2.5rem;
    margin-bottom: .75rem;
    opacity: .4;
}
.tl-empty p { font-size: .9rem; margin: 0; }

/* ================================================================
   MOBILE — convert to vertical stacked timeline (≤ 768 px)
   ================================================================ */
@media (max-width: 768px) {
    .tl-outer {
        overflow-x: visible;
        padding: 1rem .25rem;
    }
    .tl-track {
        flex-direction: column;
        align-items: stretch;
        min-width: unset;
        gap: 0;
    }

    /* Vertical arrow */
    .tl-arrow {
        flex-direction: column;
        align-items: center;
        align-self: flex-start;
        margin-top: 0;
        margin-left: 34px;   /* align under avatar centre */
        padding: 0;
    }
    .tl-arrow-line {
        width: 2px;
        height: 32px;
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
    }
    .tl-arrow-line::after {
        width: 6px;
        height: 12px;
        top: 0; left: -2px;
        animation: tl-pulse-v 2.4s ease-in-out infinite;
    }
    @keyframes tl-pulse-v {
        0%   { top: -12px; opacity: 0; }
        20%  { opacity: .8; }
        80%  { opacity: .5; }
        100% { top: calc(100% + 4px); opacity: 0; }
    }
    .tl-arrow-head {
        transform: rotate(90deg);
        margin-left: 0;
        margin-top: -2px;
    }

    /* Full-width horizontal card layout on mobile */
    .tl-card {
        width: 100%;
        flex-direction: row;
        align-items: flex-start;
        text-align: left;
        min-height: unset;
        padding: 14px;
        gap: 14px;
    }
    .tl-avatar-wrap  { margin-bottom: 0; flex-shrink: 0; }
    .tl-current-badge {
        top: -11px; left: 16px; transform: none;
    }
    .tl-step-num { top: 8px; left: 8px; }
    .tl-datetime { flex-direction: row; gap: 12px; }
    .tl-date, .tl-time { justify-content: flex-start; }
    .tl-action-badge { margin-top: 6px; }
}
</style>
@endpush
@endonce
