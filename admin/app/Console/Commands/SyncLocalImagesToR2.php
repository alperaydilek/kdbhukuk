<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncLocalImagesToR2 extends Command
{
    protected $signature = 'app:sync-local-images-to-r2 {--source= : web/public/assets/images klasörünün yolu}';

    protected $description = 'Astro sitesindeki yerel görselleri Cloudflare R2\'ye yükler ve ilgili kayıtları günceller';

    /**
     * Slug => yerel dosya adı (uzantısız) eşleşmesi.
     *
     * @var array<string, string>
     */
    protected array $serviceMap = [
        'ceza-hukuku' => 'service-01',
        'gayrimenkul-hukuku' => 'service-02',
        'ticaret-hukuku' => 'service-03',
        'is-hukuku' => 'service-04',
        'aile-hukuku' => 'service-05',
        'miras-hukuku' => 'service-06',
        'icra-ve-iflas-hukuku' => 'service-07',
        'sozlesmeler-hukuku' => 'service-08',
        'tazminat-hukuku' => 'service-09',
        'hukuki-danismanlik' => 'service-10',
    ];

    /**
     * @var array<string, string>
     */
    protected array $blogMap = [
        'kira-uyusmazliklarinda-guncel-hukuki-surecler' => 'journal-01',
        'is-sozlesmesinin-feshi-ve-calisanin-haklari' => 'journal-02',
        'gayrimenkul-satislarinda-hukuki-riskler' => 'journal-03',
        'sorusturma-asamasinda-suphelinin-haklari' => 'journal-04',
        'limited-sirketlerde-ortakliktan-cikma-ve-cikarilma' => 'journal-05',
        'anlasmali-bosanma-sureci-nasil-ilerler' => 'journal-06',
        'mobbing-iddialarinda-ispat-ve-hukuki-basvuru-yollari' => 'journal-07',
    ];

    public function handle(): int
    {
        $source = $this->option('source') ?: base_path('../web/public/assets/images');

        if (! is_dir($source)) {
            $this->error("Kaynak klasör bulunamadı: {$source}");

            return self::FAILURE;
        }

        $this->components->info("Kaynak: {$source}");

        $this->syncServices($source);
        $this->syncBlogPosts($source);
        $this->syncLawyerPortrait($source);

        $this->components->info('Tamamlandı.');

        return self::SUCCESS;
    }

    protected function upload(string $source, string $fileBaseName, string $r2Directory): ?string
    {
        $localPath = "{$source}/{$fileBaseName}.jpg";

        if (! is_file($localPath)) {
            $this->components->warn("Atlandı (yerel dosya yok): {$localPath}");

            return null;
        }

        $r2Path = "{$r2Directory}/{$fileBaseName}.jpg";

        Storage::disk('r2')->put($r2Path, file_get_contents($localPath), 'public');

        return $r2Path;
    }

    protected function syncServices(string $source): void
    {
        foreach ($this->serviceMap as $slug => $fileBaseName) {
            $service = Service::query()->firstWhere('slug', $slug);

            if (! $service) {
                $this->components->warn("Hizmet bulunamadı: {$slug}");

                continue;
            }

            $r2Path = $this->upload($source, $fileBaseName, 'services');

            if ($r2Path) {
                $service->update(['cover_image' => $r2Path]);
                $this->components->twoColumnDetail($service->title, $r2Path);
            }
        }
    }

    protected function syncBlogPosts(string $source): void
    {
        foreach ($this->blogMap as $slug => $fileBaseName) {
            $post = BlogPost::query()->firstWhere('slug', $slug);

            if (! $post) {
                $this->components->warn("Yazı bulunamadı: {$slug}");

                continue;
            }

            $r2Path = $this->upload($source, $fileBaseName, 'blog');

            if ($r2Path) {
                $post->update(['cover_image' => $r2Path]);
                $this->components->twoColumnDetail($post->title, $r2Path);
            }
        }
    }

    protected function syncLawyerPortrait(string $source): void
    {
        $r2Path = $this->upload($source, 'portrait-av-kaan-durali-bulut', 'portraits');

        if (! $r2Path) {
            return;
        }

        foreach (['home', 'hakkimizda'] as $slug) {
            $page = Page::query()->firstWhere('slug', $slug);

            if (! $page) {
                continue;
            }

            $page->update(['data' => [...($page->data ?? []), 'lawyer_photo' => $r2Path]]);
            $this->components->twoColumnDetail("Sayfa: {$slug} (lawyer_photo)", $r2Path);
        }
    }
}
