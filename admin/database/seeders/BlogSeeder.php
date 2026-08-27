<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];

        foreach (['Gayrimenkul', 'İş Hukuku', 'Ceza Hukuku', 'Ticaret Hukuku', 'Aile Hukuku'] as $name) {
            $categories[$name] = BlogCategory::query()->firstOrCreate(['name' => $name]);
        }

        foreach ($this->posts() as $i => $post) {
            $categoryName = $post['category'];
            unset($post['category']);

            BlogPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    ...$post,
                    'blog_category_id' => $categories[$categoryName]->id,
                    'status' => 'yayinda',
                    'published_at' => now()->subDays((count($this->posts()) - $i) * 15),
                    'is_featured' => $i === 0,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function posts(): array
    {
        return [
            [
                'category' => 'Gayrimenkul',
                'title' => 'Kira Uyuşmazlıklarında Güncel Hukuki Süreçler',
                'slug' => 'kira-uyusmazliklarinda-guncel-hukuki-surecler',
                'excerpt' => 'Kira bedelinin tespiti, tahliye davaları ve zorunlu arabuluculuk şartı: kiraya veren ve kiracılar için güncel sürecin ana hatları.',
                'content' => <<<'HTML'
                    <p class="lead">Kira ilişkilerinden doğan uyuşmazlıklar, son yıllarda hem mevzuat değişiklikleri hem de ekonomik koşullar nedeniyle uygulamada en sık karşılaşılan uyuşmazlık türlerinden biri hâline gelmiştir.</p>
                    <h2>Kira uyuşmazlığı nedir?</h2>
                    <p>Kira uyuşmazlığı; kiraya veren ile kiracı arasında kira bedeli, kira süresi veya tahliye gibi konularda ortaya çıkan anlaşmazlıkları ifade eder.</p>
                    <h2>Zorunlu arabuluculuk şartı</h2>
                    <p>Kira ilişkisinden kaynaklanan uyuşmazlıkların önemli bir bölümünde, dava açılmadan önce arabulucuya başvurulması dava şartı olarak düzenlenmiştir.</p>
                    <blockquote>Arabuluculuk aşaması, doğru hazırlanıldığında uyuşmazlığın daha kısa sürede çözülmesi için gerçek bir fırsattır.</blockquote>
                    <h2>Kira bedelinin tespiti davası</h2>
                    <p>Kira bedelinin tespiti davası; tarafların yeni dönem kira bedeli üzerinde anlaşamaması hâlinde, bedelin mahkeme tarafından belirlenmesini amaçlar.</p>
                    <h2>Sonuç</h2>
                    <p>Kira uyuşmazlıkları; süre koşulları, şekil şartları ve zorunlu arabuluculuk aşamasıyla dikkatli takip gerektiren bir alandır.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'Kira Uyuşmazlıklarında Güncel Hukuki Süreçler | KDB Hukuk',
                'meta_description' => 'Kira bedelinin tespiti, tahliye davaları ve zorunlu arabuluculuk şartı hakkında güncel bilgiler.',
            ],
            [
                'category' => 'İş Hukuku',
                'title' => 'İş Sözleşmesinin Feshi ve Çalışanın Hakları',
                'slug' => 'is-sozlesmesinin-feshi-ve-calisanin-haklari',
                'excerpt' => 'Haklı ve geçerli fesih ayrımı, ihbar ve kıdem tazminatı ile işe iade davasının temel koşulları.',
                'content' => <<<'HTML'
                    <p class="lead">İş sözleşmesinin feshi, hem işçi hem işveren açısından hukuki sonuçları olan önemli bir süreçtir.</p>
                    <h2>Haklı fesih nedir?</h2>
                    <p>Haklı fesih; kanunda sayılan ağır ihlal hâllerinde tarafların iş sözleşmesini derhal, tazminatsız olarak sona erdirmesidir.</p>
                    <h2>Geçerli fesih nedir?</h2>
                    <p>Geçerli fesih ise işçinin yeterliliğinden veya işletmenin gereklerinden kaynaklanan, bildirim süresine uyularak yapılan fesihtir.</p>
                    <h2>İşe iade davası</h2>
                    <p>30 veya daha fazla işçi çalıştıran işyerlerinde, en az altı aylık kıdemi olan işçi, geçersiz fesih iddiasıyla işe iade davası açabilir.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'İş Sözleşmesinin Feshi ve Çalışanın Hakları | KDB Hukuk',
                'meta_description' => 'Haklı ve geçerli fesih ayrımı, işe iade davası ve işçilik alacakları hakkında bilgi.',
            ],
            [
                'category' => 'Gayrimenkul',
                'title' => 'Gayrimenkul Satışlarında Hukuki Riskler',
                'slug' => 'gayrimenkul-satislarinda-hukuki-riskler',
                'excerpt' => 'Tapu devri öncesi yapılması gereken kontroller ve satış sözleşmelerinde dikkat edilmesi gereken hususlar.',
                'content' => <<<'HTML'
                    <p class="lead">Gayrimenkul satışı, ekonomik değeri yüksek bir işlem olduğundan önceden hukuki inceleme gerektirir.</p>
                    <h2>Tapu kaydı incelemesi</h2>
                    <p>Satış öncesinde tapu kaydı üzerindeki ipotek, haciz ve şerh gibi kısıtlamaların incelenmesi büyük önem taşır.</p>
                    <h2>Satış vaadi sözleşmesi</h2>
                    <p>Tapuda devir gerçekleşmeden önce tarafların yükümlülüklerini güvence altına almak için noterde satış vaadi sözleşmesi düzenlenebilir.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'Gayrimenkul Satışlarında Hukuki Riskler | KDB Hukuk',
                'meta_description' => 'Tapu devri öncesi kontroller ve satış sözleşmelerinde dikkat edilmesi gereken hususlar.',
            ],
            [
                'category' => 'Ceza Hukuku',
                'title' => 'Soruşturma Aşamasında Şüphelinin Hakları',
                'slug' => 'sorusturma-asamasinda-suphelinin-haklari',
                'excerpt' => 'İfade ve sorgu sürecinde savunma hakkının kapsamı ve müdafi yardımından yararlanma.',
                'content' => <<<'HTML'
                    <p class="lead">Soruşturma aşaması, ceza yargılamasının şüphelinin haklarının en hassas şekilde korunması gereken evresidir.</p>
                    <h2>Susma hakkı</h2>
                    <p>Şüpheli, kendisini suçlayıcı beyanda bulunmaya zorlanamaz; ifade vermeme hakkına sahiptir.</p>
                    <h2>Müdafi yardımından yararlanma</h2>
                    <p>Şüpheli, gözaltına alındığı andan itibaren bir müdafiin hukuki yardımından yararlanma hakkına sahiptir.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'Soruşturma Aşamasında Şüphelinin Hakları | KDB Hukuk',
                'meta_description' => 'İfade ve sorgu sürecinde savunma hakkının kapsamı hakkında bilgi.',
            ],
            [
                'category' => 'Ticaret Hukuku',
                'title' => 'Limited Şirketlerde Ortaklıktan Çıkma ve Çıkarılma',
                'slug' => 'limited-sirketlerde-ortakliktan-cikma-ve-cikarilma',
                'excerpt' => 'Ortaklar arasındaki uyuşmazlıklarda çıkma, çıkarılma ve haklı sebeple fesih yollarının değerlendirilmesi.',
                'content' => <<<'HTML'
                    <p class="lead">Limited şirketlerde ortaklar arasında zaman zaman uyuşmazlıklar yaşanabilir; kanun bu durumlar için çeşitli çözüm yolları öngörmüştür.</p>
                    <h2>Ortaklıktan çıkma</h2>
                    <p>Ortak, haklı sebeplerin varlığı hâlinde mahkemeden şirketten çıkmasına karar verilmesini talep edebilir.</p>
                    <h2>Ortaklıktan çıkarılma</h2>
                    <p>Şirket sözleşmesinde öngörülen hâllerde veya haklı sebeple genel kurul kararıyla bir ortağın çıkarılması istenebilir.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'Limited Şirketlerde Ortaklıktan Çıkma ve Çıkarılma | KDB Hukuk',
                'meta_description' => 'Ortaklar arası uyuşmazlıklarda çıkma, çıkarılma ve haklı sebeple fesih yolları.',
            ],
            [
                'category' => 'Aile Hukuku',
                'title' => 'Anlaşmalı Boşanma Süreci Nasıl İlerler?',
                'slug' => 'anlasmali-bosanma-sureci-nasil-ilerler',
                'excerpt' => 'Protokol hazırlığından duruşmaya: anlaşmalı boşanmanın koşulları ve sık yapılan hatalar.',
                'content' => <<<'HTML'
                    <p class="lead">Anlaşmalı boşanma, tarafların boşanma ve sonuçları konusunda mutabık kaldığı durumlarda görece daha hızlı işleyen bir süreçtir.</p>
                    <h2>Koşullar</h2>
                    <p>Evliliğin en az bir yıl sürmüş olması ve tarafların birlikte başvurması veya birinin açtığı davayı diğerinin kabul etmesi gerekir.</p>
                    <h2>Boşanma protokolü</h2>
                    <p>Nafaka, tazminat, velayet ve mal paylaşımı gibi konuları içeren protokolün özenle hazırlanması, ileride doğabilecek uyuşmazlıkları önler.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'Anlaşmalı Boşanma Süreci Nasıl İlerler? | KDB Hukuk',
                'meta_description' => 'Anlaşmalı boşanmanın koşulları, protokol hazırlığı ve süreç hakkında bilgi.',
            ],
            [
                'category' => 'İş Hukuku',
                'title' => 'Mobbing İddialarında İspat ve Hukuki Başvuru Yolları',
                'slug' => 'mobbing-iddialarinda-ispat-ve-hukuki-basvuru-yollari',
                'excerpt' => 'İşyerinde psikolojik taciz iddialarının değerlendirilmesi ve başvurulabilecek hukuki yollar.',
                'content' => <<<'HTML'
                    <p class="lead">Mobbing (psikolojik taciz), işyerinde sistematik ve süreklilik arz eden olumsuz davranışları ifade eder.</p>
                    <h2>İspat yükü</h2>
                    <p>Mobbing iddialarında kesin ve mutlak bir ispat şartı aranmasa da, iddiayı destekleyen somut olguların ortaya konulması gerekir.</p>
                    <h2>Başvuru yolları</h2>
                    <p>Mobbing mağduru işçi; iş sözleşmesini haklı nedenle feshedebilir, maddi ve manevi tazminat talep edebilir.</p>
                    <p><em>Bu içerik genel bilgilendirme amacı taşımaktadır ve hukuki danışmanlık niteliğinde değildir.</em></p>
                    HTML,
                'meta_title' => 'Mobbing İddialarında İspat ve Hukuki Başvuru Yolları | KDB Hukuk',
                'meta_description' => 'İşyerinde psikolojik taciz iddialarının değerlendirilmesi ve hukuki yollar.',
            ],
        ];
    }
}
