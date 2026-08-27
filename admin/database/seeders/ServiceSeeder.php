<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->services() as $order => $service) {
            $faqs = $service['faqs'] ?? [];
            unset($service['faqs']);

            $record = Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                [...$service, 'order' => $order + 1],
            );

            foreach ($faqs as $i => $faq) {
                $record->faqs()->updateOrCreate(
                    ['question' => $faq['question']],
                    [...$faq, 'order' => $i + 1],
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function services(): array
    {
        return [
            [
                'title' => 'Ceza Hukuku',
                'slug' => 'ceza-hukuku',
                'short_description' => 'Soruşturma ve kovuşturma aşamalarında müdafilik, mağdur vekilliği ve itiraz süreçlerinde temsil.',
                'description' => '<p>Ceza hukuku; soruşturma aşamasından kovuşturmanın sonuna kadar şüpheli, sanık veya mağdur sıfatıyla yürütülen süreçleri kapsar. Bu süreçte savunma hakkının etkin kullanılması, delillerin doğru değerlendirilmesi ve usul kurallarına uyum büyük önem taşır.</p>',
                'covers' => '<ul><li>Gözaltı ve ifade alma süreçlerinde müdafilik</li><li>Sulh ceza hakimliği itiraz süreçleri</li><li>Ağır ceza ve asliye ceza mahkemelerinde savunma</li><li>Mağdur ve şikayetçi vekilliği</li><li>Uzlaştırma sürecinde temsil</li></ul>',
                'scope' => '<p>Soruşturma aşamasında şüpheliye eşlik etme, ifade ve sorgu süreçlerini takip etme; kovuşturma aşamasında duruşmalara katılım, delil değerlendirmesi ve gerekli hukuki başvuruların yapılmasını kapsar.</p>',
                'process_steps' => [
                    ['title' => 'Ön Görüşme', 'description' => 'Dosyanın kapsamı ve mevcut deliller birlikte değerlendirilir.'],
                    ['title' => 'Soruşturma Takibi', 'description' => 'İfade, sorgu ve itiraz süreçleri yakından takip edilir.'],
                    ['title' => 'Kovuşturma', 'description' => 'Duruşmalara katılım sağlanır, savunma stratejisi uygulanır.'],
                ],
                'why_important' => '<p>Ceza yargılamasında usul hataları ve süre kaçırma gibi durumlar telafisi güç sonuçlar doğurabilir. Sürecin başından itibaren hukuki destek almak, savunma hakkının etkin kullanılmasını sağlar.</p>',
                'faqs' => [
                    ['question' => 'Gözaltına alınan kişi avukatla ne zaman görüşebilir?', 'answer' => 'Şüpheli, gözaltına alındığı andan itibaren müdafiiyle görüşme hakkına sahiptir. Bu görüşme, ifade alınmadan önce gerçekleştirilebilir.'],
                    ['question' => 'Uzlaştırma süreci nasıl işler?', 'answer' => 'Kanunda uzlaştırma kapsamında sayılan suçlarda, taraflar bir uzlaştırmacı aracılığıyla bir araya getirilir. Anlaşma sağlanması hâlinde dosya bu çerçevede sonuçlandırılabilir.'],
                ],
            ],
            [
                'title' => 'Gayrimenkul Hukuku',
                'slug' => 'gayrimenkul-hukuku',
                'short_description' => 'Tapu, kira, kat mülkiyeti ve imar uyuşmazlıklarında danışmanlık ve temsil.',
                'description' => '<p>Gayrimenkul hukuku; taşınmaz malların edinimi, kullanımı, devri ve bunlardan doğan uyuşmazlıkları düzenleyen hukuk dalıdır. Tapu işlemleri, kira ilişkileri, kat mülkiyeti ve imar uygulamaları bu alanın başlıca konularını oluşturur.</p>',
                'covers' => '<ul><li>Tapu iptal ve tescil davaları</li><li>Kira bedelinin tespiti ve tahliye davaları</li><li>Ortaklığın giderilmesi (izale-i şuyu) davaları</li><li>Kat mülkiyeti ve site yönetimi uyuşmazlıkları</li><li>Kamulaştırma ve imar uygulamaları</li></ul>',
                'scope' => '<p>Alım-satım süreçlerinde ön inceleme ve sözleşme denetimi, kira ilişkilerinin kurulması ve sona erdirilmesi, tapu işlemlerinin takibi ve taşınmazlardan doğan davalarda temsil hizmeti sunulmaktadır.</p>',
                'process_steps' => [
                    ['title' => 'Ön Görüşme ve Değerlendirme', 'description' => 'Uyuşmazlığın veya işlemin kapsamı belgeler üzerinden değerlendirilir.'],
                    ['title' => 'Hukuki Risk Analizi', 'description' => 'Tapu kayıtları ve ilgili mevzuat incelenerek riskler tespit edilir.'],
                    ['title' => 'Strateji ve Yol Haritası', 'description' => 'Sulh, arabuluculuk veya dava seçenekleri değerlendirilir.'],
                    ['title' => 'Sürecin Yürütülmesi', 'description' => 'Belirlenen strateji doğrultusunda işlemler yürütülür.'],
                ],
                'why_important' => '<p>Gayrimenkul işlemleri; tapu kayıtlarındaki kısıtlamalar veya sözleşme hükümlerindeki belirsizlikler nedeniyle ciddi hak kayıplarına yol açabilir. İşlem öncesi hukuki inceleme, bu risklerin önceden tespit edilmesini sağlar.</p>',
                'faqs' => [
                    ['question' => 'Gayrimenkul satışında avukat gerekli midir?', 'answer' => 'Gayrimenkul satışı için avukat zorunluluğu bulunmamakla birlikte; tapu kayıtlarının incelenmesi ve sözleşme şartlarının değerlendirilmesi açısından hukuki destek almak süreci güvence altına alır.'],
                    ['question' => 'Tapu işlemlerinde avukat ne yapar?', 'answer' => 'Avukat; tapu kayıtları üzerindeki kısıtlamaları inceler, sözleşmeleri hazırlar veya denetler ve uyuşmazlık hâlinde dava sürecini yürütür.'],
                    ['question' => 'Kira uyuşmazlıklarında süreç nasıl ilerler?', 'answer' => 'Dava açılmadan önce çoğunlukla arabuluculuk aşaması gündeme gelir. Anlaşma sağlanamazsa sulh hukuk mahkemesinde dava süreci yürütülür.'],
                ],
            ],
            [
                'title' => 'Ticaret Hukuku',
                'slug' => 'ticaret-hukuku',
                'short_description' => 'Şirketler hukuku, ticari sözleşmeler ve ticari uyuşmazlık çözümü.',
                'description' => '<p>Ticaret hukuku; şirketlerin kuruluşundan ticari ilişkilerin yürütülmesine, ortaklık yapılarından ticari uyuşmazlıkların çözümüne kadar geniş bir alanı kapsar.</p>',
                'covers' => '<ul><li>Şirket kuruluşu ve yapılandırması</li><li>Ortaklar arası uyuşmazlıklar</li><li>Ticari sözleşmelerin hazırlanması</li><li>Alacak takibi ve ticari davalar</li><li>Birleşme ve devralma süreçleri</li></ul>',
                'scope' => '<p>Şirketler hukuku danışmanlığı, ticari sözleşme müzakereleri, ortaklık uyuşmazlıkları ve ticari dava süreçlerinde temsil hizmeti sunulmaktadır.</p>',
                'process_steps' => [
                    ['title' => 'Durum Analizi', 'description' => 'Ticari ilişkinin veya uyuşmazlığın kapsamı incelenir.'],
                    ['title' => 'Hukuki Değerlendirme', 'description' => 'Sözleşmeler ve ticari kayıtlar üzerinden risk analizi yapılır.'],
                    ['title' => 'Çözüm Süreci', 'description' => 'Müzakere, arabuluculuk veya dava süreci yürütülür.'],
                ],
                'why_important' => '<p>Ticari ilişkilerde doğru sözleşme altyapısı ve zamanında hukuki müdahale, işletmenin uzun vadeli çıkarlarını korumanın temelidir.</p>',
                'faqs' => [
                    ['question' => 'Şirket kuruluşunda hukuki danışmanlık neden önemlidir?', 'answer' => 'Ortaklık yapısı, pay dağılımı ve şirket sözleşmesi hükümleri ileride doğabilecek uyuşmazlıkların önlenmesi açısından baştan doğru kurgulanmalıdır.'],
                ],
            ],
            [
                'title' => 'İş Hukuku',
                'slug' => 'is-hukuku',
                'short_description' => 'İşçi ve işveren uyuşmazlıkları, fesih süreçleri ve işçilik alacakları.',
                'description' => '<p>İş hukuku; iş sözleşmesinin kurulmasından sona ermesine kadar işçi ve işveren arasındaki ilişkiyi düzenler. İşe iade, kıdem ve ihbar tazminatı, fazla mesai gibi konular bu alanın başlıca uyuşmazlık kaynaklarıdır.</p>',
                'covers' => '<ul><li>İşe iade davaları</li><li>Kıdem ve ihbar tazminatı alacakları</li><li>Fazla mesai ve diğer işçilik alacakları</li><li>İş kazası ve meslek hastalığından doğan davalar</li><li>Mobbing iddiaları</li></ul>',
                'scope' => '<p>İş sözleşmesinin feshi öncesi danışmanlık, arabuluculuk süreci ve iş mahkemesinde dava takibi hizmetleri kapsam dahilindedir.</p>',
                'process_steps' => [
                    ['title' => 'Ön Değerlendirme', 'description' => 'İş ilişkisinin ve fesih sürecinin koşulları incelenir.'],
                    ['title' => 'Arabuluculuk', 'description' => 'Dava şartı olan arabuluculuk süreci yürütülür.'],
                    ['title' => 'Dava Süreci', 'description' => 'Anlaşma sağlanamazsa iş mahkemesinde dava açılır.'],
                ],
                'why_important' => '<p>İşçilik alacaklarında zamanaşımı süreleri ve arabuluculuk şartı gibi usul kuralları hak kaybı yaşanmaması için dikkatle takip edilmelidir.</p>',
                'faqs' => [
                    ['question' => 'İşe iade davası açmadan önce arabuluculuğa başvurmak zorunlu mu?', 'answer' => 'Evet, işe iade talepli davalarda dava açmadan önce arabulucuya başvurulması kanunen zorunlu bir dava şartıdır.'],
                ],
            ],
            [
                'title' => 'Aile Hukuku',
                'slug' => 'aile-hukuku',
                'short_description' => 'Boşanma, velayet, nafaka ve mal rejimi uyuşmazlıklarında temsil.',
                'description' => '<p>Aile hukuku; evlilik birliğinin kurulması, yürütülmesi ve sona ermesi ile bundan doğan velayet, nafaka ve mal rejimi gibi konuları kapsar.</p>',
                'covers' => '<ul><li>Anlaşmalı ve çekişmeli boşanma davaları</li><li>Velayet ve kişisel ilişki tesisi</li><li>Nafaka talepleri</li><li>Mal rejiminin tasfiyesi</li><li>Aile içi şiddete karşı koruma tedbirleri</li></ul>',
                'scope' => '<p>Boşanma sürecinin her aşamasında danışmanlık, protokol hazırlığı ve dava takibi hizmeti sunulmaktadır.</p>',
                'process_steps' => [
                    ['title' => 'Ön Görüşme', 'description' => 'Ailevi durumun ve talebin kapsamı değerlendirilir.'],
                    ['title' => 'Strateji Belirleme', 'description' => 'Anlaşmalı veya çekişmeli sürecin koşulları netleştirilir.'],
                    ['title' => 'Dava Takibi', 'description' => 'Duruşmalar ve süreç boyunca temsil sağlanır.'],
                ],
                'why_important' => '<p>Aile hukuku uyuşmazlıkları duygusal yönü ağır basan süreçlerdir; hukuki destek, hem hakların korunmasını hem de sürecin sağlıklı yönetilmesini sağlar.</p>',
                'faqs' => [
                    ['question' => 'Anlaşmalı boşanma için hangi koşullar aranır?', 'answer' => 'Evliliğin en az bir yıl sürmüş olması ve tarafların boşanmanın mali sonuçları ile çocukların durumu hakkında anlaşmaya varmış olması gerekir.'],
                ],
            ],
            [
                'title' => 'Miras Hukuku',
                'slug' => 'miras-hukuku',
                'short_description' => 'Mirasın paylaşımı, tenkis, ortaklığın giderilmesi ve vasiyetname süreçleri.',
                'description' => '<p>Miras hukuku; mirasçılık sıfatının belirlenmesinden mirasın paylaşımına, tenkis davalarından vasiyetname düzenlemelerine kadar geniş bir alanı kapsar.</p>',
                'covers' => '<ul><li>Mirasçılık belgesi (veraset ilamı) süreçleri</li><li>Tenkis davaları</li><li>Muris muvazaası davaları</li><li>Mirasın paylaşımı ve ortaklığın giderilmesi</li><li>Vasiyetname düzenlenmesi</li></ul>',
                'scope' => '<p>Miras paylaşımı öncesi danışmanlık, mirasçılar arası uyuşmazlıklarda temsil ve ilgili davaların takibi hizmetleri sunulmaktadır.</p>',
                'process_steps' => [
                    ['title' => 'Mirasçılık Tespiti', 'description' => 'Mirasçılık sıfatı ve terekenin kapsamı belirlenir.'],
                    ['title' => 'Değerlendirme', 'description' => 'Paylaşım seçenekleri ve olası uyuşmazlıklar analiz edilir.'],
                    ['title' => 'Süreç Yönetimi', 'description' => 'Anlaşma veya dava yoluyla süreç sonuçlandırılır.'],
                ],
                'why_important' => '<p>Miras paylaşımı süreçlerinde zamanaşımı süreleri ve ispat yükü gibi hususlar, hakların korunması açısından dikkatli bir hukuki takip gerektirir.</p>',
                'faqs' => [
                    ['question' => 'Tenkis davası nedir?', 'answer' => 'Mirasbırakanın saklı paylı mirasçıların haklarını zedeleyen kazandırmalarının, saklı pay sınırına çekilmesini sağlayan davadır.'],
                ],
            ],
            [
                'title' => 'İcra ve İflas Hukuku',
                'slug' => 'icra-ve-iflas-hukuku',
                'short_description' => 'Alacak takibi, itiraz davaları, konkordato ve iflas süreçleri.',
                'description' => '<p>İcra ve iflas hukuku; alacakların cebri icra yoluyla tahsili ile borçlunun mali durumuna ilişkin konkordato ve iflas süreçlerini kapsar.</p>',
                'covers' => '<ul><li>İlamlı ve ilamsız icra takipleri</li><li>İtirazın iptali ve itirazın kaldırılması davaları</li><li>Menfi tespit ve istirdat davaları</li><li>Konkordato süreçleri</li><li>İflas davaları</li></ul>',
                'scope' => '<p>Alacağın takibinden itiraz süreçlerine, gerektiğinde konkordato ve iflas süreçlerinin yürütülmesine kadar kapsamlı destek sunulmaktadır.</p>',
                'process_steps' => [
                    ['title' => 'Alacak Değerlendirmesi', 'description' => 'Alacağın niteliği ve tahsil imkânları incelenir.'],
                    ['title' => 'Takip Süreci', 'description' => 'İcra takibi başlatılır ve süreç takip edilir.'],
                    ['title' => 'Uyuşmazlık Çözümü', 'description' => 'İtiraz hâlinde gerekli dava süreçleri yürütülür.'],
                ],
                'why_important' => '<p>İcra takibinde süre ve usul kurallarına tam uyum, alacağın zamanında ve etkin şekilde tahsil edilmesi açısından belirleyicidir.</p>',
                'faqs' => [],
            ],
            [
                'title' => 'Sözleşmeler Hukuku',
                'slug' => 'sozlesmeler-hukuku',
                'short_description' => 'Sözleşme hazırlanması, incelenmesi ve sözleşmeden doğan uyuşmazlıklar.',
                'description' => '<p>Sözleşmeler hukuku; tarafların hak ve yükümlülüklerini düzenleyen her türlü sözleşmenin hazırlanması, müzakeresi ve uygulanmasına ilişkin süreçleri kapsar.</p>',
                'covers' => '<ul><li>Sözleşme taslaklarının hazırlanması</li><li>Mevcut sözleşmelerin hukuki incelemesi</li><li>Sözleşme müzakerelerinde destek</li><li>Sözleşmeden doğan uyuşmazlıklarda temsil</li></ul>',
                'scope' => '<p>İş ilişkinizin niteliğine uygun sözleşme metinlerinin hazırlanması ve mevcut sözleşmelerin risk analizi bu kapsamda değerlendirilir.</p>',
                'process_steps' => [
                    ['title' => 'İhtiyaç Analizi', 'description' => 'Sözleşme konusu ilişkinin koşulları belirlenir.'],
                    ['title' => 'Taslak Hazırlığı', 'description' => 'Tarafların menfaatini gözeten sözleşme metni oluşturulur.'],
                    ['title' => 'Müzakere ve Sonuçlandırma', 'description' => 'Karşı tarafla müzakere süreci yürütülür.'],
                ],
                'why_important' => '<p>İyi hazırlanmış bir sözleşme, ileride doğabilecek pek çok uyuşmazlığı baştan önler ve tarafların haklarını güvence altına alır.</p>',
                'faqs' => [],
            ],
            [
                'title' => 'Tazminat Hukuku',
                'slug' => 'tazminat-hukuku',
                'short_description' => 'Maddi ve manevi tazminat talepleri, trafik kazaları ve destekten yoksun kalma.',
                'description' => '<p>Tazminat hukuku; haksız fiil veya sözleşmeye aykırılık nedeniyle uğranılan zararların giderilmesine yönelik talepleri kapsar.</p>',
                'covers' => '<ul><li>Trafik kazalarından doğan tazminat talepleri</li><li>Maddi ve manevi tazminat davaları</li><li>Destekten yoksun kalma tazminatı</li><li>İş kazası kaynaklı tazminat talepleri</li></ul>',
                'scope' => '<p>Zararın tespiti, sorumluluğun belirlenmesi ve tazminat talebinin dava veya sigorta süreci üzerinden takip edilmesi hizmet kapsamındadır.</p>',
                'process_steps' => [
                    ['title' => 'Olay ve Zarar Tespiti', 'description' => 'Zararın kapsamı ve delil durumu incelenir.'],
                    ['title' => 'Sorumluluk Analizi', 'description' => 'Kusur ve sorumluluk oranları değerlendirilir.'],
                    ['title' => 'Talep Süreci', 'description' => 'Sigorta başvurusu veya dava süreci yürütülür.'],
                ],
                'why_important' => '<p>Tazminat taleplerinde zarar ve kusurun doğru şekilde ortaya konulması, talep edilecek tutarın belirlenmesinde belirleyici rol oynar.</p>',
                'faqs' => [],
            ],
            [
                'title' => 'Hukuki Danışmanlık',
                'slug' => 'hukuki-danismanlik',
                'short_description' => 'Bireysel ve kurumsal müvekkillere süreklilik arz eden önleyici hukuk hizmetleri.',
                'description' => '<p>Hukuki danışmanlık; bireysel ve kurumsal müvekkillerin gündelik iş ve işlemlerinde ortaya çıkabilecek hukuki sorulara önleyici nitelikte destek sunar.</p>',
                'covers' => '<ul><li>Süreli hukuki danışmanlık anlaşmaları</li><li>Sözleşme ve belge incelemesi</li><li>Hukuki risk değerlendirmesi</li><li>Güncel mevzuat takibi</li></ul>',
                'scope' => '<p>Düzenli veya proje bazlı danışmanlık ile müvekkillerin hukuki süreçlerini önceden planlamasına destek olunur.</p>',
                'process_steps' => [
                    ['title' => 'İhtiyaç Belirleme', 'description' => 'Danışmanlığın kapsamı ve sıklığı belirlenir.'],
                    ['title' => 'Sürekli Destek', 'description' => 'Belirlenen kapsamda düzenli hukuki destek sağlanır.'],
                ],
                'why_important' => '<p>Önleyici hukuki danışmanlık, uyuşmazlık doğmadan önce riskleri tespit ederek olası hak kayıplarının önüne geçilmesini sağlar.</p>',
                'faqs' => [],
            ],
        ];
    }
}
