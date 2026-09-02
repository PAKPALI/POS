@if ($paginator->hasPages())
    <nav class="dt-paging paging_full_numbers" aria-label="Pagination du journal des envois">
        <ul class="pagination">
            @if ($paginator->onFirstPage())
                <li class="dt-paging-button page-item disabled" aria-disabled="true">
                    <a class="page-link first" aria-disabled="true" aria-label="First" tabindex="-1">Premier</a>
                </li>
            @else
                <li class="dt-paging-button page-item">
                    <a class="page-link first" href="{{ $paginator->url(1) }}" aria-label="First">Premier</a>
                </li>
            @endif

            @if ($paginator->onFirstPage())
                <li class="dt-paging-button page-item disabled" aria-disabled="true">
                    <a class="page-link previous" aria-disabled="true" aria-label="Previous" tabindex="-1">Précédent</a>
                </li>
            @else
                <li class="dt-paging-button page-item">
                    <a class="page-link previous" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">Précédent</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="dt-paging-button page-item disabled" aria-disabled="true"><a class="page-link" aria-disabled="true" tabindex="-1">{{ $element }}</a></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="dt-paging-button page-item active" aria-current="page"><a class="page-link" href="{{ $url }}" aria-current="page">{{ $page }}</a></li>
                        @else
                            <li class="dt-paging-button page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="dt-paging-button page-item">
                    <a class="page-link next" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">Suivant</a>
                </li>
            @else
                <li class="dt-paging-button page-item disabled" aria-disabled="true">
                    <a class="page-link next" aria-disabled="true" aria-label="Next" tabindex="-1">Suivant</a>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="dt-paging-button page-item">
                    <a class="page-link last" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Last">Dernier</a>
                </li>
            @else
                <li class="dt-paging-button page-item disabled" aria-disabled="true">
                    <a class="page-link last" aria-disabled="true" aria-label="Last" tabindex="-1">Dernier</a>
                </li>
            @endif
        </ul>
    </nav>
@endif
