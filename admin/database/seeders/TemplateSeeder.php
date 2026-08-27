<?php

namespace Database\Seeders;

use App\Models\MailTemplate;
use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        MailTemplate::query()->updateOrCreate(['name' => 'Randevu Onayı'], [
            'subject' => 'Randevu Talebiniz Alındı — {{office_name}}',
            'body' => '<p>Sayın {{client_name}},</p><p>Randevu talebiniz tarafımıza ulaşmıştır. En kısa sürede sizinle iletişime geçilecektir.</p><p>Saygılarımızla,<br>{{lawyer_name}}</p>',
            'description' => 'Randevu talebi sonrası müvekkile gönderilen onay maili.',
        ]);

        MailTemplate::query()->updateOrCreate(['name' => 'Ödeme Hatırlatma'], [
            'subject' => 'Ödeme Hatırlatması — {{office_name}}',
            'body' => '<p>Sayın {{client_name}},</p><p>{{case_no}} numaralı dosyanıza ilişkin güncel bakiyeniz {{balance}} olarak görünmektedir. Bilgilerinize sunarız.</p><p>Saygılarımızla,<br>{{lawyer_name}}</p>',
            'description' => 'Bekleyen bakiyesi olan müvekkillere hatırlatma maili.',
        ]);

        SmsTemplate::query()->updateOrCreate(['name' => 'Randevu Hatırlatma'], [
            'body' => 'Sayın {{client_name}}, randevunuz yaklaşmaktadır. Bilgi için: {{office_name}}',
            'description' => 'Randevu öncesi kısa hatırlatma SMS\'i.',
        ]);

        SmsTemplate::query()->updateOrCreate(['name' => 'Ödeme Hatırlatma'], [
            'body' => 'Sayın {{client_name}}, {{balance}} tutarındaki güncel bakiyeniz hakkında bilgi almak için bizi arayabilirsiniz. {{office_name}}',
            'description' => 'Bekleyen ödeme için kısa hatırlatma SMS\'i.',
        ]);
    }
}
