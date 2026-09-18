<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

/**
 * Haritadan konum seçtiren alan. Durum, ondalık derece cinsinden
 * ['lat' => float, 'lng' => float] dizisi olarak tutulur.
 *
 * Harita katmanı OpenStreetMap, adres arama ise Nominatim üzerinden çalışır;
 * her ikisi de anahtar gerektirmez.
 */
class MapPicker extends Field
{
    protected string $view = 'filament.forms.components.map-picker';

    protected float|Closure $defaultLatitude = 41.0082;

    protected float|Closure $defaultLongitude = 28.9784;

    protected int|Closure $defaultZoom = 12;

    protected int|Closure $height = 380;

    public function defaultLocation(float|Closure $latitude, float|Closure $longitude): static
    {
        $this->defaultLatitude = $latitude;
        $this->defaultLongitude = $longitude;

        return $this;
    }

    public function defaultZoom(int|Closure $zoom): static
    {
        $this->defaultZoom = $zoom;

        return $this;
    }

    public function height(int|Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getDefaultLatitude(): float
    {
        return $this->evaluate($this->defaultLatitude);
    }

    public function getDefaultLongitude(): float
    {
        return $this->evaluate($this->defaultLongitude);
    }

    public function getDefaultZoom(): int
    {
        return $this->evaluate($this->defaultZoom);
    }

    public function getHeight(): int
    {
        return $this->evaluate($this->height);
    }
}
