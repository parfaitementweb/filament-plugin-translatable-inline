<div>
    <div
            x-data="{
                open: false,
                init() {
                    document.addEventListener('livewire:initialized', () => {
                        this.open = Boolean($refs.additionalContainer.querySelector(':invalid'));
                    })
                },
                handleOpenState() {
                    this.open = !this.open;
                    if (!this.open) {
                        this.open = Boolean($refs.additionalContainer.querySelector(':invalid'));
                    }
                }
        }"
            @form-validation-error.window="
                $nextTick(() => {
                    if ($refs.additionalContainer.querySelector('[data-validation-error]')) {
                        open = true;
                    }
                });
        "
    >
        <div>
            {{ $getChildSchema('main') }}
        </div>

        <div style="display:flex; align-items:center; gap:0.25rem; cursor:pointer; user-select:none; margin-top:0.75rem; margin-bottom:0.75rem;"
             @click="handleOpenState()"
        >
            <div x-show="!open">
                <x-filament::icon icon="heroicon-c-chevron-right" style="height:1.25rem; width:1.25rem;"/>
            </div>

            <div x-show="open">
                <x-filament::icon icon="heroicon-c-chevron-down" style="height:1.25rem; width:1.25rem;"/>
            </div>

            @foreach($getTranslatableLocales() as $locale)
                <div style="font-size:0.75rem;
                      border-radius:9999px;
                      padding:0.25rem;
                      box-shadow:0 1px 2px 0 rgb(0 0 0 / 0.05);
                      box-shadow:0 0 0 2px rgba(10,10,10,0.1) inset;
                     @if (!$isLocaleStateEmpty($locale))
                         border: 1px forestgreen solid;
                        @endif
                ">
                    <div style="padding-left:0.25rem; padding-right:0.25rem;">{{ $locale }}</div>
                </div>
            @endforeach
        </div>

        <div x-ref="additionalContainer"
             x-show="open"
        >
            <div style="padding: 0.75rem;">
                {{ $getChildSchema('additional') }}
            </div>
        </div>
    </div>
</div>