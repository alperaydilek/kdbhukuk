<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->updateOrCreate(['slug' => 'home'], [
            'title' => 'Ana Sayfa',
            'meta_title' => 'KDB Hukuk | Av. Kaan Durali Bulut',
            'meta_description' => 'KDB Hukuk, Avukat Kaan Durali Bulut tarafından bireysel ve kurumsal müvekkillere hukuki danışmanlık ve avukatlık hizmetleri sunmaktadır.',
            'data' => [
                'hero_eyebrow' => 'KDB Hukuk',
                'hero_title' => 'Hukuki süreçlerde güvenilir ve stratejik çözüm ortağınız.',
                'hero_desc' => 'KDB Hukuk, Avukat Kaan Durali Bulut liderliğinde bireysel ve kurumsal müvekkillerine hukuki danışmanlık ve dava süreçlerinde profesyonel hizmet sunmaktadır.',
                'hero_cta_primary_label' => 'Hukuki Hizmetlerimizi İnceleyin',
                'hero_cta_secondary_label' => 'İletişime Geçin',
                'intro_label' => 'KDB Hukuk',
                'intro_text' => 'Hukuki danışmanlık ve dava takibinde; analiz, öngörü ve şeffaf iletişimi merkeze alan bir çalışma anlayışı.',
                'intro_statement' => 'Her hukuki süreç yalnızca mevzuat bilgisi değil, doğru strateji gerektirir.',
                'lawyer_title' => 'KDB Hukuk Kurucusu',
                'lawyer_bio' => '<p>KDB Hukuk\'un kurucusu Avukat Kaan Durali Bulut, hukuki uyuşmazlıkların yalnızca mevcut mevzuat çerçevesinde değil; müvekkilin uzun vadeli çıkarları, riskleri ve stratejik hedefleri dikkate alınarak değerlendirilmesi gerektiği anlayışıyla hizmet vermektedir.</p><p>Müvekkil odaklı yaklaşım, şeffaf iletişim ve titiz dosya takibi; büronun her dosyada koruduğu temel çalışma prensipleridir.</p>',
                'lawyer_cta_label' => 'Kaan Durali Bulut Hakkında',
                'cta_title' => 'Hukuki desteğe mi ihtiyacınız var?',
                'cta_desc' => 'Hukuki sürecinizi değerlendirmek ve doğru yol haritasını oluşturmak için bizimle iletişime geçebilirsiniz.',
            ],
        ]);

        Page::query()->updateOrCreate(['slug' => 'hakkimizda'], [
            'title' => 'Hakkımızda',
            'meta_title' => 'Hakkımızda | KDB Hukuk — Av. Kaan Durali Bulut',
            'meta_description' => 'KDB Hukuk ve kurucusu Av. Kaan Durali Bulut hakkında: yaklaşımımız, çalışma prensiplerimiz, müvekkil ilişkileri ve etik değerlerimiz.',
            'data' => [
                'subtitle' => 'Hukuki bilgi, stratejik yaklaşım ve güven temelli temsil.',
                'lawyer_bio' => '<p>KDB Hukuk\'un kurucusu Avukat Kaan Durali Bulut, hukuki uyuşmazlıkların yalnızca mevcut mevzuat çerçevesinde değil; müvekkilin uzun vadeli çıkarları, riskleri ve stratejik hedefleri dikkate alınarak değerlendirilmesi gerektiği anlayışıyla hizmet vermektedir.</p><p>Büromuz; ceza, gayrimenkul, ticaret, iş, aile ve miras hukuku başta olmak üzere geniş bir yelpazede danışmanlık ve dava takibi hizmeti sunmaktadır.</p><p>Müvekkillerimizle kurduğumuz ilişkinin temelinde şeffaf iletişim bulunur: sürecin her aşamasında mevcut durum, olası senaryolar ve izlenen strateji açık biçimde paylaşılır.</p>',
                'principles' => [
                    ['title' => 'Yaklaşımımız', 'description' => 'Her uyuşmazlığı kendi koşulları içinde değerlendirir; dava yoluna gitmeden önce sulh, arabuluculuk ve müzakere seçeneklerini de kapsayan bütüncül bir değerlendirme yaparız.'],
                    ['title' => 'Çalışma Prensiplerimiz', 'description' => 'Titiz dosya takibi, zamanında bilgilendirme ve özenli hazırlık; büromuzun her dosyada istisnasız uyguladığı çalışma standartlarıdır.'],
                    ['title' => 'Müvekkil İlişkileri', 'description' => 'Müvekkillerimizi sürecin her aşamasında bilgilendirir; hukuki seçenekleri, olası riskleri ve muhtemel sonuçları anlaşılır bir dille aktarırız.'],
                    ['title' => 'Hukuki Strateji', 'description' => 'Kısa vadeli kazanımlar yerine, müvekkilin uzun vadeli menfaatini gözeten; risk analizi ve öngörüye dayalı stratejiler geliştiririz.'],
                    ['title' => 'Etik ve Gizlilik', 'description' => 'Avukatlık mesleğinin etik kurallarına ve sır saklama yükümlülüğüne mutlak bağlılık; müvekkil bilgilerinin gizliliği her koşulda korunur.'],
                ],
            ],
        ]);

        Page::query()->updateOrCreate(['slug' => 'iletisim'], [
            'title' => 'İletişim',
            'meta_title' => 'İletişim | KDB Hukuk — Av. Kaan Durali Bulut',
            'meta_description' => 'KDB Hukuk ile iletişime geçin. Hukuki sürecinizi değerlendirmek ve randevu talebi oluşturmak için iletişim formunu kullanabilirsiniz.',
            'data' => [
                'intro_text' => 'Hukuki sürecinizi değerlendirmek ve doğru yol haritasını oluşturmak için bize ulaşabilirsiniz.',
                'map_embed_url' => null,
            ],
        ]);

        Setting::putGroup('site', [
            'phone' => '',
            'whatsapp' => '',
            'email' => '',
            'working_hours' => 'Hafta içi 09.00 — 18.00',
            'address' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
        ]);
    }
}
