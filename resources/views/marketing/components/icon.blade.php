@php($name = $name ?? 'spark')
<svg class="marketing-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('cart')<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M3 4h2l2.5 11.5a2 2 0 0 0 2 1.5h7.8a2 2 0 0 0 1.9-1.4L21 8H6"/>@break
        @case('boxes')<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>@break
        @case('wallet')<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v16H6.5A2.5 2.5 0 0 1 4 17.5v-11Z"/><path d="M4 7h16v4h-4a2 2 0 0 0 0 4h4"/><circle cx="16" cy="13" r=".5"/>@break
        @case('people')<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0M16 5.5a3 3 0 0 1 0 5.8M18 14a5 5 0 0 1 3 4"/>@break
        @case('team')<circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.3"/><path d="M2.5 20a5.5 5.5 0 0 1 11 0M14 17a5 5 0 0 1 7.5 3"/>@break
        @case('store')<path d="M4 10v10h16V10M3 10l2-6h14l2 6M3 10a3 3 0 0 0 5 0 3 3 0 0 0 5 0 3 3 0 0 0 5 0 3 3 0 0 0 3 0"/><path d="M9 20v-5h6v5"/>@break
        @case('message')<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v7a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 4v-4.3A2.5 2.5 0 0 1 4 12.5v-7Z"/><path d="M8 8h8M8 11h5"/>@break
        @case('file')<path d="M6 3h8l4 4v14H6V3Z"/><path d="M14 3v5h4M9 12h6M9 16h6"/>@break
        @case('shield')<path d="M12 3 20 6v5c0 5-3.4 8.3-8 10-4.6-1.7-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/>@break
        @case('check')<path d="m5 12 4.5 4.5L19 7"/>@break
        @case('layers')<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/>@break
        @case('lock')<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>@break
        @case('archive')<path d="M4 7h16v13H4V7ZM3 4h18v3H3V4ZM9 11h6"/>@break
        @case('basket')<path d="m4 10 2 10h12l2-10M3 10h18M8 10l4-7 4 7M9 14v3M12 14v3M15 14v3"/>@break
        @case('tag')<path d="M4 5v6l9 9 6-6-9-9H4Z"/><circle cx="8" cy="8" r="1"/>@break
        @case('scissors')<circle cx="6" cy="7" r="2.5"/><circle cx="6" cy="17" r="2.5"/><path d="m8 8 12 9M8 16 20 7"/>@break
        @case('truck')<path d="M3 6h11v11H3V6ZM14 10h4l3 3v4h-7M7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>@break
        @case('buildings')<path d="M4 21V5l8-2v18M12 8h8v13M7 8h2M7 12h2M7 16h2M15 12h2M15 16h2"/>@break
        @case('building')<path d="M4 21V5l8-2 8 2v16M8 9h2M14 9h2M8 13h2M14 13h2M8 17h8"/>@break
        @case('cookie')<path d="M20 13.5A7.5 7.5 0 0 1 10.5 4 7.5 7.5 0 1 0 20 13.5Z"/><circle cx="10" cy="14" r=".7"/><circle cx="14" cy="17" r=".7"/><circle cx="14.5" cy="10" r=".7"/>@break
        @case('book')<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v17H6.5A2.5 2.5 0 0 0 4 22V5.5ZM12 3h5.5A2.5 2.5 0 0 1 20 5.5V22a2.5 2.5 0 0 0-2.5-2H12"/>@break
        @case('mail')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>@break
        @case('spark')<path d="m12 3 1.7 5.3L19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3ZM19 16l.7 2.3L22 19l-2.3.7L19 22l-.7-2.3L16 19l2.3-.7L19 16Z"/>@break
        @case('arrow')<path d="M4 12h15M13 6l6 6-6 6"/>@break
        @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
        @case('pause')<path d="M9 5v14M15 5v14"/>@break
        @case('play')<path d="m9 6 9 6-9 6V6Z"/>@break
        @default<circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 2.5"/>
    @endswitch
</svg>
