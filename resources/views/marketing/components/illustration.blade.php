@php
    $name = $name ?? 'dashboard';
    $class = 'marketing-illustration marketing-illustration-'.$name;
@endphp

@switch($name)
    @case('dashboard')
        <svg class="{{ $class }}" viewBox="0 0 360 220" role="presentation" aria-hidden="true" focusable="false">
            <rect x="12" y="12" width="336" height="196" rx="22" fill="var(--ds-bg-elevated)" stroke="var(--ds-border-strong)"/>
            <rect x="12" y="12" width="336" height="34" rx="22" fill="var(--ds-accent-soft)"/>
            <circle cx="31" cy="29" r="4" fill="var(--ds-accent)" opacity=".8"/>
            <circle cx="44" cy="29" r="4" fill="var(--ds-success, #35c98b)" opacity=".8"/>
            <circle cx="57" cy="29" r="4" fill="var(--ds-warning, #f5b942)" opacity=".8"/>
            <rect x="83" y="24" width="104" height="9" rx="4.5" fill="var(--ds-text-muted)" opacity=".35"/>
            <rect x="31" y="64" width="122" height="108" rx="14" fill="var(--ds-glass-1)" stroke="var(--ds-border-soft)"/>
            <rect x="48" y="82" width="55" height="7" rx="3.5" fill="var(--ds-text-muted)" opacity=".55"/>
            <rect x="48" y="99" width="79" height="18" rx="7" fill="var(--ds-accent-soft)"/>
            <rect x="48" y="132" width="82" height="7" rx="3.5" fill="var(--ds-border-strong)"/>
            <rect x="48" y="147" width="57" height="7" rx="3.5" fill="var(--ds-border-soft)"/>
            <rect x="169" y="64" width="160" height="108" rx="14" fill="var(--ds-glass-1)" stroke="var(--ds-border-soft)"/>
            <path d="M188 143 C208 127 219 135 235 113 S265 121 278 96 S303 104 313 83" fill="none" stroke="var(--ds-accent)" stroke-width="4" stroke-linecap="round"/>
            <path d="M188 145 H313" fill="none" stroke="var(--ds-border-soft)" stroke-width="2"/>
            <circle cx="235" cy="113" r="5" fill="var(--ds-accent)" stroke="var(--ds-bg-elevated)" stroke-width="3"/>
            <circle cx="278" cy="96" r="5" fill="var(--ds-success, #35c98b)" stroke="var(--ds-bg-elevated)" stroke-width="3"/>
            <rect x="190" y="82" width="47" height="7" rx="3.5" fill="var(--ds-text-muted)" opacity=".55"/>
            <rect x="268" y="21" width="57" height="16" rx="8" fill="var(--ds-success, #35c98b)" opacity=".18"/>
            <circle cx="280" cy="29" r="3" fill="var(--ds-success, #35c98b)"/>
            <rect x="288" y="26" width="27" height="6" rx="3" fill="var(--ds-success, #35c98b)" opacity=".8"/>
        </svg>
        @break
    @case('partner')
        <svg class="{{ $class }}" viewBox="0 0 420 190" role="presentation" aria-hidden="true" focusable="false">
            <path d="M79 96 H165 M253 96 H341" fill="none" stroke="var(--ds-accent)" stroke-width="3" stroke-linecap="round" stroke-dasharray="7 8" opacity=".7"/>
            <circle cx="62" cy="96" r="34" fill="var(--ds-accent-soft)" stroke="var(--ds-accent)" stroke-width="2"/>
            <path d="M49 101 C49 91 56 84 62 84 S75 91 75 101 M55 82 C55 77 59 73 62 73 S69 77 69 82" fill="none" stroke="var(--ds-accent)" stroke-width="4" stroke-linecap="round"/>
            <rect x="165" y="43" width="88" height="106" rx="16" fill="var(--ds-bg-elevated)" stroke="var(--ds-border-strong)"/>
            <rect x="184" y="61" width="50" height="8" rx="4" fill="var(--ds-text-muted)" opacity=".6"/>
            <rect x="184" y="81" width="50" height="24" rx="8" fill="var(--ds-accent-soft)"/>
            <path d="M196 93 H222" stroke="var(--ds-accent)" stroke-width="4" stroke-linecap="round"/>
            <rect x="184" y="120" width="33" height="7" rx="3.5" fill="var(--ds-border-strong)"/>
            <circle cx="358" cy="96" r="34" fill="rgba(53, 201, 139, .14)" stroke="var(--ds-success, #35c98b)" stroke-width="2"/>
            <path d="M345 96 L354 105 L372 85" fill="none" stroke="var(--ds-success, #35c98b)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M103 48 C143 21 174 22 195 39 M225 153 C246 171 281 169 317 143" fill="none" stroke="var(--ds-border-strong)" stroke-width="2" stroke-linecap="round"/>
            <circle cx="112" cy="43" r="5" fill="var(--ds-warning, #f5b942)"/>
            <circle cx="308" cy="147" r="5" fill="var(--ds-success, #35c98b)"/>
            <text x="210" y="173" text-anchor="middle" fill="var(--ds-text-muted)" font-size="10" font-weight="800" letter-spacing="1.5">PARTAGE · ATTRIBUTION · GAIN</text>
        </svg>
        @break
    @case('community')
        <svg class="{{ $class }}" viewBox="0 0 360 190" role="presentation" aria-hidden="true" focusable="false">
            <path d="M56 113 C91 43 132 43 180 95 S270 147 310 68" fill="none" stroke="var(--ds-accent)" stroke-width="2" stroke-dasharray="6 8" opacity=".65"/>
            <circle cx="52" cy="116" r="28" fill="var(--ds-accent-soft)" stroke="var(--ds-accent)" stroke-width="2"/>
            <circle cx="52" cy="107" r="8" fill="var(--ds-accent)" opacity=".9"/>
            <path d="M37 132 C39 120 46 117 52 117 S65 120 68 132" fill="var(--ds-accent)" opacity=".85"/>
            <circle cx="180" cy="94" r="32" fill="rgba(53, 201, 139, .14)" stroke="var(--ds-success, #35c98b)" stroke-width="2"/>
            <path d="M163 92 C163 82 170 76 180 76 S197 82 197 92 V105 C197 112 191 117 180 117 S163 112 163 105 Z" fill="var(--ds-success, #35c98b)" opacity=".85"/>
            <path d="M169 96 H191 M169 103 H184" stroke="var(--ds-bg-elevated)" stroke-width="3" stroke-linecap="round"/>
            <circle cx="310" cy="65" r="28" fill="rgba(139, 124, 255, .14)" stroke="#9b8cff" stroke-width="2"/>
            <circle cx="310" cy="58" r="8" fill="#9b8cff" opacity=".9"/>
            <path d="M295 83 C298 72 304 69 310 69 S322 72 325 83" fill="#9b8cff" opacity=".8"/>
            <rect x="119" y="24" width="92" height="27" rx="13.5" fill="var(--ds-bg-elevated)" stroke="var(--ds-border-soft)"/>
            <circle cx="135" cy="37.5" r="4" fill="var(--ds-accent)"/>
            <rect x="146" y="34" width="48" height="7" rx="3.5" fill="var(--ds-text-muted)" opacity=".55"/>
            <rect x="224" y="132" width="83" height="27" rx="13.5" fill="var(--ds-bg-elevated)" stroke="var(--ds-border-soft)"/>
            <circle cx="241" cy="145.5" r="4" fill="var(--ds-success, #35c98b)"/>
            <rect x="252" y="142" width="42" height="7" rx="3.5" fill="var(--ds-text-muted)" opacity=".55"/>
        </svg>
        @break
@endswitch
