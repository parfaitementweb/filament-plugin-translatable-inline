<?php

declare(strict_types=1);

namespace Parfaitementweb\FilamentPluginTranslatableInline\Forms\Components;

use Filament\Schemas\Components\Component;
use Illuminate\Support\Collection;

class TranslatableContainer extends Component
{
    protected string $view = 'filament-plugin-translatable-inline::forms.components.translatable-container';

    protected Component $baseComponent;

    protected bool $onlyMainIsRequired = false;
    protected array $requiredLocales = [];

    public static function make(Component $component): static
    {
        /** @var static $static */
        $static = app(static::class);
        $static->baseComponent = $component;

        $static->statePath($component->getName());

        $static->schema(fn () => $static->buildSchema());

        $static->configure();

        return $static;
    }

    public function getName(): string
    {
        return $this->baseComponent->getName();
    }

    public function getLabel(): ?string
    {
        return $this->baseComponent->getLabel();
    }

    protected function buildSchema(): array
    {
        $locales = $this->getTranslatableLocales();
        if ($locales->isEmpty()) {
            $this->childComponents([], 'main');
            $this->childComponents([], 'additional');

            return [];
        }
        
        $mainComponent = $this->cloneComponent($this->baseComponent, (string) $locales->first())
            ->required($this->isLocaleRequired((string) $locales->first()));

        $additional = $locales
            ->skip(1)
            ->map(function (string $locale) {
                return $this->cloneComponent($this->baseComponent, $locale)
                    ->required($this->isLocaleRequired($locale));
            })
            ->all();

        $this->childComponents([$mainComponent], 'main');
        $this->childComponents($additional, 'additional');

        // Return an empty default schema to prevent duplicate rendering.
        return [];
    }

    public function cloneComponent(Component $component, string $locale): Component
    {
        $baseLabel = $component->getLabel() ?? $component->getName();

        return $component
            ->getClone()
            ->meta('locale', $locale)
            ->label("{$baseLabel} ({$locale})")
            ->statePath($locale);
    }

    public function getTranslatableLocales(): Collection
    {
        $livewire = $this->getLivewire();

        $resourceLocales = null;

        if ($livewire && method_exists($livewire, 'getResource')) {
            $resource = $livewire::getResource();

            if ($resource && method_exists($resource, 'getTranslatableLocales')) {
                $resourceLocales = $resource::getTranslatableLocales();
            }
        }

        $fallback = filament('spatie-translatable')->getDefaultLocales() ?? [];

        return collect($resourceLocales ?? $fallback)->values();
    }

    public function isLocaleStateEmpty(string $locale): bool
    {
        $state = $this->getState() ?? [];

        return empty($state[$locale] ?? null);
    }

    public function onlyMainLocaleRequired(): self
    {
        $this->onlyMainIsRequired = true;

        return $this->schema(fn () => $this->buildSchema());
    }

    public function requiredLocales(array $locales): self
    {
        $this->requiredLocales = $locales;

        return $this->schema(fn () => $this->buildSchema());
    }

    private function isLocaleRequired(string $locale): bool
    {
        if ($this->onlyMainIsRequired) {
            return ($locale === $this->getTranslatableLocales()->first());
        }

        if (in_array($locale, $this->requiredLocales)) {
            return true;
        }

        if (empty($this->requiredLocales) && $this->baseComponent->isRequired()) {
            return true;
        }

        return false;
    }
}
