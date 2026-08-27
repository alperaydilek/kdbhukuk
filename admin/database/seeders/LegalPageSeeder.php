<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            LegalPage::query()->updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function pages(): array
    {
        return [
            [
                'slug' => 'gizlilik-politikasi',
                'title' => 'Gizlilik Politikası',
                'content' => <<<'HTML'
                    <h2>1. Genel Bilgiler</h2>
                    <p>Bu Gizlilik Politikası; KDB Hukuk web sitesini ziyaret eden kullanıcıların kişisel verilerinin hangi kapsamda toplandığını, işlendiğini ve korunduğunu açıklamaktadır.</p>
                    <p>Web sitemizi kullanarak bu politika kapsamındaki uygulamaları kabul etmiş sayılırsınız. Kişisel verilerin işlenmesine ilişkin ayrıntılı bilgiye KVKK Aydınlatma Metni üzerinden ulaşabilirsiniz.</p>
                    <h2>2. Toplanan Veriler</h2>
                    <ul>
                        <li>İletişim formu aracılığıyla paylaştığınız ad soyad, telefon, e-posta ve mesaj içeriği</li>
                        <li>Çerezler aracılığıyla elde edilen kullanım ve tercih bilgileri</li>
                        <li>Tarayıcı türü, cihaz bilgisi ve benzeri teknik veriler</li>
                    </ul>
                    <h2>3. Verilerin Kullanım Amaçları</h2>
                    <ul>
                        <li>İletişim taleplerinin karşılanması ve size dönüş yapılması</li>
                        <li>Web sitesinin işleyişinin ve kullanıcı deneyiminin iyileştirilmesi</li>
                        <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                    </ul>
                    <h2>4. Veri Güvenliği</h2>
                    <p>Kişisel verilerinizin güvenliği için uygun teknik ve idari tedbirler alınmaktadır. Veriler, işlenme amacının gerektirdiği süre boyunca muhafaza edilir.</p>
                    <h2>5. Üçüncü Taraflarla Paylaşım</h2>
                    <p>Kişisel verileriniz; açık rızanız bulunmadıkça veya kanuni bir zorunluluk söz konusu olmadıkça üçüncü taraflarla paylaşılmaz.</p>
                    <h2>6. İletişim</h2>
                    <p>Bu politika hakkındaki soru ve talepleriniz için iletişim sayfası üzerinden bize ulaşabilirsiniz.</p>
                    HTML,
            ],
            [
                'slug' => 'cerez-politikasi',
                'title' => 'Çerez Politikası',
                'content' => <<<'HTML'
                    <h2>1. Çerez Nedir?</h2>
                    <p>Çerezler; bir web sitesini ziyaret ettiğinizde tarayıcınız aracılığıyla cihazınıza kaydedilen küçük metin dosyalarıdır.</p>
                    <h2>2. Kullanılan Çerez Türleri</h2>
                    <ul>
                        <li><strong>Zorunlu çerezler:</strong> Sitenin temel işlevlerinin çalışması için gereklidir.</li>
                        <li><strong>Analitik çerezler:</strong> Sitenin nasıl kullanıldığını anlamamıza yardımcı olur; yalnızca onay vermeniz hâlinde kullanılır.</li>
                    </ul>
                    <h2>3. Çerezlerin Kullanım Amaçları</h2>
                    <ul>
                        <li>Çerez tercihinizin hatırlanması</li>
                        <li>Site performansının ve kullanıcı deneyiminin iyileştirilmesi</li>
                    </ul>
                    <h2>4. Çerez Tercihlerinin Yönetimi</h2>
                    <p>Sitemizi ilk ziyaretinizde görüntülenen çerez bildirimi üzerinden tercihlerinizi belirleyebilir, tarayıcı ayarlarınızdan çerezleri dilediğiniz zaman silebilirsiniz.</p>
                    <h2>5. Politika Güncellemeleri</h2>
                    <p>Bu Çerez Politikası gerekli görüldüğünde güncellenebilir. Güncel sürüm her zaman bu sayfada yayımlanır.</p>
                    HTML,
            ],
            [
                'slug' => 'kvkk',
                'title' => 'KVKK Aydınlatma Metni',
                'content' => <<<'HTML'
                    <h2>1. Veri Sorumlusu</h2>
                    <p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca kişisel verileriniz; veri sorumlusu sıfatıyla KDB Hukuk — Av. Kaan Durali Bulut tarafından aşağıda açıklanan kapsamda işlenebilecektir.</p>
                    <h2>2. İşlenen Kişisel Veriler</h2>
                    <ul>
                        <li>Kimlik bilgileri (ad, soyad)</li>
                        <li>İletişim bilgileri (telefon, e-posta)</li>
                        <li>İletişim formu aracılığıyla iletilen mesaj içeriği</li>
                    </ul>
                    <h2>3. İşlenme Amaçları</h2>
                    <ul>
                        <li>İletişim taleplerinin alınması ve yanıtlanması</li>
                        <li>Hukuki danışmanlık ve avukatlık hizmetlerine ilişkin ön değerlendirme</li>
                        <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                    </ul>
                    <h2>4. İlgili Kişinin Hakları (KVKK m.11)</h2>
                    <p>Herkes, veri sorumlusuna başvurarak kişisel verilerinin işlenip işlenmediğini öğrenme, düzeltilmesini veya silinmesini isteme dahil KVKK'nın 11. maddesinde sayılan haklara sahiptir.</p>
                    <h2>5. Başvuru Yöntemi</h2>
                    <p>Taleplerinizi iletişim sayfamızda yer alan kanallar üzerinden veya Kişisel Verileri Koruma Kurulu'nun belirlediği diğer yöntemlerle iletebilirsiniz.</p>
                    HTML,
            ],
        ];
    }
}
