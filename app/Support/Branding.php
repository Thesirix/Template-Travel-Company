<?php

namespace App\Support;

use App\Models\Destination;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Identité de l'agence (nom, logo, couleurs) et destinations, lues en base.
 * Les valeurs du .env (config/agency.php) servent de valeurs par défaut.
 */
class Branding
{
    public const DEFAULTS = [
        'agency_name' => null,
        'agency_logo' => null,
        'agency_logo_dark' => null,
        'agency_icon' => null,
        'color_primary' => '#4f46e5',
        'color_accent' => '#f59e0b',
        'banner_style' => 'gradient',
    ];

    private const CACHE_KEY = 'branding.settings';

    private ?array $settings = null;
    private ?Collection $destinations = null;

    public function all(): array
    {
        if ($this->settings === null) {
            $stored = [];
            try {
                if (Schema::hasTable('settings')) {
                    $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::pluck('value', 'key')->all());
                }
            } catch (Throwable) {
                // base indisponible (installation, console) : valeurs par défaut
            }
            $this->settings = array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null && $v !== ''));
        }

        return $this->settings;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function name(): string
    {
        return $this->get('agency_name') ?: config('agency.name', 'Travel Company');
    }

    /**
     * URL du logo pour fond clair (e-mails, documents), ou null pour afficher le logo texte.
     */
    public function logoUrl(): ?string
    {
        return $this->assetUrl('agency_logo');
    }

    /**
     * URL du logo pour fond sombre (menu latéral, page de connexion). À défaut, le logo principal.
     */
    public function logoOnDarkUrl(): ?string
    {
        return $this->assetUrl('agency_logo_dark') ?? $this->logoUrl();
    }

    /**
     * URL de l'icône seule (menu replié, onglet du navigateur), ou null pour les initiales.
     */
    public function iconUrl(): ?string
    {
        return $this->assetUrl('agency_icon');
    }

    private function assetUrl(string $key): ?string
    {
        $path = $this->get($key);

        return $path ? asset($path) . '?v=' . substr(md5($path), 0, 8) : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name()));

        return mb_strtoupper(mb_substr($words[0] ?? 'T', 0, 1) . mb_substr($words[1] ?? '', 0, 1));
    }

    public function primary(): string
    {
        return $this->color('color_primary');
    }

    public function accent(): string
    {
        return $this->color('color_accent');
    }

    public function bannerStyle(): string
    {
        return in_array($this->get('banner_style'), ['gradient', 'solid', 'glass'], true) ? $this->get('banner_style') : 'gradient';
    }

    /**
     * Variables CSS injectées dans les pages (couleur de la bannière, accent, déclinaisons).
     */
    public function cssVariables(): string
    {
        $p = $this->primary();
        $a = $this->accent();

        return implode(';', [
            "--tc-primary:{$p}",
            '--tc-primary-rgb:' . implode(',', self::rgb($p)),
            '--tc-primary-dark:' . self::shade($p, -0.22),
            '--tc-primary-light:' . self::shade($p, 0.86),
            "--tc-accent:{$a}",
            '--tc-accent-rgb:' . implode(',', self::rgb($a)),
            '--tc-on-primary:' . (self::luminance($p) > 0.55 ? '#0f172a' : '#ffffff'),
        ]);
    }

    /**
     * Destinations actives, dans l'ordre du menu.
     */
    public function destinations(): Collection
    {
        if ($this->destinations === null) {
            try {
                $this->destinations = Schema::hasTable('destinations')
                    ? Destination::with('country')->active()->get()
                    : collect();
            } catch (Throwable) {
                $this->destinations = collect();
            }
        }

        return $this->destinations;
    }

    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->settings = null;
        $this->destinations = null;
    }

    private function color(string $key): string
    {
        $value = (string) $this->get($key);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : self::DEFAULTS[$key];
    }

    public static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /**
     * Éclaircit (ratio > 0) ou assombrit (ratio < 0) une couleur.
     */
    public static function shade(string $hex, float $ratio): string
    {
        $mixed = array_map(
            fn ($c) => (int) round($ratio >= 0 ? $c + (255 - $c) * $ratio : $c * (1 + $ratio)),
            self::rgb($hex)
        );

        return sprintf('#%02x%02x%02x', ...$mixed);
    }

    public static function luminance(string $hex): float
    {
        [$r, $g, $b] = self::rgb($hex);

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    }
}
