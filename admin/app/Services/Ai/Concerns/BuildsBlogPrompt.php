<?php

namespace App\Services\Ai\Concerns;

trait BuildsBlogPrompt
{
    protected function systemPrompt(): string
    {
        return <<<'TEXT'
Sen KDB Hukuk için içerik üreten deneyimli bir hukuk editörüsün. Av. Kaan Durali Bulut'un
kurumsal kimliğine uygun, güven veren ama iddialı olmayan bir dille Türkçe blog yazıları
yazıyorsun. Metinlerinde "kesin kazanırız", "garanti sonuç", "en iyi avukat" gibi etik
açıdan sorunlu ifadeler KESİNLİKLE kullanma. Bunun yerine "hukuki danışmanlık", "stratejik
değerlendirme", "hukuki risk analizi" gibi ölçülü ifadeler tercih et.

Metni; SEO (arama motoru optimizasyonu), AEO (cevap motoru optimizasyonu — net soru-cevap
formatları) ve GEO (üretken yapay zekâ motorları için optimize edilmiş, açık ve doğrudan
cevaplanabilir paragraflar) ilkelerine göre kurgula. H2/H3 alt başlıklar kullan, en az bir
"X nedir?" tipinde net tanım paragrafı ve makale sonunda kısa bir hukuki uyarı cümlesi ekle
("Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.").

SADECE geçerli bir JSON nesnesi döndür, başka hiçbir açıklama ekleme. JSON şu alanları
içermeli: title (string, 60 karakteri geçmeyen çekici başlık), excerpt (string, 1-2 cümlelik
özet), content (string, HTML — <h2>, <h3>, <p>, <ul>/<li>, <blockquote> etiketleri
kullanılabilir; <html>/<body> etiketi kullanma), meta_title (string, 60 karakter civarı),
meta_description (string, 155 karakter civarı).
TEXT;
    }

    protected function userPrompt(string $topic): string
    {
        return "Konu: {$topic}\n\nBu konu hakkında KDB Hukuk için 700-1000 kelimelik, ".
            'yukarıdaki kurallara tam uyan bir blog yazısı üret.';
    }

    /**
     * @return array{title: string, excerpt: string, content: string, meta_title: string, meta_description: string}
     */
    protected function parseJsonResponse(string $raw): array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```json\s*|```$/m', '', $raw) ?? $raw;

        $decoded = json_decode(trim($raw), true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('Yapay zekâ yanıtı geçerli bir JSON içermiyor.');
        }

        return [
            'title' => (string) ($decoded['title'] ?? ''),
            'excerpt' => (string) ($decoded['excerpt'] ?? ''),
            'content' => (string) ($decoded['content'] ?? ''),
            'meta_title' => (string) ($decoded['meta_title'] ?? ($decoded['title'] ?? '')),
            'meta_description' => (string) ($decoded['meta_description'] ?? ($decoded['excerpt'] ?? '')),
        ];
    }
}
