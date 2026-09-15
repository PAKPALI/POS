@php($name = $name ?? 'share')
<svg class="marketing-social-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('whatsapp')<path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L4 20l1.1-3.7A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M9 8.5c.2-.5.5-.6.8-.6h.6c.2 0 .4.1.5.4l.7 1.7c.1.2.1.4-.1.6l-.5.6c.6 1.1 1.5 1.9 2.7 2.4l.6-.6c.2-.2.4-.2.7-.1l1.5.7c.3.1.4.3.3.6-.2 1-.9 1.5-1.8 1.5-2.1-.1-5.7-2.2-6.6-5.4-.2-.7-.1-1.3.6-1.8Z"/>@break
        @case('tiktok')<path d="M14 4v10.2a3.8 3.8 0 1 1-2.8-3.7"/><path d="M14 4c.5 2.2 1.8 3.7 4.5 4"/>@break
        @case('facebook')<path d="M14.5 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.6 1.7-1.6h1.8V3.8c-.3 0-1.3-.1-2.4-.1-2.4 0-4.1 1.5-4.1 4.2V10H9v3h2.5v8"/>@break
        @case('instagram')<rect x="3.5" y="3.5" width="17" height="17" rx="4"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.7" r=".7" fill="currentColor" stroke="none"/>@break
        @default<circle cx="12" cy="12" r="8"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/>
    @endswitch
</svg>
