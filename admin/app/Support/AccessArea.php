<?php

namespace App\Support;

/**
 * Panel navigasyon gruplarına karşılık gelen erişim izinleri.
 * Roller bu izinler üzerinden hangi alanlara erişebileceğini belirler.
 */
class AccessArea
{
    public const CLIENTS = 'access_muvekkiller';

    public const ACCOUNTING = 'access_muhasebe';

    public const TASKS = 'access_gorevler';

    public const CONTENT = 'access_icerik';

    public const COMMUNICATIONS = 'access_iletisim';

    public const TEMPLATES = 'access_sablonlar';

    public const SETTINGS = 'access_ayarlar';

    public const TEAM = 'access_ekip';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::CLIENTS => 'Müvekkiller',
            self::ACCOUNTING => 'Muhasebe',
            self::TASKS => 'Görevler',
            self::CONTENT => 'İçerik Yönetimi',
            self::COMMUNICATIONS => 'İletişim',
            self::TEMPLATES => 'İletişim Şablonları',
            self::SETTINGS => 'Ayarlar',
            self::TEAM => 'Ekip ve Yetkiler',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_keys(self::labels());
    }
}
