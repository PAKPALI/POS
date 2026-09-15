@if(!empty($socialNetworks))
    <div class="marketing-social-modal" data-social-invite-modal hidden role="dialog" aria-modal="true" aria-labelledby="social-invite-title">
        <div class="marketing-social-modal-backdrop" data-social-modal-close></div>
        <div class="marketing-social-modal-dialog" role="document" tabindex="-1">
            <button class="marketing-social-modal-close" type="button" data-social-modal-close aria-label="Fermer"><span aria-hidden="true">×</span></button>
            <span class="marketing-social-modal-kicker">@include('marketing.components.icon', ['name' => 'spark']) La communauté Maxanou</span>
            <h2 id="social-invite-title">Suivez Maxanou là où cela vous convient.</h2>
            <p>Retrouvez les nouveautés, conseils et échanges autour de l’application sur nos canaux officiels.</p>
            <div class="marketing-social-modal-networks">
                @foreach($socialNetworks as $key => $network)
                    @if($key === 'whatsapp')
                        <button type="button" class="marketing-social-network-card is-whatsapp" data-whatsapp-community-trigger>@include('marketing.components.social-icon', ['name' => $key])<span><strong>{{ $network['label'] }}</strong><small>Poser vos questions à la communauté</small></span>@include('marketing.components.icon', ['name' => 'arrow'])</button>
                    @else
                        <a class="marketing-social-network-card" href="{{ $network['url'] }}" target="_blank" rel="noopener noreferrer">@include('marketing.components.social-icon', ['name' => $key])<span><strong>{{ $network['label'] }}</strong><small>{{ $network['description'] }}</small></span>@include('marketing.components.icon', ['name' => 'arrow'])</a>
                    @endif
                @endforeach
            </div>
            <button class="marketing-button marketing-button-secondary marketing-social-later" type="button" data-social-modal-close>Rejoindre plus tard</button>
        </div>
    </div>

    @if(isset($socialNetworks['whatsapp']))
        <div class="marketing-social-modal" data-whatsapp-community-modal hidden role="dialog" aria-modal="true" aria-labelledby="whatsapp-community-title">
            <div class="marketing-social-modal-backdrop" data-whatsapp-community-close></div>
            <div class="marketing-social-modal-dialog" role="document" tabindex="-1">
                <button class="marketing-social-modal-close" type="button" data-whatsapp-community-close aria-label="Fermer"><span aria-hidden="true">×</span></button>
                <span class="marketing-social-modal-kicker is-whatsapp">@include('marketing.components.social-icon', ['name' => 'whatsapp']) Communauté WhatsApp Maxanou</span>
                <h2 id="whatsapp-community-title">Un espace d’entraide, avec un cadre clair.</h2>
                <p>Vous pourrez échanger avec des utilisateurs, partenaires et administrateurs afin de poser des questions et mieux utiliser Maxanou.</p>
                <ul class="marketing-community-rules">
                    <li>@include('marketing.components.icon', ['name' => 'check'])<span>Les discussions doivent concerner Maxanou et l’utilisation de l’application.</span></li>
                    <li>@include('marketing.components.icon', ['name' => 'check'])<span>Les insultes, images ou sujets inappropriés sont interdits.</span></li>
                    <li>@include('marketing.components.icon', ['name' => 'check'])<span>Chaque membre reste responsable de ses publications et peut être retiré du groupe si ce cadre n’est pas respecté.</span></li>
                </ul>
                <label class="marketing-community-consent"><input type="checkbox" data-whatsapp-community-consent><span>J’ai lu ces règles et j’accepte de les respecter.</span></label>
                <a class="marketing-button marketing-button-primary marketing-whatsapp-join" href="{{ $socialNetworks['whatsapp']['url'] }}" target="_blank" rel="noopener noreferrer" data-whatsapp-community-join aria-disabled="true" tabindex="-1">Rejoindre la communauté @include('marketing.components.icon', ['name' => 'arrow'])</a>
            </div>
        </div>
    @endif
@endif
